<?php

namespace Tests\Unit\Domain;

use App\Domain\CommentSaveFilter;
use App\Exception\SaveFailedException;
use App\Exception\UploadFailedException;
use App\Model\Sort;
use App\Repository\CommentService;
use App\Service\StorageService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Slim\Http\UploadedFile;

class CommentSaveFilterTest extends TestCase
{
    private $comment;
    private $storage;

    protected function setUp()
    {
        parent::setUp();

        $this->storage = $this->createMock(StorageService::class);
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

        $this->filter = new CommentSaveFilter($this->storage, $this->comment);
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

        $this->filter = new CommentSaveFilter($this->storage, $this->comment);
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
            'picture' => new UploadedFile(__DIR__ . '/../../data/dummy.png'),
        ]);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment);
        $this->filter->save($request);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testSaveError()
    {
        $this->comment->method('saveComment')->willThrowException(new SaveFailedException());
        $request = $this->createMock(Request::class);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment);
        $this->filter->save($request);
    }

    public function testSave()
    {
        $request = $this->createMock(Request::class);
        $request->method('getParsedBody')->willReturn([
            'thread_id' => 1,
            'user_id'   => 1,
            'comment'   => 'Comment',
        ]);
        $this->comment->method('saveComment')->willReturn(10);

        $this->filter = new CommentSaveFilter($this->storage, $this->comment);
        $new_comment_id = $this->filter->save($request);
        $this->assertEquals(10, $new_comment_id);
    }

    public function testGenerateUrl()
    {
        $filter = new CommentSaveFilter($this->storage, $this->comment);
        $failed = $filter->generateUrl('/base', new Sort('desc'), 0, 0);
        $this->assertEquals('/base', $failed);

        $success = $filter->generateUrl('/base', new Sort('desc'), 5, 10);
        $this->assertEquals('/base?thread_id=5#c10', $success);

        $success = $filter->generateUrl('/base', new Sort('asc'), 5, 10);
        $this->assertEquals('/base?thread_id=5&sort=asc#c10', $success);
    }

    public function testInvalidUploadFile()
    {
        $filter = new CommentSaveFilter($this->storage, $this->comment);

        $method = new \ReflectionMethod(CommentSaveFilter::class, 'invalidUploadFile');
        $method->setAccessible(true);

        $file = new UploadedFile('', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_NO_FILE);
        $this->assertTrue($method->invokeArgs($filter, [$file]));

        $file = new UploadedFile('', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_FORM_SIZE);
        $this->assertTrue($method->invokeArgs($filter, [$file]));

        $file = new UploadedFile('', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_OK);
        $this->assertFalse($method->invokeArgs($filter, [$file]));
    }
}
