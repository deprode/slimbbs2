<?php

namespace Tests\Unit\Domain;

use App\Domain\ThreadFilter;
use App\Model\User;
use App\Repository\CommentService;
use App\Repository\UserService;
use App\Service\MessageService;
use PHPUnit\Framework\TestCase;
use Slim\Csrf\Guard;
use Slim\Http\Request;

class ThreadFilterTest extends TestCase
{
    private $filter;
    private $request;
    private $user_data;

    protected function setUp()
    {
        parent::setUp();

        $csrf = new Guard();
        $comment = $this->createMock(CommentService::class);
        $comment->method('getComments')->willReturn([
            [
                'comment_id'     => 1,
                'user_id'        => 1,
                'created_at'     => '2017-12-06 13:42:28',
                'comment'        => 'sample comment test',
                'photo_url'      => 'http://via.placeholder.com/32x32',
                'user_name'      => 'testuser',
                'user_image_url' => 'http://via.placeholder.com/48x48'
            ],
            [
                'comment_id'     => 2,
                'user_id'        => 10,
                'created_at'     => '2017-12-10 15:40:59',
                'comment'        => 'sample comment reply',
                'photo_url'      => 'http://via.placeholder.com/32x32',
                'user_name'      => 'sample_user',
                'user_image_url' => 'http://via.placeholder.com/48x48'
            ]
        ]);

        $message = $this->createMock(MessageService::class);
        $message->expects($this->any())
            ->method('getInfoMessage')
            ->willReturn('TestMessage');
        $message->expects($this->any())
            ->method('getErrorMessage')
            ->willReturn('ErrorMessage');

        $user = $this->createMock(UserService::class);
        $this->user_data = new User();
        $this->user_data->user_image_url = 'http://example.com/icon';
        $this->user_data->user_name = 'testuser';
        $user->expects($this->any())
            ->method('getUser')
            ->willReturn($this->user_data);

        $setting = [
            'region' => 'aws_s3_region',
            'bucket' => 'aws_s3_bucket'
        ];

        $this->filter = new ThreadFilter($csrf, $comment, $message, $user, $setting);

        $this->request = $this->createMock(Request::class);
        $this->request
            ->expects($this->any())
            ->method('getParams')
            ->willReturn([
                'thread_id' => '1',
            ]);
        $this->request
            ->expects($this->any())
            ->method('getAttributes')
            ->willReturn([
                'csrf_name'  => 'csrf_name',
                'csrf_value' => 'csrf_value',
                'isLoggedIn' => '1',
                'isAdmin'    => '1',
                'userId'     => '100',
                'username'   => 'testuser'
            ]);
    }

    public function testFilteringSort()
    {
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->any())
            ->method('getParams')
            ->willReturn([
                'thread_id' => '1',
                'sort'      => 'asc'
            ]);
        $data = $this->filter->filtering($request);

        $this->assertEquals(1, $data['comment_top']['comment_id']);

        $request = $this->createMock(Request::class);
        $request
            ->expects($this->any())
            ->method('getParams')
            ->willReturn([
                'thread_id' => '1',
                'sort'      => 'desc'
            ]);
        $data = $this->filter->filtering($request);

        $this->assertEquals(2, $data['comment_top']['comment_id']);
    }

    public function testFiltering()
    {
        $data = $this->filter->filtering($this->request);

        $this->assertEquals('csrf_name', $data['nameKey']);
        $this->assertEquals('csrf_value', $data['valueKey']);

        $this->assertEquals('100', $data['user_id']);
        $this->assertEquals('1', $data['is_admin']);
        $this->assertEquals('1', $data['loggedIn']);

        $this->assertEquals($this->user_data, $data['user']);

        $this->assertEquals('TestMessage', $data['info']);
        $this->assertEquals('ErrorMessage', $data['error']);

        $this->assertEquals('aws_s3_region', $data['region']);
        $this->assertEquals('aws_s3_bucket', $data['bucket']);
    }
}
