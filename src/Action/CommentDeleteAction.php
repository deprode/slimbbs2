<?php

namespace App\Action;


use App\Domain\CommentService;
use App\Domain\MessageService;
use App\Exception\DeleteFailedException;
use App\Responder\DeleteResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentDeleteAction
{
    private $log;
    private $comment;
    private $message;
    private $responder;

    public function __construct(LoggerInterface $log, CommentService $comment, MessageService $message, DeleteResponder $responder)
    {
        $this->log = $log;
        $this->comment = $comment;
        $this->message = $message;
        $this->responder = $responder;
    }

    public function delete(Request $request, Response $response)
    {
        $this->log->info("Slimbbs '/thread' route comment delete");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrfInvalid($response);
        }

        $data = $request->getParsedBody();

        // Validation
        $user_id = $request->getAttribute('userId');
        $is_anonymous = $user_id == 0;
        if ($request->getAttribute('has_errors') || $is_anonymous) {
            return $this->responder->invalid($response);
        }

        try {
            if ($request->getAttribute('isAdmin')) {
                $delete = $this->comment->deleteCommentByAdmin($data['thread_id'], $data['comment_id']);
            } else {
                $delete = $this->comment->deleteComment($data['thread_id'], $data['comment_id'], $user_id);
            }

            if ($delete) {
                $this->message->setMessage($this->message::INFO, 'DeletedComment');
            }
        } catch (DeleteFailedException $e) {
            $this->log->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->deleteFailed($response);
        }

        $url = $this->getRedirectUrl($request->getUri()->getPath(), $data);
        return $this->responder->deleted($response, $url);
    }

    private function getRedirectUrl(string $base_path, array $data): string
    {
        $query = $data['query'] ?? null;
        if ($query) {
            $url = '/search?query=' . $query;
        } else {
            $url = $base_path . (empty(intval($data['thread_id'])) ? '' : '?thread_id=' . intval($data['thread_id']));
        }

        return $url;
    }
}