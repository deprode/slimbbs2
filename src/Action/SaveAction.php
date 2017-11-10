<?php

namespace App\Action;

use App\Responder\SaveResponder;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;

class SaveAction
{
    private $logger;
    private $responder;

    public function __construct(LoggerInterface $logger, SaveResponder $responder)
    {
        $this->logger = $logger;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        // Sample log message
        $this->logger->info("Slimbbs '/' route save");

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->csrf_invalid($response);
        }

        // Validation
        if($request->getAttribute('has_errors')){
            return $this->responder->invalid($response);
        }

        $data = $request->getParsedBody();

        return $this->responder->saved($response, $data);
    }
}