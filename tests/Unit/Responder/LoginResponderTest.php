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
        $responder = new LoginResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->success(new Response(), '/redirect');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/redirect', $response->getHeader('Location')[0]);
    }

    public function testOAuthFailed()
    {
        $responder = new LoginResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->oAuthFailed(new Response());

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertContains('ログインに失敗しました。時間をおいてから、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testSaveFailed()
    {
        $responder = new LoginResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->saveFailed(new Response());

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains('ユーザー情報の保存に失敗しました。管理責任者までお問い合わせください。', (string)$response->getBody());
    }

}
