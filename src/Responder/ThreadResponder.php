<?php

namespace App\Responder;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class ThreadResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Response $response, array $data): ResponseInterface
    {
        return $this->view->render($response, 'thread.twig', $data);
    }

    public function invalid(Response $response, string $redirect): Response
    {
        return $response->withRedirect($redirect, 302);
    }

    public function fetchFailed(Response $response): ResponseInterface
    {
        $error_msg = "コメントの取得に失敗しました。スレッドが削除されたかもしれません。";
        $response = $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
        return $response->withStatus(400);
    }
}