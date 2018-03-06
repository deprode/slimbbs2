<?php

namespace Tests\Unit\Domain;

use App\Domain\SearchFilter;
use App\Repository\CommentService;
use PHPUnit\Framework\TestCase;
use Slim\Csrf\Guard;
use Slim\Http\Request;

class SearchFilterTest extends TestCase
{
    private $filter;
    private $request;
    private $comment_data;

    protected function setUp()
    {
        parent::setUp();

        $this->comment_data = [
            [
                'comment_id'     => 1,
                'user_id'        => 1,
                'created_at'     => '2017-12-06 13:42:28',
                'comment'        => 'sample comment test',
                'photo_url'      => 'http://via.placeholder.com/32x32',
                'user_name'      => 'testuser',
                'user_image_url' => 'http://via.placeholder.com/48x48'
            ]
        ];

        $csrf = new Guard();
        $comment = $this->createMock(CommentService::class);
        $comment->method('convertTime')->willReturn($this->comment_data);

        $this->filter = new SearchFilter($csrf, $comment);

        $this->request = $this->createMock(Request::class);
        $this->request
            ->expects($this->any())
            ->method('getParam')
            ->willReturn('search_comment');
        $this->request
            ->expects($this->any())
            ->method('getAttributes')
            ->willReturn([
                'csrf_name'  => 'csrf_name',
                'csrf_value' => 'csrf_value',
                'isLoggedIn' => '1',
                'isAdmin'    => '1',
            ]);
    }

    public function testFiltering()
    {
        $data = $this->filter->filtering($this->request);

        $this->assertEquals('csrf_name', $data['nameKey']);
        $this->assertEquals('csrf_value', $data['valueKey']);

        $this->assertEquals('1', $data['is_admin']);
        $this->assertEquals('1', $data['loggedIn']);

        $this->assertEquals($this->comment_data, $data['comments']);
        $this->assertEquals('search_comment', $data['query']);
    }
}
