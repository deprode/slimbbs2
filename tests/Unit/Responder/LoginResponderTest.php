<?php

namespace Tests\Unit\Responder;

use App\Service\MessageService;
use App\Responder\LoginResponder;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;
use Slim\Http\Response;

class LoginResponderTest extends TestCase
{
    private $message;

    public function setUp()
    {
        parent::setUp();
        $_SESSION = [];
        $this->message = new MessageService(new Messages());
    }

    public function testSuccess()
    {
        $responder = new LoginResponder($this->message);
        $response = $responder->success(new Response(), '/redirect');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
    }

    public function testOAuthFailed()
    {
        $responder = new LoginResponder($this->message);
        $response = $responder->oAuthFailed(new Response());

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
        $this->assertContains('ログインに失敗しました。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testSaveFailed()
    {
        $responder = new LoginResponder($this->message);
        $response = $responder->saveFailed(new Response());

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
        $this->assertContains('ユーザー情報の保存に失敗しました。', $_SESSION['slimFlash']['Error'][0]);
    }

}
