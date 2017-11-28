<?php

namespace Tests\Functional;

class ThreadTest extends BaseTestCase
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

        $this->runApp('POST', '/', ['comment' => 'thread_test', 'user_id' => '1']);
    }

    public function testスレッドの表示()
    {
        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('thread_test', (string)$response->getBody());
    }

    public function testスレッドの表示失敗()
    {
        $response = $this->runApp('GET', '/thread?thread_id=a');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotContains('thread_test', (string)$response->getBody());
    }

    public function test存在しないスレッド()
    {
        $response = $this->runApp('GET', '/thread?thread_id=0');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotContains('thread_test', (string)$response->getBody());
    }

    public function testスレッドに返信()
    {
        $response = $this->runApp('POST', '/thread', ['comment' => 'comment_test', 'thread_id' => "1", 'user_id' => '1']);
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/thread?thread_id=1', (string)$response->getHeader('location')[0]);
        $this->assertNotContains('Slimbbs', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('comment_test', (string)$response->getBody());
    }

    public function test返信のValidationエラー()
    {
        $response = $this->runApp('POST', '/thread', ['comment' => 'comment_test']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('Slimbbs', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('comment_test', (string)$response->getBody());
    }

}
