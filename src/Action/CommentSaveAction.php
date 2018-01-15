<?php

namespace App\Action;


use App\Domain\AuthService;
use App\Domain\CommentService;
use App\Domain\MessageService;
use App\Domain\StorageService;
use App\Exception\SaveFailedException;
use App\Exception\UploadFailedException;
use App\Model\Comment;
use App\Model\Sort;
use App\Responder\SaveResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentSaveAction
{
    private $logger;
    private $responder;
    private $comment;
    private $auth;
    private $message;
    private $storage;

    public function __construct(LoggerInterface $logger, CommentService $comment, SaveResponder $responder, AuthService $auth, MessageService $message, StorageService $storage)
    {
        $this->logger = $logger;
        $this->responder = $responder;
        $this->comment = $comment;
        $this->auth = $auth;
        $this->message = $message;
        $this->storage = $storage;
    }

    public function save(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route comment save");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrfInvalid($response);
        }

        // Validation
        if ($request->getAttribute('has_errors')) {
            return $this->responder->invalid($response, $request->getUri()->getPath());
        }

        $files = $request->getUploadedFiles();
        if (!empty($files['picture']->file)) {
            try {
                $filename = $this->storage->upload($files['picture']);
            } catch (UploadFailedException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                return $this->responder->uploadFailed($response);
            }
        }

        $data = $request->getParsedBody();
        try {
            $sort = new Sort($data['sort'] ?? 'desc');
        } catch (\InvalidArgumentException $e) {
            $sort = new Sort('desc');
        }
        $url = $request->getUri()->getPath() . (empty(intval($data['thread_id'])) ? '' : '?thread_id=' . intval($data['thread_id']) . '&sort=' . $sort->value());

        try {
            $comment = new Comment();
            $comment->thread_id = $data['thread_id'];
            $comment->user_id = $this->auth->getUserId();
            $comment->comment = $data['comment'];
            $comment->photo_url = $filename ?? '';
            $this->comment->saveComment($comment);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->saveFailed($response);
        }

        $this->message->setMessage('SavedComment');
        return $this->responder->saved($response, $url);
    }
}