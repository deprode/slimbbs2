<?php

namespace App\Responder;

use Slim\Http\Response;
use Slim\Views\Twig;

class ThreadResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Response $response, array $data)
    {
        return $this->view->render($response, 'thread.twig', $data);
    }

    public function invalid(Response $response)
    {
        return $response->withRedirect('/', 300);
    }
}