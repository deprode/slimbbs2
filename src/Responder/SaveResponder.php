<?php

namespace App\Responder;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class SaveResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function csrfInvalid(Response $response): ResponseInterface
    {
        $error_msg = "投稿に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
        return $response->withStatus(400);
    }

    public function invalid(Response $response, string $redirect): ResponseInterface
    {
        $error_msg = "投稿に失敗しました。投稿内容を見直して、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg, 'redirect' => $redirect]);
        return $response->withStatus(400);
    }

    public function uploadFailed(Response $response, string $redirect): ResponseInterface
    {
        $error_msg = "画像のアップロードに失敗しました。元の画面から、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg, 'redirect' => $redirect]);
        return $response->withStatus(400);
    }

    public function saveFailed(Response $response, string $redirect): ResponseInterface
    {
        $error_msg = "保存に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg, 'redirect' => $redirect]);
        return $response->withStatus(400);
    }

    public function saved(Response $response, string $redirect): Response
    {
        return $response->withRedirect($redirect, 303);
    }
}