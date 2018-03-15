<?php

namespace Tests\Unit\Domain;

use App\Domain\UserFilter;
use App\Model\User;
use App\Repository\CommentService;
use App\Repository\UserService;
use App\Service\AuthService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class UserFilterTest extends TestCase
{
    private $filter;
    private $request;
    private $args;
    private $data;
    private $user_data;

    public function setUp()
    {
        parent::setUp();

        $this->user_data = new User();
        $this->user_data->user_image_url = 'http://example.com/image_url';
        $this->user_data->user_id = '1';
        $this->user_data->user_name = 'username';
        $user = $this->createMock(UserService::class);
        $user->method('getUser')->willReturn($this->user_data);

        $this->data = [
            'comment_id' => 100,
            'thread_id'  => 10,
            'user_id'    => 1,
            'like_count' => 0,
            'comment'    => 'comment'
        ];
        $comment = $this->createMock(CommentService::class);
        $comment->method('getCommentsByUser')->willReturn($this->data);
        $auth = $this->createMock(AuthService::class);
        $auth->method('equalUser')->willReturn(true);
        $setting = [
            'region' => 'aws_s3_region',
            'bucket' => 'aws_s3_bucket'
        ];

        $this->filter = new UserFilter($user, $comment, $auth, $setting);

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

        $this->assertEquals('http://example.com/image_url', $data['user']->user_image_url);
        $this->assertEquals(1, $data['user']->user_id);
        $this->assertEquals('username', $data['user']->user_name);

        $this->assertEquals($this->data, $data['comments']);

        $this->assertEquals('1', $data['loggedIn']);
        $this->assertEquals('100', $data['user_id']);
        $this->assertEquals(true, $data['same_user']);

        $this->assertEquals('aws_s3_region', $data['region']);
        $this->assertEquals('aws_s3_bucket', $data['bucket']);
    }

    public function testNeedsLimit()
    {
        $class = new \ReflectionClass(UserFilter::class);
        $method = $class->getMethod('needsLimit');
        $method->setAccessible(true);

        $this->assertTrue($method->invokeArgs($this->filter, [false, false]));  // MEMO: この条件は事前にはじかれる
        $this->assertTrue($method->invokeArgs($this->filter, [false, true]));
        $this->assertTrue($method->invokeArgs($this->filter, [true, false]));
        $this->assertFalse($method->invokeArgs($this->filter, [true, true]));
    }
}
