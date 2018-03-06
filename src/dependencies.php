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
    $dns = $db['driver'] . ':host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['database'] . ';charset=utf8mb4;';
    try {
        $db_connection = new PDO($dns, $db['username'], $db['password']);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        $c['logger']->alert($e->getMessage(), ['exception' => $e]);
        exit();
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

$container['twitter'] = function ($c) {
    return new Abraham\TwitterOAuth\TwitterOAuth(getenv('TWITTER_CONSUMER_KEY'), getenv('TWITTER_CONSUMER_SECRET'));
};

$container['session'] = function ($c) {
    return new \RKA\Session();
};

// Flash message
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['s3'] = function ($c) {
    $settings = $c->get('settings')['s3'];
    return new \Aws\S3\S3Client([
        'version'     => 'latest',
        'region'      => $settings['region'],
        'credentials' => [
            'key'    => $settings['key'],
            'secret' => $settings['secret'],
        ],
    ]);
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------
$container['App\Action\HomeAction'] = function ($c) {
    return new App\Action\HomeAction($c->get('logger'), $c->get('HomeFilter'), $c->get('HomeResponder'));
};

$container['App\Action\SaveAction'] = function ($c) {
    return new App\Action\SaveAction($c->get('logger'), $c->get('SaveFilter'), $c->get('SaveResponder'));
};

$container['App\Action\LoginAction'] = function ($c) {
    return new App\Action\LoginAction($c->get('logger'), $c->get('OAuthService'), $c->get('LoginFilter'), $c->get('LoginResponder'));
};

$container['App\Action\LogoutAction'] = function ($c) {
    return new App\Action\LogoutAction($c->get('logger'), $c->get('AuthService'));
};

$container['App\Action\SearchAction'] = function ($c) {
    return new App\Action\SearchAction($c->get('logger'), $c->get('SearchFilter'), $c->get('SearchResponder'));
};

$container['App\Action\ThreadAction'] = function ($c) {
    return new App\Action\ThreadAction($c->get('logger'), $c->get('MessageService'), $c->get('ThreadFilter'), $c->get('ThreadResponder'));
};

$container['App\Action\CommentSaveAction'] = function ($c) {
    return new App\Action\CommentSaveAction($c->get('logger'), $c->get('CommentSaveFilter'), $c->get('SaveResponder'));
};

$container['App\Action\CommentUpdateAction'] = function ($c) {
    return new App\Action\CommentUpdateAction($c->get('logger'), $c->get('CommentUpdateFilter'));
};

$container['App\Action\CommentDeleteAction'] = function ($c) {
    return new App\Action\CommentDeleteAction($c->get('logger'), $c->get('CommentDeleteFilter'), $c->get('DeleteResponder'));
};

$container['App\Action\LikeAction'] = function ($c) {
    return new App\Action\LikeAction($c->get('logger'), $c->get('LikeFilter'));
};

$container['App\Action\QuitAction'] = function ($c) {
    return new App\Action\QuitAction($c->get('QuitFilter'), $c->get('QuitResponder'));
};

$container['App\Action\AccountDeleteAction'] = function ($c) {
    return new App\Action\AccountDeleteAction($c->get('AccountDeleteFilter'), $c->get('AuthService'), $c->get('QuitedResponder'));
};

$container['App\Action\UserAction'] = function ($c) {
    return new App\Action\UserAction($c->get('UserFilter'), $c->get('UserResponder'));
};
// -----------------------------------------------------------------------------
// Domain factories
// -----------------------------------------------------------------------------
$container['HomeFilter'] = function ($c) {
    return new App\Domain\HomeFilter($c->get('ThreadService'), $c->get('MessageService'), $c->get('csrf'));
};

$container['SaveFilter'] = function ($c) {
    return new \App\Domain\SaveFilter($c->get('AuthService'), $c->get('CommentService'));
};

$container['LoginFilter'] = function ($c) {
    return new \App\Domain\LoginFilter($c->get('UserService'), $c->get('OAuthService'));
};

$container['SearchFilter'] = function ($c) {
    return new \App\Domain\SearchFilter($c->get('csrf'), $c->get('CommentService'));
};

$container['ThreadFilter'] = function ($c) {
    return new \App\Domain\ThreadFilter($c->get('csrf'), $c->get('CommentService'), $c->get('MessageService'), $c->get('settings')['s3']);
};

$container['CommentSaveFilter'] = function ($c) {
    return new \App\Domain\CommentSaveFilter($c->get('StorageService'), $c->get('CommentService'));
};

$container['CommentUpdateFilter'] = function ($c) {
    return new \App\Domain\CommentUpdateFilter($c->get('CommentService'));
};

$container['CommentDeleteFilter'] = function ($c) {
    return new \App\Domain\CommentDeleteFilter($c->get('CommentService'));
};

$container['LikeFilter'] = function ($c) {
    return new \App\Domain\LikeFilter($c->get('CommentService'));
};

$container['QuitFilter'] = function ($c) {
    return new \App\Domain\QuitFilter($c->get('MessageService'), $c->get('csrf'));
};

$container['AccountDeleteFilter'] = function ($c) {
    return new \App\Domain\AccountDeleteFilter($c->get('UserService'));
};

$container['UserFilter'] = function ($c) {
    return new \App\Domain\UserFilter($c->get('UserService'), $c->get('CommentService'), $c->get('AuthService'), $c->get('settings')['s3']);
};

// -----------------------------------------------------------------------------
// Service(&Repositories) factories
// -----------------------------------------------------------------------------
$container['DatabaseService'] = function ($c) {
    return new App\Service\DatabaseService($c->get('db'));
};

$container['CommentService'] = function ($c) {
    return new App\Repository\CommentService($c->get('DatabaseService'));
};

$container['ThreadService'] = function ($c) {
    return new App\Repository\ThreadService($c->get('DatabaseService'));
};

$container['UserService'] = function ($c) {
    return new App\Repository\UserService($c->get('DatabaseService'));
};

$container['AuthService'] = function ($c) {
    return new App\Service\AuthService($c->get('session'), $c->get('settings')['admin_id']);
};

$container['OAuthService'] = function ($c) {
    return new App\Service\OAuthService($c->get('twitter'), $c->get('AuthService'), $c->get('router')->pathFor('callback'));
};

$container['MessageService'] = function ($c) {
    return new App\Service\MessageService($c->get('flash'));
};

$container['StorageService'] = function ($c) {
    return new App\Service\StorageService($c->get('s3'), $c->get('settings')['s3']['bucket']);
};
// -----------------------------------------------------------------------------
// Responder factories
// -----------------------------------------------------------------------------
$container['HomeResponder'] = function ($c) {
    return new App\Responder\HomeResponder($c->get('view'));
};

$container['SaveResponder'] = function ($c) {
    return new App\Responder\SaveResponder($c->get('MessageService'));
};

$container['LoginResponder'] = function ($c) {
    return new App\Responder\LoginResponder($c->get('MessageService'));
};

$container['ThreadResponder'] = function ($c) {
    return new App\Responder\ThreadResponder($c->get('view'), $c->get('MessageService'));
};

$container['DeleteResponder'] = function ($c) {
    return new App\Responder\DeleteResponder($c->get('MessageService'));
};

$container['SearchResponder'] = function ($c) {
    return new App\Responder\SearchResponder($c->get('view'), $c->get('MessageService'));
};

$container['QuitResponder'] = function ($c) {
    return new App\Responder\QuitResponder($c->get('view'));
};

$container['QuitedResponder'] = function ($c) {
    return new App\Responder\QuitedResponder($c->get('view'), $c->get('MessageService'));
};

$container['UserResponder'] = function ($c) {
    return new App\Responder\UserResponder($c->get('view'), $c->get('MessageService'));
};
// -----------------------------------------------------------------------------
// Validation factories
// -----------------------------------------------------------------------------

$container['App\Validation\Translator'] = function ($c) {
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
            '{{name}} must contain only letters (a-z) and digits (0-9)'         => '{{name}}は半角英数字のみ入力してください',
            '{{name}} must contain only digits (0-9)'                           => '{{name}}は半角数字のみ入力してください'
        ];
        return $messages[$message];
    };
};

