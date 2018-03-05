<?php

namespace Test\Unit;

use App\Collection\ThreadCollection;
use App\Repository\ThreadService;
use App\Service\DatabaseService;

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
            'updated_at' => '2018-01-31 15:48:31',
        ];
    }

    /**
     * @expectedException \App\Exception\FetchFailedException
     */
    public function testGetThreads()
    {
        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->willReturn($this->data);
        $this->thread = new ThreadService($dbs);

        $threads = new ThreadCollection($this->data);
        $this->assertEquals($threads, $this->thread->getThreads());

        $error_dbs = $this->createMock(DatabaseService::class);
        $error_dbs->expects($this->any())->method('fetchAll')->will($this->throwException(new \PDOException()));
        $this->thread = new ThreadService($error_dbs);

        // throw error
        $this->thread->getThreads();
    }

    public function testGetSortValue()
    {
        $dbs = $this->createMock(DatabaseService::class);
        $this->thread = new ThreadService($dbs);

        $this->assertEquals('DESC', $this->thread->getSortValue());
        $this->assertEquals('DESC', $this->thread->getSortValue('new'));
        $this->assertEquals('ASC', $this->thread->getSortValue('old'));
        $this->assertEquals('DESC', $this->thread->getSortValue('hoge'));
    }
}
