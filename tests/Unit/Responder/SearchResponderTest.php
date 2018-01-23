<?php

namespace Tests\Unit\Responder;

use App\Responder\SearchResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;


class SearchResponderTest extends TestCase
{
    private $view;

    public function setUp()
    {
        $router = $this->createMock(Router::class);
        $router->expects($this->any())->method('pathFor')->willReturn('/');
        $this->view = new Twig(__DIR__ . '/../../../templates');
        $this->view->addExtension(new TwigExtension($router, __DIR__ . '/../../../templates'));
    }

    public function testShowComments()
    {
        $response = new Response();
        $response = $response->write('query');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new SearchResponder($twig);
        $response = $responder->comments(new Response(), ['query' => 'query']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('query', (string)$response->getBody());
    }

    public function testEmptyQuery()
    {
        $responder = new SearchResponder($this->view);
        $response = $responder->emptyQuery(new Response(), '/');

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testFetchFailed()
    {
        $responder = new SearchResponder($this->view);
        $response = $responder->fetchFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('検索データの取得に失敗しました', (string)$response->getBody());
    }
}
