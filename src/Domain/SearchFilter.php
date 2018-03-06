<?php


namespace App\Domain;


use App\Exception\ValidationException;
use App\Repository\CommentService;
use Slim\Csrf\Guard as Csrf;
use Slim\Http\Request;

class SearchFilter
{
    private $csrf;
    private $comment;

    public function __construct(Csrf $csrf, CommentService $comment)
    {
        $this->csrf = $csrf;
        $this->comment = $comment;
    }

    /**
     * @param Request $request
     * @return array
     * @throws ValidationException
     * @throws \App\Exception\FetchFailedException
     */
    public function filtering(Request $request): array
    {
        $query = $request->getParam('query');

        if (empty($query)) {
            throw new ValidationException();
        }

        $data = [];
        $data['query'] = $query;
        $data['comments'] = $this->comment->convertTime($this->comment->searchComments($query));

        $attributes = $request->getAttributes();

        // csrf
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $data['nameKey'] = $nameKey;
        $data['valueKey'] = $valueKey;
        $data['name'] = $attributes[$nameKey] ?? '';
        $data['value'] = $attributes[$valueKey] ?? '';

        // auth
        $data['is_admin'] = $attributes['isAdmin'] ?? '';
        $data['loggedIn'] = $attributes['isLoggedIn'] ?? '';

        return $data;
    }
}