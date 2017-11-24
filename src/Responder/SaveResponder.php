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
        $error_msg = "失敗しました。元の画面から、もう一度やり直してください。";
        $response = $response->withStatus(400);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }

    public function invalid(Response $response, string $redirect)
    {
        return $response->withRedirect($redirect, 200);
    }

    public function saved(Response $response, string $redirect)
    {
        return $response->withRedirect($redirect, 303);
    }
}