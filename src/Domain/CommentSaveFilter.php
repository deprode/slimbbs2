<?php


namespace App\Domain;


use App\Exception\CsrfException;
use App\Exception\ValidationException;
use App\Model\Comment;
use App\Repository\CommentService;
use App\Service\StorageService;
use Slim\Http\Request;

class CommentSaveFilter
{
    private $storage;
    private $comment;

    public function __construct(StorageService $storage, CommentService $comment)
    {
        $this->storage = $storage;
        $this->comment = $comment;
    }

    /**
     * @param Request $request
     * @throws CsrfException
     * @throws ValidationException
     * @throws \App\Exception\SaveFailedException
     * @throws \App\Exception\UploadFailedException
     */
    public function save(Request $request): void
    {
        $attributes = $request->getAttributes();

        $csrf_status = $attributes['csrf_status'] ?? '';
        if ($csrf_status === "bad_request") {
            throw new CsrfException();
        }

        // Validation
        $validation_status = $attributes['has_errors'];
        if ($validation_status) {
            throw new ValidationException();
        }

        // upload file
        $files = $request->getUploadedFiles();
        if (!empty($files['picture']->file)) {
            $filename = $this->storage->upload($files['picture']);
        }

        // save comment
        $data = $request->getParsedBody();
        $comment = new Comment();
        $comment->thread_id = $data['thread_id'];
        $comment->user_id = $attributes['userId'] ?? 0;
        $comment->comment = $data['comment'];
        $comment->photo_url = $filename ?? '';
        $this->comment->saveComment($comment);
    }
}