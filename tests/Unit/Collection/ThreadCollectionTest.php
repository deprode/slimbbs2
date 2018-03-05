<?php

namespace Tests\Unit\Collection;

use App\Collection\ThreadCollection;
use App\Model\Thread;
use PHPUnit\Framework\TestCase;

class ThreadCollectionTest extends TestCase
{
    public function testCount()
    {
        $threads = new ThreadCollection([new Thread()]);

        $this->assertEquals(1, $threads->count());
    }

    public function testGetIterator()
    {
        $threads = new ThreadCollection([new Thread()]);

        foreach ($threads as $thread) {
            $this->assertInstanceOf(Thread::class, $thread);
        }
    }
}
