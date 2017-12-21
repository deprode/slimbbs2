<?php

namespace Tests\Unit\Responder;

use App\Responder\SearchResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Views\Twig;


class SearchResponderTest extends TestCase
{
    public function testShowComments()
    {
        $response = new Response();
        $response = $response->withStatus(200);
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
        $response = new Response();
        $response = $response->withRedirect('/', 302);

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);


        $responder = new SearchResponder($twig);
        $response = $responder->emptyQuery(new Response(), '/');

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testFetchFailed()
    {
        $response = new Response();
        $response = $response->withStatus(400);
        $response = $response->write('検索データの取得に失敗しました');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);


        $responder = new SearchResponder($twig);
        $response = $responder->fetchFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('検索データの取得に失敗しました', (string)$response->getBody());
    }
}
