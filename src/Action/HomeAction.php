<?php

namespace App\Action;

use App\Domain\CommentService;
use App\Responder\HomeResponder;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;
use Slim\Csrf\Guard as Csrf;

final class HomeAction
{
    private $logger;
    private $csrf;
    private $comments;
    private $responder;

    public function __construct(LoggerInterface $logger, Csrf $csrf, CommentService $comments, HomeResponder $responder)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
        $this->comments = $comments;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route");

        $data['comments'] = $this->comments->getComments();

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $request->getAttribute($nameKey);
        $data['value'] = $request->getAttribute($valueKey);

        // Render index view
        return $this->responder->index($response, $data);
    }
}