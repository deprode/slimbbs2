<?php

namespace App\Domain;


use App\Model\User;
use RKA\Session;

class AuthService
{
    private $session;
    private $admin_id;

    public function __construct(Session $session, int $admin_id)
    {
        $this->session = $session;
        $this->admin_id = $admin_id;
    }

    public function setOAuthToken(array $token)
    {
        $this->session->set('oauth_token', $token['oauth_token']);
        $this->session->set('oauth_token_secret', $token['oauth_token_secret']);
    }

    public function getOAuthToken()
    {
        $token = [];
        $token['token'] = $this->session->get('oauth_token');
        $token['secret'] = $this->session->get('oauth_token_secret');
        return $token;
    }

    public function verifyToken($token)
    {
        return $this->session->get('oauth_token') === $token;
    }

    public function setUserInfo(User $user)
    {
        $this->session->set('user_id', $user->user_id);
        $this->session->set('user_name', $user->user_name);
        $this->session->set('user_img', $user->user_image_url);
    }

    public function getUserId()
    {
        return $this->session->get('user_id') ?? 0;
    }

    public function equalUser(int $user_id)
    {
        return (int)$this->getUserId() === $user_id;
    }

    public function getAdminId()
    {
        return $this->admin_id;
    }

    public function isAdmin()
    {
        return $this->admin_id === (int)$this->getUserId();
    }

    public function isLoggedIn()
    {
        return $this->session->get('user_id') !== null;
    }

    public function regenerate()
    {
        $this->session->regenerate();
    }

    public function logout()
    {
        $this->session->destroy();
    }
}