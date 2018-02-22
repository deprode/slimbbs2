<?php


namespace App\Domain;


use App\Exception\CsrfException;
use App\Exception\DeleteFailedException;
use App\Exception\NotAllowedException;
use App\Repository\CommentService;
use Slim\Http\Request;

class CommentDeleteFilter
{
    private $comment;

    public function __construct(CommentService $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param Request $request
     * @throws CsrfException
     * @throws \OutOfBoundsException
     * @throws NotAllowedException
     * @throws DeleteFailedException
     */
    public function delete(Request $request): void
    {
        $attributes = $request->getAttributes();

        $csrf_status = $attributes['csrf_status'] ?? '';
        if ($csrf_status === "bad_request") {
            throw new CsrfException();
        }

        $data = $request->getParsedBody();

        // Validation
        $validate = $attributes['has_errors'] ?? '';
        if ($validate) {
            throw new \OutOfBoundsException();
        }

        $user_id = $attributes['userId'] ?? 0;
        $is_anonymous = $user_id == 0;
        if ($is_anonymous) {
            throw new NotAllowedException();
        }

        $is_admin = $attributes['isAdmin'] ?? 0;
        if ($is_admin) {
            $delete = $this->comment->deleteCommentByAdmin($data['thread_id'], $data['comment_id']);
        } else {
            $delete = $this->comment->deleteComment($data['thread_id'], $data['comment_id'], $user_id);
        }

        if ($delete === false) {
            throw new DeleteFailedException();
        }
    }
}