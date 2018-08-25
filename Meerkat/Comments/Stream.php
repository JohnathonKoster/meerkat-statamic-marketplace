<?php

namespace Statamic\Addons\Meerkat\Comments;

use Countable;
use Statamic\API\Arr;
use Statamic\API\Data;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\YAML;
use Illuminate\Support\Str;
use Statamic\Extend\Extensible;
use Illuminate\Support\Collection;
use Statamic\Addons\Meerkat\MeerkatAPI;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Addons\Meerkat\Forms\Submission;
use Statamic\Addons\Meerkat\DesignerMode\Factory;
use Statamic\Addons\Meerkat\Paths\PathHelperTrait;

class Stream implements Countable
{
    use Extensible, PathHelperTrait;


    /**
     * Represents the path to the comment.
     *
     * @var string
     */
    const INTERNAL_PATH = 'internal_path';

    /**
     * Represents the internal response status.
     *
     * @var string
     */
    const INTERNAL_RESPONSE = 'internal_response';

    /**
     * Represents the internal path to the response's parent path.
     *
     * @var string
     */
    const INTERNAL_RESPONSE_PATH = 'internal_response_path';

    /**
     * Represents the internal response's parent comment ID.
     *
     * @var string
     */
    const INTERNAL_RESPONSE_ID = 'internal_response_id';

    /**
     * Represents the internal response's parent comment instance.
     *
     * @var string
     */
    const INTERNAL_RESPONSE_CONTEXT = 'internal_response_context';

    /**
     * Represents whether or not the Comment has replies.
     *
     * @var string
     */
    const INTERNAL_RESPONSE_HAS_REPLIES = 'internal_response_has_replies';

    /**
     * The current context identifier.
     *
     * @var null|string
     */
    protected $context = '';

    /**
     * The FileAccessor instance.
     *
     * @var \Statamic\Filesystem\FileAccessor
     */
    protected $disk;

    /**
     * The context object cache.
     *
     * @var Collection
     */
    protected $contextCache;

    /**
     * The CommentCollection instance.
     *
     * @var CommentCollection|null
     */
    protected $comments = null;

    /**
     * Indicates if soft deleted comments should be retrieved.
     *
     * @var bool
     */
    protected $withTrashed = false;

    /**
     * The conversation participant cache.
     *
     * @var array
     */
    protected $conversationParticipantCache = [];

    public function __construct($context = null)
    {
        $this->addon_name = 'Meerkat';
        $this->disk = File::disk('content');
        $this->contextCache = collect();

        if ($context !== null) {
            $this->context = $context;
        }
    }

    /**
     * Gets the comment count for all comment streams.
     *
     * @return int
     */
    public function count()
    {
        return $this->getCount(true);
    }

    /**
     * Gets the comment count for the current Stream.
     *
     * @param  bool $forAll If true, returns the count for all streams. Otherwise just this stream.
     * @return int
     */
    public function getCount($forAll = false)
    {
        if ($this->comments == null) {
            $this->getComments();
        }

        if (!$forAll) {
            return $this->comments->count();
        }

        return count($this->disk->filesystem()->allFiles('/comments/'));
    }

    /**
     * Gets the number of published comments.
     *
     * @return int
     */
    public function getPublishedCount()
    {
        return $this->getComments()->filter(function (Comment $comment) {
            return $comment->published();
        })->count();
    }

    /**
     * Gets the number of unpublished comments.
     *
     * @return int
     */
    public function getUnpublishedCount()
    {
        return $this->getComments()->filter(function (Comment $comment) {
            return (!$comment->published());
        })->count();
    }

    /**
     * Gets the numiber of comments marked as spam.
     *
     * @return int
     */
    public function getSpamCount()
    {
        return $this->getComments()->filter(function (Comment $comment) {
            return $comment->isSpam();
        })->count();
    }

    /**
     * Sets the context for the Stream.
     *
     * @param $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    private function getStreamParticipants($stream, &$comments)
    {
        if (!array_key_exists($stream, $this->conversationParticipantCache)) {
            $participants = [];

            foreach ($comments as $comment) {
                if ($comment->getStreamName() == $stream) {
                    $participant = $this->getParticipantData($comment);
                    $participants[$participant['key']] = $participant['data'];
                }
            }

            $this->conversationParticipantCache[$stream] = $participants;
        }

        return $this->conversationParticipantCache[$stream];
    }

    /**
     * Attaches a comment to the current Stream.
     *
     * @param Submission $submission
     */
    public function attachComment(Submission $submission)
    {
        $filename = '/comments/' . $this->context . '/' . $submission->id() . '/comment.md';

        if (mb_strlen(trim($this->context)) == 0 || $this->context == null) {
            return;
        }

        $data = $submission->data();

        if ($content = array_get($data, 'content')) {
            unset($data['content']);
        }

        $data['id'] = $submission->id();
        $data['published'] = $this->getConfigBool('auto_publish', false);

        $data['user_ip'] = request()->getClientIp();
        $data['user_agent'] = request()->header('User-Agent');
        $data['referrer'] = request()->server('HTTP_REFERER');
        $data['page_url'] = $this->getContext($this->context)->absoluteUrl();

        if (auth()->user() !== null && auth()->user()->get('email') == $data['email']) {
            $data['authenticated_user'] = auth()->user()->get('id');

            if ($this->getConfig('auto_publish_authenticated_users', true)) {
                $data['published'] = true;
            }

        }

        $eventData = $this->emitEvent('comment.creating', [$data]);

        foreach ($eventData as $modifiedData) {
            if (is_array($modifiedData)) {
                $data = array_merge($data, $modifiedData);
            }
        }

        if (!isset($data['spam'])) {
            $data['spam'] = false;
        }

        if ($data['spam'] === true && $data['spam_auto_delete'] === true) {
            return;
        }

        // Remove the spam_auto_delete key.
        unset($data['spam_auto_delete']);

        $this->disk->put($filename, YAML::dump($data, $content));
    }

