<?php

namespace App\Action;


use App\Domain\AuthService;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;
use App\Domain\CommentService;
use App\Model\Comment;
use App\Responder\SaveResponder;

class ThreadSaveAction
{
    private $logger;
    private $responder;
    private $comment;
    private $auth;

    public function __construct(LoggerInterface $logger, CommentService $comment, SaveResponder $responder, AuthService $auth)
    {
        $this->logger = $logger;
        $this->responder = $responder;
        $this->comment = $comment;
        $this->auth = $auth;
    }

    public function save(Request $request, Response $response)
    {
        // Sample log message
        $this->logger->info("Slimbbs '/' route comment save");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrf_invalid($response);
        }

        // Validation
        if($request->getAttribute('has_errors')){
            return $this->responder->invalid($response);
        }

        $data = $request->getParsedBody();

        try {
            $comment = new Comment();
            $comment->thread_id = $data['thread_id'];
            $comment->user_id = $this->auth->getUserId();
            $comment->comment = $data['comment'];
            $this->comment->saveComment($comment);
        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit();
        }

        $url = $request->getUri()->getPath() . '?thread_id=' . $data['thread_id'];
        return $this->responder->saved($response, $url);
    }
}