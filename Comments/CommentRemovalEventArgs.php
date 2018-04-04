<?php

namespace Statamic\Addons\Meerkat\Comments;

use Illuminate\Support\Fluent;

class CommentRemovalEventArgs extends Fluent
{
    
    protected $doSoftDelete = false;

    protected $isTargetComment = false;

    public function __construct($isTarget, $attributes = [])
    {
        parent::__construct($attributes);

        $this->isTargetComment = $isTarget;
    }

    public function isTarget()
    {
        return $this->isTargetComment;
    }

    public function keep()
    {
        $this->doSoftDelete = true;
    }

    public function shouldKeep()
    {
        return $this->doSoftDelete;
    }

}