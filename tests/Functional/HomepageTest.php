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
            exit(-1);
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
        $this->assertContains('<link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">', (string)$response->getBody());
    }

    public function test投稿()
    {
        // *注: CSRF(middleware)を切ってテストしています。
        $response = $this->runApp('POST', '/', ['comment' => 'aaaa']);

        $this->assertEquals(303, $response->getStatusCode());
        // リダイレクトされるので何も表示されない
        $this->assertNotContains('Slimbbs', (string)$response->getBody());

        $response = $this->runApp('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('aaaa', (string)$response->getBody());
    }

    public function test通らない投稿()
    {
        // *注: CSRF(middleware)を切ってテストしています。
        $response = $this->runApp('POST', '/', ['body' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa1']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());
    }
}