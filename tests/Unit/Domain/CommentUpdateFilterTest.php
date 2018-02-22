<?php

namespace Tests\Unit\Domain;

use App\Domain\CommentUpdateFilter;
use App\Exception\SaveFailedException;
use App\Repository\CommentService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class CommentUpdateFilterTest extends TestCase
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

        $this->filter = new CommentUpdateFilter($this->comment);
        $this->filter->update($request);
    }

    /**
     * @expectedException \App\Exception\ValidationException
     */
    public function testValidatioinError()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getAttribute')->willReturn([
            'has_errors' => ["error"],
        ]);

        $this->filter = new CommentUpdateFilter($this->comment);
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
            'comment'    => 'testComment'
        ]);
        $this->comment->method('updateComment')->willThrowException(new SaveFailedException());

        $this->filter = new CommentUpdateFilter($this->comment);
        $this->filter->update($request);
    }

    public function testUpdate()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
            'comment'    => 'testComment'
        ]);

        $this->filter = new CommentUpdateFilter($this->comment);
        $this->filter->update($request);
    }
}
