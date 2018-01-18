<?php

namespace App\Action;


use App\Domain\AuthService;
use App\Domain\CommentService;
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
    private $auth;
    private $comment;
    private $responder;

    public function __construct(LoggerInterface $logger, Csrf $csrf, AuthService $auth, CommentService $comment, SearchResponder $responder)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
        $this->auth = $auth;
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

        $data = [];
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $request->getAttribute($nameKey);
        $data['value'] = $request->getAttribute($valueKey);
        $data['comments'] = $comment;
        $data['is_admin'] = $this->auth->isAdmin();
        $data['loggedIn'] = $this->auth->isLoggedIn();
        $data['query'] = $query;

        return $this->responder->comments($response, $data);
    }
}