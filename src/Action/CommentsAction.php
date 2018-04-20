<?php


namespace App\Action;


use App\Domain\CommentsFilter;
use App\Exception\FetchFailedException;
use App\Exception\NotAllowedException;
use App\Exception\ValidationException;
use App\Responder\CommentsResponder;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentsAction
{
    private $filter;
    private $responder;

    public function __construct(CommentsFilter $filter, CommentsResponder $responder)
    {
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function fetch(Request $request, Response $response): ResponseInterface
    {
        try {
            $comments = $this->filter->filtering($request);
            return $this->responder->success($response, $comments);
        } catch (NotAllowedException $e) {
            return $this->responder->invalid($response);
        } catch (ValidationException $e) {
            return $this->responder->failed($response);
        } catch (FetchFailedException $e) {
            return $this->responder->failed($response);
        }
    }
}