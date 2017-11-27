<?php

namespace App\Action;

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

    public function __construct(LoggerInterface $logger, CommentService $comment, SaveResponder $responder)
    {
        $this->logger = $logger;
        $this->responder = $responder;
        $this->comment = $comment;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route save");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrf_invalid($response);
        }

        // Validation
        if($request->getAttribute('has_errors')){
            return $this->responder->invalid($response, '/');
        }

        $data = $request->getParsedBody();

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