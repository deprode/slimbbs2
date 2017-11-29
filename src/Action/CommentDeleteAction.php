<?php

namespace App\Action;


use App\Domain\AuthService;
use App\Domain\CommentService;
use App\Responder\DeleteResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentDeleteAction
{
    private $log;
    private $comment;
    private $auth;
    private $responder;

    public function __construct(LoggerInterface $log, CommentService $comment, AuthService $auth, DeleteResponder $responder)
    {
        $this->log = $log;
        $this->comment = $comment;
        $this->auth = $auth;
        $this->responder = $responder;
    }

    public function delete(Request $request, Response $response)
    {
        $this->log->info("Slimbbs '/thread' route comment delete");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrfInvalid($response);
        }

        $data = $request->getParsedBody();
        $url = $request->getUri()->getPath() . (empty(intval($data['thread_id'])) ? '' : '?thread_id=' . intval($data['thread_id']));

        // Validation
        if($request->getAttribute('has_errors') || $this->auth->getUserId() == 0){
            return $this->responder->invalid($response, $url);
        }

        $delete = $this->comment->deleteComment($data['comment_id'], $this->auth->getUserId());
        if ($delete) {
            return $this->responder->deleted($response, $url);
        }

        return $this->responder->deleteFailed($response, $url);
    }
}