<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/', 'App\Action\HomeAction:index')
    ->setName('home');

$app->post('/', 'App\Action\SaveAction:index')
    ->setName('save')
    ->add($container->get('App\Validation\SaveValidation'));

$app->get('/login', 'App\Action\LoginAction:index')
    ->setName('login');

$app->get('/login/callback', 'App\Action\LoginAction:callback')
    ->setName('callback');

$app->get('/logout', 'App\Action\LogoutAction:index')
    ->setName('logout');

$app->get('/thread', 'App\Action\ThreadAction:index')
    ->setName('thread');

$app->post('/thread', 'App\Action\ThreadSaveAction:save')
    ->setName('thread')
    ->add($container->get('App\Validation\ThreadSaveValidation'));