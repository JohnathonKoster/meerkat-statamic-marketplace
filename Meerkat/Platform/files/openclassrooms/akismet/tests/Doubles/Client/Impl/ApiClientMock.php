<?php

namespace OpenClassrooms\Akismet\Doubles\Client\Impl;

use OpenClassrooms\Akismet\Client\ApiClient;

/**
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
class ApiClientMock implements ApiClient
{

    /**
     * @var array
     */
    public static $params;

    /**
     * @var bool
     */
    public static $postReturn;

    /**
     * @var string
     */
    public static $resource;

    public function __construct()
    {
        self::$params = null;
        self::$postReturn = null;
        self::$resource = null;
    }

    /**
     * {@inheritdoc}
     */
    public function post($resource, array $params)
    {
        self::$resource = $resource;
        self::$params = $params;

        return self::$postReturn;
    }
}
