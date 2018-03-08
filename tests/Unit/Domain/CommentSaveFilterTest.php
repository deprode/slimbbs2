<?php

namespace Tests\Unit\Domain;

use App\Domain\CommentSaveFilter;
use App\Exception\SaveFailedException;
use App\Exception\UploadFailedException;
use App\Model\Sort;
use App\Repository\CommentService;
use App\Service\AuthService;
use App\Service\StorageService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Slim\Http\UploadedFile;

class CommentSaveFilterTest extends TestCase
{
    private $comment;
    private $storage;
    private $auth;

    protected function setUp()
    {
        parent::setUp();

        $this->storage = $this->createMock(StorageService::class);
        $this->comment = $this->createMock(CommentService::class);
        $this->auth = $this->createMock(AuthService::class);
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

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($request);
    }

    /**
     * @expectedException \App\Exception\ValidationException
     */
    public function testAuthError()
    {
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'has_errors' => ["error"],
        ]);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($request);
    }

    /**
     * @expectedException \App\Exception\UploadFailedException
     */
    public function testUploadError()
    {
        $this->storage->method('upload')->willThrowException(new UploadFailedException());
        $request = $this->createMock(Request::class);
        $request->method('getUploadedFiles')->willReturn([
            'picture' => new UploadedFile('file'),
        ]);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($request);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testSaveError()
    {
        $this->comment->method('saveComment')->willThrowException(new SaveFailedException());
        $request = $this->createMock(Request::class);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($request);
    }

    public function testSave()
    {
        $this->auth->method('getUserId')->willReturn(1);
        $request = $this->createMock(Request::class);
        $request->method('getParsedBody')->willReturn([
            'thread_id' => 1,
            'user_id'   => 1,
            'comment'   => 'Comment',
        ]);
        $this->comment->method('saveComment')->willReturn(10);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $new_comment_id = $this->filter->save($request);
        $this->assertEquals(10, $new_comment_id);
    }

    public function testGenerateUrl()
    {
        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $failed = $this->filter->generateUrl('/base', new Sort('desc'), 0, 0);
        $this->assertEquals('/base', $failed);

        $success = $this->filter->generateUrl('/base', new Sort('desc'), 5, 10);
        $this->assertEquals('/base?thread_id=5#c10', $success);

        $success = $this->filter->generateUrl('/base', new Sort('asc'), 5, 10);
        $this->assertEquals('/base?thread_id=5&sort=asc#c10', $success);
    }
}
