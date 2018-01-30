<?php

namespace Tests\Functional;

class QuitTest extends BaseTestCase
{
    private $pdo;

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

        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }

        $this->pdo = $db_connection;

        $_SESSION = [];
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['admin_id'] = getenv('ADMIN_ID');
    }

    public function test削除画面表示()
    {
        $response = $this->runApp('GET', '/quit');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('退会する', (string)$response->getBody());
    }

    public function testユーザーデータ削除()
    {
        $this->runApp('POST', '/', ['comment' => 'TestData', 'user_id' => getenv('USER_ID')]);

        // MEMO:ログアウト処理にcookieをリセットしてエラーが出るため、その処理を飛ばす
        ini_set('session.use_cookies', '0');

        $response = $this->runApp('DELETE', '/quit', ['user_id' => getenv('USER_ID')]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('退会しました', (string)$response->getBody());

        $_SESSION['user_id'] = 0;
        $_SESSION['admin_id'] = getenv('ADMIN_ID');

        $response = $this->runApp('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('TestData', (string)$response->getBody());
    }

    public function test匿名ははじく()
    {
        $_SESSION['user_id'] = null;
        $response = $this->runApp('GET', '/quit');
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);

        $response = $this->runApp('DELETE', '/quit');
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
    }

    public function testユーザーデータ削除失敗()
    {
        $_SESSION['user_id'] = 0;

        $response = $this->runApp('DELETE', '/quit');
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('アカウント削除に失敗しました。', (string)$response->getBody());
    }
}