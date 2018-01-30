<?php

namespace App\Action;


use App\Domain\AuthService;
use App\Domain\MessageService;
use App\Domain\UserService;
use App\Exception\DeleteFailedException;
use App\Responder\QuitedResponder;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class AccountDeleteAction
{
    private $message;
    private $user;
    private $auth;
    private $responder;

    public function __construct(MessageService $message, UserService $user, AuthService $auth, QuitedResponder $responder)
    {
        $this->message = $message;
        $this->user = $user;
        $this->auth = $auth;
        $this->responder = $responder;
    }

    public function delete(Request $request, Response $response): ResponseInterface
    {
        $loggedIn = $request->getAttribute('isLoggedIn');
        if ($loggedIn == false) {
            return $this->responder->redirect($response);
        }

        if ($request->getAttribute('csrf_status') === "bad_request") {
            return $this->responder->redirect($response);
        }

        $user_id = $request->getAttribute('userId');

        try {
            $result = $this->user->deleteAccount($user_id);
            if ($result === false) {
                throw new DeleteFailedException();
            }
            $this->auth->logout();

            return $this->responder->quited($response);
        } catch (DeleteFailedException $e) {
            return $this->responder->deleteFailed($response);
        }
    }

}