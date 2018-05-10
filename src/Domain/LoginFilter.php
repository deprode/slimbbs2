<?php

namespace App\Domain;


use App\Exception\OAuthException;
use App\Repository\UserService;
use App\Service\OAuthService;
use Slim\Http\Request;

class LoginFilter
{
    private $user;
    private $oauth;

    public function __construct(UserService $user, OAuthService $oauth)
    {
        $this->user = $user;
        $this->oauth = $oauth;
    }

    /**
     * @param Request $request
     * @return void
     * @throws OAuthException
     * @throws \App\Exception\SaveFailedException
     */
    public function save(Request $request): void
    {
        $params = $request->getParams();

        $oauth_token = $params['oauth_token'];
        $oauth_verifier = $params['oauth_verifier'];

        if (!$oauth_verifier || !$this->oauth->verifyToken($oauth_token, $oauth_verifier)) {
            throw new OAuthException();
        }
        $this->oauth->oAuth($oauth_verifier);

        // ユーザー情報の取得
        $user_info = (array)$this->oauth->getUserInfo();
        $access_token = $this->oauth->getToken();

        $user = $this->user->convertUser($user_info, $access_token);
        $this->user->saveUser($user);

        $this->oauth->loginUser($user);
    }
}