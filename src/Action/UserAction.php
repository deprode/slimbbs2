<?php


namespace App\Action;


use App\Repository\CommentService;
use App\Repository\UserService;
use App\Exception\FetchFailedException;
use App\Responder\UserResponder;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class UserAction
{
    private $user;
    private $comment;
    private $responder;

    public function __construct(UserService $user, CommentService $comment, UserResponder $responder)
    {
        $this->user = $user;
        $this->comment = $comment;
        $this->responder = $responder;
    }

    public function index(Request $request, Response $response, array $args): ResponseInterface
    {
        $username = $args['name'] ?? $request->getAttribute('username');

        if (empty($username)) {
            return $this->responder->nameEmpty($response);
        }

        $data = [];
        try {
            $user = $this->user->getUser($username);
            $data['comments'] = $this->comment->convertTime($this->comment->getCommentsByUser($user['user_id']));
        } catch (FetchFailedException $e) {
            return $this->responder->fetchFailed($response);
        }

        $data['image_url'] = $user['user_image_url'];
        $data['id'] = $user['user_id'];
        $data['name'] = $user['user_name'];

        $data['loggedIn'] = $request->getAttribute('isLoggedIn');
        $data['user_id'] = $request->getAttribute('userId');
        $data['username'] = $request->getAttribute('username');

        return $this->responder->index($response, $data);
    }
}