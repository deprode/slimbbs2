<?php

namespace App\Action;


use App\Domain\CommentService;
use App\Exception\FetchFailedException;
use App\Responder\SearchResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class SearchAction
{
    private $logger;
    private $comment;
    private $responder;

    public function __construct(LoggerInterface $logger, CommentService $comment, SearchResponder $responder)
    {
        $this->logger = $logger;
        $this->comment = $comment;
        $this->responder = $responder;
    }

    public function comments(Request $request, Response $response)
    {
        $query = $request->getParam('query');

        if (empty($query)) {
            return $this->responder->emptyQuery($response, '/');
        }

        try {
            $comment = $this->comment->searchComments($query);
        } catch (FetchFailedException $e) {
            $this->logger->error($e);
            return $this->responder->fetchFailed($response);
        }

        return $this->responder->comments($response, ['comments' => $comment]);
    }
}