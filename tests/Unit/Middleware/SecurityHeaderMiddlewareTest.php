<?php

namespace Tests\Unit\Middleware;

use App\Middleware\SecurityHeaderMiddleware;
use Slim\Http\Response;

class SecurityHeaderMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testAppendSecurityHeader()
    {
        $response = new Response();
        $shm = new SecurityHeaderMiddleware();
        $response = $shm->appendSecurityHeader($response);

        $this->assertEquals('nosniff', $response->getHeader('X-Content-Type-Options')[0]);
        $this->assertEquals('SAMEORIGIN', $response->getHeader('X-Frame-Options')[0]);
    }
}
