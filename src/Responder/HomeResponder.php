<?php

namespace App\Responder;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class HomeResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Response $response, array $data): ResponseInterface
    {
        return $this->view->render($response, 'index.twig', $data);
    }

    public function fetchFailed(Response $response): ResponseInterface
    {
        $error_msg = "スレッドの取得に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
        return $response->withStatus(400);
    }
}