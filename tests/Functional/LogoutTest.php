<?php

namespace Tests\Functional;

class LogoutTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $_SESSION = [];
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['admin_id'] = getenv('ADMIN_ID');
    }

    public function testLogout()
    {
        // setcookieを使っていて、PHPUnitでheaderが出力されてからrunAppするので、warningが出る（ので抑制）
        $response = @$this->runApp('GET', '/logout');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }
}
