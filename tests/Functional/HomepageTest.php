<?php

namespace Tests\Functional;

class HomepageTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $dns = 'mysql:host='.getenv('MYSQL_HOST').';port='.getenv('MYSQL_PORT').';dbname='.getenv('MYSQL_DATABASE');
        try {
            $db_connection = new \PDO($dns, getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
            $db_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db_connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $sql = 'TRUNCATE TABLE `comments`';
            $prepare = $db_connection->prepare($sql);
            $prepare->execute();
            $sql = 'TRUNCATE TABLE `threads`';
            $prepare = $db_connection->prepare($sql);
            $prepare->execute();

        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }

        $_SESSION = [];
    }

    public function testトップページの表示()
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
        $this->assertContains('<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/pure/1.0.0/pure-min.css">', (string)$response->getBody());
    }

    public function test投稿()
    {
        // *注: CSRF(middleware)を切ってテストしています。
        $response = $this->runApp('POST', '/', ['comment' => 'aaaa', 'user_id' => '1']);

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Error', (string)$response->getBody());
        // リダイレクトされるので何も表示されない

        $response = $this->runApp('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('aaaa', (string)$response->getBody());
        $this->assertContains('<a href="/thread?thread_id=1">', (string)$response->getBody());
    }

    public function test通らない投稿()
    {
        // *注: CSRF(middleware)を切ってテストしています。
        $response = $this->runApp('POST', '/', ['comment' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa1', 'user_id' => '1']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }

    public function test匿名投稿()
    {
        $response = $this->runApp('POST', '/', ['comment' => 'aa1']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }
}