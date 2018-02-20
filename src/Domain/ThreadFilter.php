<?php

namespace App\Domain;


use App\Model\Sort;
use App\Repository\CommentService;
use App\Service\MessageService;
use Slim\Csrf\Guard;
use Slim\Http\Request;

class ThreadFilter
{
    private $csrf;
    private $comment;
    private $message;
    private $s3_settings;

    public function __construct(Guard $csrf, CommentService $comment, MessageService $message, array $s3_settings)
    {
        $this->csrf = $csrf;
        $this->comment = $comment;
        $this->message = $message;
        $this->s3_settings = $s3_settings;
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

        $data['sort'] = new Sort($params['sort'] ?? 'desc');
        $data['thread_id'] = $thread_id;

        $data['comments'] = $this->comment->convertTime(
            $this->comment->getComments((int)$thread_id, $data['sort'])
        );
        if (empty($data['comments'])) {
            throw new \UnexpectedValueException();
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