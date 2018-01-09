<?php

namespace Tests\Unit;

use App\Model\Comment;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testToString()
    {
        $comment = new Comment();
        $comment->comment_id = 123;
        $comment->thread_id = 123;
        $comment->user_id = 123;
        $comment->like_count = 123;
        $comment->comment = 'sample comment';
        $comment->photo_url = 'http://example.com';
        $comment->created_at = '2017-12-31';
        $comment->updated_at = null;

        $string = <<<TOSTRING
comment_id: 123
thread_id: 123
user_id: 123
like_count: 123
comment: sample comment
photo_url: http://example.com
created_at: 2017-12-31
updated_at: 
TOSTRING;

        $this->assertEquals($string, (string)$comment);
        $this->assertInternalType('string', (string)$comment);
    }
}