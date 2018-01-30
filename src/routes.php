<?php

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

$app->get('/search', 'App\Action\SearchAction:comments')
    ->setName('search')
    ->add($container->get('App\Validation\SearchValidation'));

$app->get('/thread', 'App\Action\ThreadAction:index')
    ->setName('thread');

$app->post('/thread', 'App\Action\CommentSaveAction:save')
    ->setName('thread_save')
    ->add($container->get('App\Validation\CommentSaveValidation'));

$app->delete('/thread', 'App\Action\CommentDeleteAction:delete')
    ->setName('delete_comment')
    ->add($container->get('App\Validation\CommentDeleteValidation'));

$app->put('/thread', 'App\Action\CommentUpdateAction:update')
    ->setName('update_comment')
    ->add($container->get('App\Validation\CommentUpdateValidation'));

$app->post('/like', 'App\Action\LikeAction:add')
    ->setName('add_like')
    ->add($container->get('App\Validation\CommentLikeValidation'));

$app->get('/quit', 'App\Action\QuitAction:index')
    ->setName('quit');


$app->delete('/quit', 'App\Action\AccountDeleteAction:delete')
    ->setName('quit')
    ->add($container->get('App\Validation\CommentLikeValidation'));
