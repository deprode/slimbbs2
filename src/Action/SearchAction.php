<?php

namespace App\Action;


use App\Domain\SearchFilter;
use App\Exception\FetchFailedException;
use App\Exception\ValidationException;
use App\Responder\SearchResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class SearchAction
{
    private $logger;
    private $filter;
    private $responder;

    public function __construct(LoggerInterface $logger, SearchFilter $filter, SearchResponder $responder)
    {
        $this->logger = $logger;
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function comments(Request $request, Response $response)
    {
        try {
            return $this->responder->comments($response, $this->filter->filtering($request));
        } catch (ValidationException $e) {
            return $this->responder->emptyQuery($response, '/');
        } catch (FetchFailedException $e) {
            $this->logger->error($e);
            return $this->responder->fetchFailed($response, '/');
        }
    }
}