<?php

namespace App\Domain;


use App\Exception\OAuthException;
use App\Exception\SaveFailedException;
use App\Exception\UploadFailedException;
use App\Repository\UserService;
use App\Service\OAuthService;
use App\Service\StorageService;
use Slim\Http\Request;

class LoginFilter
{
    private $user;
    private $oauth;
    private $storage;

    public function __construct(UserService $user, OAuthService $oauth, StorageService $storage)
    {
        $this->user = $user;
        $this->oauth = $oauth;
        $this->storage = $storage;
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

        $user_info['profile_image_url_https'] = $this->fetchUserProfileIconPath($user_info['profile_image_url_https']);
        $user = $this->user->convertUser($user_info, $access_token);
        $this->user->saveUser($user);

        $this->oauth->loginUser($user);
    }

    /**
     * @param string $icon_url
     * @return string
     * @throws SaveFailedException
     */
    private function fetchUserProfileIconPath(string $icon_url)
    {
        $icon = @file_get_contents($icon_url);
        if ($icon === false) {
            throw new SaveFailedException();
        }

        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer($file_info, $icon);
        finfo_close($file_info);

        try {
            $filename = $this->storage->upload($icon, $mime_type, 'icon/');
            $file_path = $this->storage->getFullPath($filename);
        } catch (UploadFailedException $e) {
            throw new SaveFailedException();
        }

        return $file_path;
    }

}