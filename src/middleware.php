<?php
// Application middleware

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app->add($container->get('csrf'));

$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
    $response = $response->withHeader('X-Frame-Options', 'SAMEORIGIN');

    return $next($request, $response);
});