<?php


namespace App\Domain;


use App\Exception\CsrfException;
use App\Exception\UploadFailedException;
use App\Exception\ValidationException;
use App\Model\Comment;
use App\Model\Sort;
use App\Repository\CommentService;
use App\Service\StorageService;
use Slim\Http\Request;
use Slim\Http\UploadedFile;

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
     * @param UploadedFile $file
     * @return bool
     */
    private function invalidUploadFile(UploadedFile $file)
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return true;
        }
        return false;
    }

    /**
     * @param string $mime_type
     * @return bool
     */
    private function validFileType(string $mime_type)
    {
        return in_array($mime_type, ['image/jpeg', 'image/gif', 'image/png']);
    }

    /**
     * @param Request $request
     * @return int
     * @throws CsrfException
     * @throws ValidationException
     * @throws \App\Exception\SaveFailedException
     * @throws \App\Exception\UploadFailedException
     */
    public function save(Request $request): int
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

        /** @var UploadedFile $upload_file */
        $upload_file = $files['picture'] ?? new UploadedFile(null);
        if (!empty($upload_file->file)) {
            if ($this->invalidUploadFile($upload_file)) {
                throw new UploadFailedException();
            }
            $mime_type = mime_content_type($upload_file->file);
            if ($this->validFileType($mime_type)) {
                $file_data = $upload_file->getStream()->detach();
                $filename = $this->storage->upload($file_data, $mime_type);
            }
        }

        // save comment
        $data = $request->getParsedBody();
        $comment = new Comment();
        $comment->thread_id = $data['thread_id'];
        $comment->user_id = $attributes['userId'] ?? 0;
        $comment->comment = $data['comment'];
        $comment->photo_url = $filename ?? '';
        return $this->comment->saveComment($comment);
    }

    public function generateUrl(string $base_path, Sort $sort, int $thread_id = 0, int $comment_id = 0): string
    {
        if ($base_path && $thread_id && $comment_id) {
            $url = $base_path;
            $url .= '?thread_id=' . $thread_id;
            $url .= ($sort->value() == 'desc') ? '' : '&sort=' . $sort->value();
            $url .= '#c' . $comment_id;
        } else {
            $url = $base_path;
        }

        return $url;
    }
}