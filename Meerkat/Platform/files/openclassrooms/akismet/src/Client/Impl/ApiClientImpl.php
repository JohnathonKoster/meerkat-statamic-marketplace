<?php

namespace OpenClassrooms\Akismet\Client\Impl;

use GuzzleHttp\ClientInterface;
use OpenClassrooms\Akismet\Client\ApiClient;

/**
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
class ApiClientImpl implements ApiClient
{

    /**
     * The front page or home URL of the instance making the request.
     * For a blog, site, or wiki this would be the front page.
     *
     * Note: Must be a full URI, including http://.
     *
     * @var string
     */
    private $blog;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param string $key  The API key
     * @param string $blog The front page or home URL
     */
    public function __construct($key, $blog)
    {
        $this->blog = $blog;
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://'.$key.'.rest.akismet.com/1.1/']);
    }

    /**
     * {@inheritdoc}
     */
    public function post($resource, array $params)
    {
        $params['blog'] = $this->blog;

        $response = $this->client->post($resource, ['form_params' => $params]);

        return $response->getBody()->getContents();
    }
}
