<?php

namespace Tests\Unit\Domain;

use App\Domain\CommentDeleteFilter;
use App\Exception\DeleteFailedException;
use App\Repository\CommentService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class CommentDeleteFilterTest extends TestCase
{
    private $comment;

    protected function setUp()
    {
        parent::setUp();

        $this->comment = $this->createMock(CommentService::class);
    }

    /**
     * @expectedException \App\Exception\CsrfException
     */
    public function testCsrfError()
    {
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'csrf_status' => "bad_request",
        ]);

        $this->filter = new CommentDeleteFilter($this->comment);
        $this->filter->delete($request);
    }

    /**
     * @expectedException \App\Exception\ValidationException
     */
    public function testValidationError()
    {
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'has_errors' => ["error"],
        ]);

        $this->filter = new CommentDeleteFilter($this->comment);
        $this->filter->delete($request);
    }

    /**
     * @expectedException \App\Exception\NotAllowedException
     */
    public function testAuthError()
    {
        $request = $this->createMock(Request::class);

        $this->filter = new CommentDeleteFilter($this->comment);
        $this->filter->delete($request);
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteError()
    {
        $this->comment->method('deleteComment')->willThrowException(new DeleteFailedException());
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'userId' => "1",
        ]);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
        ]);

        $this->filter = new CommentDeleteFilter($this->comment);
        $this->filter->delete($request);
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteAdminError()
    {
        $this->comment->method('deleteCommentByAdmin')->willThrowException(new DeleteFailedException());
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'userId'  => "100",
            'isAdmin' => "1",
        ]);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
        ]);

        $this->filter = new CommentDeleteFilter($this->comment);
        $this->filter->delete($request);
    }

    public function testDelete()
    {
        $this->comment->method('deleteComment')->willReturn(true);
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'userId' => "100",
        ]);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
        ]);

        $this->filter = new CommentDeleteFilter($this->comment);
        $this->filter->delete($request);
    }
}
