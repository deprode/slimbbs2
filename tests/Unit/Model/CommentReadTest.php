<?php

namespace Tests\Unit\Model;

use App\Model\CommentRead;
use PHPUnit\Framework\TestCase;

class CommentReadTest extends TestCase
{
    public function testCreatedAtStr()
    {
        $comment = new CommentRead();
        $comment->created_at = '2018-01-01 00:00:00';
        $result = $comment->createdAtStr();

        $this->assertEquals('1月1日', $result);
    }

    public function testToArray()
    {
        $comment = new CommentRead();
        $comment->comment_id = 1;
        $comment->thread_id = 1;
        $comment->user_id = 10;
        $comment->like_count = 100;
        $comment->photo_url = '';
        $comment->created_at = '2018-10-10 10:10:10';
        $comment->updated_at = '2018-11-11 11:11:11';
        $comment->user_name = 'testuser';
        $comment->user_image_url = 'http://example.com/icon';
        $array = $comment->toArray();

        $this->assertInternalType('array', $array);
        $this->assertEquals([
            'comment_id'     => 1,
            'thread_id'      => 1,
            'user_id'        => 10,
            'like_count'     => 100,
            'comment'        => null,
            'photo_url'      => '',
            'created_at'     => '2018-10-10 10:10:10',
            'updated_at'     => '2018-11-11 11:11:11',
            'user_name'      => 'testuser',
            'user_image_url' => 'http://example.com/icon',
        ], $array);
    }

    public function test__toString()
    {
        $comment = new CommentRead();
        $comment->comment_id = 123;
        $comment->thread_id = 123;
        $comment->user_id = 123;
        $comment->like_count = 123;
        $comment->comment = 'sample comment';
        $comment->photo_url = 'http://example.com';
        $comment->created_at = '2017-12-31';
        $comment->updated_at = '2018-01-01';
        $comment->user_name = 'testuser';
        $comment->user_image_url = 'http://example.com/icon';

        $string = <<<TOSTRING
comment_id: 123
thread_id: 123
user_id: 123
like_count: 123
comment: sample comment
photo_url: http://example.com
created_at: 2017-12-31
updated_at: 2018-01-01
user_name: testuser
user_image_url: http://example.com/icon
TOSTRING;

        $this->assertEquals($string, (string)$comment);
        $this->assertInternalType('string', (string)$comment);
    }
}
