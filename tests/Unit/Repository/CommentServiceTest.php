<?php

namespace Test\Unit;

use App\Model\Comment;
use App\Model\CommentRead;
use App\Model\Sort;
use App\Repository\CommentService;
use App\Service\DatabaseService;
use PHPUnit\Framework\TestCase;

class CommentServiceTest extends TestCase
{
    private $comment;
    private $error_comment;
    private $data;

    protected function setUp()
    {
        parent::setUp();

        $comment_data = new CommentRead();
        $comment_data->comment_id = 1;
        $comment_data->user_id = 1;
        $comment_data->created_at = '2017-12-06 13:42:28';
        $comment_data->comment = 'sample comment test';
        $comment_data->photo_url = 'http://via.placeholder.com/32x32';
        $comment_data->user_name = 'testuser';
        $comment_data->user_image_url = 'http://via.placeholder.com/48x48';

        $this->data = [$comment_data];

        $comment_limit = 2;

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->willReturn($this->data);
        $dbs->expects($this->any())->method('execute')->willReturn(1);
        $dbs->expects($this->any())->method('lastInsertId')->willReturn(10);
        $this->comment = new CommentService($dbs, $comment_limit);

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->will($this->throwException(new \PDOException()));
        $dbs->expects($this->any())->method('execute')->will($this->throwException(new \PDOException()));
        $this->error_comment = new CommentService($dbs, $comment_limit);
    }

    /**
     * @expectedException \App\Exception\FetchFailedException
     */
    public function testGetComments()
    {
        $comments = $this->comment->getComments(1, new Sort('desc'));
        $this->assertEquals($this->data, $comments);

        $this->error_comment->getComments(1, new Sort('desc'));
    }

    /**
     * @expectedException \App\Exception\FetchFailedException
     */
    public function testGetCommentsByUser()
    {
        $comments = $this->comment->getCommentsByUser(1, true);
        $this->assertEquals($this->data, $comments);

        $this->error_comment->getCommentsByUser(1, true);
    }

    /**
     * @expectedException \App\Exception\FetchFailedException
     */
    public function testSearchComments()
    {
        $comments = $this->comment->searchComments('comment');
        $this->assertEquals($this->data, $comments);

        $this->error_comment->searchComments(1);
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

        $this->error_comment->saveThread($comment);
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
        $comment->photo_url = 'https://examnple.com/picture';
        $this->assertEquals(10, $this->comment->saveComment($comment));

        $this->error_comment->saveComment($comment);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testUpdateComment()
    {
        $this->assertEquals(1, $this->comment->updateComment(1, 1, 'hoge'));
        $this->error_comment->updateComment(1, 1, 'hoge');
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteComment()
    {
        $this->assertTrue($this->comment->deleteComment(1, 1));
        $this->error_comment->deleteComment(1, 1);
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteCommentByAdmin()
    {
        $this->assertTrue($this->comment->deleteCommentByAdmin(1, 1));
        $this->error_comment->deleteCommentByAdmin(1, 1);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testAddLike()
    {
        $this->assertTrue($this->comment->addLike(1, 1));
        $this->error_comment->addLike(1, 1);
    }
}
