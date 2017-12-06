<?php

namespace Test\Unit;

use App\Domain\CommentService;
use App\Domain\DatabaseService;
use App\Model\Comment;

class CommentServiceTest extends \PHPUnit_Framework_TestCase
{
    private $comment;
    private $data;

    protected function setUp()
    {
        parent::setUp();

        $this->data = [
            'comment_id'     => 1,
            'user_id'        => 1,
            'created_at'     => '2017-12-06 13:42:28',
            'user_name'      => 'testuser',
            'user_image_url' => 'http://via.placeholder.com/64x64'
        ];

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->willReturn($this->data);
        $dbs->expects($this->any())->method('execute')->willReturn(1);
        $this->comment = new CommentService($dbs);
    }

    public function testGetComments()
    {
        $comments = $this->comment->getComments(1);
        $this->assertEquals($this->data, $comments);
    }

    public function testSaveThread()
    {
        $comment = new Comment();
        $comment->user_id = 1;
        $comment->comment = 'aaaa';
        $this->assertEquals(1, $this->comment->saveThread($comment));
    }

    public function testSaveComment()
    {
        $comment = new Comment();
        $comment->thread_id = 1;
        $comment->user_id = 1;
        $comment->comment = 'aaaa';
        $this->assertEquals(1, $this->comment->saveComment($comment));
    }

    public function testDeleteComment()
    {
        $this->assertTrue($this->comment->deleteComment(1, 1));
    }

    public function testDeleteCommentByAdmin()
    {
        $this->assertTrue($this->comment->deleteCommentByAdmin(1, 1));
    }
}
