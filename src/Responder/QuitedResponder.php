<?php

namespace App\Responder;


use App\Domain\MessageService;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class QuitedResponder
{
    private $view;
    private $message;

    public function __construct(Twig $view, MessageService $message)
    {
        $this->view = $view;
        $this->message = $message;
    }

    public function quited(Response $response): ResponseInterface
    {
        return $this->view->render($response, 'quited.twig');
    }

    public function deleteFailed(Response $response): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'AccountDeleteFailed');
        return $response->withRedirect('/quit', 303);
    }

    public function redirect(Response $response): ResponseInterface
    {
        return $response->withRedirect('/', 303);
    }
}