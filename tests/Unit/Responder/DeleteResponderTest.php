<?php

namespace Tests\Unit\Responder;

use App\Responder\DeleteResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class DeleteResponderTest extends TestCase
{
    private $view;

    public function setUp()
    {
        $router = $this->createMock(Router::class);
        $router->expects($this->any())->method('pathFor')->willReturn('/');
        $this->view = new Twig(__DIR__ . '/../../../templates');
        $this->view->addExtension(new TwigExtension($router, __DIR__ . '/../../../templates'));
    }

    public function testCsrfInvalid()
    {
        $responder = new DeleteResponder($this->view);
        $response = $responder->csrfInvalid(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testInvalid()
    {
        $responder = new DeleteResponder($this->view);
        $response = $responder->invalid(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testDeleteFailed()
    {
        $responder = new DeleteResponder($this->view);
        $response = $responder->deleteFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('削除に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testDeleted()
    {
        $responder = new DeleteResponder($this->view);
        $response = $responder->deleted(new Response(), '/redirect');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
    }
}
