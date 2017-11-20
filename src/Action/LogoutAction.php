<?php

namespace App\Action;

use App\Domain\AuthService;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;

class LogoutAction
{
    private $logger;
    private $auth;

    public function __construct(LoggerInterface $logger, AuthService $auth)
    {
        $this->logger = $logger;
        $this->auth = $auth;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/logout' route");

        $this->auth->logout();

        return $response->withRedirect('/', 303);
    }
}