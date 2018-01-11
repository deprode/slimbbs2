<?php

namespace App\Responder;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class LoginResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function success(Response $response, string $url): Response
    {
        return $response->withRedirect($url, 303);
    }

    public function oAuthFailed(Response $response): ResponseInterface
    {
        $error_msg = "ログインに失敗しました。時間をおいてから、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
        return $response->withStatus(401);
    }

    public function saveFailed(Response $response): ResponseInterface
    {
        $error_msg = "ユーザー情報の保存に失敗しました。管理責任者までお問い合わせください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
        return $response->withStatus(500);
    }
}