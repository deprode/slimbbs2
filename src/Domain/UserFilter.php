<?php

namespace App\Domain;


use App\Repository\CommentService;
use App\Repository\UserService;
use Slim\Http\Request;

class UserFilter
{
    private $user;
    private $comment;

    public function __construct(UserService $user, CommentService $comment)
    {
        $this->user = $user;
        $this->comment = $comment;
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

        $user = $this->user->getUser($username);
        $data['image_url'] = $user['user_image_url'] ?? '';
        $data['id'] = $user['user_id'] ?? '';
        $data['name'] = $user['user_name'] ?? '';   // ユーザーが指定したユーザーネーム

        $data['comments'] = $this->comment->convertTime($this->comment->getCommentsByUser($user['user_id']));

        $data['loggedIn'] = $attributes['isLoggedIn'] ?? '';
        $data['user_id'] = $attributes['userId'] ?? '';
        $data['username'] = $attributes['username'] ?? '';  // sessionのユーザーネーム

        return $data;
    }
}