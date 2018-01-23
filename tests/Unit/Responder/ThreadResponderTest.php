<?php

namespace Tests\Unit\Responder;

use App\Responder\ThreadResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class ThreadResponderTest extends TestCase
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

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new ThreadResponder($twig);
        $response = $responder->index(new Response(), ['threads' => ['1', '2']]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('1, 2', (string)$response->getBody());
    }

    public function testInvalid()
    {
        $responder = new ThreadResponder($this->view);
        $response = $responder->invalid(new Response(), '/redirect');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
    }

    public function testFetchFailed()
    {
        $responder = new ThreadResponder($this->view);
        $response = $responder->fetchFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('コメントの取得に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }
}
