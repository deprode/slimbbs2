<?php

namespace App\Action;

use App\Domain\AuthService;
use App\Domain\CommentService;
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
    private $responder;
    private $auth;

    public function __construct(LoggerInterface $logger, Csrf $csrf, CommentService $comment, AuthService $auth, ThreadResponder $responder)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
        $this->comment = $comment;
        $this->responder = $responder;
        $this->auth = $auth;
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

        // Render index view
        return $this->responder->index($response, $data);
    }
}