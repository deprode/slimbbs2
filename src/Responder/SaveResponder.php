<?php

namespace App\Responder;

use App\Service\MessageService;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class SaveResponder
{
    private $message;

    public function __construct(MessageService $message)
    {
        $this->message = $message;
    }

    public function csrfInvalid(Response $response): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'CsrfFailed');
        return $response->withRedirect('/', 302);
    }

    public function invalid(Response $response, string $redirect): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'CommentInvalid');
        return $response->withRedirect($redirect, 302);
    }

    public function uploadFailed(Response $response, string $redirect): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'UploadFailed');
        return $response->withRedirect($redirect, 303);
    }

    public function saveFailed(Response $response, string $redirect): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'CommentSaveFailed');
        return $response->withRedirect($redirect, 303);
    }

    public function saved(Response $response, string $redirect): Response
    {
        $this->message->setMessage($this->message::INFO, 'SavedThread');
        return $response->withRedirect($redirect, 303);
    }

    public function saveComment(Response $response, string $redirect): Response
    {
        $this->message->setMessage($this->message::INFO, 'SavedComment');
        return $response->withRedirect($redirect, 303);
    }
}