<?php

namespace OpenClassrooms\Akismet\Services\Impl;

use OpenClassrooms\Akismet\Client\ApiClient;
use OpenClassrooms\Akismet\Models\Comment;
use OpenClassrooms\Akismet\Services\AkismetService;

/**
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
class AkismetServiceImpl implements AkismetService
{
    const COMMENT_CHECK = 'comment-check';

    const SUBMIT_HAM = 'submit-ham';

    const SUBMIT_SPAM = 'submit-spam';

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @param ApiClient $apiClient
     */
    public function setApiClient(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * {@inheritdoc}
     */
    public function commentCheck(Comment $comment)
    {
        return 'true' === $this->apiClient->post(self::COMMENT_CHECK, $comment->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function submitSpam(Comment $comment)
    {
        $this->apiClient->post(self::SUBMIT_SPAM, $comment->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function submitHam(Comment $comment)
    {
        $this->apiClient->post(self::SUBMIT_HAM, $comment->toArray());
    }
}
