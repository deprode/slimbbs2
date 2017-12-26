<?php

namespace Tests\Functional;


class SearchTest extends BaseTestCase
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
        $_SESSION['user_id'] = 1;

        $this->runApp('POST', '/', ['comment' => 'サンプル コメント テスト', 'user_id' => '1']);
    }

    public function test検索()
    {
        $response = $this->runApp('GET', '/search', ['query' => 'コメント']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<section class="comment">', (string)$response->getBody());
        $this->assertContains('コメント', (string)$response->getBody());
    }

    public function test空クエリで検索()
    {
        $response = $this->runApp('GET', '/search', ['query' => '']);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);
    }

    public function test検索結果がない()
    {
        $response = $this->runApp('GET', '/search', ['query' => 'not found']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('<section class="comment">', (string)$response->getBody());
        $this->assertContains('コメントがありません', (string)$response->getBody());
    }
}