<?php

namespace Tests\Functional;


class LoginTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $_SESSION = [];
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['admin_id'] = getenv('ADMIN_ID');
    }

    public function testLogin()
    {
        $response = $this->runApp('GET', '/login');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }

    public function testOAuthFailed()
    {
        $response = $this->runApp('GET', '/login/callback', ['oauth_token' => '', 'oauth_verifier' => '']);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertContains('Error', (string)$response->getBody());
    }

}