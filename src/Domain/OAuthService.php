<?php

namespace App\Domain;


use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use App\Exception\OAuthException;
use App\Model\User;

class OAuthService
{
    private $twitter;
    private $auth;
    private $callback_url;

    public function __construct(TwitterOAuth $twitter, AuthService $auth, string $callback_url)
    {
        $this->twitter = $twitter;
        $this->auth = $auth;
        $this->callback_url = $callback_url;
    }

    public function getLoginUrl(string $basepath): string
    {
        $request_token = $this->twitter->oauth('oauth/request_token', ['oauth_callback' => $basepath . $this->callback_url]);
        $this->auth->setOAuthToken($request_token);

        return $this->twitter->url('oauth/authorize', ['oauth_token' => $request_token['oauth_token']]);
    }

    public function verifyToken(string $oauth_token, string $oauth_verifier): bool
    {
        return ($this->auth->verifyToken($oauth_token) && $oauth_verifier);
    }

    /**
     * @throws OAuthException
     */
    public function oAuth(string $oauth_verifier): void
    {
        $connection = $this->twitter;
        $token = $this->auth->getOAuthToken();

        $connection->setOauthToken($token['token'], $token['secret']);
        try {
            $access_token = $connection->oauth('oauth/access_token', ['oauth_verifier' => $oauth_verifier, 'oauth_token'=> $token['token']]);
        } catch (TwitterOAuthException $e) {
            throw new OAuthException();
        }

        $this->auth->setOAuthToken($access_token);
    }

    public function getUserInfo(): \stdClass
    {
        $access_token = $this->auth->getOAuthToken();
        $user_connection = $this->twitter;
        $user_connection->setOauthToken($access_token['token'], $access_token['secret']);

        return $user_connection->get('account/verify_credentials');
    }

    public function getToken(): array
    {
        return $this->auth->getOAuthToken();
    }

    public function loginUser(User $user): void
    {
        $this->auth->regenerate();
        $this->auth->setUserInfo($user);
    }
}