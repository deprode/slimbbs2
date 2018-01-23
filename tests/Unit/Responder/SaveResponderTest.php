<?php

namespace Tests\Unit;

use App\Responder\SaveResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Route;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class SaveResponderTest extends TestCase
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
        $responder = new SaveResponder($this->view);
        $response = $responder->csrfInvalid(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('投稿に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testInvalid()
    {
        $responder = new SaveResponder($this->view);
        $response = $responder->invalid(new Response(), '/upload_failed');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('/upload_failed', (string)$response->getBody());
        $this->assertContains('投稿に失敗しました。投稿内容を見直して、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testUploadFailed()
    {
        $responder = new SaveResponder($this->view);
        $response = $responder->uploadFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('画像のアップロードに失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testSaveFailed()
    {
        $responder = new SaveResponder($this->view);
        $response = $responder->saveFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('保存に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testSaveSuccess()
    {
        $responder = new SaveResponder($this->view);
        $response = $responder->saved(new Response(), '/success');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/success', $response->getHeader('location'));
    }
}
