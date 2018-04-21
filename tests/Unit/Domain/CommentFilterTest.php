<?php

namespace Tests\Unit\Domain;

use App\Domain\CommentFilter;
use App\Model\CommentRead;
use App\Repository\CommentService;
use Slim\Http\Request;

class CommentFilterTest extends \PHPUnit_Framework_TestCase
{
    private $filter;
    private $request;
    private $comment_data;

    protected function setUp()
    {
        $this->comment_data = new CommentRead();
        $this->comment_data->comment_id = 1;
        $this->comment_data->thread_id = 1;
        $this->comment_data->user_id = 1;
        $this->comment_data->created_at = '2017-12-06 13:42:28';
        $this->comment_data->comment = 'sample comment test';
        $this->comment_data->photo_url = 'http://via.placeholder.com/32x32';
        $this->comment_data->user_name = 'testuser';
        $this->comment_data->user_image_url = 'http://via.placeholder.com/48x48';

        $comment = $this->createMock(CommentService::class);
        $comment->method('getComment')->willReturn($this->comment_data);

        $this->request = $this->createMock(Request::class);
        $this->request
            ->expects($this->any())
            ->method('getAttributes')
            ->willReturn([
                'comment_id' => '1',
                'isLoggedIn' => '1',
                'isAdmin'    => '1',
            ]);

        $setting = [
            'region' => 'aws_s3_region',
            'bucket' => 'aws_s3_bucket'
        ];

        $this->filter = new CommentFilter($comment, $setting);
    }

    public function testFiltering()
    {
        $data = $this->filter->filtering($this->request);

        $this->assertEquals($this->comment_data, $data['comment']);

        $this->assertEquals('1', $data['is_admin']);
        $this->assertEquals('1', $data['loggedIn']);

        $this->assertEquals('aws_s3_region', $data['region']);
        $this->assertEquals('aws_s3_bucket', $data['bucket']);
    }
}
