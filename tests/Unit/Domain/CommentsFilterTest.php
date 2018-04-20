<?php

namespace Tests\Unit\Domain;

use App\Domain\CommentsFilter;
use App\Model\CommentRead;
use App\Repository\CommentService;
use Slim\Http\Request;

class CommentsFilterTest extends \PHPUnit_Framework_TestCase
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
        $comment->method('getComments')->willReturn([$this->comment_data]);

        $this->request = $this->createMock(Request::class);
        $this->request->method('isXhr')->willReturn(true);
        $this->request
            ->expects($this->any())
            ->method('getAttributes')
            ->willReturn([
                'thread_id'  => '1',
                'comment_id' => '1',
            ]);

        $this->filter = new CommentsFilter($comment);
    }

    /**
     * @expectedException \App\Exception\NotAllowedException
     */
    public function testIsXHRError()
    {
        $request = $this->createMock(Request::class);

        $this->filter->filtering($request);
    }

    /**
     * @expectedException \App\Exception\ValidationException
     */
    public function testValidatioinError()
    {
        $request = $this->createMock(Request::class);
        $request->method('isXhr')->willReturn(true);
        $request->method('getAttribute')->willReturn([
            'has_errors' => ["error"],
        ]);

        $this->filter->filtering($request);
    }

    public function testFiltering()
    {
        $data = $this->filter->filtering($this->request);

        $data_dist = $this->comment_data->toArray();
        $data_dist['created_at'] = '12月6日';
        $this->assertEquals([
            'c1' => $data_dist
        ], $data);
    }
}
