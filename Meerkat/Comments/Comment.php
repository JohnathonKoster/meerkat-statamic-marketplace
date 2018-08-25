<?php

namespace Statamic\Addons\Meerkat\Comments;

use Statamic\API\File;
use Statamic\API\User;
use Statamic\API\YAML;
use Illuminate\Support\Str;
use Statamic\Extend\Extensible;
use Statamic\Addons\Meerkat\Forms\Submission;
use Statamic\Addons\Meerkat\Comments\CommentManager;
use Statamic\Addons\Meerkat\Comments\CommentRemovalEventArgs;
use Statamic\Addons\Meerkat\Comments\Traits\HasRecursiveCommentKey;

class Comment extends Submission
{
    use HasRecursiveCommentKey, Extensible;

    /**
     * The parent Comment instance, if any.
     *
     * @var Comment|null
     */
    protected $parentComment = null;

    /**
     * The replies.
     *
     * @var CommentCollection|null
     */
    protected $replies = null;

    /**
     * The participants.
     *
     * @var array|null
     */
    protected $participants = null;

    protected $reportCommentHasRepliesOverride = false;

    protected $conversationParticipants = null;

    /**
     * List any attributes that you want to track the dirty state of here.
     *
     * @var array
     */
    protected $markAsDirty = [
        'comment',
        'content'
    ];

    /**
     * The FileAccessor instance.
     *
     * @var \Statamic\Filesystem\FileAccessor
     */
    protected $disk;

    /**
     * Indicates if the comment was soft deleted.
     *
     * @var bool
     */
    protected $wasSoftDeleted = false;

    public function __construct()
    {
        $this->addon_name = 'Meerkat';

        $this->disk = File::disk('content');
    }

    /**
     * Returns the context's stream name.
     *
     * @return string
     */
    public function getStreamName()
    {
        $path = array_get($this->originalData, 'internal_path', '');

        if (mb_strlen($path) == 0) {
            return '';
        }

        $parts = explode('/', $path);

        if (count($parts) > 1) {
            $path = $parts[1];
        } else {
            return '';
        }

        return $path;
    }

    public function alwaysReportCommentHasReplies()
    {
        $this->reportCommentHasRepliesOverride = true;
    }

    /**
     * Determines if a comment has been approved.
     *
     * @return bool
     */
    public function published()
    {
        $published = array_get($this->originalData, 'published', true);

        if (is_string($published) && trim(mb_strtolower($published)) == 'true') {
            return true;
        } elseif (is_string($published) && trim(mb_strtolower($published)) == 'false') {
            return false;
        }

        return $published;
    }

    /**
     * Determines if a comment has been approved.
     *
     * Alias of `published()`
     *
     * @return bool
     */
    public function approved()
    {
        return $this->published();
    }

    /**
     * Gets the Carbon date instance.
     *
     * @return \Carbon\Carbon
     */
    public function getDate()
    {
        return $this->date();
    }

    /**
     * Indicates if the Comment is spam.
     *
     * @return bool
     */
    public function isSpam()
    {
        $spam = array_get($this->originalData, 'spam', false);

        if (is_string($spam) && trim(mb_strtolower($spam)) == 'true') {
            return true;
        } elseif (is_string($spam) && trim(mb_strtolower($spam)) == 'false') {
            return false;
        }

        return $spam;
    }

    /**
     * Indicates if the comment is a reply.
     *
     * @return bool
     */
    public function isReply()
    {
        return $this->original(Stream::INTERNAL_RESPONSE, false);
    }

    /**
     * Gets the ID of the parent comment.
     *
     * This method returns null when there is no parent comment.
     *
     * @return int|string|null
     */
    public function getParentID()
    {
        return $this->original(Stream::INTERNAL_RESPONSE_ID, null);
    }

    /**
     * Returns the parent Comment instance.
     *
     * @return Comment|null
     */
    public function getParent()
    {
        if ($this->isReply()) {
            return $this->parentComment;
        }

        return null;
    }

    /**
     * Indicates if the Comment is the root comment.
     *
     * This method is the logical opposite of the isReply() method.
     *
     * @return bool
     */
    public function isRoot()
    {
        return !$this->isReply();
    }

    /**
     * Sets the parent comment.
     *
     * You should not call this method from any third-party
     * addons or extensions. It is used internally only.
     *
     * @param Comment $comment
     */
    public function setParentComment(Comment &$comment)
    {
        $this->parentComment = $comment;
    }

    /**
     * Set the replies.
     *
     * You should not call this method from any third-party
     * addons or extensions. It is used internally only.
     *
     * @param $replies
     */
    public function setReplies(&$replies)
    {
        $this->replies = $replies;
    }

