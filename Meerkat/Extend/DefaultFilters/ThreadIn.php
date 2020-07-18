<?php

namespace Statamic\Addons\Meerkat\Extend\DefaultFilters;

use Statamic\Addons\Meerkat\Comments\Filters\CommentFilter;
use Statamic\Addons\Meerkat\Extend\ThemeFilters;
use Statamic\Addons\Meerkat\Helpers\ListInput;

/**
 * Class ThreadIn
 *
 * Contains the thread-related default Meerkat threads.
 *
 * @package Statamic\Addons\Meerkat\Extend\DefaultFilters
 * @since 1.5.85
 */
class ThreadIn
{

    /**
     * Registers the default thread:<> filters.
     *
     * @param ThemeFilters $filters The filter manager.
     */
    public function register(ThemeFilters $filters)
    {
        $filters->filterWithTagContext('thread:in', function ($comments) {
            $tempThreads = ListInput::parse($this->get('threads', []));
            $threadList = [];

            foreach ($tempThreads as $value) {
                /** @var CommentFilter $this */

                if ($value === '*current*' && $this->hasContext()) {
                    $context = $this->getContext();

                    if (array_key_exists('id', $context)) {
                        $threadList[] = $context['id'];
                    } else {
                        $threadList[] = $value;
                    }
                }
            }

            return $comments->filter(function ($comment) use ($threadList) {

                if (array_key_exists('comment_context', $comment) == false) { return false; }

                $context = $comment['comment_context'];

                if (array_key_exists('id', $context) == false) { return false; }

                return in_array($comment['comment_context']['id'], $threadList);
            });
        }, 'threads', [
            'meerkat:all-comments'
        ]);


        $filters->filterWithTagContext('thread:not_in', function ($comments) {
            $tempThreads = ListInput::parse($this->get('threads', []));
            $threadList = [];

            foreach ($tempThreads as $value) {
                if ($value === '*current*' && $this->hasContext()) {
                    $context = $this->getContext();

                    if (array_key_exists('id', $context)) {
                        $threadList[] = $context['id'];
                    } else {
                        $threadList[] = $value;
                    }
                }
            }

            return $comments->filter(function ($comment) use ($threadList) {

                if (array_key_exists('comment_context', $comment) == false) { return false; }

                $context = $comment['comment_context'];

                if (array_key_exists('id', $context) == false) { return false; }

                return in_array($comment['comment_context']['id'], $threadList) == false;
            });
        }, 'threads', [
            'meerkat:all-comments'
        ]);
    }

}