$container['App\Validation\SearchValidation'] = function ($c) {
    $translator = $c->get('App\Validation\Translator');
    $searchValidators = [
        'query' => \Respect\Validation\Validator::stringType()->setName('検索ワード'),
    ];
    return new \DavidePastore\Slim\Validation\Validation($searchValidators, $translator);
};

$container['App\Validation\SaveValidation'] = function ($c) {
    $translator = $c->get('App\Validation\Translator');
    $saveValidators = [
        'user_id' => \Respect\Validation\Validator::intVal()->digit()->setName('ユーザーID'),
        'comment' => \Respect\Validation\Validator::stringType()->notEmpty()->length(null, 400)->setName('本文'),
    ];
    return new \DavidePastore\Slim\Validation\Validation($saveValidators, $translator);
};

$container['App\Validation\CommentSaveValidation'] = function ($c) {
    $translator = $c->get('App\Validation\Translator');
    $saveValidators = [
        'user_id'   => \Respect\Validation\Validator::intVal()->digit()->setName('ユーザーID'),
        'thread_id' => \Respect\Validation\Validator::intVal()->notEmpty()->setName('スレッドID'),
        'comment'   => \Respect\Validation\Validator::stringType()->notEmpty()->length(1, 400)->setName('本文'),
    ];
    return new \DavidePastore\Slim\Validation\Validation($saveValidators, $translator);
};

$container['App\Validation\CommentDeleteValidation'] = function ($c) {
    $translator = $c->get('App\Validation\Translator');
    $deleteValidators = [
        'thread_id'  => \Respect\Validation\Validator::intVal()->digit()->notEmpty()->setName('スレッドID'),
        'comment_id' => \Respect\Validation\Validator::intVal()->digit()->notEmpty()->setName('コメントID'),
    ];
    return new \DavidePastore\Slim\Validation\Validation($deleteValidators, $translator);
};

$container['App\Validation\CommentLikeValidation'] = function ($c) {
    $translator = $c->get('App\Validation\Translator');
    $likeValidators = [
        'thread_id'  => \Respect\Validation\Validator::intVal()->digit()->notEmpty()->setName('スレッドID'),
        'comment_id' => \Respect\Validation\Validator::intVal()->digit()->notEmpty()->setName('コメントID'),
    ];
    return new \DavidePastore\Slim\Validation\Validation($likeValidators, $translator);
};

$container['App\Validation\CommentUpdateValidation'] = function ($c) {
    $translator = $c->get('App\Validation\Translator');
    $updateValidators = [
        'thread_id'  => \Respect\Validation\Validator::intVal()->digit()->notEmpty()->setName('スレッドID'),
        'comment_id' => \Respect\Validation\Validator::intVal()->digit()->notEmpty()->setName('コメントID'),
        'comment'    => \Respect\Validation\Validator::stringType()->notEmpty()->length(null, 400)->setName('本文')
    ];
    return new \DavidePastore\Slim\Validation\Validation($updateValidators, $translator);
};