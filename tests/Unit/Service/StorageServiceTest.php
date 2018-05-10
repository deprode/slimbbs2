<?php

namespace Tests\Unit\Domain;

use App\Service\StorageService;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;
use Slim\Http\UploadedFile;

class StorageServiceTest extends TestCase
{
    private $file;

    public function setUp()
    {
        $this->file = new UploadedFile(__DIR__ . '/../../data/dummy.png', 'test.jpg', 'image/jpg', 0, UPLOAD_ERR_OK);
        $this->file = $this->file->getStream()->detach();
    }

    public function testUpload()
    {
        $mock = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['putObject'])
            ->getMock();

        $storage = new StorageService($mock, 'bucket');
        $filename = $storage->upload($this->file, 'image/png', 'prefix_');
        $this->assertInternalType('string', $filename);
        $this->assertTrue(strpos($filename, 'prefix_') === 0);
    }

    /**
     * @expectedException \App\Exception\UploadFailedException
     */
    public function testUploadFailed()
    {
        $err_mock = $this->createMock(S3Exception::class);
        $mock = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['putObject'])
            ->getMock();
        $mock->expects($this->any())->method('putObject')->will($this->throwException($err_mock));
        $storage = new StorageService($mock, 'bucket');
        $storage->upload($this->file);
    }

    public function testGetFullPath()
    {
        $mock = $this->createMock(S3Client::class);
        $mock->expects($this->any())->method('getRegion')->willReturn('region');
        $storage = new StorageService($mock, 'bucket');

        $this->assertEquals('https://s3-region.amazonaws.com/bucket/filename', $storage->getFullPath('filename'));
    }
}
