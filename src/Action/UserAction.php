<?php


namespace App\Action;


use App\Domain\UserFilter;
use App\Exception\FetchFailedException;
use App\Responder\UserResponder;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class UserAction
{
    private $filter;
    private $responder;

    public function __construct(UserFilter $filter, UserResponder $responder)
    {
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response, array $args): ResponseInterface
    {
        try {
            return $this->responder->index($response, $this->filter->filtering($request, $args));
        } catch (\UnexpectedValueException $e) {
            return $this->responder->nameEmpty($response);
        } catch (FetchFailedException $e) {
            return $this->responder->fetchFailed($response);
        }
    }
}