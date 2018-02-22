<?php

namespace Tests\Unit\Domain;

use App\Domain\SaveFilter;
use App\Exception\SaveFailedException;
use App\Repository\CommentService;
use App\Service\AuthService;
use Slim\Http\Request;

class SaveFilterTest extends \PHPUnit_Framework_TestCase
{
    private $comment;
    private $auth;
    private $comment_data;

    protected function setUp()
    {
        parent::setUp();

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

        $this->filter = new SaveFilter($this->auth, $this->comment);
        $this->filter->save($this->request);
    }

    /**
     * @expectedException \App\Exception\NotAllowedException
     */
    public function testAuthError()
    {
        $this->auth->method('equalUser')->willReturn(false);
        $this->request = $this->createMock(Request::class);

        $this->filter = new SaveFilter($this->auth, $this->comment);
        $this->filter->save($this->request);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testSaveError()
    {
        $this->comment->method('saveThread')->willThrowException(new SaveFailedException());
        $this->auth->method('equalUser')->willReturn(true);
        $this->request = $this->createMock(Request::class);

        $this->filter = new SaveFilter($this->auth, $this->comment);
        $this->filter->save($this->request);
    }

    public function testSave()
    {
        $this->auth->method('equalUser')->willReturn(true);
        $this->request = $this->createMock(Request::class);
        $this->request->method('getParsedBody')->willReturn([
            'user_id' => 1,
            'comment' => 'Comment',
        ]);

        $this->filter = new SaveFilter($this->auth, $this->comment);
        $this->filter->save($this->request);
    }
}
