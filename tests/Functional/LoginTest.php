<?php

namespace Tests\Functional;


class LoginTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testLogin()
    {
        $response = $this->runApp('GET', '/login');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }

    public function testOAuthFailed()
    {
        $response = $this->runApp('GET', '/login');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }

}