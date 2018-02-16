<?php

namespace App\Responder;


use App\Domain\MessageService;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class DeleteResponder
{
    private $message;

    public function __construct(MessageService $message)
    {
        $this->message = $message;
    }

    public function csrfInvalid(Response $response): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'CommentDeleteFailed');
        return $response->withRedirect('/', 302);
    }

    public function invalid(Response $response): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'CommentDeleteFailed');
        return $response->withRedirect('/', 302);
    }

    public function deleteFailed(Response $response, string $redirect): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'CommentDeleteFailed');
        return $response->withRedirect($redirect, 303);
    }

    public function deleted(Response $response, string $redirect): Response
    {
        return $response->withRedirect($redirect, 303);
    }
}