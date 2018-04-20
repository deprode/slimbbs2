<?php


namespace App\Domain;


use App\Exception\NotAllowedException;
use App\Exception\ValidationException;
use App\Model\CommentRead;
use App\Repository\CommentService;
use Slim\Http\Request;

class CommentsFilter
{
    private $comment;

    public function __construct(CommentService $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param Request $request
     * @return array
     * @throws \App\Exception\FetchFailedException
     * @throws NotAllowedException
     * @throws ValidationException
     */
    public function filtering(Request $request): array
    {
        if (!$request->isXhr()) {
            throw new NotAllowedException();
        }

        if ($request->getAttribute('has_errors')) {
            throw new ValidationException();
        }

        $attributes = $request->getAttributes();
        $comments = $this->comment->getComments($attributes['thread_id'], $attributes['comment_id']);

        $filtered_comments = [];
        /**
         * @var CommentRead $comment
         */
        foreach ($comments as &$comment) {
            $comment_array = $comment->toArray();
            $comment_array['created_at'] = $comment->createdAtStr();
            $filtered_comments['c' . $comment->comment_id] = $comment_array;
        }

        return $filtered_comments;
    }
}