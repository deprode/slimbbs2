<?php

namespace App\Action;

use App\Domain\LoginFilter;
use App\Exception\OAuthException;
use App\Exception\SaveFailedException;
use App\Responder\LoginResponder;
use App\Service\OAuthService;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class LoginAction
{
    private $logger;
    private $oauth;
    private $filter;
    private $responder;

    public function __construct(LoggerInterface $logger, OAuthService $oauth, LoginFilter $filter, LoginResponder $responder)
    {
        $this->logger = $logger;
        $this->oauth = $oauth;
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/login' route");
        $url = $this->oauth->getLoginUrl($request->getUri()->getBaseUrl());

        return $response->withRedirect($url, 303);
    }

    public function callback(Request $request, Response $response)
    {
        try {
            $this->filter->save($request);
            return $this->responder->success($response, '/');
        } catch (OAuthException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->oAuthFailed($response);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->saveFailed($response);
        }
    }

}