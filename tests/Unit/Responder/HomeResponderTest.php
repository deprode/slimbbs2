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
        $responder = new HomeResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->fetchFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('スレッドの取得に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

}
