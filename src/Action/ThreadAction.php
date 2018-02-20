<?php

namespace App\Action;

use App\Domain\ThreadFilter;
use App\Exception\FetchFailedException;
use App\Responder\ThreadResponder;
use App\Service\MessageService;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ThreadAction
{
    private $logger;
    private $message;
    private $filter;
    private $responder;

    public function __construct(LoggerInterface $logger, MessageService $message, ThreadFilter $filter, ThreadResponder $responder)
    {
        $this->logger = $logger;
        $this->message = $message;
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route");
        try {
            return $this->responder->index($response, $this->filter->filtering($request));
        } catch (\InvalidArgumentException $e) {
            return $this->responder->invalid($response, '/');
        } catch (FetchFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->fetchFailed($response, '/');
        } catch (\UnexpectedValueException $e) {
            $this->message->setMessage($this->message::INFO, 'DeletedThread');
            return $this->responder->invalid($response, '/');
        }
    }
}