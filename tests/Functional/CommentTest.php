<?php

namespace Tests\Functional;


class CommentTest extends BaseTestCase
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

        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }

        $_SESSION = [];
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['admin_id'] = getenv('ADMIN_ID');

        $this->runApp('POST', '/', ['comment' => 'サンプル コメント テスト', 'user_id' => '1']);
    }

    public function testコメントの表示()
    {
        $response = $this->runApp('GET', '/comment/1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<section class="comment js-comment comment_top" id="top" data-comment="サンプル コメント テスト">', (string)$response->getBody());
    }

    public function testコメントIDがない場合()
    {
        $response = $this->runApp('GET', '/comment');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);
    }

    public function testコメントがない場合()
    {
        $response = $this->runApp('GET', '/comment/2');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/');
        $this->assertContains('コメントの取得に失敗しました。', (string)$response->getBody());
    }
}
