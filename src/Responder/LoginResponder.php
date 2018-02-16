<?php

namespace App\Responder;

use App\Domain\MessageService;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class LoginResponder
{
    private $message;

    public function __construct(MessageService $message)
    {
        $this->message = $message;
    }

    public function success(Response $response, string $url): Response
    {
        return $response->withRedirect($url, 303);
    }

    public function oAuthFailed(Response $response): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'LoginFailed');
        return $response->withRedirect('/', 303);
    }

    public function saveFailed(Response $response): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'UserSaveFailed');
        return $response->withRedirect('/', 303);
    }
}