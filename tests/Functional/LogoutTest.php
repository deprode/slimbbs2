<?php

namespace Tests\Functional;

class LogoutTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testLogout()
    {
        // setcookieを使っていて、PHPUnitでheaderが出力されてからrunAppするので、warningが出る（ので抑制）
        $response = @$this->runApp('GET', '/logout');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }
}
