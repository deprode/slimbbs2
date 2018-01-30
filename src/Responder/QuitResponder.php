<?php

namespace App\Responder;


use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class QuitResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function quit(Response $response, array $data): ResponseInterface
    {
        return $this->view->render($response, 'quit.twig', $data);
    }

    public function authInvalid(Response $response): ResponseInterface
    {
        return $response->withRedirect('/', 303);
    }
}