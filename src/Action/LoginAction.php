<?php

namespace App\Action;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use App\Domain\AuthService;
use App\Domain\UserService;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;

class LoginAction
{
    private $logger;
    private $twitter;
    private $user;
    private $auth;

    public function __construct(LoggerInterface $logger, TwitterOAuth $twitter, UserService $user, AuthService $auth)
    {
        $this->logger = $logger;
        $this->twitter = $twitter;
        $this->user = $user;
        $this->auth = $auth;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/login' route");

        $callback = $request->getUri()->getBasePath() . '/login/callback';
        $request_token = $this->twitter->oauth('oauth/request_token', ['oauth_callback' => $callback]);

        $this->auth->setOAuthToken($request_token);

        $url = $this->twitter->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
        return $response->withRedirect($url, 303);
    }

    public function callback(Request $request, Response $response)
    {
        $oauth_token = $request->getParam('oauth_token');
        $oauth_verifier = $request->getParam('oauth_verifier');

        if ($this->auth->verifyToken($oauth_token) && $oauth_verifier) {
            $token = $this->auth->getOAuthToken();

            $connection = $this->twitter;
            $connection->setOauthToken($token['token'], $token['secret']);
            try {
                $access_token = $connection->oauth('oauth/access_token', ['oauth_verifier' => $oauth_verifier, 'oauth_token'=> $token['token']]);
            } catch (TwitterOAuthException $e) {
                return $response->withRedirect('/', 403);
            }

            // ユーザー情報の取得
            $user_connection = $this->twitter;
            $user_connection->setOauthToken($access_token['oauth_token'], $access_token['oauth_token_secret']);
            $user_info = $user_connection->get('account/verify_credentials');

            try {
                $user = $this->user->convertUser($user_info, $access_token);
                $this->user->saveUser($user);
            } catch (\PDOException $e) {
                return $response->withRedirect('/', 400);
            }

            $this->auth->regenerate();
            $this->auth->setUserInfo($user);
        }

        return $response->withRedirect('/', 303);
    }

}