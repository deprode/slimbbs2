<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Model\Comment;

class CommentTest extends TestCase
{
    public function testSetAndGet()
    {
        $comment = new Comment();
        $comment->comment = 'aaaa';
        $this->assertEquals('aaaa', $comment->comment);
    }

    public function testGetNull()
    {
        $comment = new Comment();
        $comment->hoge = 'hoge';
        $this->assertEquals(null, $comment->hoge);
    }

    public function testIsSet()
    {
        $comment = new Comment();
        $this->assertEquals(true, isset($comment->comment));
        $this->assertEquals(false, $comment->hoge);
    }
}