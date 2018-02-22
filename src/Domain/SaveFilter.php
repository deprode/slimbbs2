<?php


namespace App\Domain;


use App\Exception\CsrfException;
use App\Exception\NotAllowedException;
use App\Model\Comment;
use App\Repository\CommentService;
use App\Service\AuthService;
use Slim\Http\Request;

class SaveFilter
{
    private $auth;
    private $comment;

    public function __construct(AuthService $auth, CommentService $comment)
    {
        $this->auth = $auth;
        $this->comment = $comment;
    }

    /**
     * @param Request $request
     * @throws CsrfException
     * @throws NotAllowedException
     * @throws \App\Exception\SaveFailedException
     */
    public function save(Request $request): void
    {
        $attributes = $request->getAttributes();

        $csrf_status = $attributes['csrf_status'] ?? '';
        if ($csrf_status === "bad_request") {
            throw new CsrfException();
        }

        $data = $request->getParsedBody();
        $user_id = $data['user_id'] ?? 0;
        // 認証されたユーザと違うIDが送信された
        if (!$this->auth->equalUser((int)$user_id)) {
            throw new NotAllowedException();
        }

        // Validation
        $validate = $attributes['has_errors'] ?? '';
        if (empty($validate) === false) {
            throw new \OutOfBoundsException();
        }

        $comment = new Comment();
        $comment->comment = $data['comment'];
        $comment->user_id = $data['user_id'];
        $this->comment->saveThread($comment);
    }
}