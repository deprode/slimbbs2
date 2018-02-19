<?php

namespace App\Service;


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

    public function setOAuthToken(array $token): void
    {
        $this->session->set('oauth_token', $token['oauth_token']);
        $this->session->set('oauth_token_secret', $token['oauth_token_secret']);
    }

    public function getOAuthToken(): array
    {
        $token = [];
        $token['token'] = $this->session->get('oauth_token');
        $token['secret'] = $this->session->get('oauth_token_secret');
        return $token;
    }

    public function verifyToken($token): bool
    {
        return $this->session->get('oauth_token') === $token;
    }

    public function setUserInfo(User $user): void
    {
        $this->session->set('user_id', $user->user_id);
        $this->session->set('user_name', $user->user_name);
        $this->session->set('user_img', $user->user_image_url);
    }

    public function getUsername(): string
    {
        return $this->session->get('user_name') ?? '';
    }

    public function getUserId(): int
    {
        return $this->session->get('user_id') ?? 0;
    }

    public function equalUser(int $user_id): bool
    {
        return (int)$this->getUserId() === $user_id;
    }

    public function getAdminId(): int
    {
        return $this->admin_id;
    }

    public function isAdmin(): bool
    {
        return $this->getAdminId() === $this->getUserId();
    }

    public function isLoggedIn(): bool
    {
        return $this->session->get('user_id') !== null;
    }

    public function regenerate(): void
    {
        $this->session->regenerate();
    }

    public function logout(): void
    {
        $this->session->destroy();
    }
}