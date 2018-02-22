<?php

namespace App\Action;


use App\Domain\LikeFilter;
use App\Exception\NotAllowedException;
use App\Exception\SaveFailedException;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class LikeAction
{
    private $logger;
    private $filter;

    public function __construct(LoggerInterface $log, LikeFilter $filter)
    {
        $this->logger = $log;
        $this->filter = $filter;
    }

    public function add(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/like' route save");

        try {
            $this->filter->update($request);

            // MEMO: withStatusを使うとwithJSONでデータが渡せなくなるので、PHP側ではwithStatusを使う
            return $response->withStatus(204);
        } catch (NotAllowedException $e) {
            return $response->withJson([], 500);
        } catch (\OutOfBoundsException $e) {
            return $response->withStatus(400);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $response->withStatus(500);
        }
    }

}