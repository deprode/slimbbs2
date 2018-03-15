<?php

namespace App\Domain;


use App\Repository\CommentService;
use App\Repository\UserService;
use App\Service\AuthService;
use Slim\Http\Request;

class UserFilter
{
    private $user;
    private $comment;
    private $auth;
    private $s3_settings;

    public function __construct(UserService $user, CommentService $comment, AuthService $auth, array $s3_settings)
    {
        $this->user = $user;
        $this->comment = $comment;
        $this->auth = $auth;
        $this->s3_settings = $s3_settings;
    }

    /**
     * @param Request $request
     * @param array $args
     * @return array
     * @throws \UnexpectedValueException
     * @throws \App\Exception\FetchFailedException
     */
    public function filtering(Request $request, array $args): array
    {
        $attributes = $request->getAttributes();

        $username = $args['name'] ?? $attributes['username'] ?? '';

        if (empty($username)) {
            throw new \UnexpectedValueException();
        }

        $data = [];

        $data['user'] = $this->user->getUser($username);

        $user_id = $data['user']->user_id;

        $limit = $this->needsLimit($this->auth->isLoggedIn(), $this->auth->equalUser($user_id));
        $data['comments'] = $this->comment->getCommentsByUser($user_id, $limit);

        $data['loggedIn'] = $attributes['isLoggedIn'] ?? '';
        $data['user_id'] = $attributes['userId'] ?? '';
        $data['same_user'] = $this->auth->equalUser($user_id);

        // s3
        $data['region'] = $this->s3_settings['region'];
        $data['bucket'] = $this->s3_settings['bucket'];

        return $data;
    }

    /**
     * ログインしていてユーザー名が同じ場合以外は、表示数制限
     * @param $is_logged_in
     * @param $equal_user
     * @return bool
     */
    protected function needsLimit($is_logged_in, $equal_user)
    {
        return !($is_logged_in && $equal_user);
    }

}