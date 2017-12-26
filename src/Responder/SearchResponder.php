<?php

namespace App\Responder;

use Slim\Http\Response;
use Slim\Views\Twig;

class SearchResponder
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function comments(Response $response, array $data)
    {
        return $this->view->render($response, 'search.twig', $data);
    }

    public function emptyQuery(Response $response, string $redirect)
    {
        return $response->withRedirect($redirect);
    }

    public function fetchFailed(Response $response)
    {
        $error_msg = "検索データの取得に失敗しました。元の画面から、もう一度やり直してください。";
        $response = $response->withStatus(400);
        return $this->view->render($response, 'error.twig', ['error_message' => $error_msg]);
    }

}