<?php

namespace App\Responder;


use Slim\Http\Response;

class LoginResponder
{
    public function success(Response $response, string $url)
    {
        return $response->withRedirect($url, 303);
    }

    public function oAuthFailed(Response $response, string $url)
    {
        return $response->withRedirect($url, 403);
    }

    public function saveFailed(Response $response, string $url)
    {
        return $response->withRedirect($url, 400);
    }
}