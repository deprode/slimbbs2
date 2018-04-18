<?php

namespace Tests\Functional;

class ThreadTest extends BaseTestCase
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
        $_SESSION['user_id'] = getenv('USER_ID');
        $_SESSION['admin_id'] = getenv('ADMIN_ID');
        $_SESSION['user_name'] = 'testuser';

        $this->runApp('POST', '/', ['comment' => 'thread_test', 'user_id' => '1']);
    }

    public function testスレッドの表示()
    {
        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('thread_test', (string)$response->getBody());
        $this->assertContains('testuser', (string)$response->getBody());
        $this->assertContains('<img class="comment__header__wrap__icon" src="http://via.placeholder.com/48x48" width="48" height="48" alt="testuser">', (string)$response->getBody());
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
        return $this->runApp('POST', '/thread', ['comment' => 'comment_test', 'thread_id' => "1", 'user_id' => (string)$user_id, 'sort' => 'desc']);
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
        $this->assertContains('コメントを保存しました。', (string)$response->getBody());

        sleep(1);

        $response = $this->runApp('GET', '/');
        $this->assertContains('2個のコメント', (string)$response->getBody());
        $this->assertContains('秒前', (string)$response->getBody());
    }

    public function testタイトルの切り詰め()
    {
        $comment = <<<COMMENT
123456789
12345678
COMMENT;
        $expect = <<<EXPECT
123456789
123456… - Slimbbs
EXPECT;

        $this->runApp('POST', '/', ['comment' => $comment, 'user_id' => '1']);

        $response = $this->runApp('GET', '/thread?thread_id=2');
        $this->assertContains($expect, (string)$response->getBody());
    }

    public function test返信のValidationエラー()
    {
        $response = $this->runApp('POST', '/thread', ['comment' => 'comment_test']);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/thread', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('投稿に失敗しました。', (string)$response->getBody());
        $this->assertNotContains('comment_test', (string)$response->getBody());
        $this->assertNotContains('コメントを保存しました。', (string)$response->getBody());
    }

    public function testCSRFコメントの投稿エラー()
    {
        $this->withMiddleware = true;
        $this->postReply();

        $response = $this->runApp('POST', '/thread', ['comment' => 'comment_test']);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);
        $this->assertNotContains('投稿に失敗しました。', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('失敗しました。', (string)$response->getBody());
    }

    public function test画像の投稿()
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

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('コメントを保存しました。', (string)$response->getBody());
        $this->assertContains('<img src="https://s3-ap-northeast-1.amazonaws.com/slimbbs2/', (string)$response->getBody());
        $this->assertContains('alt="file_upload_test"', (string)$response->getBody());
    }

    public function test投稿の削除()
    {
        $this->postReply();

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '2']);
        $this->assertEquals(303, $response->getStatusCode());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('comment_test', (string)$response->getBody());
        $this->assertContains('コメントを削除しました。', (string)$response->getBody());
    }

    public function testCSRF削除エラー()
    {
        $this->withMiddleware = true;
        $this->postReply();

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '2']);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);
    }

    public function testスレッドの削除()
    {
        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '1']);
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/thread?thread_id=1', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/');
        $this->assertContains('スレッドは削除されました。', (string)$response->getBody());
    }

    public function testスレッドの削除後IDがずれないか()
    {
        $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '1']);
        $this->runApp('POST', '/', ['comment' => 'thread_test', 'user_id' => '1']);

        $response = $this->runApp('GET', '/thread?thread_id=2');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('thread_test', (string)$response->getBody());
    }

    public function test投稿の削除の失敗()
    {
        $this->postReply();

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '0']);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/');
        $this->assertContains('削除に失敗しました。', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('comment_test', (string)$response->getBody());
        $this->assertNotContains('コメントを削除しました。', (string)$response->getBody());
    }

    public function test匿名で削除不可()
    {
        $_SESSION['user_id'] = null;
        $this->postReply("0");

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '2']);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('comment_test', (string)$response->getBody());
        $this->assertContains('削除に失敗しました。', (string)$response->getBody());
    }

    public function test管理者で削除()
    {
        $this->postReply("0");
        $_SESSION['user_id'] = getenv('ADMIN_ID');

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('削除', (string)$response->getBody());

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '2']);
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Error', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('comment_test', (string)$response->getBody());
        $this->assertContains('コメントを削除しました。', (string)$response->getBody());
    }

    public function test管理者でスレッド削除()
    {
        $_SESSION['user_id'] = getenv('ADMIN_ID');

        $response = $this->runApp('DELETE', '/thread', ['thread_id' => '1', 'comment_id' => '1']);
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertNotContains('Error', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', (string)$response->getHeader('location')[0]);

        $response = $this->runApp('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('削除済み', (string)$response->getBody());
        $this->assertContains('スレッドは削除されました。', (string)$response->getBody());
    }

    public function testそうだねをつける()
    {
        $response = $this->runApp('POST', '/like', ['thread_id' => '1', 'comment_id' => '1'], true);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNotContains('Error', (string)$response->getBody());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('data-like="1"', (string)$response->getBody());
    }

    public function test匿名でそうだねが出ない()
    {
        $_SESSION['user_id'] = null;

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('<input type="submit" value="そうだね">', (string)$response->getBody());
    }

    public function testコメントの編集()
    {
        $response = $this->runApp('PUT', '/thread', ['thread_id' => "1", 'comment_id' => "1", 'comment' => 'comment_test2'], true);
        $this->assertEquals(204, $response->getStatusCode());

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('comment_test2', (string)$response->getBody());
    }

    public function testメッセージ表示()
    {
        $_SESSION['slimFlash'] = [];
        $_SESSION['slimFlash']['Info'][0] = 'スレッドを作成しました';
        $_SESSION['slimFlash']['Error'][0] = 'スレッドは削除されました';

        $response = $this->runApp('GET', '/');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('スレッドを作成しました', (string)$response->getBody());
        $this->assertContains('スレッドは削除されました', (string)$response->getBody());
    }

    public function test投稿フォームのユーザー名表示()
    {
        $anony_user = <<<ANONY_USER
<div class="comment_form__header__username">
            匿名ユーザー
        </div>
ANONY_USER;

        $test_user = <<<TEST_USER
<div class="comment_form__header__username">
            @testuser
        </div>
TEST_USER;

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<img class="comment_form__header__wrap__icon" src="http://via.placeholder.com/48x48" width="48" height="48" alt="testuser">', (string)$response->getBody());
        $this->assertContains($test_user, (string)$response);

        $_SESSION['user_id'] = null;

        $response = $this->runApp('GET', '/thread?thread_id=1');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<img class="comment_form__header__wrap__icon" src="/assets/img/anonymous-user.svg" width="48" height="48" alt="匿名ユーザー">', (string)$response->getBody());
        $this->assertContains($anony_user, (string)$response->getBody());
    }
}
