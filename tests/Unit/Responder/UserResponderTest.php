<?php

namespace Tests\Unit\Responder;

use App\Responder\UserResponder;
use App\Service\MessageService;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class UserResponderTest extends TestCase
{
    private $view;
    private $message;

    protected function setUp()
    {
        parent::setUp();
        $_SESSION = [];

        $router = $this->createMock(Router::class);
        $router->expects($this->any())->method('pathFor')->willReturn('/');
        $this->view = new Twig(__DIR__ . '/../../../templates');
        $this->view->addExtension(new TwigExtension($router, __DIR__ . '/../../../templates'));

        $this->message = new MessageService(new Messages());
    }

    public function testNameEmpty()
    {
        $responder = new UserResponder($this->view, $this->message);
        $response = $responder->nameEmpty(new Response());

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
    }

    public function testIndex()
    {
        $response = new Response();
        $response = $response->write('1, 2');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new UserResponder($twig, $this->message);
        $response = $responder->index(new Response(), ['comments' => ['1', '2']]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('1, 2', (string)$response->getBody());
    }

    public function testFetchFailed()
    {
        $responder = new UserResponder($this->view, $this->message);
        $response = $responder->fetchFailed(new Response());

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeader('Location')[0]);
        $this->assertContains('このユーザーは存在しません。', $_SESSION['slimFlash']['Error'][0]);
        $this->assertNotContains('このユーザーは存在しません。', (string)$response->getBody());
    }
}
