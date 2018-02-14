<?php

namespace App\Responder;


use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class QuitedResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function quited(Response $response): ResponseInterface
    {
        return $this->view->render($response, 'quited.twig');
    }

    public function deleteFailed(Response $response): ResponseInterface
    {
        $error_msg = "アカウント削除に失敗しました。お手数ですが、もう一度やり直してください。";
        $response = $this->view->render($response, 'error.twig', [
            'error_message' => $error_msg,
            'redirect'      => 'quit',
        ]);
        return $response->withStatus(400);
    }

    public function redirect(Response $response): ResponseInterface
    {
        return $response->withRedirect('/', 303);
    }
}