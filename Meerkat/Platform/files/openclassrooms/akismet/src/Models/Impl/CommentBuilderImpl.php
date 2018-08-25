<?php

namespace OpenClassrooms\Akismet\Models\Impl;

use OpenClassrooms\Akismet\Models\Comment;
use OpenClassrooms\Akismet\Models\CommentBuilder;

/**
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
class CommentBuilderImpl implements CommentBuilder
{

    /**
     * @var Comment
     */
    private $comment;

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $this->comment = new CommentImpl();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserIp($userIp)
    {
        $this->comment->setUserIp($userIp);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserAgent($userAgent)
    {
        $this->comment->setUserAgent($userAgent);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withReferrer($referrer)
    {
        $this->comment->setReferrer($referrer);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withPermalink($permalink)
    {
        $this->comment->setPermalink($permalink);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withAuthorName($authorName)
    {
        $this->comment->setAuthorName($authorName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withAuthorEmail($authorEmail)
    {
        $this->comment->setAuthorEmail($authorEmail);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withContent($content)
    {
        $this->comment->setContent($content);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        return $this->comment;
    }
}
