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

    public function csrfInvalid(Response $response)
    {
        $error_msg = "投稿に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $response->withStatus(400);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }

    public function invalid(Response $response, string $redirect)
    {
        return $response->withRedirect($redirect, 200);
    }

    public function saveFailed(Response $response)
    {
        $error_msg = "保存に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $response->withStatus(400);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }

    public function saved(Response $response, string $redirect)
    {
        return $response->withRedirect($redirect, 303);
    }
}