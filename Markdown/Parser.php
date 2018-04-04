<?php

namespace Statamic\Addons\Meerkat\Markdown;

class Parser
{

    /**
     * Prepares the comment's content for display.
     *
     * @param  string $content
     * @return string
     */
    public static function prepare($content)
    {
        return strip_tags($content, '<a><p><ul><li><ol><code><pre>');
    }

    /**
     * Parses the content and prepares it for display.
     *
     * @param  string $content
     * @return string
     */
    public static function parseCommentContent($content)
    {
        return self::prepare(markdown($content));
    }

}