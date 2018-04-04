<?php

namespace Statamic\Addons\Meerkat\Comments\Traits;

trait HasRecursiveCommentKey
{

    /**
     * The key name to be used for nested comment replies.
     *
     * This value is generally modified by the Meerkat tag
     * system when a template developer applies some
     * nested scope to the Meerkat:responses tag.
     *
     * @var string
     */
    protected $nestedCommentKey = 'replies';

    /**
     * Sets the nested comment key.
     *
     * @param  string $key
     * @return $this
     */
    public function setRecursiveCommentKey($key)
    {
        $this->nestedCommentKey = $key;

        return $this;
    }

    /**
     * Gets the nested comment key.
     *
     * @return string
     */
    public function getRecursiveCommentKey()
    {
        return $this->nestedCommentKey;
    }

}