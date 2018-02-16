<?php

namespace App\Responder;

use App\Domain\MessageService;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class SearchResponder
{
    private $view;
    private $message;

    public function __construct(Twig $view, MessageService $message)
    {
        $this->view = $view;
        $this->message = $message;
    }

    public function comments(Response $response, array $data): ResponseInterface
    {
        return $this->view->render($response, 'search.twig', $data);
    }

    public function emptyQuery(Response $response, string $redirect): Response
    {
        return $response->withRedirect($redirect);
    }

    public function fetchFailed(Response $response, string $redirect): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'SearchFailed');
        return $response->withRedirect($redirect, 303);
    }

}