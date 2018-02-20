<?php

namespace Tests\Unit\Domain;

use App\Domain\QuitFilter;
use App\Service\MessageService;
use PHPUnit\Framework\TestCase;
use Slim\Csrf\Guard;
use Slim\Http\Request;

class QuitFilterTest extends TestCase
{
    private $filter;
    private $request;

    protected function setUp()
    {
        parent::setUp();

        $message = $this->getMockBuilder(MessageService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getErrorMessage'])
            ->getMock();
        $message->expects($this->any())
            ->method('getErrorMessage')
            ->willReturn('ErrorMessage');

        $this->filter = new QuitFilter($message, new Guard());

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
                'csrf_name'  => 'csrf_name',
                'csrf_value' => 'csrf_value',
                'isLoggedIn' => '1',
                'userId'     => '100'
            ]);
    }

    public function testFiltering()
    {
        $data = $this->filter->filtering($this->request);

        $this->assertEquals('csrf_name', $data['nameKey']);
        $this->assertEquals('csrf_value', $data['valueKey']);

        $this->assertEquals('1', $data['loggedIn']);

        $this->assertEquals('ErrorMessage', $data['error']);

    }
}
