<?php

namespace Tests\Functional;

class HomepageTest extends BaseTestCase
{
    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */
    public function testGetHomepageWithoutName()
    {
        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Slimbbs', (string)$response->getBody());
        $this->assertNotContains('SlimFramework', (string)$response->getBody());
    }

    public function testResetCSSをリンク()
    {
        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">', (string)$response->getBody());
    }

    public function test投稿()
    {
        // *注: CSRF(middleware)を切ってテストしています。
        $response = $this->runApp('POST', '/', ['body' => 'aaaa']);

        $this->assertEquals(303, $response->getStatusCode());
        // リダイレクトされるので何も表示されない
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }

    public function test通らない投稿()
    {
        // *注: CSRF(middleware)を切ってテストしています。
        $response = $this->runApp('POST', '/', ['body' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa1']);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }
}