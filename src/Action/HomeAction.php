<?php

namespace App\Action;

use App\Domain\AuthService;
use App\Domain\MessageService;
use App\Domain\ThreadService;
use App\Responder\HomeResponder;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;
use Slim\Csrf\Guard as Csrf;

final class HomeAction
{
    private $logger;
    private $csrf;
    private $thread;
    private $auth;
    private $message;
    private $responder;

    public function __construct(LoggerInterface $logger, Csrf $csrf, ThreadService $thread, AuthService $auth, MessageService $message, HomeResponder $responder)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
        $this->thread = $thread;
        $this->auth = $auth;
        $this->message = $message;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route");

        $data['loggedIn'] = $this->auth->isLoggedIn();

        $data['threads'] = $this->thread->getThreads();

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $request->getAttribute($nameKey);
        $data['value'] = $request->getAttribute($valueKey);
        $data['user_id'] = $this->auth->getUserId();
        $data['saved'] = $this->message->getMessage('SavedThread');
        $data['deleted'] = $this->message->getMessage('DeletedThread') ?? $this->message->getMessage('DeletedComment');

        // Render index view
        return $this->responder->index($response, $data);
    }
}