<?php


namespace App\Action;

use App\Domain\CommentFilter;
use App\Exception\FetchFailedException;
use App\Responder\CommentResponder;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentAction
{
    private $filter;
    private $responder;

    public function __construct(CommentFilter $filter, CommentResponder $responder)
    {
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        try {
            $data = $this->filter->filtering($request);
            return $this->responder->index($response, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->responder->invalid($response, '/');
        } catch (FetchFailedException $e) {
            return $this->responder->fetchFailed($response, '/');
        }
    }
}