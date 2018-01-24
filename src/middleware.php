<?php
// Application middleware

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app->add($container->get('csrf'));

$app->add(new \App\Middleware\SecurityHeaderMiddleware());

$app->add(new \App\Middleware\AuthMiddleware($container->get('AuthService')));