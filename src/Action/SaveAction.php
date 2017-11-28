<?php

namespace App\Action;

use App\Domain\AuthService;
use App\Model\Comment;
use App\Responder\SaveResponder;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;
use App\Domain\CommentService;

class SaveAction
{
    private $logger;
    private $responder;
    private $comment;
    private $auth;

    public function __construct(LoggerInterface $logger, CommentService $comment, AuthService $auth, SaveResponder $responder)
    {
        $this->logger = $logger;
        $this->responder = $responder;
        $this->auth = $auth;
        $this->comment = $comment;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route save");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrfInvalid($response);
        }

        // Validation
        $data = $request->getParsedBody();
        $user_id = $data['user_id'] ?? 0;
        if($request->getAttribute('has_errors') && $this->auth->equalUser((int)$user_id)){
            return $this->responder->invalid($response, '/');
        }

        try {
            $comment = new Comment();
            $comment->comment = $data['comment'];
            $comment->user_id = $data['user_id'];
            $this->comment->saveThread($comment);
        } catch (\PDOException $e) {
            return $this->responder->saveFailed($response);
        }

        return $this->responder->saved($response, '/');
    }
}