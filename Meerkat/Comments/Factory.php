<?php

namespace Statamic\Addons\Meerkat\Comments;

use Illuminate\Support\Str;
use Statamic\Addons\Meerkat\Markdown\Parser;
use Statamic\Addons\Meerkat\Helpers\Str as MeerkatStr;

class Factory
{

    /**
     * Smartly converts a Comment index into an array.
     *
     * @param Comment $comment
     * @return array
     */
    public static function makeApiData(Comment $comment)
    {
        $context = $comment->get('context');

        if ($context == null) {
            return null;
        }

        $data = $comment->toArray();
        $data['datestring'] = (string)$data['date'];
        $data['datestamp'] = $data['date']->timestamp;
        $data['gravatar'] = md5($data['email']);
        $data['url'] = $comment->get('url');

        if (mb_strlen($data['url']) == 0) {
            $data['url'] = false;
        }

        $currentLocale = meerkat_get_config('cp.locale', 'en');

        $data['user_ip'] = $comment->get('ip');
        $data['published'] = $comment->published();
        $data['spam'] = $comment->isSpam();
        $data['id'] = $comment->get('id');

        $data['comment'] = Parser::prepare($data['comment']);
        $data['in_response_to'] = $context->get('title');
        $data['in_response_to_url'] = $context->url();
        $data['in_response_to_edit_url'] = $context->editUrl();
        $data['in_response_string'] = meerkat_trans('comments.in_response_to', [
            'article' => '<a href="' . $data['in_response_to_edit_url'] . '" title="' . $data['in_response_to'] . '">' . Str::limit($data['in_response_to'], 55) . '</a>',
            'date' => $data['datestring']
        ], 'messages', $currentLocale);

        $data['is_reply'] = $comment->isReply();
        $data['has_replies'] = $comment->hasReplies();
        $data['is_root'] = $comment->isRoot();
        $data['parent_comment_id'] = $comment->getParentID();
        $data['original_markdown'] = $comment->getOriginalMarkdown();

        if ($comment->isReply()) {
            $data['in_reply_to_string'] = meerkat_trans('comments.in_reply_to', [
                'author' => '<a href="#meerkat-comment-' . $comment->getParentID() . '" title="' . meerkat_trans('actions.jump_to_author_post', [], 'messages', $currentLocale) . '">' . $comment->getParent()->get('name') . '</a>'
            ], 'messages', $currentLocale);
            $data['parent_comment_name'] = $comment->getParent()->get('name');
        } else {
            $data['in_reply_to_string'] = null;
        }

        $data['initials'] = MeerkatStr::initials($data['name']);

        // Some utility things to make working with vue.js easier.
        $data['checked'] = false;
        $data['editing'] = false;
        $data['saving'] = false;
        $data['writing_reply'] = false;
        $data['new_reply'] = '';

        $data['conversation_participants'] = collect($data['conversation_participants'])->map(function ($participant) {
            $participant['gravatar'] = md5($participant['email']);
            $participant['initials'] = MeerkatStr::initials($participant['name']);

            return $participant;
        })->toArray();

        $data['participants'] = collect($data['participants'])->map(function ($participant) {
            $participant['gravatar'] = md5($participant['email']);
            $participant['initials'] = MeerkatStr::initials($participant['name']);

            return $participant;
        })->toArray();
        
        return $data;
    }

}