<?php

namespace Tests\Unit\Responder;

use App\Responder\QuitedResponder;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class QuitedResponderTest extends \PHPUnit_Framework_TestCase
{
    private $view;

    public function setUp()
    {
        $router = $this->createMock(Router::class);
        $router->expects($this->any())->method('pathFor')->willReturn('/');
        $this->view = new Twig(__DIR__ . '/../../../templates');
        $this->view->addExtension(new TwigExtension($router, __DIR__ . '/../../../templates'));
    }

    public function testQuited()
    {
        $response = new Response();
        $response = $response->write('退会しました');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new QuitedResponder($twig);
        $response = $responder->quited(new Response());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('退会しました', (string)$response->getBody());
    }

    public function testDeleteFailed()
    {
        $responder = new QuitedResponder($this->view);
        $response = $responder->deleteFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('アカウント削除に失敗しました。お手数ですが、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testRedirect()
    {
        $responder = new QuitedResponder($this->view);
        $response = $responder->redirect(new Response());

        $this->assertEquals(303, $response->getStatusCode());
    }
}
