<?php

namespace App\Action;


use App\Domain\CommentService;
use App\Domain\MessageService;
use App\Exception\SaveFailedException;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class LikeAction
{
    private $logger;
    private $comment;
    private $message;

    public function __construct(LoggerInterface $log, CommentService $comment, MessageService $message)
    {
        $this->logger = $log;
        $this->comment = $comment;
        $this->message = $message;
    }

    public function add(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/like' route save");

        if (!$request->isXhr()) {
            return $response->withJson([], 500);
        }

        if ($request->getAttribute('has_errors')) {
            return $response->withStatus(400);
        }

        $comment_id = $request->getParsedBodyParam('comment_id');
        $thread_id = $request->getParsedBodyParam('thread_id');
        try {
            $this->comment->addLike($thread_id, $comment_id);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['excepion' => $e]);
            return $response->withStatus(500);
        }

        // MEMO: withStatusを使うとwithJSONでデータが渡せなくなるので、PHP側ではwithStatusを使う
        return $response->withStatus(204);
    }

}