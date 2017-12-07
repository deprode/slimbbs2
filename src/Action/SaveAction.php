<?php

namespace App\Action;

use App\Domain\AuthService;
use App\Domain\CommentService;
use App\Domain\MessageService;
use App\Exception\SaveFailedException;
use App\Model\Comment;
use App\Responder\SaveResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class SaveAction
{
    private $logger;
    private $responder;
    private $comment;
    private $message;
    private $auth;

    public function __construct(LoggerInterface $logger, CommentService $comment, AuthService $auth, MessageService $message, SaveResponder $responder)
    {
        $this->logger = $logger;
        $this->responder = $responder;
        $this->auth = $auth;
        $this->message = $message;
        $this->comment = $comment;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route save");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrfInvalid($response);
        }

        $data = $request->getParsedBody();
        $user_id = $data['user_id'] ?? 0;
        // 認証されたユーザと違うIDが送信された
        if (!$this->auth->equalUser((int)$user_id)) {
            return $this->responder->invalid($response, '/');
        }

        // Validation
        if (empty($request->getAttribute('has_errors')) === false) {
            return $this->responder->invalid($response, '/');
        }

        try {
            $comment = new Comment();
            $comment->comment = $data['comment'];
            $comment->user_id = $data['user_id'];
            $this->comment->saveThread($comment);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->saveFailed($response);
        }

        $this->message->setMessage('SavedThread');
        return $this->responder->saved($response, '/');
    }
}