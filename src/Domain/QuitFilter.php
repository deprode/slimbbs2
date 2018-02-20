<?php


namespace App\Domain;


use App\Service\MessageService;
use Slim\Csrf\Guard;
use Slim\Http\Request;

class QuitFilter
{
    private $message;
    private $csrf;

    public function __construct(MessageService $message, Guard $csrf)
    {
        $this->message = $message;
        $this->csrf = $csrf;
    }

    /**
     * @param Request $request
     * @return array
     * @throws \UnexpectedValueException
     */
    public function filtering(Request $request): array
    {
        $attributes = $request->getAttributes();

        $data = [];

        $data['loggedIn'] = $attributes['isLoggedIn'] ?? '';
        if ($data['loggedIn'] == false) {
            throw new \UnexpectedValueException();
        }

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $attributes[$nameKey] ?? '';
        $data['value'] = $attributes[$valueKey] ?? '';

        $data['error'] = $this->message->getErrorMessage();

        return $data;
    }
}