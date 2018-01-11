<?php

namespace App\Responder;


use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class DeleteResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function csrfInvalid(Response $response): ResponseInterface
    {
        $error_msg = "削除に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
        return $response->withStatus(400);
    }

    public function invalid(Response $response): ResponseInterface
    {
        $error_msg = "削除に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
        return $response->withStatus(400);
    }

    public function deleteFailed(Response $response): ResponseInterface
    {
        $error_msg = "削除に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
        return $response->withStatus(400);
    }

    public function deleted(Response $response, string $redirect): Response
    {
        return $response->withRedirect($redirect, 303);
    }
}