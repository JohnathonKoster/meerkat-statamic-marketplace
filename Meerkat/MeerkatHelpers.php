<?php

namespace Statamic\Addons\Meerkat;

use Statamic\API\URL;
use Statamic\API\Config;

trait MeerkatHelpers
{

    /**
     * Gets the path to the Statamic Control Panel.
     * @return string
     */
    protected function meerkatCpPath()
    {
        return URL::assemble(SITE_ROOT, str_finish(CP_ROUTE, '/'));
    }

    protected function meerkatGetConfig($configurationItem, $default = null) {
        return Config::get($configurationItem, $default);
    }

    protected function meerkatPath()
    {
        return realpath(site_path('/addons/Meerkat'));
    }

    protected function collectStreams($value = [])
    {
        return new \Statamic\Addons\Meerkat\Comments\StreamCollection($value);
    }

    protected function getCommentIds(\Statamic\Addons\Meerkat\Comments\Comment &$comment) {
        $commentsRemoved = [];

        if ($comment->hasReplies()) {
            foreach ($comment->getReplies() as $reply) {
                $commentsRemoved = array_merge($commentsRemoved, $this->getCommentIds($reply));
            }
        }

        $commentsRemoved[] = $comment['id'];

        return $commentsRemoved;
    }

    protected function getCommentsAndReplies(\Statamic\Addons\Meerkat\Comments\Comment &$comment) {
        $discoveredComments = [];

        if ($comment->hasReplies()) {
            foreach ($comment->getReplies() as $reply) {
                $discoveredComments = array_merge($discoveredComments, $this->getCommentsAndReplies($reply));
            }
        }

        $discoveredComments[] = $comment;

        return $discoveredComments;
    }

    protected  function collectComments($value = [])
    {
        return new \Statamic\Addons\Meerkat\Comments\CommentCollection($value);
    }

    protected function isMeerkatRequest() {
        return \Illuminate\Support\Str::contains(request()->getRequestUri(), [
            'addons/meerkat',
            '/forms',
            '/collections/entries',
            '/pages/edit'
        ]);
    }

    protected function isMeerkatPublisherRequest() {
        return \Illuminate\Support\Str::contains(request()->getRequestUri(), [
            '/collections/entries',
            '/pages/edit'
        ]);
    }

    protected function isCpDashboard() {
        return \Illuminate\Support\Str::contains(request()->getRequestUri(), 'dashboard');
    }

    protected function meerkatTrans($id, $parameters = [], $domain = 'messages', $locale = null) {
        return trans("addons.Meerkat::{$id}", $parameters, $domain, $locale);
    }
}
