<?php

namespace Tests\Unit\Responder;

use App\Domain\MessageService;
use App\Responder\QuitedResponder;
use Slim\Flash\Messages;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class QuitedResponderTest extends \PHPUnit_Framework_TestCase
{
    private $view;
    private $message;

    public function setUp()
    {
        parent::setUp();
        $_SESSION = [];
        $this->message = new MessageService(new Messages());

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

        $responder = new QuitedResponder($twig, $this->message);
        $response = $responder->quited(new Response());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('退会しました', (string)$response->getBody());
    }

    public function testDeleteFailed()
    {
        $responder = new QuitedResponder($this->view, $this->message);
        $response = $responder->deleteFailed(new Response());

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/quit', $response->getHeader('Location')[0]);
        $this->assertContains('アカウント削除に失敗しました。', $_SESSION['slimFlash']['Error'][0]);
    }

    public function testRedirect()
    {
        $responder = new QuitedResponder($this->view, $this->message);
        $response = $responder->redirect(new Response());

        $this->assertEquals(303, $response->getStatusCode());
    }
}
