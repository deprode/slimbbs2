<?php

namespace App\Responder;


use Slim\Http\Response;

class LoginResponder
{
    public function success(Response $response)
    {
        return $response->withRedirect('/', 303);
    }

    public function oAuthFailed(Response $response)
    {
        return $response->withRedirect('/', 403);
    }

    public function saveFailed(Response $response)
    {
        return $response->withRedirect('/', 400);
    }
}