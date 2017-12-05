<?php

namespace App\Responder;


use Slim\Http\Response;
use Slim\Views\Twig;

class DeleteResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function csrfInvalid(Response $response)
    {
        $error_msg = "削除に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $response->withStatus(400);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }

    public function invalid(Response $response)
    {
        $error_msg = "削除に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $response->withStatus(400);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }

    public function deleteFailed(Response $response)
    {
        $error_msg = "削除に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $response->withStatus(400);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }

    public function deleted(Response $response, string $redirect)
    {
        return $response->withRedirect($redirect, 303);
    }
}