<?php

namespace App\Action;


use App\Domain\AuthService;
use App\Domain\MessageService;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;
use App\Domain\CommentService;
use App\Model\Comment;
use App\Responder\SaveResponder;

class CommentSaveAction
{
    private $logger;
    private $responder;
    private $comment;
    private $auth;
    private $message;

    public function __construct(LoggerInterface $logger, CommentService $comment, SaveResponder $responder, AuthService $auth, MessageService $message)
    {
        $this->logger = $logger;
        $this->responder = $responder;
        $this->comment = $comment;
        $this->auth = $auth;
        $this->message = $message;
    }

    public function save(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route comment save");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrfInvalid($response);
        }

        // Validation
        if($request->getAttribute('has_errors')){
            return $this->responder->invalid($response, $request->getUri()->getPath());
        }

        $data = $request->getParsedBody();
        $url = $request->getUri()->getPath() . (empty(intval($data['thread_id'])) ? '' : '?thread_id=' . intval($data['thread_id']));

        try {
            $comment = new Comment();
            $comment->thread_id = $data['thread_id'];
            $comment->user_id = $this->auth->getUserId();
            $comment->comment = $data['comment'];
            $this->comment->saveComment($comment);
        } catch (\PDOException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->saveFailed($response);
        }

        $this->message->setMessage('SavedComment');
        return $this->responder->saved($response, $url);
    }
}