<?php

namespace App\Action;

use Abraham\TwitterOAuth\TwitterOAuthException;
use App\Domain\OAuthService;
use App\Domain\UserService;
use App\Responder\LoginResponder;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;

class LoginAction
{
    private $logger;
    private $twitter;
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

        if ($this->oauth->verifyToken($oauth_token, $oauth_verifier)) {

            try {
                $this->oauth->oAuth($oauth_verifier);
            } catch (TwitterOAuthException $e) {
                $this->responder->oAuthFailed($response, '/');
            }

            // ユーザー情報の取得
            $user_info = $this->oauth->getUserInfo();
            $access_token = $this->oauth->getToken();

            try {
                $user = $this->user->convertUser($user_info, $access_token);
                $this->user->saveUser($user);
            } catch (\PDOException $e) {
                $this->responder->saveFailed($response, '/');
            }

            $this->oauth->loginUser($user);

            return $this->responder->success($response, '/');
        }

        $this->responder->oAuthFailed($response, '/');
    }

}