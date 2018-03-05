<?php

namespace Tests\Unit\Domain;

use App\Collection\ThreadCollection;
use App\Domain\HomeFilter;
use App\Repository\ThreadService;
use App\Service\MessageService;
use PHPUnit\Framework\TestCase;
use Slim\Csrf\Guard;
use Slim\Http\Request;

class HomeFilterTest extends TestCase
{
    private $filter;
    private $request;
    private $data;

    protected function setUp()
    {
        parent::setUp();

        $this->data = new ThreadCollection([
            'thread_id'  => 1,
            'comment'    => 'aaaa',
            'created_at' => '2017-12-06 13:42:28',
            'updated_at' => '3日前',
        ]);

        $thread = $this->getMockBuilder(ThreadService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getThreads'])
            ->getMock();

        $thread->method('getThreads')->willReturn($this->data);


        $message = $this->getMockBuilder(MessageService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInfoMessage', 'getErrorMessage'])
            ->getMock();
        $message->expects($this->any())
            ->method('getInfoMessage')
            ->willReturn('TestMessage');
        $message->expects($this->any())
            ->method('getErrorMessage')
            ->willReturn('ErrorMessage');

        $this->filter = new HomeFilter($thread, $message, new Guard());

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParams', 'getAttributes'])
            ->getMock();

        $this->request
            ->expects($this->any())
            ->method('getParams')
            ->willReturn(['sort' => 'desc']);
        $this->request
            ->expects($this->any())
            ->method('getAttributes')
            ->willReturn([
                'name'       => 'csrf_name',
                'value'      => 'csrf_value',
                'isLoggedIn' => '1',
                'userId'     => '100'
            ]);
    }

    public function testFiltering()
    {
        $data = $this->filter->filtering($this->request);

        $this->assertEquals('desc', $data['sort']);
        $this->assertEquals($this->data, $data['threads']);

        $this->assertEquals('csrf_name', $data['nameKey']);
        $this->assertEquals('csrf_value', $data['valueKey']);

        $this->assertEquals('1', $data['loggedIn']);
        $this->assertEquals('100', $data['user_id']);

        $this->assertEquals('TestMessage', $data['info']);
        $this->assertEquals('ErrorMessage', $data['error']);
    }
}