    /**
     * Attaches a reply to a given comment by ID.
     *
     * @param $replyTo
     * @param Submission $submission
     */
    public function attachReply($replyTo, Submission $submission)
    {
        /** @var CommentManager $commentManager */
        $commentManager = app(CommentManager::class);
        $comment = $commentManager->findOrFail($replyTo);

        $filename = $comment->getRepliesPath() . $submission->id() . '/comment.md';

        $data = $submission->data();

        if ($content = array_get($data, 'content')) {
            unset($data['content']);
        }

        $data['id'] = $submission->id();
        $data['published'] = $this->getConfigBool('auto_publish', false);

        $data['user_ip'] = request()->getClientIp();
        $data['user_agent'] = request()->header('User-Agent');
        $data['referrer'] = request()->server('HTTP_REFERER');

        if (auth()->user() !== null && auth()->user()->get('email') == $data['email']) {
            $data['authenticated_user'] = auth()->user()->get('id');

            if ($this->getConfig('auto_publish_authenticated_users', true)) {
                $data['published'] = true;
            }
        }

        $this->disk->put($filename, YAML::dump($data, $content));
    }

    /**
     * Gets the context object for the given context.
     *
     * @param $context
     * @return Entry
     */
    private function getContext($context)
    {
        if (!$this->contextCache->has($context)) {
            $this->contextCache[$context] = Data::find($context);
        }

        return $this->contextCache[$context];
    }

    /**
     * Gets the context object for the current Stream context.
     *
     * @return mixed|object
     */
    public function context()
    {
        return $this->getContext($this->context);
    }

    /**
     * Indicate that the comment stream should include deleted comments.
     */
    public function withTrashed()
    {
        $this->withTrashed = true;
    }

    /**
     * Indicates that the comment stream should not include deleted comments.
     */
    public function withoutTrashed()
    {
        $this->withTrashed = false;
    }

    private function getParticipantData(Comment $comment)
    {
        $name  = array_get($comment, 'name', '');
        $email = array_get($comment, 'email', '');
        $url   = array_get($comment, 'url', '');

        $participantKey = $name.$email.$url;
        
        return [
            'key'  => $participantKey,
            'data' => [
                'name'  => $name,
                'email' => $email,
                'url'   => $url
            ]
        ];
    }

    public function getDesignerModeComments()
    {
        // Get a reference to the current disk.
        $currentDisk = $this->disk;
        $this->disk = File::disk('local');

        $directories = $this->disk->filesystem()->allDirectories('site/addons/Meerkat/DesignerMode/designer_mode');
        
        $comments = $this->getCommentsFromDirectories($directories, false, true);

        // Swap the disks back.
        $this->disk = $currentDisk;

        return with(new Factory)->processCollection($comments);
    }

