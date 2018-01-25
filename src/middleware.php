<?php
// Application middleware

$app->add($container->get('csrf'));

$app->add(new \App\Middleware\SecurityHeaderMiddleware());

$app->add(new \App\Middleware\AuthMiddleware($container->get('AuthService')));