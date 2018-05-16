<?php

namespace Test\Unit;


use App\Model\User;

class AuthServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \App\Service\AuthService $auth
     */
    private $auth;

    public function setUp()
    {
        parent::setUp();
        $_SESSION = [];
        $this->auth = new \App\Service\AuthService(new \RKA\Session(), 100);
    }

    public function testSetOAuthToken()
    {
        $token = [];
        $token['oauth_token'] = 'token';
        $token['oauth_token_secret'] = 'secret';
        $this->auth->setOAuthToken($token);

        $this->assertEquals('token', $_SESSION['oauth_token']);
        $this->assertEquals('secret', $_SESSION['oauth_token_secret']);
    }

    public function testGetOAuthToken()
    {
        $_SESSION['oauth_token'] = 'token';
        $_SESSION['oauth_token_secret'] = 'secret';
        $token = $this->auth->getOAuthToken();

        $this->assertEquals('token', $token['token']);
        $this->assertEquals('secret', $token['secret']);
    }

    public function testVerifyToken()
    {
        $_SESSION['oauth_token'] = 'token';
        $this->assertEquals(true, $this->auth->verifyToken('token'));
    }

    public function testUserInfo()
    {
        $user = new User();
        $user->user_id = '1';
        $user->user_name = 'aaaa';
        $user->user_image_url = 'http://example.com';
        $this->auth->setUserInfo($user);

        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('aaaa', $_SESSION['user_name']);
        $this->assertEquals('http://example.com', $_SESSION['user_img']);
    }

    public function testUsername()
    {
        $_SESSION = ['user_name' => 'username'];
        $this->assertEquals('username', $this->auth->getUsername());
        $_SESSION = [];
        $this->assertEquals('', $this->auth->getUsername());
    }

    public function testUserId()
    {
        $_SESSION = ['user_id' => 1];
        $this->assertEquals(1, $this->auth->getUserId());
        $_SESSION = [];
        $this->assertEquals(0, $this->auth->getUserId());
    }

    public function testEqualUser()
    {
        $_SESSION = ['user_id' => 1];
        $this->assertEquals(true, $this->auth->equalUser(1));
    }

    public function testAdminId()
    {
        $this->assertEquals(100, $this->auth->getAdminId());
    }

    public function testIsAdmin()
    {
        $_SESSION = ['user_id' => 1];
        $this->assertEquals(false, $this->auth->isAdmin());
        $_SESSION = ['user_id' => 100];
        $this->assertEquals(true, $this->auth->isAdmin());
    }

    public function testIsLoggedIn()
    {
        $_SESSION = ['user_id' => 1];
        $this->assertEquals(true, $this->auth->isLoggedIn());
        $_SESSION = [];
        $this->assertEquals(false, $this->auth->isLoggedIn());
    }

    public function testLogout()
    {
        $_SESSION = ['user_id' => 1];
        @$this->auth->logout();
        $this->assertEquals([], $_SESSION);
    }

    public function testGetUserHash()
    {
        $token = 'password-12345';

        $hash = $this->auth->getUserhash($token);
        $this->assertTrue(password_verify($token, $hash));

        $_SESSION = ['oauth_token' => $token];

        $hash = $this->auth->getUserhash();
        $this->assertTrue(password_verify($token, $hash));
    }

    public function testVerifyUserHash()
    {
        $token = 'password-12345';
        $hash = $this->auth->getUserhash($token);

        $this->assertTrue($this->auth->verifyUserHash($token, $hash));
    }
}
