<?php

namespace Tests\Unit\Domain;

use App\Domain\UserFilter;
use App\Repository\CommentService;
use App\Repository\UserService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class UserFilterTest extends TestCase
{
    private $filter;
    private $request;
    private $args;
    private $data;

    public function setUp()
    {
        parent::setUp();

        $user = $this->createMock(UserService::class);
        $user->method('getUser')->willReturn([
            'user_image_url' => 'http://example.com/image_url',
            'user_id'        => '1',
            'user_name'      => 'username'
        ]);

        $this->data = [
            'comment_id' => 100,
            'thread_id'  => 10,
            'user_id'    => 1,
            'like_count' => 0,
            'comment'    => 'comment'
        ];
        $comment = $this->createMock(CommentService::class);
        $comment->method('getCommentsByUser')->willReturn($this->data);
        $comment->method('convertTime')->willReturn($this->data);
        $setting = [
            'region' => 'aws_s3_region',
            'bucket' => 'aws_s3_bucket'
        ];

        $this->filter = new UserFilter($user, $comment, $setting);

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributes'])
            ->getMock();

        $this->request->method('getAttributes')->willReturn([
            'isLoggedIn' => '1',
            'userId'     => '100',
            'username'   => 'test_user'
        ]);

        $this->args = [
            'name' => 'username'
        ];
    }

    public function testFiltering()
    {
        $data = $this->filter->filtering($this->request, $this->args);

        $this->assertEquals('http://example.com/image_url', $data['image_url']);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('username', $data['name']);

        $this->assertEquals($this->data, $data['comments']);

        $this->assertEquals('1', $data['loggedIn']);
        $this->assertEquals('100', $data['user_id']);
        $this->assertEquals('test_user', $data['username']);

        $this->assertEquals('aws_s3_region', $data['region']);
        $this->assertEquals('aws_s3_bucket', $data['bucket']);
    }
}
