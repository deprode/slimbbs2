<?php


namespace App\Domain;


use App\Exception\FetchFailedException;
use App\Exception\NotAllowedException;
use App\Exception\SaveFailedException;
use App\Exception\ValidationException;
use App\Repository\CommentService;
use App\Repository\UserService;
use App\Service\AuthService;
use Slim\Http\Request;

class CommentUpdateFilter
{
    private $comment;
    private $auth;
    private $user;

    public function __construct(CommentService $comment, AuthService $auth, UserService $user)
    {
        $this->comment = $comment;
        $this->auth = $auth;
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @throws NotAllowedException
     * @throws ValidationException
     * @throws \App\Exception\SaveFailedException
     */
    public function update(Request $request): void
    {
        if (!$request->isXhr()) {
            throw new NotAllowedException();
        }

        $validation_status = $request->getAttribute('has_errors');
        if ($validation_status) {
            throw new ValidationException();
        }

        $params = $request->getParsedBody();
        $thread_id = $params['thread_id'] ?? 0;
        $comment_id = $params['comment_id'] ?? 0;
        $user_id = $params['user_id'] ?? null;
        $comment = $params['comment'] ?? null;

        try {
            $token = $this->user->getUserToken($user_id);
        } catch (FetchFailedException $e) {
            throw new ValidationException;
        }

        if (!$this->auth->verifyUserHash($token, $params['user_hash'])) {
            throw new ValidationException();
        }

        $count = $this->comment->updateComment($thread_id, $comment_id, $user_id, $comment);
        if ($count !== 1) {
            throw new SaveFailedException();
        }
    }
}