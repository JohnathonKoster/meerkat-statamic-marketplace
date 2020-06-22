<?php

namespace Statamic\Addons\Meerkat\Comments;

use Statamic\API\Helper;
use Statamic\Extend\Extensible;
use Statamic\Addons\Meerkat\Comments\Spam\Guard;

class CommentManager
{
    use Extensible;

    /**
     * The Manager instance.
     *
     * @var Manager
     */
    protected $streamManager;

    /**
     * The spam Guard instance.
     *
     * @var Guard
     */
    protected $guard;

    public function __construct(Manager $streamManager, Guard $guard)
    {
        $this->addon_name = 'Meerkat';
        $this->streamManager = $streamManager;
        $this->guard = $guard;
    }

    private function findComments(array $commentIDs, $commentsToSearch, &$comments)
    {
        $commentsToSearch->each(function (Comment $comment) use (&$comments, $commentIDs) {
            if (in_array((string)$comment, $commentIDs)) {
                $comments[(string)$comment] = $comment;                
            }

            if ($comment->hasReplies()) {
                $this->findComments($commentIDs, $comment->getReplies(), $comments);
            }
        });
    }

    /**
     * Gets the comment (or comments) from the file store.
     *
     * If you supply an array for $commentID, multiple comments
     * will be returned from this method. This method always
     * return an iterable collection, regardless of number.
     *
     * @param  string|array $commentID
     * @return CommentCollection
     */
    public function getComments($commentID)
    {
        $commentIDs = Helper::ensureArray($commentID);

        $discoveredComments = [];

        $this->findComments($commentIDs, $this->streamManager->allComments(), $discoveredComments);

        return collect_comments($discoveredComments);
    }

    /**
     * Get the Comment for the given ID or throw an exception.
     *
     * @param $commentID
     * @return Comment
     *
     * @throws CommentNotFoundException
     */
    public function findOrFail($commentID)
    {
        if (!is_null($comment = $this->getComments($commentID)->first())) {
            return $comment;
        }

        throw new CommentNotFoundException("Comment '{$commentID}' was not found.");
    }

    /**
     * Removes a list of comments.
     *
     * This method will return a list of all the comments and replies that were removed.
     *
     * @param  array $commentIDs
     * @return array
     */
    public function removeComments(array $commentIDs)
    {
        $comments = $this->getComments($commentIDs);

        $removedComments = [];

        /** @var Comment $comment */
        foreach ($comments as $comment) {
            $removedComments = array_merge($comment->delete(), $removedComments);
        }

        foreach ($removedComments as $removedComment) {
            $this->emitEvent('comment.removed', $removedComment);
        }

        return $removedComments;
    }

    /**
     * Approves the specified comments.
     *
     * @param  array $commentIDs
     * @return CommentCollection
     */
    public function approveComments(array $commentIDs)
    {
        return $this->getComments($commentIDs)->each(function (Comment $comment) {
            $comment->published = true;
            // If we are explicitly approving a comment, let's also set the spam
            // flag to false to prevent it from appearing in the spam bucket.
            $comment->spam = false;
            $comment->save();
            $this->emitEvent('comment.approved', $comment);
        });
    }

    /**
     * Un-approves the specified comments.
     *
     * @param  array $commentIDs
     * @return CommentCollection
     */
    public function unApproveComments(array $commentIDs)
    {
        return $this->getComments($commentIDs)->each(function (Comment $comment) {
            $comment->published = false;
            $comment->save();
            $this->emitEvent('comment.unapproved', $comment);
        });
    }

    /**
     * Marks the specified comments as spam.
     *
     * @param  array $commentIDs
     * @return array
     */
    public function markCommentsAsSpam(array $commentIDs)
    {
        $wasSuccess = true;
        $wasSaved = false;
        $specimenSent = false;
        $errors = [];

        $this->getComments($commentIDs)->each(function (Comment $comment) use (&$wasSaved, &$wasSuccess, &$errors, &$specimenSent) {
            $comment->spam = true;
            $comment->save();

            $wasSaved = true;

            if ($this->getConfigBool('guard_submit_results')) {
                $specimenSent = true;

                $result = $this->guard->submitSpam($comment->getStoredData());

                if ($result['wasSuccess'] == false) {
                    $wasSuccess = false;
                    $errors = array_merge($errors, $result['errors']);
                }
            }

            $this->emitEvent('comment.markedAsSpam', $comment);
        });

        return [
            'wasSuccess' => $wasSuccess,
            'wasSaved' => $wasSaved,
            'didSend' => $specimenSent,
            'errors' => $errors
        ];
    }

    /**
     * Marks the specified comments as not spam.
     *
     * @param  array $commentIDs
     * @return CommentCollection
     */
    public function markCommentsAsNotSpam(array $commentIDs)
    {
        $wasSuccess = true;
        $wasSaved = false;
        $specimenSent = false;
        $errors = [];

        $this->getComments($commentIDs)->each(function (Comment $comment) use (&$wasSaved, &$wasSuccess, &$errors, &$specimenSent) {
            $comment->spam = false;
            $comment->save();

            $wasSaved = true;

            if ($this->getConfigBool('guard_submit_results')) {
                $specimenSent = true;

                $result = $this->guard->submitHam($comment->getStoredData());

                if ($result['wasSuccess'] == false) {
                    $wasSuccess = false;
                    $errors = array_merge($errors, $result['errors']);
                }
            }

            $this->emitEvent('comment.markedAsNotSpam', $comment);
        });

        return [
            'wasSuccess' => $wasSuccess,
            'wasSaved' => $wasSaved,
            'didSend' => $specimenSent,
            'errors' => $errors
        ];
    }

    /**
     * Sets the content for the comment.
     *
     * @param int|string $commentID
     * @param string     $newContent
     *
     * @throws CommentNotFoundException
     */
    public function setCommentContent($commentID, $newContent)
    {
        $comment = $this->findOrFail($commentID);
        $comment->comment = $newContent;
        $comment->save();
    }

}