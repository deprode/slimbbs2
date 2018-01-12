<?php

namespace App\Domain;

use App\Exception\UploadFailedException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Slim\Http\UploadedFile;

class StorageService
{
    private $storage;
    private $bucket;

    public function __construct(S3Client $storage, string $bucket)
    {
        $this->storage = $storage;
        $this->bucket = $bucket;
    }

    public function valid(UploadedFile $file): bool
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return false;
        }
        return true;
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws UploadFailedException
     */
    public function upload(UploadedFile $file): string
    {
        if (!$this->valid($file)) {
            throw new UploadFailedException();
        }

        $filename = bin2hex(openssl_random_pseudo_bytes(32));

        try {
            $this->storage->upload(
                $this->bucket,
                $filename,
                $file->getStream()->detach(),
                'public-read');
        } catch (S3Exception $e) {
            throw new UploadFailedException();
        }

        return $filename;
    }
}