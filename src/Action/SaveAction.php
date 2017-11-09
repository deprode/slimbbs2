<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;

class SaveAction
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response)
    {
        // Sample log message
        $this->logger->info("Slimbbs '/' route save");
        $args = [];

        if ($request->getAttribute('csrf_status') === "bad_request") {
            $response = $response->withStatus(400);
            return $response->withRedirect('/');
        }

        // Validation
        if($request->getAttribute('has_errors')){
            $response = $response->withStatus(400);
            return $response->withRedirect('/');
        }

        $data = $request->getParsedBody();

        $args['body'] = $data['body'];

        $response = $response->withStatus(303);
        return $response->withRedirect('/');
    }
}