<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['view'] = function ($container) {
    $cache = (getenv('TWIG_CACHE') == "false") ? false : getenv('TWIG_CACHE');

    $settings = $container->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
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
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $dns = $db['driver'].':host='.$db['host'].';port='.$db['port'].';dbname='.$db['database'];
    try {
        $db_connection = new PDO($dns, $db['username'], $db['password']);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    return $db_connection;
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

// Validation
$container['validate'] = function ($c) {
    return new \Respect\Validation\Validator();
};

$container['twitter'] = function($c) {
    return new Abraham\TwitterOAuth\TwitterOAuth(getenv('TWITTER_CONSUMER_KEY'), getenv('TWITTER_CONSUMER_SECRET'));
};

$container['session'] = function($c) {
    return new \RKA\Session();
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------
$container['App\Action\HomeAction'] = function ($c) {
    return new App\Action\HomeAction($c->get('logger'), $c->get('csrf'), $c->get('CommentService'), $c->get('AuthService'), $c->get('HomeResponder'));
};

$container['App\Action\SaveAction'] = function ($c) {
    return new App\Action\SaveAction($c->get('logger'), $c->get('CommentService'), $c->get('SaveResponder'));
};

$container['App\Action\LoginAction'] = function ($c) {
    return new App\Action\LoginAction($c->get('logger'), $c->get('twitter'), $c->get('UserService'), $c->get('AuthService'), $c->get('LoginResponder'));
};

$container['App\Action\LogoutAction'] = function ($c) {
    return new App\Action\LogoutAction($c->get('logger'), $c->get('AuthService'));
};
// -----------------------------------------------------------------------------
// Domain factories
// -----------------------------------------------------------------------------
$container['CommentService'] = function($c) {
    return new App\Domain\CommentService($c->get('db'));
};

$container['UserService'] = function($c) {
    return new App\Domain\UserService($c->get('db'));
};

$container['AuthService'] = function($c) {
    return new App\Domain\AuthService($c->get('session'));
};
// -----------------------------------------------------------------------------
// Responder factories
// -----------------------------------------------------------------------------
$container['HomeResponder'] = function($c) {
    return new App\Responder\HomeResponder($c->get('view'));
};

$container['SaveResponder'] = function($c) {
    return new App\Responder\SaveResponder($c->get('view'));
};

$container['LoginResponder'] = function($c) {
    return new App\Responder\LoginResponder();
};
// -----------------------------------------------------------------------------
// Validation factories
// -----------------------------------------------------------------------------

$container['App\Validation\Translator'] = function($c){
    return $translator = function ($message) {
        $messages = [
            'These rules must pass for {{name}}'                                => '{{name}}で守られていないルールがあります',
            'All of the required rules must pass for {{name}}'                  => '{{name}}で守られていないルールがあります',
            '{{name}} must have a length lower than {{maxValue}}'               => '{{name}}は{{maxValue}}文字以下で入力してください',
            '{{name}} must be an integer number'                                => '{{name}}には整数を入力してください',
            '{{name}} must be a string'                                         => '{{name}}には文字列を入力してください',
            '{{name}} must not be empty'                                        => '{{name}}は必須です',
            '{{name}} must have a length between {{minValue}} and {{maxValue}}' => '{{name}}は{{minValue}}〜{{maxValue}}字の範囲で入力してください',
            '{{name}} must be valid email'                                      => '{{name}}にはEメールアドレスのみ書き込めます',
            '{{name}} must be an URL'                                           => '{{name}}にはURLのみ書き込めます',
            '{{name}} must validate against {{regex}}'                          => '{{name}}には英数字かアンダーバーを使ってください',
            '{{name}} must be of the type array'                                => '{{name}}が選択されていません',
            'Each item in {{name}} must be valid'                               => '{{name}}が不正な形式です',
            '{{name}} must not be in {{haystack}}'                              => '{{name}}にNGワードが含まれています',
            '{{name}} must be a valid date. Sample format: {{format}}'          => '{{name}}が日付の形式（{{format}}）ではありません',
            '{{name}} must be greater than or equal to {{interval}}'            => '{{name}}は{{interval}}より大きい値にしてください',
            '{{name}} must contain only letters (a-z) and digits (0-9)'         => '{{name}}は半角英数字のみ入力してください'
        ];
        return $messages[$message];
    };
};

$container['App\Validation\SaveValidation'] = function($c) {
    $translator = $c->get('App\Validation\Translator');
    $saveValidators = [
        'comment'     => \Respect\Validation\Validator::stringType()->notEmpty()->length(null, 400)->setName('本文'),
    ];
    return new \DavidePastore\Slim\Validation\Validation($saveValidators, $translator);
};