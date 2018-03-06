<?php

namespace Tests\Functional;

/***
 * マイページのFunctionalテスト
 * @package Tests\Functional
 */
class UserTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $dns = 'mysql:host=' . getenv('MYSQL_HOST') . ';port=' . getenv('MYSQL_PORT') . ';dbname=' . getenv('MYSQL_DATABASE');
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
            $sql = 'INSERT INTO `users` (`user_id`, `user_name`, `user_image_url`, `access_token`, `access_secret`) VALUES (1, "testuser", "http://via.placeholder.com/48x48", "dummy_token", "dummy_secret")';
            $prepare = $db_connection->prepare($sql);
            $prepare->execute();

        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }

        $_SESSION = [];
    }

    public function testマイページの表示()
    {
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['user_name'] = 'testuser';
        $_SESSION['admin_id'] = getenv('ADMIN_ID');

        $response = $this->runApp('GET', '/user');

        $this->assertContains('section', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('マイページ', (string)$response->getBody());
        $this->assertContains('href="https://twitter.com/testuser"', (string)$response->getBody());
        $this->assertNotContains('<section class="comment">', (string)$response->getBody());
        $this->assertContains('退会する', (string)$response->getBody());
    }

    public function testコメントの表示()
    {
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['user_name'] = 'testuser';
        $_SESSION['admin_id'] = getenv('ADMIN_ID');

        $this->runApp('POST', '/', ['comment' => 'サンプル コメント テスト', 'user_id' => '1']);

        $response = $this->runApp('GET', '/user/testuser');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<section class="comment">', (string)$response->getBody());
    }

    public function test画像の表示()
    {
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['user_name'] = 'testuser';
        $_SESSION['admin_id'] = getenv('ADMIN_ID');

        // 画像が表示されるか
        $_FILES = [
            'picture' => [
                'name'     => 'dummy.png',
                'type'     => 'image/png',
                'tmp_name' => __DIR__ . '/../data/dummy.png',
                'error'    => 0,
                'size'     => 13188
            ]
        ];

        $this->runApp('POST', '/thread', ['comment' => 'file_upload_test', 'thread_id' => "1", 'user_id' => (string)1]);
        $response = $this->runApp('GET', '/user/testuser');

        $this->assertContains('<img src="https://s3-ap-northeast-1.amazonaws.com/slimbbs2/', (string)$response->getBody());
        $this->assertContains('alt="file_upload_test"', (string)$response->getBody());
    }

    public function test匿名でユーザーページの表示()
    {
        $_SESSION = [];
        $_SESSION['user_id'] = null;
        $_SESSION['user_name'] = '';
        $_SESSION['admin_id'] = getenv('ADMIN_ID');

        $response = $this->runApp('GET', '/user/testuser');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<div class="content profile">', (string)$response->getBody());
        $this->assertContains('コメントがありません', (string)$response->getBody());
        $this->assertNotContains('マイページ', (string)$response->getBody());
        $this->assertNotContains('退会する', (string)$response->getBody());
    }

    public function test匿名でマイページ表示()
    {
        $_SESSION = [];
        $_SESSION['user_id'] = null;
        $_SESSION['user_name'] = '';
        $_SESSION['admin_id'] = getenv('ADMIN_ID');

        $response = $this->runApp('GET', '/user');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
    }
}