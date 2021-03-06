<?php

namespace App\Middleware;


use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthMiddleware
{
    private $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function AddAuth(ServerRequestInterface $request): ServerRequestInterface
    {
        $request = $request->withAttribute('userId', $this->auth->getUserId());
        $request = $request->withAttribute('adminId', $this->auth->getAdminId());
        $request = $request->withAttribute('isAdmin', (int)$this->auth->isAdmin());
        $request = $request->withAttribute('isLoggedIn', (int)$this->auth->isLoggedIn());
        $request = $request->withAttribute('username', $this->auth->getUsername());
        $request = $request->withAttribute('userHash', $this->auth->getUserHash());

        return $request;
    }

    function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $request = $this->AddAuth($request);

        $response = $next($request, $response);

        return $response;
    }
}