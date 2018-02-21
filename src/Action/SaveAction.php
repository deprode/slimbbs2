<?php

namespace App\Action;

use App\Domain\SaveFilter;
use App\Exception\CsrfException;
use App\Exception\SaveFailedException;
use App\Responder\SaveResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class SaveAction
{
    private $logger;
    private $filter;
    private $responder;

    public function __construct(LoggerInterface $logger, SaveFilter $filter, SaveResponder $responder)
    {
        $this->logger = $logger;
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route save");

        try {
            $this->filter->save($request);
            return $this->responder->saved($response, '/');
        } catch (CsrfException $e) {
            return $this->responder->csrfInvalid($response);
        } catch (\UnexpectedValueException $e) {
            return $this->responder->invalid($response, '/');
        } catch (\OutOfBoundsException $e) {
            return $this->responder->invalid($response, '/');
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->saveFailed($response, '/');
        }
    }
}