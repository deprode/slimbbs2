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
    }

    public function testコメントの表示()
    {
        $this->runApp('POST', '/', ['comment' => 'サンプル コメント テスト', 'user_id' => '1']);

        $response = $this->runApp('GET', '/comment/1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<section class="comment js-comment comment_top" id="top" data-comment="サンプル コメント テスト">', (string)$response->getBody());
    }

    public function testコメントIDがない場合()
    {
        $this->runApp('POST', '/', ['comment' => 'サンプル コメント テスト', 'user_id' => '1']);

        $response = $this->runApp('GET', '/comment');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);
    }

    public function testコメントがない場合()
    {
        $this->runApp('POST', '/', ['comment' => 'サンプル コメント テスト', 'user_id' => '1']);

        $response = $this->runApp('GET', '/comment/2');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/');
        $this->assertContains('コメントの取得に失敗しました。', (string)$response->getBody());
    }

    public function test画像の投稿で画像表示()
    {
        $_FILES = [
            'picture' => [
                'name'     => 'dummy.png',
                'type'     => 'image/png',
                'tmp_name' => __DIR__ . '/../data/dummy.png',
                'error'    => 0,
                'size'     => 13188
            ]
        ];

        $response = $this->runApp('POST', '/thread', ['comment' => 'file_upload_test', 'thread_id' => "1", 'user_id' => (string)1]);
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/thread?thread_id=1', (string)$response->getHeader('location')[0]);
        $this->assertNotContains('Slimbbs', (string)$response->getBody());

        $response = $this->runApp('GET', '/comment/1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('alt="file_upload_test"', (string)$response->getBody());
        $this->assertContains('<img src="https://s3-' . getenv('AWS_S3_REGION') . '.amazonaws.com/' . getenv('AWS_S3_BUCKET_NAME') . '/', (string)$response->getBody());
    }
}
