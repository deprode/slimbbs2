<?php

namespace App\Action;

use App\Domain\AuthService;
use App\Domain\CommentService;
use App\Domain\MessageService;
use App\Responder\ThreadResponder;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;
use Slim\Csrf\Guard as Csrf;

class ThreadAction
{
    private $logger;
    private $csrf;
    private $comment;
    private $auth;
    private $message;
    private $responder;

    public function __construct(LoggerInterface $logger, Csrf $csrf, CommentService $comment, AuthService $auth, MessageService $message, ThreadResponder $responder)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
        $this->comment = $comment;
        $this->auth = $auth;
        $this->message = $message;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route");

        $thread_id = $request->getParam('thread_id');
        if (empty($thread_id) || !is_numeric($thread_id)) {
            return $this->responder->invalid($response, '/');
        }

        $data['comments'] = $this->comment->getComments($thread_id);
        if (empty($data['comments'])) {
            $this->message->setMessage('DeletedThread');
            return $this->responder->invalid($response, '/');
        }

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $request->getAttribute($nameKey);
        $data['value'] = $request->getAttribute($valueKey);
        $data['thread_id'] = $thread_id;
        $data['user_id'] = $this->auth->getUserId();
        $data['is_admin'] = $this->auth->isAdmin();
        $data['saved'] = $this->message->getMessage('SavedComment');
        $data['deleted'] = $this->message->getMessage('DeletedComment');

        // Render index view
        return $this->responder->index($response, $data);
    }
}