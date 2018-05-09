<?php

namespace App\Service;

use App\Exception\UploadFailedException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class StorageService
{
    private $storage;
    private $bucket;

    public function __construct(S3Client $storage, string $bucket)
    {
        $this->storage = $storage;
        $this->bucket = $bucket;
    }

    /**
     * @param resource $file
     * @param string $mime_type
     * @return string
     * @throws UploadFailedException
     */
    public function upload($file, string $mime_type = 'application/octet-stream'): string
    {
        if (!is_resource($file)) {
            throw new UploadFailedException();
        }

        $filename = bin2hex(openssl_random_pseudo_bytes(32));

        try {
            $this->storage->putObject(
                [
                    'Bucket'      => $this->bucket,
                    'Key'         => $filename,
                    'Body'        => $file,
                    'ACL'         => 'public-read',
                    'ContentType' => $mime_type
                ]
            );
        } catch (S3Exception $e) {
            throw new UploadFailedException();
        }

        return $filename;
    }
}