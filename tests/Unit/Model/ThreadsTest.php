<?php

namespace Tests\Unit;

use App\Model\Thread;

class ThreadsTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $thread = new Thread();
        $thread->comment_id = 123;
        $thread->thread_id = 123;
        $thread->user_id = 123;
        $thread->comment = 'sample comment';
        $thread->created_at = '2017-12-31';

        $string = <<<TOSTRING
thread_id: 123
comment_id: 123
user_id: 123
comment: sample comment
created_at: 2017-12-31
TOSTRING;

        $this->assertEquals($string, (string)$thread);
    }

}
