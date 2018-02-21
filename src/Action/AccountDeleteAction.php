<?php

namespace App\Action;


use App\Domain\AccountDeleteFilter;
use App\Exception\CsrfException;
use App\Exception\DeleteFailedException;
use App\Responder\QuitedResponder;
use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class AccountDeleteAction
{
    private $filter;
    private $auth;
    private $responder;

    public function __construct(AccountDeleteFilter $filter, AuthService $auth, QuitedResponder $responder)
    {
        $this->filter = $filter;
        $this->auth = $auth;
        $this->responder = $responder;
    }

    public function delete(Request $request, Response $response): ResponseInterface
    {
        try {
            $this->filter->delete($request);
            $this->auth->logout();
            return $this->responder->quited($response);
        } catch (CsrfException $e) {
            return $this->responder->redirect($response);

        } catch (\OutOfBoundsException $e) {
            return $this->responder->redirect($response);

        } catch (DeleteFailedException $e) {
            return $this->responder->deleteFailed($response);
        }
    }

}