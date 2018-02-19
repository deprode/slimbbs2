<?php

namespace App\Action;


use App\Service\CommentService;
use App\Exception\FetchFailedException;
use App\Responder\SearchResponder;
use Psr\Log\LoggerInterface;
use Slim\Csrf\Guard as Csrf;
use Slim\Http\Request;
use Slim\Http\Response;

class SearchAction
{
    private $logger;
    private $csrf;
    private $comment;
    private $responder;

    public function __construct(LoggerInterface $logger, Csrf $csrf, CommentService $comment, SearchResponder $responder)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
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
            $comment = $this->comment->convertTime($this->comment->searchComments($query));
        } catch (FetchFailedException $e) {
            $this->logger->error($e);
            return $this->responder->fetchFailed($response, '/');
        }

        $data = [];
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $request->getAttribute($nameKey);
        $data['value'] = $request->getAttribute($valueKey);
        $data['comments'] = $comment;
        $data['is_admin'] = $request->getAttribute('isAdmin');
        $data['loggedIn'] = $request->getAttribute('isLoggedIn');
        $data['query'] = $query;

        return $this->responder->comments($response, $data);
    }
}