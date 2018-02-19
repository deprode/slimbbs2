<?php

namespace App\Action;


use App\Repository\CommentService;
use App\Exception\SaveFailedException;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentUpdateAction
{
    private $logger;
    private $comment;

    public function __construct(LoggerInterface $logger, CommentService $comment)
    {
        $this->logger = $logger;
        $this->comment = $comment;
    }

    public function update(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/thread' route comment update");

        if (!$request->isXhr()) {
            return $response->withJson([], 500);
        }

        if ($request->getAttribute('has_errors')) {
            return $response->withStatus(400);
        }

        $thread_id = $request->getParsedBodyParam('thread_id');
        $comment_id = $request->getParsedBodyParam('comment_id');
        $comment = $request->getParsedBodyParam('comment');
        try {
            $this->comment->updateComment($thread_id, $comment_id, $comment);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $response->withStatus(400);
        }

        return $response->withStatus(204);
    }
}