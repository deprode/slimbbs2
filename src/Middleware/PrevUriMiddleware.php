<?php


namespace App\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RKA\Session;

class PrevUriMiddleware
{
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    protected function getPrevUri(ServerRequestInterface $request): ServerRequestInterface
    {
        $prev_uri = $this->session->get('PrevUri');

        $request = $request->withAttribute('PREV_URI', $prev_uri);

        return $request;
    }

    protected function setNowUri(ServerRequestInterface $request): void
    {
        $scheme = $request->getUri()->getScheme() . '://';
        $host = $request->getUri()->getHost();
        $port = $request->getUri()->getPort() != 80 ? ':' . $request->getUri()->getPort() : '';
        $path = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery() ? '?' . $request->getUri()->getQuery() : '';

        $this->session->set('PrevUri', $scheme . $host . $port . $path . $query);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $request = $this->getPrevUri($request);

        $this->setNowUri($request);

        $response = $next($request, $response);

        return $response;
    }
}