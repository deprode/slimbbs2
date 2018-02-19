<?php

namespace App\Responder;

use App\Service\MessageService;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class UserResponder
{
    private $view;
    private $message;

    public function __construct(Twig $view, MessageService $message)
    {
        $this->view = $view;
        $this->message = $message;
    }

    public function nameEmpty(Response $response): ResponseInterface
    {
        return $response->withRedirect('/');
    }

    public function index(Response $response, array $data): ResponseInterface
    {
        return $this->view->render($response, 'user.twig', $data);
    }

    public function fetchFailed(Response $response): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'CommentFetchFailed');

        return $response->withRedirect('/', 303);
    }
}