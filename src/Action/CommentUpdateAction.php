<?php

namespace App\Action;


use App\Domain\CommentUpdateFilter;
use App\Exception\NotAllowedException;
use App\Exception\SaveFailedException;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentUpdateAction
{
    private $logger;
    private $filter;

    public function __construct(LoggerInterface $logger, CommentUpdateFilter $filter)
    {
        $this->logger = $logger;
        $this->filter = $filter;
    }

    public function update(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/thread' route comment update");

        try {
            $this->filter->update($request);
            return $response->withStatus(204);
        } catch (NotAllowedException $e) {
            return $response->withJson([], 500);
        } catch (\OutOfBoundsException $e) {
            return $response->withStatus(400);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $response->withStatus(400);
        }
    }
}