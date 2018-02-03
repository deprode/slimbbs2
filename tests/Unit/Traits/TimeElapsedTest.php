<?php

namespace Tests\Unit\Traits;

use App\Traits\TimeElapsed;
use PHPUnit\Framework\TestCase;

class TimeElapsedClass
{
    use TimeElapsed;
}

class TimeElapsedTest extends TestCase
{
    private $time_elapse;

    protected function setUp()
    {
        parent::setUp();

        $this->time_elapse = new TimeElapsedClass();
    }

    public function testTimeToString()
    {
        $time = new \DateTime();
        $this->assertEquals('今', $this->time_elapse->timeToString($time));

        $time = new \DateTime('-1 year');
        $this->assertEquals($time->format('Y年n月j日'), $this->time_elapse->timeToString($time));

        $time = new \DateTime('-1 month');
        $this->assertEquals($time->format('n月j日'), $this->time_elapse->timeToString($time));

        $time = new \DateTime('-27 days');
        $this->assertEquals($time->format('27日前'), $this->time_elapse->timeToString($time));

        $time = new \DateTime('-1 days');
        $this->assertEquals($time->format('1日前'), $this->time_elapse->timeToString($time));

        $time = new \DateTime('-1 hour');
        $this->assertEquals($time->format('1時間前'), $this->time_elapse->timeToString($time));

        $time = new \DateTime('-1 min');
        $this->assertEquals($time->format('1分前'), $this->time_elapse->timeToString($time));

        $time = new \DateTime('-59 sec');
        $this->assertEquals($time->format('59秒前'), $this->time_elapse->timeToString($time));

        $time = new \DateTime('-1 sec');
        $this->assertEquals($time->format('1秒前'), $this->time_elapse->timeToString($time));

        $time = new \DateTime('2000-03-31', new \DateTimeZone('Asia/Tokyo'));
        $time->modify('-31 days');
        $this->assertEquals('2000年2月29日', $this->time_elapse->timeToString($time));
    }
}
