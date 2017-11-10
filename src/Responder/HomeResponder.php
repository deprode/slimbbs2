<?php

namespace App\Responder;

use Slim\Views\Twig;
use Slim\Http\Response;

class HomeResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Response $response, array $data)
    {
        return $this->view->render($response, 'index.twig', $data);
    }
}