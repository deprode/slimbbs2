<?php

namespace App\Responder;

use Slim\Views\Twig;
use Slim\Http\Response;

class SaveResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function csrf_invalid(Response $response)
    {
        return $response->withRedirect('/', 400);
    }

    public function invalid(Response $response)
    {
        return $response->withRedirect('/', 200);
    }

    public function saved(Response $response, array $data)
    {
        return $response->withRedirect('/', 303);
    }
}