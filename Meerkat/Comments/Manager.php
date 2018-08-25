<?php

namespace Statamic\Addons\Meerkat\Comments;

use Carbon\Carbon;
use Statamic\API\File;
use Statamic\Extend\Extensible;

class Manager
{
    use Extensible;

    public function __construct()
    {
        $this->addon_name = 'Meerkat';
    }

    /**
     * Gets a Stream instance for a given context.
     *
     * @param string $context
     * @param bool $autoCreate
     * @return Stream
     */
    public function getStream($context, $autoCreate = false)
    {
        if ($autoCreate && !$this->streamExists($context)) {
            $this->createStream($context);
        }

        return new Stream($context);
    }

    public function getEmptyStream()
    {
        return new Stream;
    }

    public function streamExists($context)
    {
        return $this->getDisk()->exists('comments/' . $context);
    }

    /**
     * Gets the Content disk.
     *
     * @return \Statamic\Filesystem\FileAccessor
     */
    private function getDisk()
    {
        return File::disk('content');
    }

    /**
     * Creates a new comment stream for the given context.
     *
     * @param  $context
     * @return bool
     */
    public function createStream($context)
    {
        return $this->getDisk()->filesystem()->makeDirectory('comments/' . $context);
    }

    /**
     * Removes a comment stream for the given context.
     *
     * @param $context
     */
    public function removeStream($context)
    {
        
    }

    /**
     * Get the valid comment streams.
     *
     * @return StreamCollection
     */
    public function getStreams($streamFilter = [])
    {
        $streams = collect_streams($this->getDisk()->filesystem()
            ->directories('/comments/'))
            ->filter(function ($dir) {
                return count($this->getDisk()->filesystem()->directories($dir)) >= 1;
            });

        if (count($streamFilter) > 0) {
            $streams = $streams->filter(function ($dir) use ($streamFilter) {
                return \Illuminate\Support\Str::contains($dir, $streamFilter);
            });
        }

        $streams = $streams->map(function ($streamDirectory) {
                return new Stream(explode('/', $streamDirectory)[1]);
            });

        return $streams;
    }

    /**
     * Gets the total number of pending comments.
     *
     * @return int
     */
    public function countPending()
    {
        return $this->allComments()->sum(function (Comment $comment) {
            return $comment->get('is_pending');
        });
    }

    /**
     * Gets all comments.
     *
     * @return CommentCollection
     */
    public function allComments($flatList = false)
    {
        $comments = collect_comments();

        $this->getStreams()->each(function (Stream $stream) use (&$comments, $flatList) {
            $stream->getComments($flatList)->each(function (Comment $comment) use (&$comments) {
                $comments->push($comment);
            });
        });

        return $comments;
    }

    public function getStreamComments($stream, $flatList = false)
    {
        $comments = collect_comments();

        $this->getStreams([$stream])->each(function (Stream $stream) use (&$comments, $flatList) {
            $stream->getComments($flatList)->each(function (Comment $comment) use (&$comments) {
                $comments->push($comment);
            });
        });

        return $comments;
    }

    /**
     * Returns all comments.
     *
     * Alias for `allComments()`.
     *
     * @return CommentCollection
     */
    public function all()
    {
        return $this->allComments();
    }

    public function areCommentsEnabled(Carbon $postDate)
    {
        $autoClosePostsAfter = $this->getConfigInt('automatically_close_comments', 0);
    
        if ($autoClosePostsAfter == 0 || auth()->user() != null) {
            return true;
        }

        $lastPostDate = $postDate->addDays($autoClosePostsAfter);
        $difference = \Carbon::now()->diffInDays($lastPostDate);
        
        if ($difference > $autoClosePostsAfter) {
            return false;
        }

        return true;
    }

}