<?php

namespace App\Action;


use App\Domain\CommentDeleteFilter;
use App\Exception\CsrfException;
use App\Exception\DeleteFailedException;
use App\Exception\NotAllowedException;
use App\Responder\DeleteResponder;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CommentDeleteAction
{
    private $log;
    private $filter;
    private $responder;

    public function __construct(LoggerInterface $log, CommentDeleteFilter $filter, DeleteResponder $responder)
    {
        $this->log = $log;
        $this->filter = $filter;
        $this->responder = $responder;
    }

    public function delete(Request $request, Response $response)
    {
        $this->log->info("Slimbbs '/thread' route comment delete");

        $data = $request->getParsedBody();
        $url = $this->getRedirectUrl($request->getUri()->getPath(), $data);

        try {
            $this->filter->delete($request);
            return $this->responder->deleted($response, $url);
        } catch (CsrfException $e) {
            return $this->responder->csrfInvalid($response);
        } catch (\OutOfBoundsException $e) {
            return $this->responder->invalid($response);
        } catch (NotAllowedException $e) {
            return $this->responder->invalid($response);
        } catch (DeleteFailedException $e) {
            $this->log->error($e->getMessage(), ['exception' => $e]);
            return $this->responder->deleteFailed($response, $url);
        }
    }

    private function getRedirectUrl(string $base_path, array $data): string
    {
        $query = $data['query'] ?? null;
        if ($query) {
            $url = '/search?query=' . $query;
        } else {
            $url = $base_path . (empty(intval($data['thread_id'])) ? '' : '?thread_id=' . intval($data['thread_id']));
        }

        return $url;
    }
}