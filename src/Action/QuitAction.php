<?php

namespace App\Action;


use App\Domain\QuitFilter;
use App\Responder\QuitResponder;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class QuitAction
{
    private $filter;
    private $responder;

    public function __construct(QuitFilter $filter, QuitResponder $responder)
    {
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response): ResponseInterface
    {
        try {
            return $this->responder->quit($response, $this->filter->filtering($request));
        } catch (\UnexpectedValueException $e) {
            return $this->responder->authInvalid($response);
        }
    }
}