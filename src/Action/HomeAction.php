<?php

namespace App\Action;

use App\Exception\FetchFailedException;
use App\Repository\ThreadService;
use App\Responder\HomeResponder;
use App\Service\MessageService;
use Psr\Log\LoggerInterface;
use Slim\Csrf\Guard as Csrf;
use Slim\Http\Request;
use Slim\Http\Response;

final class HomeAction
{
    private $logger;
    private $csrf;
    private $thread;
    private $message;
    private $responder;

    public function __construct(LoggerInterface $logger, Csrf $csrf, ThreadService $thread, MessageService $message, HomeResponder $responder)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
        $this->thread = $thread;
        $this->message = $message;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route");

        $data['loggedIn'] = $request->getAttribute('isLoggedIn');

        $sort = $request->getParam('sort');
        try {
            $threads = $this->thread->getThreads($sort);
            $data['threads'] = $this->thread->convertTime($threads);
        } catch (FetchFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->fetchFailed($response);
        }

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $request->getAttribute($nameKey);
        $data['value'] = $request->getAttribute($valueKey);
        $data['sort'] = $sort;
        $data['user_id'] = $request->getAttribute('userId');

        $data['info'] = $this->message->getMessage($this->message::INFO);
        $data['error'] = $this->message->getMessage($this->message::ERROR);

        // Render index view
        return $this->responder->index($response, $data);
    }
}