<?php

namespace OpenClassrooms\Akismet\Doubles\Client;

use OpenClassrooms\Akismet\Client\ApiClient;
use OpenClassrooms\Akismet\Client\Impl\ApiClientImpl;
use OpenClassrooms\Akismet\Doubles\guzzlehttp\guzzle\src\ClientMock;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class ClientImplTest
 *
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
class ApiClientImplTest extends \PHPUnit_Framework_TestCase
{
    const BLOG = 'http://yourblogdomainname.com';

    const KEY = '123ApiKey';

    const PARAMS = [
        'param1' => 'myParam1',
        'blog'   => self::BLOG,
    ];

    const RESOURCE_NAME = 'resource';

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var StreamInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleClientMock;

    /**
     * @test
     */
    public function postResource_ReturnResponse()
    {
        $this->initMock();
        $response = $this->apiClient->post(self::RESOURCE_NAME, self::PARAMS);

        $this->assertEquals(ClientMock::$response->getBody()->getContents(), $response);
        $this->assertEquals(self::RESOURCE_NAME, ClientMock::$uri);
        $this->assertEquals(['form_params' => self::PARAMS], ClientMock::$options);
    }

    private function initMock()
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn('string');
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getBody')->willReturn($streamMock);
        ClientMock::$response = $responseMock;
    }

    protected function setUp()
    {
        $this->apiClient = new ApiClientImpl(self::KEY, self::BLOG);
        $this->guzzleClientMock = new ClientMock();
        $this->setPropertyToObject($this->apiClient, 'client', $this->guzzleClientMock);
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @param mixed  $propertyValue
     */
    private function setPropertyToObject($object, $propertyName, $propertyValue)
    {
        $reflectionClass = new \ReflectionClass($object);
        $guzzleProperty = $reflectionClass->getProperty($propertyName);
        $guzzleProperty->setAccessible(true);
        $guzzleProperty->setValue($object, $propertyValue);
    }
}
