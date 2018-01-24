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
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['admin_id'] = getenv('ADMIN_ID');
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
        $this->assertContains('<link rel="stylesheet" href="//cdn.rawgit.com/filipelinhares/ress/master/dist/ress.min.css">', (string)$response->getBody());
    }

    public function testCSSFWをリンク()
    {
        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<link rel="stylesheet" href="//cdn.rawgit.com/milligram/milligram/master/dist/milligram.min.css">', (string)$response->getBody());
    }

    public function test投稿()
    {
        // *注: CSRF(middleware)を切ってテストしています。
        $response = $this->runApp('POST', '/', ['comment' => '¥骶𠮷🍢', 'user_id' => '1']);

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Error', (string)$response->getBody());
        // リダイレクトされるので何も表示されない

        $response = $this->runApp('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('¥骶𠮷🍢', (string)$response->getBody());
        $this->assertContains('<a href="/thread?thread_id=1">', (string)$response->getBody());
        $this->assertContains('スレッドを作成しました。', (string)$response->getBody());
    }

    public function testCSRFスレッド作成エラー()
    {
        $this->withMiddleware = true;
        $response = $this->runApp('POST', '/', ['comment' => 'Test', 'user_id' => '1']);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('投稿に失敗しました。', (string)$response->getBody());
    }

    public function test通らない投稿()
    {
        // *注: CSRF(middleware)を切ってテストしています。
        $response = $this->runApp('POST', '/', ['comment' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa1', 'user_id' => '1']);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Error', (string)$response->getBody());
    }

    public function test匿名投稿()
    {
        $_SESSION['user_id'] = null;
        $response = $this->runApp('POST', '/', ['comment' => 'aaaa', 'user_id' => '0']);

        $this->assertNotContains('Error', (string)$response->getBody());
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('aaaa', (string)$response->getBody());
    }
}