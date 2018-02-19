<?php

namespace App\Action;

use App\Domain\HomeFilter;
use App\Exception\FetchFailedException;
use App\Responder\HomeResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class HomeAction
{
    private $logger;
    private $filter;
    private $responder;

    public function __construct(LoggerInterface $logger, HomeFilter $filter, HomeResponder $responder)
    {
        $this->logger = $logger;
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route");

        try {
            // Render index view
            return $this->responder->index($response, $this->filter->filtering($request));
        } catch (FetchFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->fetchFailed($response);
        }
    }
}