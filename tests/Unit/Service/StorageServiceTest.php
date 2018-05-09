<?php

namespace Tests\Unit\Domain;

use App\Service\StorageService;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;
use Slim\Http\UploadedFile;

class StorageServiceTest extends TestCase
{
    /**
     * @expectedException \App\Exception\UploadFailedException
     */
    public function testUpload()
    {
        $mock = $this->createMock(S3Client::class);
        $mock->expects($this->any())->method('upload')->willReturn(0);

        $file = new UploadedFile(__DIR__ . '/../../data/dummy.png', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_OK);
        $storage = new StorageService($mock, 'bucket');
        $filename = $storage->upload($file);
        $this->assertInternalType('string', $filename);

        $err_mock = $this->createMock(S3Exception::class);
        $mock = $this->createMock(S3Client::class);
        $mock->expects($this->any())->method('upload')->will($this->throwException($err_mock));
        $storage = new StorageService($mock, 'bucket');
        $storage->upload($file);
    }
}
