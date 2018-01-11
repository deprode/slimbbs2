<?php

namespace Tests\Unit\Responder;

use App\Responder\HomeResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Views\Twig;

class HomeResponderTest extends TestCase
{
    public function testIndex()
    {
        $response = new Response();
        $response = $response->withStatus(200);
        $response = $response->write('1, 2');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new HomeResponder($twig);
        $response = $responder->index(new Response(), ['threads' => ['1', '2']]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('1, 2', (string)$response->getBody());
    }

    public function testFetchFailed()
    {
        $response = new Response();
        $response = $response->withStatus(400);
        $response = $response->write('スレッドの取得に失敗しました。元の画面から、もう一度やり直してください。');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new HomeResponder($twig);
        $response = $responder->fetchFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('スレッドの取得に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

}
