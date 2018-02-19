<?php

namespace Test\Unit;

use App\Service\CommentService;
use App\Service\DatabaseService;
use App\Model\Comment;
use App\Model\Sort;

class CommentServiceTest extends \PHPUnit_Framework_TestCase
{
    private $comment;
    private $error_comment;
    private $data;

    protected function setUp()
    {
        parent::setUp();

        $this->data = [
            [
                'comment_id'     => 1,
                'user_id'        => 1,
                'created_at'     => '2017-12-06 13:42:28',
                'comment'        => 'sample comment test',
                'photo_url'      => 'http://via.placeholder.com/32x32',
                'user_name'      => 'testuser',
                'user_image_url' => 'http://via.placeholder.com/48x48'
            ]
        ];

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->willReturn($this->data);
        $dbs->expects($this->any())->method('execute')->willReturn(1);
        $this->comment = new CommentService($dbs);

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->will($this->throwException(new \PDOException()));
        $dbs->expects($this->any())->method('execute')->will($this->throwException(new \PDOException()));
        $this->error_comment = new CommentService($dbs);
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
        $comments = $this->comment->getCommentsByUser(1);
        $this->assertEquals($this->data, $comments);

        $this->error_comment->getCommentsByUser(1);
    }

    public function testConvertTime()
    {
        $this->comment = null;

        $data = '3日前';

        $this->comment = $this->getMockBuilder(CommentService::class)
            ->setMethods(['timeToString'])
            ->setConstructorArgs([$this->createMock(DatabaseService::class)])
            ->getMock();

        $this->comment->expects($this->any())->method('timeToString')->willReturn($data);

        $comments = [
            0 => ['created_at' => '2018-01-01 00:00:00']
        ];

        $result = $this->comment->convertTime($comments);

        $this->assertEquals([0 => ['created_at' => '3日前']], $result);
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
        $this->assertEquals(1, $this->comment->saveComment($comment));

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
