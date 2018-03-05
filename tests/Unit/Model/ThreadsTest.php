<?php

namespace Tests\Unit;

use App\Model\Thread;

class ThreadsTest extends \PHPUnit_Framework_TestCase
{
    private $thread;

    protected function setUp()
    {
        parent::setUp();
        $this->thread = new Thread();
        $this->thread->thread_id = 123;
        $this->thread->comment = 'sample comment';
        $this->thread->user_name = 'testuser';
        $this->thread->count = 10;
        $this->thread->updated_at = '2017-12-31 00:00:00';
    }

    public function testToString()
    {
        $string = <<<TOSTRING
thread_id: 123
comment: sample comment
user_name: testuser
count: 10
updated_at: 2017-12-31 00:00:00
TOSTRING;

        $this->assertEquals($string, (string)$this->thread);
        $this->assertInternalType('string', (string)$this->thread);
    }

    public function testUpdatedAtStr()
    {
        $result = $this->thread->updatedAtStr();

        $this->assertEquals('12月31日', $result);
    }
}
