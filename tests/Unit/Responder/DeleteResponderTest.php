<?php

namespace Tests\Unit\Responder;

use App\Responder\DeleteResponder;
use App\Service\MessageService;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;
use Slim\Http\Response;

class DeleteResponderTest extends TestCase
{
    private $message;

    public function setUp()
    {
        parent::setUp();
        $_SESSION = [];
        $this->message = new MessageService(new Messages());
    }

    public function testCsrfInvalid()
    {
        $responder = new DeleteResponder($this->message);
        $response = $responder->csrfInvalid(new Response());

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
        $this->assertContains('削除に失敗しました。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testInvalid()
    {
        $responder = new DeleteResponder($this->message);
        $response = $responder->invalid(new Response());

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
        $this->assertContains('削除に失敗しました。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testDeleteFailed()
    {
        $responder = new DeleteResponder($this->message);
        $response = $responder->deleteFailed(new Response(), '/redirect');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
        $this->assertContains('削除に失敗しました。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testDeleted()
    {
        $responder = new DeleteResponder($this->message);
        $response = $responder->deleted(new Response(), '/redirect');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
        $this->assertContains('コメントを削除しました。', $_SESSION['slimFlash']['Info'][0]);
    }
}
