<?php

namespace App\Domain;


use App\Model\CommentRead;
use App\Repository\CommentService;
use App\Repository\UserService;
use App\Service\MessageService;
use Slim\Csrf\Guard;
use Slim\Http\Request;

class ThreadFilter
{
    private $csrf;
    private $comment;
    private $message;
    private $user;
    private $s3_settings;

    public function __construct(Guard $csrf, CommentService $comment, MessageService $message, UserService $user, array $s3_settings)
    {
        $this->csrf = $csrf;
        $this->comment = $comment;
        $this->message = $message;
        $this->user = $user;
        $this->s3_settings = $s3_settings;
    }

    /**
     * @param CommentRead[] $comments
     * @return string
     */
    private function convertComment(array $comments)
    {
        $convert_comments = [];
        foreach ($comments as $comment) {
            $comment_array = $comment->toArray();
            $comment_array['created_at'] = $comment->createdAtStr();
            $convert_comments['c' . $comment->comment_id] = $comment_array;
        }

        return str_replace('\\', '\\\\', json_encode($convert_comments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    /**
     * @param Request $request
     * @return array
     * @throws \InvalidArgumentException
     * @throws \App\Exception\FetchFailedException
     * @throws \UnexpectedValueException
     */
    public function filtering(Request $request): array
    {
        $params = $request->getParams();
        $attributes = $request->getAttributes();
        $data = [];

        $thread_id = $params['thread_id'] ?? null;
        if (empty($thread_id) || !is_numeric($thread_id)) {
            throw new \InvalidArgumentException();
        }

        $data['thread_id'] = $thread_id;

        $comments = $this->comment->getComments((int)$thread_id);
        if (empty($comments)) {
            throw new \UnexpectedValueException();
        }
        $data['comments'] = $this->convertComment($comments);

        $data['comment_top'] = $this->comment->getTopComment((int)$thread_id);

        if (!empty($attributes['isLoggedIn'])) {
            $data['user'] = $this->user->getUser($attributes['username']);
        }

        // csrf
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $attributes[$nameKey] ?? '';
        $data['value'] = $attributes[$valueKey] ?? '';

        // auth
        $data['user_id'] = $attributes['userId'] ?? '';
        $data['is_admin'] = $attributes['isAdmin'] ?? '';
        $data['loggedIn'] = $attributes['isLoggedIn'] ?? '';

        // message
        $data['info'] = $this->message->getInfoMessage();
        $data['error'] = $this->message->getErrorMessage();

        // s3
        $data['region'] = $this->s3_settings['region'];
        $data['bucket'] = $this->s3_settings['bucket'];

        return $data;
    }
}