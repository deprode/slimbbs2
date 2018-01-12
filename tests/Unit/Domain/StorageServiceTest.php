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
        $err_mock = $this->createMock(S3Exception::class);

        $mock = $this->createMock(S3Client::class);
        $mock->expects($this->at(0))->method('upload')->willReturn(0);
        $mock->expects($this->at(1))->method('upload')->will($this->throwException($err_mock));

        $file = new UploadedFile(__DIR__ . '/../../data/dummy.png', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_OK);
        $storage = new StorageService($mock, 'bucket');
        $filename = $storage->upload($file);
        $this->assertInternalType('string', $filename);

        $storage->upload($file);
    }
}
