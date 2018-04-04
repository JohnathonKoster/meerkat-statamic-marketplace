<?php

namespace Statamic\Addons\Meerkat\Comments;

use Carbon\Carbon;
use Statamic\Data\DataCollection;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Addons\Meerkat\Comments\Traits\HasRecursiveCommentKey;

class CommentCollection extends DataCollection
{
    use HasRecursiveCommentKey;

    /**
     * Remove unpublished comments.
     *
     * @return static
     */
    public function removeUnpublished()
    {
        return new static ($this->filter(function ($item) {
            return (method_exists($item, 'published')) ? $item->published() : true;
        }));
    }

    /**
     * Removes the comments from the first level.
     *
     * @return static
     */
    public function removeFirstLevelReplies()
    {
        return new static ($this->filter(function (Comment $item) {
            return !$item->isReply();
        }));
    }

    protected function removeDuplicateReplies($replies, $visitedComments)
    {

    }

    /**
     * Removes content before whose date is before a given date
     *
     * @param mixed $before
     * @return static
     */
    public function removeBefore($before)
    {
        $before = Carbon::parse($before);

        return new static ($this->filter(function ($item) use ($before) {
            return $before->lte($item->getDate());
        }));
    }

    /**
     * Removes content before whose date is after a given date
     *
     * @param mixed $after
     * @return static
     */
    public function removeAfter($after)
    {
        $after = Carbon::parse($after);

        return new static ($this->filter(function ($item) use ($after) {
            return $after->gte($item->getDate());
        }));
    }

    /**
     * Converts the collection into an array.
     *
     * @param  bool $raiseEvent If true, individual comment events will be raised.
     * @return array
     */
    public function toArray($raiseEvent = false)
    {
        return array_values(array_map(function ($value) use ($raiseEvent) {

            if ($value instanceof Comment) {
                return $value->setRecursiveCommentKey($this->getRecursiveCommentKey())->toArray($raiseEvent);
            }

            return $value instanceof Arrayable ? $value->toArray() : $value;

            }, $this->items)
        );
    }


}