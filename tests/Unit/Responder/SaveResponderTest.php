<?php

namespace Tests\Unit;

use App\Responder\SaveResponder;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Slim\Views\Twig;

class SaveResponderTest extends TestCase
{
    public function testSaveFailed()
    {
        $responder = new SaveResponder(new Twig(__DIR__ . '/../../../templates'));
        $response = $responder->invalid(new Response(), '/invalid');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('/invalid', (string)$response->getBody());
    }

    public function testSaveSuccess()
    {
        $responder = new SaveResponder(new Twig(''));
        $response = $responder->saved(new Response(), '/success');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/success', $response->getHeader('location'));
    }
}
