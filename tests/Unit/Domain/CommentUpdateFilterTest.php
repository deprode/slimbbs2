<?php

namespace Tests\Unit\Domain;

use App\Domain\CommentUpdateFilter;
use App\Exception\FetchFailedException;
use App\Exception\SaveFailedException;
use App\Repository\CommentService;
use App\Repository\UserService;
use App\Service\AuthService;
use PHPUnit\Framework\TestCase;
use RKA\Session;
use Slim\Http\Request;

class CommentUpdateFilterTest extends TestCase
{
    private $comment;
    private $auth;
    private $user;
    private $hash;

    protected function setUp()
    {
        parent::setUp();

        $this->comment = $this->createMock(CommentService::class);
        $this->auth = new AuthService(new Session(), 10);
        $this->user = $this->createMock(UserService::class);
        $this->user->expects($this->any())->method('getUserToken')->willReturn('user_hash');

        $this->hash = $this->auth->getUserHash('user_hash');

        $_SESSION = [];
        $_SESSION['oauth_token'] = 'user_hash';
    }

    /**
     * @expectedException \App\Exception\NotAllowedException
     */
    public function testIsXHRError()
    {
        $request = $this->createMock(Request::class);

        $this->filter = new CommentUpdateFilter($this->comment, $this->auth, $this->user);
        $this->filter->update($request);
    }

    /**
     * @expectedException \App\Exception\ValidationException
     */
    public function testValidationError()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getAttribute')->willReturn([
            'has_errors' => ["error"],
        ]);

        $this->filter = new CommentUpdateFilter($this->comment, $this->auth, $this->user);
        $this->filter->update($request);
    }

    /**
     * @expectedException \App\Exception\ValidationException
     */
    public function testNoHashError()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
            'user_id'    => '1',
            'comment'    => 'testComment',
            'user_hash'  => 'dummy_hash'
        ]);
        $this->comment->method('updateComment')->willThrowException(new SaveFailedException());

        $this->filter = new CommentUpdateFilter($this->comment, $this->auth, $this->user);
        $this->filter->update($request);
    }

    /**
     * @expectedException \App\Exception\ValidationException
     */
    public function testNoTokenError()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
            'user_id'    => '1',
            'comment'    => 'testComment',
            'user_hash'  => 'dummy_hash'
        ]);
        $this->user->expects($this->any())->method('getUserToken')->willThrowException(new FetchFailedException());

        $this->filter = new CommentUpdateFilter($this->comment, $this->auth, $this->user);
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
            'user_id'    => '1',
            'comment'    => 'testComment',
            'user_hash'  => $this->hash
        ]);
        $this->comment->method('updateComment')->willThrowException(new SaveFailedException());

        $this->filter = new CommentUpdateFilter($this->comment, $this->auth, $this->user);
        $this->filter->update($request);
    }

    /**
     * 何らかの原因でSQLが条件に一致せず、コメントの更新がない場合
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testUpdateFailed()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
            'user_id'    => '1',
            'comment'    => 'testComment',
            'user_hash'  => $this->hash
        ]);
        $this->comment->method('updateComment')->willReturn(0);

        $this->filter = new CommentUpdateFilter($this->comment, $this->auth, $this->user);
        $this->filter->update($request);
    }

    public function testUpdate()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getParsedBody')->willReturn([
            'thread_id'  => 1,
            'comment_id' => 1,
            'user_id'    => '1',
            'comment'    => 'testComment',
            'user_hash'  => $this->hash
        ]);
        $this->comment->method('updateComment')->willReturn(1);

        $this->filter = new CommentUpdateFilter($this->comment, $this->auth, $this->user);
        $this->filter->update($request);
    }
}
