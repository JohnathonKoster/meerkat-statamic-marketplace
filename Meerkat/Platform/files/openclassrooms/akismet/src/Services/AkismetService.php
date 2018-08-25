<?php

namespace OpenClassrooms\Akismet\Services;

use OpenClassrooms\Akismet\Models\Comment;

/**
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
interface AkismetService
{
    /**
     * @param Comment $comment
     *
     * @return bool
     */
    public function commentCheck(Comment $comment);

    /**
     * @param Comment $comment
     */
    public function submitSpam(Comment $comment);

    /**
     * @param Comment $comment
     */
    public function submitHam(Comment $comment);
}
