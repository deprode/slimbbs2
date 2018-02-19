<?php

namespace App\Action;

use App\Service\CommentService;
use App\Service\MessageService;
use App\Exception\FetchFailedException;
use App\Model\Sort;
use App\Responder\ThreadResponder;
use Psr\Log\LoggerInterface;
use Slim\Csrf\Guard as Csrf;
use Slim\Http\Request;
use Slim\Http\Response;

class ThreadAction
{
    private $logger;
    private $csrf;
    private $comment;
    private $message;
    private $responder;
    private $settings;

    public function __construct(LoggerInterface $logger, Csrf $csrf, CommentService $comment, MessageService $message, ThreadResponder $responder, array $settings)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
        $this->comment = $comment;
        $this->message = $message;
        $this->responder = $responder;
        $this->settings = $settings;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route");

        try {
            $thread_id = $request->getParam('thread_id');
            if (empty($thread_id) || !is_numeric($thread_id)) {
                throw new \InvalidArgumentException();
            }

            $param = $request->getParam('sort') ?? 'desc';
            $sort = new Sort($param);
        } catch (\InvalidArgumentException $e) {
            return $this->responder->invalid($response, '/');
        }

        try {
            $data['comments'] = $this->comment->convertTime($this->comment->getComments((int)$thread_id, $sort));

        } catch (FetchFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->fetchFailed($response, '/');
        }

        if (empty($data['comments'])) {
            $this->message->setMessage($this->message::INFO, 'DeletedThread');
            return $this->responder->invalid($response, '/');
        }

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $request->getAttribute($nameKey);
        $data['value'] = $request->getAttribute($valueKey);
        $data['thread_id'] = $thread_id;
        $data['sort'] = $sort;
        $data['user_id'] = $request->getAttribute('userId');
        $data['is_admin'] = $request->getAttribute('isAdmin');
        $data['loggedIn'] = $request->getAttribute('isLoggedIn');
        $data['info'] = $this->message->getMessage($this->message::INFO);
        $data['error'] = $this->message->getMessage($this->message::ERROR);
        $data['region'] = $this->settings['region'];
        $data['bucket'] = $this->settings['bucket'];

        // Render index view
        return $this->responder->index($response, $data);
    }
}