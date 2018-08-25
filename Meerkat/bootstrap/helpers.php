<?php

use Statamic\API\URL;
use Statamic\API\Config;

if (!function_exists('meerkat_cppath')) {
    function meerkat_cppath() {
        return URL::assemble(SITE_ROOT, str_finish(CP_ROUTE, '/'));
    }
}

if (!function_exists('meerkat_get_config')) {
    function meerkat_get_config($configurationItem, $default = null) {
        return Config::get($configurationItem, $default);
    }
}

if (!function_exists('meerkat_path')) {
    /**
     * Returns the path to the Meerkat addon directory.
     *
     * @return string
     */
    function meerkat_path() {
        return realpath(site_path('/addons/Meerkat'));
    }
}

if (!function_exists('meerkat_get_comment_ids')) {
    /**
     * Returns an array of all the Comment instance IDs for the given Comment.
     *
     * This helper function will recursively find all the comment IDs.
     *
     * @param \Statamic\Addons\Meerkat\Comments\Comment $comment
     * @return array
     */
    function meerkat_get_comment_ids(\Statamic\Addons\Meerkat\Comments\Comment &$comment) {
        $commentsRemoved = [];

        if ($comment->hasReplies()) {
            foreach ($comment->getReplies() as $reply) {
                $commentsRemoved = array_merge($commentsRemoved, meerkat_get_comment_ids($reply));
            }
        }

        $commentsRemoved[] = $comment['id'];

        return $commentsRemoved;
    }
}

if (!function_exists('meerkat_get_comments_and_replies')) {
    /**
     * Returns an array containing the comment and all of its replies.
     *
     * @param \Statamic\Addons\Meerkat\Comments\Comment $comment
     * @return array
     */
    function meerkat_get_comments_and_replies(\Statamic\Addons\Meerkat\Comments\Comment &$comment) {
        $discoveredComments = [];

        if ($comment->hasReplies()) {
            foreach ($comment->getReplies() as $reply) {
                $discoveredComments = array_merge($discoveredComments, meerkat_get_comments_and_replies($reply));
            }
        }

        $discoveredComments[] = $comment;

        return $discoveredComments;
    }
}

if (!function_exists('is_meerkat_request')) {
    /**
     * Determines if the request is a Meerkat addon request.
     *
     * @return bool
     */
    function is_meerkat_request() {
        return \Illuminate\Support\Str::contains(request()->getRequestUri(), [
            'addons/meerkat',
            '/forms',
            '/collections/entries',
            '/pages/edit'
        ]);
    }
}

if (!function_exists('is_meerkat_publisher_request')) {
    function is_meerkat_publisher_request() {
        return \Illuminate\Support\Str::contains(request()->getRequestUri(), [
            '/collections/entries',
            '/pages/edit'
        ]); 
    }
}

if (!function_exists('is_cp_dashboard')) {
    function is_cp_dashboard() {
        return \Illuminate\Support\Str::contains(request()->getRequestUri(), 'dashboard');
    }
}

if (!function_exists('meerkat_trans')) {
    /**
     * Translates the given Meerkat message.
     *
     * @param  string  $id
     * @param  array   $parameters
     * @param  string  $domain
     * @param  string  $locale
     * @return string
     */
    function meerkat_trans($id, $parameters = [], $domain = 'messages', $locale = null) {
        return trans("addons.Meerkat::{$id}", $parameters, $domain, $locale);
    }
}

if (!function_exists('collect_comments')) {
    /**
     * @param  array $value
     * @return \Statamic\Addons\Meerkat\Comments\CommentCollection
     */
    function collect_comments($value = [])
    {
        return new \Statamic\Addons\Meerkat\Comments\CommentCollection($value);
    }
}

if (!function_exists('collect_streams')) {
    /**
     * @param  array $value
     * @return \Statamic\Addons\Meerkat\Comments\StreamCollection
     */
    function collect_streams($value = [])
    {
        return new \Statamic\Addons\Meerkat\Comments\StreamCollection($value);
    }
}