<?php

namespace App\Action;


use App\Responder\QuitResponder;
use Psr\Http\Message\ResponseInterface;
use Slim\Csrf\Guard as Csrf;
use Slim\Http\Request;
use Slim\Http\Response;

class QuitAction
{
    private $csrf;
    private $responder;

    public function __construct(Csrf $csrf, QuitResponder $responder)
    {
        $this->csrf = $csrf;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response): ResponseInterface
    {
        $loggedIn = $request->getAttribute('isLoggedIn');
        if ($loggedIn == false) {
            return $this->responder->authInvalid($response);
        }

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $data = [];
        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $request->getAttribute($nameKey);
        $data['value'] = $request->getAttribute($valueKey);
        $data['loggedIn'] = $request->getAttribute('isLoggedIn');

        return $this->responder->quit($response, $data);
    }
}