<?php

namespace Tests\Unit\Domain;

use App\Domain\AccountDeleteFilter;
use App\Repository\UserService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class AccountDeleteFilterTest extends TestCase
{
    private $user;

    protected function setUp()
    {
        parent::setUp();

        $this->user = $this->createMock(UserService::class);
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

        $this->filter = new AccountDeleteFilter($this->user);
        $this->filter->delete($request);
    }

    /**
     * @expectedException \App\Exception\NotAllowedException
     */
    public function testAuthError()
    {
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'isLoggedIn' => false,
        ]);

        $this->filter = new AccountDeleteFilter($this->user);
        $this->filter->delete($request);
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteError()
    {
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'isLoggedIn' => "1",
        ]);
        $this->user->method('deleteAccount')->willReturn(false);

        $this->filter = new AccountDeleteFilter($this->user);
        $this->filter->delete($request);
    }

    public function testDelete()
    {
        $request = $this->createMock(Request::class);
        $request->method('getAttributes')->willReturn([
            'isLoggedIn' => "1",
        ]);
        $this->user->method('deleteAccount')->willReturn(true);

        $this->filter = new AccountDeleteFilter($this->user);
        $this->filter->delete($request);
    }
}