    /**
     * Set the participants.
     *
     * You should not call this method from any third-party
     * addons or extensions. It is used internally only.
     *
     * @param $participants
     */
    public function setParticipants($participants)
    {
        $this->participants = $participants;
    }

    /**
     * Sets the conversation participants.
     *
     * You should not call this method from any third-party
     * addons or extensions. It is used internally only.
     *
     * @param $participants
     */
    public function setConversationParticipants($participants)
    {
        $this->conversationParticipants = $participants;
    }

    /**
     * Gets the conversation participants.
     *
     * @return null|array
     */
    public function getConversationParticipants()
    {
        return $this->conversationParticipants;
    }

    /**
     * Gets the Comment instance's replies.
     *
     * @return null|CommentCollection
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Gets the Comment participants.
     *
     * @return null|array
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Indicates if the Comment instance has replies.
     *
     * @return bool
     */
    public function hasReplies()
    {
        if ($this->reportCommentHasRepliesOverride == true) {
            return true;
        }
        
        if ($this->replies === null || count($this->replies) == 0) {
            return false;
        }

        return true;
    }

    /**
     * Indicates if the Comment was soft deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->original('is_deleted', false);
    }

    /**
     * Deletes the Comment and any replies from disk.
     *
     * This method returns an array of all the comments that were removed.
     *
     * @return array
     */
    public function delete()
    {
        $commentsToDelete = collect(meerkat_get_comments_and_replies($this))->sortByDesc(function ($comment) {
            return strlen($comment->getPath());  
        });

        $comments = [];
        $commentCount = $commentsToDelete->count();

        for ($i = 0; $i < $commentCount; $i++) {
            $comment = $commentsToDelete[$i];

            $isSoftDelete = false;

            $eventArgs = new CommentRemovalEventArgs(($i == ($commentCount - 1)), $comment->toArray());
            
            foreach ($this->emitEvent('comment.beforeDelete', [&$eventArgs]) as $removedData) {                
                $comment->data($eventArgs->toArray());

                if ($isSoftDelete == false && $eventArgs->shouldKeep()) {
                    $isSoftDelete = true;
                }
            }

            $comments[] = [
                $isSoftDelete,
                $comment
            ];
        }

        $comments = array_reverse($comments);

        if (count($comments) == 0) {
            return;
        }

        $lastWasSoftDelete = $comments[0][0];

        foreach ($comments as &$comment) {

            if (!$lastWasSoftDelete) {
                $comment[0] = false;
                continue;
            }

            if ($lastWasSoftDelete && $comment[0] == false) {
                $lastWasSoftDelete = false;
            }
        }
        
        $softDeleteComments = [];
        $actualDeleteComments = [];

        $manager = app(CommentManager::class);

        foreach ($comments as $comment) {
            if ($comment[0]) {
                $softDeleteComments[] = $comment[1]->original('id');
            } else {
                $actualDeleteComments[] = $comment[1]->original('id');
            }
        }

        // Take care of actual deletes.
        if (count($actualDeleteComments) > 0) {
            $actualDeletes = $manager->getComments($actualDeleteComments);

            foreach ($actualDeletes as $ctd) {
                $commentDirectory = mb_substr($ctd->getPath(), 0, -11);

                $this->disk->filesystem()->deleteDirectory($commentDirectory);
            }
        }
        
        // Take care of soft deletes.
        if (count($softDeleteComments) > 0) {
            $softDeletes = $manager->getComments($softDeleteComments);

            foreach ($softDeletes as $csd) {
                $csd->softDelete();
            }
        }

        return array_merge($softDeleteComments, $actualDeleteComments);
    }

    public function softDelete()
    {
        $this->wasSoftDeleted = true;
        $this->save();
    }

    /**
     * Gets the computed data, particularly useful for JSON APIs.
     *
     * @return array
     */
    protected function getComputedData()
    {
        return [
            'is_spam' => $this->isSpam() ? "1" : "0",
            'is_published' => $this->approved() ? "1" : "0",
            'is_pending' => $this->approved() ? "0" : "1"
        ];
    }

    /**
     * Gets an attribute from the Comment submission.
     *
     * @param $field
     * @return mixed
     */
    public function get($field)
    {
        return array_get(array_merge($this->data(), $this->originalData, $this->getComputedData()), $field);
    }

    /**
     * Get the path to the file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->original(Stream::INTERNAL_PATH);
    }

    /**
     * Get the path that replies should be stored in.
     *
     * @return string
     */
    public function getRepliesPath()
    {
        return mb_substr($this->getPath(), 0, -10).'replies/';
    }

