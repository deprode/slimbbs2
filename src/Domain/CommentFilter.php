<?php

namespace App\Domain;

use App\Repository\CommentService;
use Slim\Http\Request;

class CommentFilter
{
    private $comment;
    private $s3_settings;

    public function __construct(CommentService $comment, array $s3_settings)
    {
        $this->comment = $comment;
        $this->s3_settings = $s3_settings;
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

        // s3
        $data['region'] = $this->s3_settings['region'];
        $data['bucket'] = $this->s3_settings['bucket'];

        return $data;
    }
}