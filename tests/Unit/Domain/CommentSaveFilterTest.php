<?php

namespace Tests\Unit\Domain;

use App\Domain\CommentSaveFilter;
use App\Exception\SaveFailedException;
use App\Exception\UploadFailedException;
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
        $this->request = $this->createMock(Request::class);
        $this->request->method('getAttributes')->willReturn([
            'csrf_status' => "bad_request",
        ]);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($this->request);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testAuthError()
    {
        $this->request = $this->createMock(Request::class);
        $this->request->method('getAttributes')->willReturn([
            'has_errors' => ["error"],
        ]);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($this->request);
    }

    /**
     * @expectedException \App\Exception\UploadFailedException
     */
    public function testUploadError()
    {
        $this->storage->method('upload')->willThrowException(new UploadFailedException());
        $this->request = $this->createMock(Request::class);
        $this->request->method('getUploadedFiles')->willReturn([
            'picture' => new UploadedFile('file'),
        ]);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($this->request);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testSaveError()
    {
        $this->comment->method('saveComment')->willThrowException(new SaveFailedException());
        $this->request = $this->createMock(Request::class);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($this->request);
    }

    public function testSave()
    {
        $this->auth->method('getUserId')->willReturn(1);
        $this->request = $this->createMock(Request::class);
        $this->request->method('getParsedBody')->willReturn([
            'thread_id' => 1,
            'user_id'   => 1,
            'comment'   => 'Comment',
        ]);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment, $this->auth);
        $this->filter->save($this->request);
    }
}
