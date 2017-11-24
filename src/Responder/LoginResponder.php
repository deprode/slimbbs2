<?php

namespace App\Responder;

use Slim\Views\Twig;
use Slim\Http\Response;

class LoginResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function success(Response $response, string $url)
    {
        return $response->withRedirect($url, 303);
    }

    public function oAuthFailed(Response $response)
    {
        $error_msg = "ログインに失敗しました。時間をおいてから、もう一度やり直してください。";
        $response = $response->withStatus(401);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }

    public function saveFailed(Response $response)
    {
        $error_msg = "ユーザー情報の保存に失敗しました。管理責任者までお問い合わせください。";
        $response = $response->withStatus(500);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }
}