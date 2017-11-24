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
        $responder = new SaveResponder(new Twig(''));
        $response = $responder->invalid(new Response(), '/invalid');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('/invalid', $response->getHeader('location'));
    }

    public function testSaveSuccess()
    {
        $responder = new SaveResponder(new Twig(''));
        $response = $responder->saved(new Response(), '/success');

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertContains('/success', $response->getHeader('location'));
    }
}
