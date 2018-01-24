<?php

namespace App\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityHeaderMiddleware
{
    public function appendSecurityHeader($response): ResponseInterface
    {
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
        $response = $response->withHeader('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $response = $this->appendSecurityHeader($response);
        return $next($request, $response);
    }
}