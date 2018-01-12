<?php

namespace Tests\Unit;

use App\Responder\SaveResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Views\Twig;

class SaveResponderTest extends TestCase
{

    public function testCsrfInvalid()
    {
        $responder = new SaveResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->csrfInvalid(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('投稿に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testInvalid()
    {
        $responder = new SaveResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->invalid(new Response(), '/upload_failed');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('/upload_failed', (string)$response->getBody());
        $this->assertContains('投稿に失敗しました。投稿内容を見直して、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testUploadFailed()
    {
        $responder = new SaveResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->uploadFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('画像のアップロードに失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testSaveFailed()
    {
        $responder = new SaveResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->saveFailed(new Response());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('保存に失敗しました。元の画面から、もう一度やり直してください。', (string)$response->getBody());
    }

    public function testSaveSuccess()
    {
        $responder = new SaveResponder(new Twig(''));
        $response = $responder->saved(new Response(), '/success');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/success', $response->getHeader('location'));
    }
}
