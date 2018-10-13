<?php

namespace Tests\Unit\Middleware;

use App\Middleware\PrevUriMiddleware;
use RKA\Session;
use Slim\Http\Environment;
use Slim\Http\Request;

class PrevUriMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPrevUri()
    {
        $_SESSION = [];
        $_SESSION['PrevUri'] = 'http://sample.example.com';

        $class = new \ReflectionClass(PrevUriMiddleware::class);
        $method = $class->getMethod('getPrevUri');
        $method->setAccessible(true);

        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/',
            ]
        );

        $request = Request::createFromEnvironment($environment);
        $middleware = new PrevUriMiddleware(new Session());
        $request = $method->invokeArgs($middleware, [$request]);

        $this->assertEquals('http://sample.example.com', $request->getAttribute('PREV_URI'));
    }

    public function testSetNowUri()
    {
        $_SESSION = [];

        $class = new \ReflectionClass(PrevUriMiddleware::class);
        $method = $class->getMethod('setNowUri');
        $method->setAccessible(true);

        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'HTTPS'          => '',
                'HTTP_HOST'      => 'example.com:8080',
                'REQUEST_URI'    => '/script_name',
                'QUERY_STRING'   => 'query=string',
            ]
        );

        $request = Request::createFromEnvironment($environment);
        $middleware = new PrevUriMiddleware(new Session());

        $method->invokeArgs($middleware, [$request]);

        $this->assertEquals('http://example.com:8080/script_name?query=string', $_SESSION['PrevUri']);
    }
}
