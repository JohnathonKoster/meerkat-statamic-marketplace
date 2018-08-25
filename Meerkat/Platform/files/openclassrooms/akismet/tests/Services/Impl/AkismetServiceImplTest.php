<?php

namespace OpenClassrooms\Akismet\Tests\Services\Impl;

use OpenClassrooms\Akismet\Doubles\Client\Impl\ApiClientMock;
use OpenClassrooms\Akismet\Doubles\Models\CommentStub;
use OpenClassrooms\Akismet\Models\Impl\CommentBuilderImpl;
use OpenClassrooms\Akismet\Services\AkismetService;
use OpenClassrooms\Akismet\Services\Impl\AkismetServiceImpl;

/**
 * Class AkismetServiceImplTest
 *
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
class AkismetServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const BLOG_URL = 'http://www.blogdomainname.com/';

    const KEY = '123APIKey';

    /**
     * @var AkismetService
     */
    private $akismetService;

    /**
     * @test
     */
    public function commentCheck()
    {
        $commentBuilder = new CommentBuilderImpl();

        $response = $this->akismetService->commentCheck(
            $commentBuilder
                ->create()
                ->withUserIp(CommentStub::USER_IP)
                ->withUserAgent(CommentStub::USER_AGENT)
                ->withReferrer(CommentStub::REFERRER)
                ->withPermalink(CommentStub::PERMALINK)
                ->withAuthorName(CommentStub::AUTHOR_NAME)
                ->withAuthorEmail(CommentStub::AUTHOR_EMAIL)
                ->withContent(CommentStub::CONTENT)
                ->build()
        );

        $this->assertTrue($response);
        $this->assertEquals(AkismetServiceImpl::COMMENT_CHECK, ApiClientMock::$resource);
        $this->assertCommentCheckParams();
    }

    private function assertCommentCheckParams()
    {
        $this->assertEquals(CommentStub::USER_IP, ApiClientMock::$params['user_ip']);
        $this->assertEquals(CommentStub::USER_AGENT, ApiClientMock::$params['user_agent']);
        $this->assertEquals(CommentStub::REFERRER, ApiClientMock::$params['referrer']);
        $this->assertEquals(CommentStub::PERMALINK, ApiClientMock::$params['permalink']);
        $this->assertEquals(CommentStub::AUTHOR_NAME, ApiClientMock::$params['comment_author']);
        $this->assertEquals(CommentStub::AUTHOR_EMAIL, ApiClientMock::$params['comment_author_email']);
        $this->assertEquals(CommentStub::CONTENT, ApiClientMock::$params['comment_content']);
    }

    /**
     * @test
     */
    public function submitSpam()
    {
        ApiClientMock::$postReturn = 'Thanks for making the web a better place.';

        $commentBuilder = new CommentBuilderImpl();

        $this->akismetService->submitSpam(
            $commentBuilder
                ->create()
                ->withUserIp(CommentStub::USER_IP)
                ->withUserAgent(CommentStub::USER_AGENT)
                ->withReferrer(CommentStub::REFERRER)
                ->withPermalink(CommentStub::PERMALINK)
                ->withAuthorName(CommentStub::AUTHOR_NAME)
                ->withAuthorEmail(CommentStub::AUTHOR_EMAIL)
                ->withContent(CommentStub::CONTENT)
                ->build()
        );

        $this->assertEquals(AkismetServiceImpl::SUBMIT_SPAM, ApiClientMock::$resource);
        $this->assertCommentCheckParams();
    }

    /**
     * @test
     */
    public function submitHam()
    {
        ApiClientMock::$postReturn = 'Thanks for making the web a better place.';

        $commentBuilder = new CommentBuilderImpl();

        $this->akismetService->submitHam(
            $commentBuilder
                ->create()
                ->withUserIp(CommentStub::USER_IP)
                ->withUserAgent(CommentStub::USER_AGENT)
                ->withReferrer(CommentStub::REFERRER)
                ->withPermalink(CommentStub::PERMALINK)
                ->withAuthorName(CommentStub::AUTHOR_NAME)
                ->withAuthorEmail(CommentStub::AUTHOR_EMAIL)
                ->withContent(CommentStub::CONTENT)
                ->build()
        );

        $this->assertEquals(AkismetServiceImpl::SUBMIT_HAM, ApiClientMock::$resource);
        $this->assertCommentCheckParams();
    }

    protected function setUp()
    {
        $this->akismetService = new AkismetServiceImpl();
        $this->akismetService->setApiClient(new ApiClientMock());
        ApiClientMock::$postReturn = 'true';
    }
}
