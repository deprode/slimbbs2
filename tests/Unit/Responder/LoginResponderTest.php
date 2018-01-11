<?php

namespace Tests\Unit\Responder;

use App\Responder\LoginResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Views\Twig;

class LoginResponderTest extends TestCase
{
    public function testSuccess()
    {
        $twig = $this->createMock(Twig::class);
        $responder = new LoginResponder($twig);
        $response = $responder->success(new Response(), '/redirect');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
    }

    public function testOAuthFailed()
    {
        $response = new Response();
        $response = $response->write('ログインに失敗しました。時間をおいてから、もう一度やり直してください。');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new LoginResponder($twig);
        $response = $responder->oAuthFailed(new Response());

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertContains('ログインに失敗しました。時間をおいてから、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testSaveFailed()
    {
        $response = new Response();
        $response = $response->write('ユーザー情報の保存に失敗しました。管理責任者までお問い合わせください。');

        $twig = $this->createMock(Twig::class);
        $twig->expects($this->any())->method('render')->willReturn($response);

        $responder = new LoginResponder($twig);
        $response = $responder->saveFailed(new Response());

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains('ユーザー情報の保存に失敗しました。管理責任者までお問い合わせください。', (string)$response->getBody());
    }

}
