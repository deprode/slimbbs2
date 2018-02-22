<?php


namespace App\Domain;


use App\Exception\NotAllowedException;
use App\Repository\CommentService;
use Slim\Http\Request;

class CommentUpdateFilter
{
    private $comment;

    public function __construct(CommentService $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param Request $request
     * @throws NotAllowedException
     * @throws \OutOfBoundsException
     * @throws \App\Exception\SaveFailedException
     */
    public function update(Request $request): void
    {
        if (!$request->isXhr()) {
            throw new NotAllowedException();
        }

        $validation_status = $request->getAttribute('has_errors');
        if ($validation_status) {
            throw new \OutOfBoundsException();
        }

        $params = $request->getParsedBody();
        $thread_id = $params['thread_id'] ?? 0;
        $comment_id = $params['comment_id'] ?? 0;
        $comment = $params['comment'] ?? null;

        $this->comment->updateComment($thread_id, $comment_id, $comment);
    }
}