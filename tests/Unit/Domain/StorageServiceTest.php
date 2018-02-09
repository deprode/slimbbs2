<?php

namespace Tests\Unit\Domain;

use App\Domain\StorageService;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;
use Slim\Http\UploadedFile;

class StorageServiceTest extends TestCase
{

    public function testValid()
    {
        $storage = new StorageService(new S3Client([
            'version' => 'latest',
            'region'  => 'ap-northeast-1'
        ]), 'sample');

        $file = new UploadedFile('', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_NO_FILE);
        $this->assertFalse($storage->valid($file));

        $file = new UploadedFile('', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_FORM_SIZE);
        $this->assertFalse($storage->valid($file));

        $file = new UploadedFile('', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_OK);
        $this->assertTrue($storage->valid($file));
    }

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
