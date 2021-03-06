<?php

namespace Tests\Unit\Responder;

use App\Responder\HomeResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class HomeResponderTest extends TestCase
{
    private $view;

    public function setUp()
    {
        $router = $this->createMock(Router::class);
        $router->expects($this->any())->method('pathFor')->willReturn('/');
        $this->view = new Twig(__DIR__ . '/../../../templates');
        $this->view->addExtension(new TwigExtension($router, __DIR__ . '/../../../templates'));
    }

    public function testIndex()
    {
        $response = new Response();
        $response = $response->write('1, 2');

        // twig独自拡張がいろいろ依存しているので、Mockを作っている
        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new HomeResponder($twig);
        $response = $responder->index(new Response(), ['threads' => ['1', '2']]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('1, 2', (string)$response->getBody());
    }

    public function testFetchFailed()
    {
        $responder = new HomeResponder($this->view);
        $response = $responder->fetchFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('スレッドの取得に失敗しました。しばらく時間をおいてから、再度読み込んでください。', (string)$response->getBody());
    }

}
