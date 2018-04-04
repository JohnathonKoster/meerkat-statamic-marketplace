<?php

namespace Statamic\Addons\Meerkat\Comments\Metrics;

use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Addons\Meerkat\Comments\Comment;
use Statamic\Addons\Meerkat\Comments\CommentCollection;

class CommentMetrics implements Jsonable, Arrayable, JsonSerializable
{

    /**
     * The CommentCollection instance.
     *
     * @var CommentCollection
     */
    protected $comments;

    /**
     * Indicates if the metrics have been gathered.
     *
     * @var bool
     */
    protected $metricsGathered = false;

    /**
     * The pending comment count.
     *
     * @var int
     */
    protected $pendingCount = 0;

    /**
     * The total comment count.
     *
     * @var int
     */
    protected $allCount = 0;

    /**
     * The approved comment count.
     *
     * @var int
     */
    protected $approvedCount = 0;

    /**
     * The spam comment count.
     *
     * @var int
     */
    protected $spamCount = 0;

    /**
     * Sets the CommentCollection instance.
     *
     * @param  CommentCollection $comments
     * @return static
     */
    public function setComments(CommentCollection $comments)
    {
        $this->comments = $comments;
        $this->metricsGathered = false;

        return $this;
    }

    /**
     * Gathers the comment metrics.
     */
    private function gatherMetrics()
    {
        if ($this->metricsGathered) {
            return;
        }

        $this->comments->each(function (Comment $comment) {
            if ($comment->approved()) {
                $this->approvedCount++;
            } else {
                $this->pendingCount++;
            }

            if ($comment->isSpam()) {
                $this->spamCount++;
            }

            $this->allCount++;
        });

        $this->metricsGathered = true;
    }

    /**
     * Gets the total number of pending comments.
     *
     * @return int
     */
    public function pending()
    {
        $this->gatherMetrics();

        return $this->pendingCount;
    }

    /**
     * Gets the total number of comments.
     *
     * @return int
     */
    public function all()
    {
        $this->gatherMetrics();

        return $this->allCount;
    }

    /**
     * Gets the total number of approved comments.
     *
     * @return int
     */
    public function approved()
    {
        $this->gatherMetrics();

        return $this->approvedCount;
    }

    /**
     * Gets the total number of spam comments.
     *
     * @return int
     */
    public function spam()
    {
        $this->gatherMetrics();

        return $this->spamCount;
    }

    public function toArray()
    {
        return [
            'spam' => $this->spam(),
            'approved' => $this->approved(),
            'all' => $this->all(),
            'pending' => $this->pending()
        ];
    }

    function jsonSerialize()
    {
        return $this->toArray();
    }


    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

}