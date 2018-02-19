<?php

namespace Tests\Unit;

use App\Service\MessageService;
use App\Responder\SaveResponder;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;
use Slim\Http\Response;

class SaveResponderTest extends TestCase
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
        $responder = new SaveResponder($this->message);
        $response = $responder->csrfInvalid(new Response());

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
        $this->assertContains('失敗しました。元の画面から、もう一度やり直してください。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testInvalid()
    {
        $responder = new SaveResponder($this->message);
        $response = $responder->invalid(new Response(), '/invalid');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/invalid', $response->getHeader('Location')[0]);
        $this->assertContains('投稿に失敗しました。投稿内容を見直して、もう一度やり直してください。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testUploadFailed()
    {
        $responder = new SaveResponder($this->message);
        $response = $responder->uploadFailed(new Response(), '/upload_failed');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/upload_failed', $response->getHeader('Location')[0]);
        $this->assertContains('画像のアップロードに失敗しました。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testSaveFailed()
    {
        $responder = new SaveResponder($this->message);
        $response = $responder->saveFailed(new Response(), '/failed');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/failed', $response->getHeader('Location')[0]);
        $this->assertContains('保存に失敗しました。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testSaveSuccess()
    {
        $responder = new SaveResponder($this->message);
        $response = $responder->saved(new Response(), '/success');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/success', $response->getHeader('location'));
    }
}
