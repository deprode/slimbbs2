<?php


namespace App\Responder;


use App\Service\MessageService;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class CommentResponder
{
    private $view;
    private $message;

    public function __construct(Twig $view, MessageService $message)
    {
        $this->view = $view;
        $this->message = $message;
    }

    public function index(Response $response, array $data): ResponseInterface
    {
        return $this->view->render($response, 'comment.twig', $data);
    }

    public function invalid(Response $response, string $redirect): Response
    {
        return $response->withRedirect($redirect, 302);
    }

    public function fetchFailed(Response $response, string $redirect): ResponseInterface
    {
        $this->message->setMessage($this->message::ERROR, 'CommentFetchFailed');

        return $response->withRedirect($redirect, 303);
    }

}