<?php

namespace Tests\Unit\Domain;

use App\Domain\LikeFilter;
use App\Exception\SaveFailedException;
use App\Repository\CommentService;
use Slim\Http\Request;

class LikeFilterTest extends \PHPUnit_Framework_TestCase
{
    private $comment;

    protected function setUp()
    {
        parent::setUp();

        $this->comment = $this->createMock(CommentService::class);
    }

    /**
     * @expectedException \App\Exception\NotAllowedException
     */
    public function testIsXHRError()
    {
        $request = $this->createMock(Request::class);

        $this->filter = new LikeFilter($this->comment);
        $this->filter->update($request);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testValidatioinError()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getAttribute')->willReturn([
            'has_errors' => ["error"],
        ]);

        $this->filter = new LikeFilter($this->comment);
        $this->filter->update($request);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testUpdateError()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
        ]);
        $this->comment->method('addLike')->willThrowException(new SaveFailedException());

        $this->filter = new LikeFilter($this->comment);
        $this->filter->update($request);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testCommentNotFoundError()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
        ]);
        $this->comment->method('addLike')->willReturn(0);

        $this->filter = new LikeFilter($this->comment);
        $this->filter->update($request);
    }

    public function testUpdate()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
        ]);
        $this->comment->method('addLike')->willReturn(1);

        $this->filter = new LikeFilter($this->comment);
        $this->filter->update($request);
    }
}
