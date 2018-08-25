<?php

namespace OpenClassrooms\Akismet\Doubles\guzzlehttp\guzzle\src;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Arnaud Lefevre <arnaud.lefevre@openclassrooms.com>
 */
class ClientMock extends Client
{
    /**
     * @var ResponseInterface
     */
    public static $response;

    /**
     * @var string
     */
    public static $uri;

    /**
     * @var array
     */
    public static $options;

    public function __construct()
    {
        self::$response = null;
        self::$uri = null;
        self::$options = [];
    }

    /**
     * {@inheritdoc}
     */
    public function post($uri, array $options = [])
    {
        self::$uri = $uri;
        self::$options = $options;

        return self::$response;
    }
}
