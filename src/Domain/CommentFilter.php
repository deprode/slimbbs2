<?php

namespace App\Domain;

use App\Repository\CommentService;
use Slim\Http\Request;

class CommentFilter
{
    private $comment;

    public function __construct(CommentService $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param Request $request
     * @return array
     * @throws \InvalidArgumentException
     * @throws \App\Exception\FetchFailedException
     */
    public function filtering(Request $request): array
    {
        $attributes = $request->getAttributes();

        $comment_id = $attributes['comment_id'] ?? '';

        if (!is_numeric($comment_id)) {
            throw new \InvalidArgumentException();
        }

        $data = [];
        $data['comment'] = $this->comment->getComment($comment_id);

        // auth
        $data['is_admin'] = $attributes['isAdmin'] ?? '';
        $data['loggedIn'] = $attributes['isLoggedIn'] ?? '';

        return $data;
    }
}