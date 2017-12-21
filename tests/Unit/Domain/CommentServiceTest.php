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
            'comment'        => 'sample comment test',
            'user_name'      => 'testuser',
            'user_image_url' => 'http://via.placeholder.com/64x64'
        ];

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->at(0))->method('fetchAll')->willReturn($this->data);
        $dbs->expects($this->at(1))->method('fetchAll')->will($this->throwException(new \PDOException()));
        $dbs->expects($this->at(0))->method('execute')->willReturn(1);
        $dbs->expects($this->at(1))->method('execute')->will($this->throwException(new \PDOException()));
        $this->comment = new CommentService($dbs);
    }

    /**
     * @expectedException \App\Exception\FetchFailedException
     */
    public function testGetComments()
    {
        $comments = $this->comment->getComments(1);
        $this->assertEquals($this->data, $comments);

        $this->comment->getComments(1);
    }

    /**
     * @expectedException \App\Exception\FetchFailedException
     */
    public function testSearchComments()
    {
        $comments = $this->comment->searchComments('comment');
        $this->assertEquals($this->data, $comments);

        $this->comment->searchComments(1);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testSaveThread()
    {
        $comment = new Comment();
        $comment->user_id = 1;
        $comment->comment = 'aaaa';
        $this->assertEquals(1, $this->comment->saveThread($comment));

        $this->comment->saveThread($comment);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testSaveComment()
    {
        $comment = new Comment();
        $comment->thread_id = 1;
        $comment->user_id = 1;
        $comment->comment = 'aaaa';
        $this->assertEquals(1, $this->comment->saveComment($comment));

        $this->comment->saveComment($comment);
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteComment()
    {
        $this->assertTrue($this->comment->deleteComment(1, 1));
        $this->assertTrue($this->comment->deleteComment(1, 1));
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteCommentByAdmin()
    {
        $this->assertTrue($this->comment->deleteCommentByAdmin(1, 1));
        $this->assertTrue($this->comment->deleteCommentByAdmin(1, 1));
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testAddLike()
    {
        $this->assertTrue($this->comment->addLike(1, 1));
        $this->assertTrue($this->comment->addLike(1, 1));
    }
}
