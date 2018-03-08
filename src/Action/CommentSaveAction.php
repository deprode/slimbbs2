<?php

namespace App\Action;


use App\Domain\CommentSaveFilter;
use App\Exception\CsrfException;
use App\Exception\SaveFailedException;
use App\Exception\UploadFailedException;
use App\Exception\ValidationException;
use App\Model\Sort;
use App\Responder\SaveResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentSaveAction
{
    private $logger;
    private $filter;
    private $responder;

    public function __construct(LoggerInterface $logger, CommentSaveFilter $filter, SaveResponder $responder)
    {
        $this->logger = $logger;
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function save(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route comment save");

        // make redirect url
        $data = $request->getParsedBody();

        try {
            $sort = new Sort($data['sort'] ?? 'desc');
        } catch (\InvalidArgumentException $e) {
            $sort = new Sort('desc');
        }

        // save comment
        $url = $request->getUri()->getPath();
        try {
            $comment_id = $this->filter->save($request);
            $url = $this->filter->generateUrl($request->getUri()->getPath(), $sort, $data['thread_id'], $comment_id);

            return $this->responder->saveComment($response, $url);
        } catch (CsrfException $e) {
            return $this->responder->csrfInvalid($response);
        } catch (ValidationException $e) {
            return $this->responder->invalid($response, $url);
        } catch (UploadFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->uploadFailed($response, $url);
        } catch (SaveFailedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->saveFailed($response, $url);
        }
    }
}