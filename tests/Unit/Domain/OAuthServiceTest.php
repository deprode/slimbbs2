<?php

namespace Test\Unit;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Domain\AuthService;
use App\Domain\OAuthService;
use App\Exception\OAuthException;
use App\Model\User;

class OAuthServiceTest extends \PHPUnit_Framework_TestCase
{
    private $oauth;
    private $twitter;

    protected function setUp()
    {
        parent::setUp();

        $this->twitter = $this->createMock(TwitterOAuth::class);
        $this->twitter->expects($this->any())->method('get')->willReturn(new \stdClass());
        $this->twitter->expects($this->any())->method('oauth')->willReturn(['oauth_token' => 'token', 'oauth_token_secret' => 'secret']);
        $this->twitter->expects($this->any())->method('url')->willReturn('http://example.com');
        $auth = $this->createMock(AuthService::class);
        $auth->expects($this->any())->method('getOAuthToken')->willReturn(['token' => 'token', 'secret' => 'secret']);
        $auth->expects($this->any())->method('verifyToken')->willReturn(true);
        $this->oauth = new OAuthService($this->twitter, $auth, '/callback');
        $_SESSION = [];
    }

    public function testGetLoginUrl()
    {
        $this->assertEquals('http://example.com', $this->oauth->getLoginUrl('http://localhost'));
    }

    public function testVerifyToken()
    {
        $this->assertTrue($this->oauth->verifyToken('token', 'verifier'));
    }

    /**
     * @expectedException \App\Exception\OAuthException
     */
    public function testOAuth()
    {
        $this->assertNull($this->oauth->oAuth('verifier'));
        $this->twitter->expects($this->any())->method('oauth')->will($this->throwException(new OAuthException()));
        $this->oauth->oAuth('verifier');
    }

    public function testGetUserInfo()
    {
        $this->assertEquals(new \stdClass(), $this->oauth->getUserInfo());
    }

    public function testGetToken()
    {
        $_SESSION['oauth_token'] = 'token';
        $_SESSION['oauth_token_secret'] = 'secret';

        $this->assertEquals(['token' => 'token', 'secret' => 'secret'], $this->oauth->getToken());
    }

    public function testLoginUser()
    {
        $this->assertNull($this->oauth->loginUser(new User()));
    }
}
