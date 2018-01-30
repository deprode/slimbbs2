<?php

namespace Tests\Unit\Responder;

use App\Responder\QuitResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class QuitResponderTest extends TestCase
{
    private $view;

    public function setUp()
    {
        $router = $this->createMock(Router::class);
        $router->expects($this->any())->method('pathFor')->willReturn('/');
        $this->view = new Twig(__DIR__ . '/../../../templates');
        $this->view->addExtension(new TwigExtension($router, __DIR__ . '/../../../templates'));
    }

    public function testQuit()
    {
        $data = ['name' => 'key'];
        $response = new Response();
        $response = $response->write($data['name']);

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new QuitResponder($twig);
        $response = $responder->quit(new Response(), $data);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('key', (string)$response->getBody());
    }

    public function testAuthInvalid()
    {
        $responder = new QuitResponder($this->view);
        $response = $responder->authInvalid(new Response());

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
    }
}
