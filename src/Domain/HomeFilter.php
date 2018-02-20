<?php

namespace App\Domain;


use App\Exception\FetchFailedException;
use App\Repository\ThreadService;
use App\Service\MessageService;
use Slim\Csrf\Guard as Csrf;
use Slim\Http\Request;

class HomeFilter
{
    private $thread;
    private $message;
    private $csrf;

    public function __construct(ThreadService $thread, MessageService $message, Csrf $csrf)
    {
        $this->thread = $thread;
        $this->message = $message;
        $this->csrf = $csrf;
    }

    /**
     * @param Request $request
     * @return array
     * @throws FetchFailedException
     */
    public function filtering(Request $request): array
    {
        $param = $request->getParams();
        $attributes = $request->getAttributes();
        $data = [];

        // threads
        $data['sort'] = $param['sort'] ?? '';
        $threads = $this->thread->getThreads($data['sort']);
        $data['threads'] = $this->thread->convertTime($threads);

        // csrf
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $attributes[$nameKey] ?? '';
        $data['value'] = $attributes[$valueKey] ?? '';

        // auth
        $data['loggedIn'] = $attributes['isLoggedIn'] ?? '';
        $data['user_id'] = $attributes['userId'] ?? '';

        // message
        $data['info'] = $this->message->getInfoMessage();
        $data['error'] = $this->message->getErrorMessage();

        return $data;
    }
}