<?php

namespace App\Action;

use App\Service\OAuthService;
use App\Repository\UserService;
use App\Exception\OAuthException;
use App\Exception\SaveFailedException;
use App\Responder\LoginResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class LoginAction
{
    private $logger;
    private $user;
    private $oauth;
    private $responder;

    public function __construct(LoggerInterface $logger, UserService $user, OAuthService $oauth, LoginResponder $responder)
    {
        $this->logger = $logger;
        $this->user = $user;
        $this->oauth = $oauth;
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
        $oauth_token = $request->getParam('oauth_token');
        $oauth_verifier = $request->getParam('oauth_verifier');

        if ($oauth_verifier && $this->oauth->verifyToken($oauth_token, $oauth_verifier)) {

            try {
                $this->oauth->oAuth($oauth_verifier);
            } catch (OAuthException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                return $this->responder->oAuthFailed($response);
            }

            // ユーザー情報の取得
            $user_info = $this->oauth->getUserInfo();
            $access_token = $this->oauth->getToken();

            try {
                $user = $this->user->convertUser($user_info, $access_token);
                $this->user->saveUser($user);
            } catch (SaveFailedException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                return $this->responder->saveFailed($response);
            }

            $this->oauth->loginUser($user);

            return $this->responder->success($response, '/');
        }

        return $this->responder->oAuthFailed($response);
    }

}