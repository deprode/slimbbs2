<?php

namespace App\Action;

use App\Domain\LoginFilter;
use App\Exception\OAuthException;
use App\Exception\SaveFailedException;
use App\Responder\LoginResponder;
use App\Service\OAuthService;
use Psr\Log\LoggerInterface;
use RKA\Session;
use Slim\Http\Request;
use Slim\Http\Response;

class LoginAction
{
    private $logger;
    private $oauth;
    private $session;
    private $filter;
    private $responder;

    public function __construct(LoggerInterface $logger, OAuthService $oauth, Session $session, LoginFilter $filter, LoginResponder $responder)
    {
        $this->logger = $logger;
        $this->oauth = $oauth;
        $this->session = $session;
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/login' route");

        // ログイン後にリダイレクトするURLをセッションに記憶させる
        $this->session->set('logged_in_redirect_uri', $request->getAttribute('PREV_URI'));

        $scheme = $request->getUri()->getScheme() . '://';
        $host = $request->getUri()->getHost();
        $port = ($request->getUri()->getPort() && $request->getUri()->getPort() != 80) ? ':' . $request->getUri()->getPort() : '';
        $url = $this->oauth->getLoginUrl($scheme . $host . $port);

        return $response->withRedirect($url, 303);
    }

    public function callback(Request $request, Response $response)
    {
        $url = $this->session->get('logged_in_redirect_uri') ?? '/';
        try {
            $this->filter->save($request);
            return $this->responder->success($response, $url);
        } catch (OAuthException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->oAuthFailed($response);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->saveFailed($response);
        }
    }

}