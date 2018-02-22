<?php


namespace App\Domain;


use App\Exception\NotAllowedException;
use App\Exception\SaveFailedException;
use App\Repository\CommentService;
use Slim\Http\Request;

class LikeFilter
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
     * @throws SaveFailedException
     */
    public function update(Request $request): void
    {
        if (!$request->isXhr()) {
            throw new NotAllowedException();
        }

        if ($request->getAttribute('has_errors')) {
            throw new \OutOfBoundsException();
        }

        $params = $request->getParsedBody();
        $comment_id = $params['comment_id'] ?? 0;
        $thread_id = $params['thread_id'] ?? 0;

        $result = $this->comment->addLike($thread_id, $comment_id);
        if ($result === false) {
            throw new SaveFailedException();
        }
    }
}