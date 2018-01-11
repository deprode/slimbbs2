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
        $response = new Response();
        $response = $response->write('削除に失敗しました。元の画面から、もう一度やり直してください。');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new DeleteResponder($twig);
        $response = $responder->csrfInvalid(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testInvalid()
    {
        $response = new Response();
        $response = $response->write('削除に失敗しました。元の画面から、もう一度やり直してください。');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new DeleteResponder($twig);
        $response = $responder->invalid(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testDeleteFailed()
    {
        $response = new Response();
        $response = $response->write('削除に失敗しました。元の画面から、もう一度やり直してください。');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new DeleteResponder($twig);
        $response = $responder->deleteFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testDeleted()
    {
        $twig = $this->createMock(Twig::class);
        $responder = new DeleteResponder($twig);
        $response = $responder->deleted(new Response(), '/redirect');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
    }
}
