<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['view'] = function ($container) {
    $cache = (getenv('TWIG_CACHE') == "false") ? false : getenv('TWIG_CACHE');

    $view = new \Slim\Views\Twig('templates', [
        'cache' => $cache
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));

    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// database
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

// csrf
$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function (\Slim\Http\Request $request, \Slim\Http\Response $response, $next) {
        $request = $request->withAttribute("csrf_status", "bad_request");
        return $next($request, $response);
    });
    return $guard;
};