<?php

namespace Tests\Unit\Responder;

use App\Responder\CommentsResponder;
use Slim\Http\Response;

class CommentsResponderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $_SESSION = [];
    }

    public function testFailed()
    {
        $responder = new CommentsResponder();
        $response = $responder->failed(new Response());
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testInvalid()
    {
        $responder = new CommentsResponder();
        $response = $responder->invalid(new Response());
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testSuccess()
    {
        $responder = new CommentsResponder();
        $response = $responder->success(new Response(), ['success']);
        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $body->rewind();
        $dataJson = $body->getContents();
        $this->assertEquals('["success"]', $dataJson);
    }
}
