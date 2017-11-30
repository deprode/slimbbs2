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
            $sql = 'TRUNCATE TABLE `users`';
            $prepare = $db_connection->prepare($sql);
            $prepare->execute();
            $sql = 'INSERT INTO `users` (`user_id`, `user_name`, `user_image_url`, `access_token`, `access_secret`) VALUES (1, "testuser", "http://via.placeholder.com/64x64", "dummy_token", "dummy_secret")';
            $prepare = $db_connection->prepare($sql);
            $prepare->execute();

        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }

        $_SESSION = [];
        $_SESSION['user_id'] = 1;

        $this->runApp('POST', '/', ['comment' => 'thread_test', 'user_id' => '1']);
    }

    public function testスレッドの表示()
    {
        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('thread_test', (string)$response->getBody());
        $this->assertContains('testuser', (string)$response->getBody());
        $this->assertContains('<img src="http://via.placeholder.com/64x64" alt="testuser">', (string)$response->getBody());
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

    public function postReply($user_id = "1")
    {
        return $this->runApp('POST', '/thread', ['comment' => 'comment_test', 'thread_id' => "1", 'user_id' => (string)$user_id]);
    }

    public function testスレッドに返信()
    {
        $response = $this->postReply();
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Error', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('comment_test', (string)$response->getBody());
    }

    public function test投稿の削除()
    {
        $this->postReply();

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '2']);
        $this->assertEquals(303, $response->getStatusCode());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('comment_test', (string)$response->getBody());
    }

    public function testスレッドの削除()
    {
        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '1']);
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/thread?thread_id=1', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);
    }

    public function test投稿の削除の失敗()
    {
        $this->postReply();

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '0']);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Error', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('comment_test', (string)$response->getBody());
    }

    public function test匿名で削除不可()
    {
        $_SESSION['user_id'] = 0;
        $this->postReply("0");

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '2']);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Error', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('comment_test', (string)$response->getBody());
        $this->assertNotContains('削除', (string)$response->getBody());
    }
}
