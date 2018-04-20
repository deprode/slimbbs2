<?php


namespace App\Responder;


use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class CommentsResponder
{
    public function success(Response $response, array $data): ResponseInterface
    {
        return $response->withJson($data, 200);
    }

    public function invalid(Response $response): ResponseInterface
    {
        return $response->withStatus(400);
    }

    public function failed(Response $response): ResponseInterface
    {
        return $response->withStatus(500);
    }
}