    private function getCommentsFromDirectories($directories, $flatList = false)
    {
        if (count($directories) == 0) {
            $this->comments = collect_comments();
            return $this->comments;
        }

        $form = MeerkatAPI::getForm();

        $comments = collect_comments($directories)->map(function ($directory) {
            $commentPath = $directory . '/comment.md';

            $data = [];

            if ($this->disk->exists($commentPath)) {
                $data = YAML::parse($this->disk->get($commentPath));
                $data[self::INTERNAL_PATH] = $commentPath;

                // This is where we determine if the current comment is actually a reply.
                if (Str::contains($commentPath, 'replies')) {
                    $pathParts = array_slice(explode('/', $commentPath), 0, -3);
                    $inReplyTo = array_pop($pathParts);
                    $data[self::INTERNAL_RESPONSE] = true;
                    $data[self::INTERNAL_RESPONSE_PATH] = implode('/', array_merge($pathParts, [$inReplyTo, 'comment.md']));
                    $data[self::INTERNAL_RESPONSE_ID] = $inReplyTo;
                } else {
                    $data[self::INTERNAL_RESPONSE] = false;
                    $data[self::INTERNAL_RESPONSE_ID] = $data[self::INTERNAL_RESPONSE_PATH] = null;
                }

                // Now, we will do the opposite and determine if the comment has any replies.
                if ($this->disk->exists(mb_substr($commentPath, 0, -10) . 'replies')) {
                    // Tentatively indicate whether or not this comment has replies.
                    $data[self::INTERNAL_RESPONSE_HAS_REPLIES] = true;
                } else {
                    $data[self::INTERNAL_RESPONSE_HAS_REPLIES] = false;
                }

            }

            if (Arr::has($data, 'comment')) {
                $data['comment_markdown'] = $data['comment'];
                $data['comment'] = markdown($data['comment']);
            }

            if (Arr::has($data, 'content')) {
                $data['content_markdown'] = $data['content'];
                $data['content'] = markdown($data['content']);
            }

            $data['context'] = $this->getContext(explode('/', $commentPath)[1]);

            return $data;
        })->filter(function ($data) {
            return count($data) > 1;
        })->filter(function ($data) {
            $softDeleted = array_get($data, 'is_deleted', false);
            
            if ($this->withTrashed) {
                return true;
            }

            if ($softDeleted) {
                return false;
            }

            return true;
        })->map(function ($data) use (&$form) {
            $comment = new Comment;
            $comment->form($form);
            $comment->data($data);

            return $comment;
        })->keyBy('id');

        // If the comment is a reply, we will grab the parent comment
        // instance from the collection. This way we always have a
        // handy reference to the parent comment at all times.
        $comments->map(function (Comment $comment) use (&$comments) {
            if ($comment->isReply()) {
                $root = $comments->whereLoose('id', $comment->getParentID())->first();
                $comment->setParentComment($root);
            }

            return $comment;
        });

        // If the comment appears to have replies, we will search
        // through all of the comments and determine if it is a
        // reply to the current Comment root instance. This
        // process will also set the thread participants.
        $comments->each(function (Comment $comment) use (&$comments, $flatList) {

            $commentParticipants = [];

            // Add the comment author as a participant.
            $participant = $this->getParticipantData($comment);
            $commentParticipants[$participant['key']] = $participant['data'];
            
            if ($comment[self::INTERNAL_RESPONSE_HAS_REPLIES]) {

                $rootPath = mb_substr($comment[self::INTERNAL_PATH], 0, -10);
                
                $replies = $comments->filter(function (Comment $reply) use (&$rootPath, &$comment, &$test, $flatList, &$commentParticipants) {

                    $replyPath   = $reply[self::INTERNAL_PATH];
                    $commentPath = $comment[self::INTERNAL_PATH];
                    $commentLevel = substr_count($commentPath, 'replies');
                    $replyLevel = substr_count($replyPath, 'replies');

                    if (($replyPath !== $commentPath)
                        && (strlen($replyPath) > strlen($commentPath))
                        && ($commentLevel + 1 == $replyLevel)) {

                        $checkValue = Str::startsWith($replyPath, $rootPath);

                        if ($checkValue) {
                            $comment->alwaysReportCommentHasReplies();

                            $participant = $this->getParticipantData($reply);
                            $commentParticipants[$participant['key']] = $participant['data'];

                            if (!$flatList) {
                                return $checkValue;
                            }
                        }
                    }

                    return false;
                });

                $comment->setReplies($replies);                
            }

            $comment->setParticipants($commentParticipants);            
        });

        $sortedComments = $comments->sortBy('id');

        $sortedComments->each(function (Comment $comment) {
            if (! $comment->isRoot()) {
                $parent = $comment->getParent();
                $comment->setParticipants($parent->getParticipants());
            }
        });

        $comments->each(function (Comment $comment) use (&$comments) {
            $participants = $this->getStreamParticipants($comment->getStreamName(), $comments);
            $comment->setConversationParticipants($participants);
        });

        if (!$flatList) {
            $comments = $comments->filter(function (Comment $comment) {
                return !$comment->isReply();
            });
        }

        $this->comments = $comments;

        return $comments;
    }

    /**
     * Gets the comments for the given Stream context.
     *
     * @return CommentCollection
     */
    public function getComments($flatList = false)
    {
        $directories = $this->disk->filesystem()->allDirectories(Path::makeRelative('comments/' . $this->context));

        return $this->getCommentsFromDirectories($directories, $flatList);
    }

    /**
     * Gets the comment stream participants.
     *
     * @param  array $usingFields
     * @return Collection
     */
    public function getParticipants(array $usingFields = ['email', 'name'])
    {
        if (count($usingFields) == 0) {
            return collect();
        }

        return $this->getFields($usingFields)->unique($usingFields[0]);
    }

    /**
     * Gets only the fields from the comment stream data.
     *
     * @param  array $fields
     * @return Collection
     */
    public function getFields(array $fields)
    {
        if ($this->comments == null) {
            $this->getComments();
        }

        return $this->comments->map(function ($value) {
            return $value->toArray();
        })->map(function ($value) use ($fields) {
            return Arr::only($value, $fields);
        });
    }

    /**
     * Converts the Stream into an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data['context_id'] = $this->context;
        $data['context'] = $this->context()->toArray()['title'];
        $data['comments'] = $this->getCount();
        $data['published_count'] = $this->getPublishedCount();
        $data['pending_count'] = $this->getUnpublishedCount();
        $data['spam_count'] = $this->getSpamCount();

        $data['participants'] = count($this->getParticipants());

        return $data;
    }

}