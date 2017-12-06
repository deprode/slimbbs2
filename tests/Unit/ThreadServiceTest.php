<?php

namespace Test\Unit;

use App\Domain\DatabaseService;
use App\Domain\ThreadService;

class ThreadServiceTest extends \PHPUnit_Framework_TestCase
{
    private $thread;
    private $data;

    protected function setUp()
    {
        parent::setUp();

        $this->data = [
            'thread_id'  => 1,
            'comment'    => 'aaaa',
            'created_at' => '2017-12-06 13:42:28',
        ];

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->willReturn($this->data);
        $this->thread = new ThreadService($dbs);
    }

    public function testGetThreads()
    {
        $this->assertEquals($this->data, $this->thread->getThreads());
    }

}
