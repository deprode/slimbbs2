<?php

namespace App\Responder;

use Slim\Http\Response;
use Slim\Views\Twig;

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