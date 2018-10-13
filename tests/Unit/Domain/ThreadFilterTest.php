<?php

namespace Tests\Unit\Domain;

use App\Domain\ThreadFilter;
use App\Model\CommentRead;
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

        $comment1 = new CommentRead();
        $comment1->comment_id = 1;
        $comment1->thread_id = 1;
        $comment1->user_id = 1;
        $comment1->comment = 'sample comment test';
        $comment1->like_count = 100;
        $comment1->photo_url = 'http://via.placeholder.com/32x32';
        $comment1->created_at = '2017-12-06 13:42:28';
        $comment1->user_name = 'testuser';
        $comment1->user_image_url = 'http://via.placeholder.com/48x48';

        $comment2 = new CommentRead();
        $comment2->comment_id = 2;
        $comment2->thread_id = 1;
        $comment2->user_id = 10;
        $comment2->comment = 'sample comment reply';
        $comment2->like_count = 100;
        $comment2->photo_url = 'http://via.placeholder.com/32x32';
        $comment2->created_at = '2017-12-10 15:40:59';
        $comment2->user_name = 'sample_user';
        $comment2->user_image_url = 'http://via.placeholder.com/48x48';

        $csrf = new Guard();
        $comment = $this->createMock(CommentService::class);
        $comment->method('getComments')->willReturn([
            $comment1, $comment2
        ]);
        $top_comment = new CommentRead();
        $top_comment->comment_id = 1;
        $comment->expects($this->any())
            ->method('getTopComment')
            ->willReturn($top_comment);

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
                'username'   => 'testuser',
                'userHash'   => 'user_hash'
            ]);
    }

    public function testConvertComment()
    {
        $comment = new CommentRead();
        $comment->comment_id = 1;
        $comment->thread_id = 1;
        $comment->user_id = 10;
        $comment->comment = 'sample& "comment" <\'test\'>';
        $comment->like_count = 100;
        $comment->photo_url = '';
        $comment->created_at = '2017-10-10 10:10:10';
        $comment->user_name = 'testuser';
        $comment->user_image_url = 'http://example.com/icon';

        $method = new \ReflectionMethod(ThreadFilter::class, 'convertComment');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->filter, [[$comment]]);

        $this->assertInternalType('string', $result);

        $data = json_decode($result);
        $this->assertEquals(1, $data->c1->{'comment_id'});
        $this->assertEquals('2017年10月10日', $data->c1->{'created_at'});
        $this->assertEquals('sample\u0026 \u0022comment\u0022 \u003C\u0027test\u0027\u003E', $data->c1->{'comment'});
    }

    public function testFilteringSort()
    {
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->any())
            ->method('getParams')
            ->willReturn([
                'thread_id' => '1'
            ]);
        $data = $this->filter->filtering($request);

        $this->assertEquals(1, $data['comment_top']->comment_id);
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
        $this->assertEquals('user_hash', $data['hash']);

        $this->assertInternalType('string', $data['comments']);

        $this->assertEquals('TestMessage', $data['info']);
        $this->assertEquals('ErrorMessage', $data['error']);

        $this->assertEquals('aws_s3_region', $data['region']);
        $this->assertEquals('aws_s3_bucket', $data['bucket']);
    }
}
