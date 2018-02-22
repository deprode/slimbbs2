<?php


namespace App\Domain;


use App\Exception\CsrfException;
use App\Exception\DeleteFailedException;
use App\Exception\NotAllowedException;
use App\Repository\UserService;
use Slim\Http\Request;

class AccountDeleteFilter
{
    private $user;

    public function __construct(UserService $user)
    {
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @throws CsrfException
     * @throws NotAllowedException
     * @throws \App\Exception\DeleteFailedException
     */
    public function delete(Request $request): void
    {
        $attributes = $request->getAttributes();

        $csrf_status = $attributes['csrf_status'] ?? '';
        if ($csrf_status === "bad_request") {
            throw new CsrfException();
        }

        $loggedIn = $attributes['isLoggedIn'] ?? false;
        if ($loggedIn == false) {
            throw new NotAllowedException();
        }

        $user_id = $attributes['userId'] ?? 0;

        $result = $this->user->deleteAccount($user_id);
        if ($result === false) {
            throw new DeleteFailedException();
        }
    }

}