    /**
     * Gets the original markdown representation of comment.
     *
     * @return string
     */
    public function getOriginalMarkdown()
    {
        if ($this->original('content') !== null) {
            return $this->original('content_markdown');
        }

        return $this->original('comment_markdown');
    }

    /**
     * Determines if the comment was posted by an authenticated user.
     *
     * @return bool
     */
    public function postedByAuthenticatedUser()
    {
        $user = $this->original('authenticated_user', null);

        return !is_null($user);
    }

    /**
     * Gets the authenticated user that posted the comment, if any.
     * 
     * @return null|\Statamic\Contracts\Data\Users\User
     */
    public function user()
    {
        if ($this->postedByAuthenticatedUser()) {
            return User::find($this->original('authenticated_user'));
        }

        return null;
    }

    /**
     * Save the comment submission.
     */
    public function save()
    {
        $filename = $this->getPath();

        $data = $this->originalData;

        $contentValue = 'comment';
        $originalMarkdown = '';
        $originalValue = '';
        $updatingContentField = false;

        if (isset($data['content'], $data['comment']) && $this->hasDirtyAttribute('comment')) {
            $updatingContentField = true;
        }

        if ($content = array_get($data, 'content')) {
            // Store the content value before we unset it.
            $originalValue = $data['content'];
            unset($data['content']);
            $originalMarkdown = $data['content_markdown'];

            // Remove the values that are just used internally.
            unset($data['content_markdown']);
            $contentValue = 'content';
        }

        if (isset($data['comment'])) {
            $originalMarkdown = $data['comment_markdown'];
            $originalValue = $data['comment'];
            unset($data['comment']);
            // Remove the values that are just used internally.
            unset($data['comment_markdown']);
            $contentValue = 'comment';
        }

        // Set the markdown value based on what key we are using
        // to store the comment's value. Developer's really
        // should use the `comment` form value, but hey,
        // we might as well be nice and be flexible.
        if ($contentValue == 'content' || $updatingContentField) {
            if ($this->hasDirtyAttribute('comment')) {
                // Updating the comment's content.
                $content = $originalValue;
            } else {
                $content = $originalMarkdown;
            }
        } else {
            if ($this->hasDirtyAttribute('comment')) {
                // Updating the comment's content.
                $data[$contentValue] = $originalValue;
            } else {
                $data[$contentValue] = $originalMarkdown;
            }
        }

        // Unset the context, as it is not stored on the comment directly.
        if (isset($data['context'])) {
            unset($data['context']);
        }

        // Remove all of the internal_ keys.
        foreach ($data as $key => $value) {
            if (Str::startsWith($key, 'internal_')) {
                unset($data[$key]);
            }
        }

        if ($this->wasSoftDeleted) {
            $data['is_deleted'] = true;
        }

        $this->disk->put($filename, YAML::dump($data, $content));
    }

    /**
     * Convert to an array
     *
     * @param  bool  $raiseEvent
     *
     * @return array
     */
    public function toArray($raiseEvent = false)
    {
        $data = $this->data();
        $data['id'] = $this->id();
        $data['date'] = $this->date();
        $fields = $this->formset()->fields();
        $field_names = array_keys($fields);

        // Populate the missing fields with empty values.
        foreach ($field_names as $field) {
            $data[$field] = array_get($data, $field);
        }

        // Ensure the array is ordered the same way the fields are.
        $data = array_merge(array_flip($field_names), $data);
        
        $data['has_replies'] = $this->hasReplies();
        $data['is_reply'] = $this->isReply();

        if ($this->hasReplies()) {
            $data[$this->nestedCommentKey] = $this->getReplies()->setRecursiveCommentKey($this->getRecursiveCommentKey())->toArray($raiseEvent);
        } else {
            $data[$this->nestedCommentKey] = [];
        }

        $threadParticipants = $this->getParticipants();
        $conversationParticipants = $this->getConversationParticipants();

        if (!is_null($threadParticipants)) {
            $data['participants'] = array_values($threadParticipants);
        } else {
            $data['participants'] = [];
        }

        if (!is_null($conversationParticipants)) {
            $data['conversation_participants'] = array_values($conversationParticipants);
        } else {
            $data['conversation_participants'] = [];
        }

        // An event can be raised that will allow developers to modify the content of
        // the comment before it is returned to the various different Meerkat API
        // components. For example, a developer can generate the Gravatar URL
        // for any particular comment author by modifying the event data.
        if ($raiseEvent) {

            foreach ($this->emitEvent('comments.collecting', [$data]) as $collectedData) {
                if (is_array($collectedData)) {
                    $data = array_merge($data, $collectedData);
                }
            }

        }
        
        $data['is_deleted'] = $this->original('is_deleted', false);

        return $data;
    }

    public function __toString()
    {
        return (string) $this->get('id');
    }

}