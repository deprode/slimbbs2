<?php

namespace Tests\Unit\Responder;

use App\Responder\DeleteResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Views\Twig;

class DeleteResponderTest extends TestCase
{
    public function testCsrfInvalid()
    {
        $responder = new DeleteResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->csrfInvalid(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testInvalid()
    {
        $responder = new DeleteResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->invalid(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testDeleteFailed()
    {
        $responder = new DeleteResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->deleteFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testDeleted()
    {
        $responder = new DeleteResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->deleted(new Response(), '/redirect');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
    }
}
