<?php

namespace Tests\Unit;

use App\Model\Model;

class Test extends Model
{
    protected $test;

    public function __toString()
    {
        return 'Test';
    }
}

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAndGet()
    {
        $test = new Test();
        $test->test = 'test';
        $this->assertEquals('test', $test->test);
    }

    public function testGetNull()
    {
        $test = new Test();
        $test->hoge = 'hoge';
        $this->assertEquals(null, $test->hoge);
    }

    public function testIsSet()
    {
        $test = new Test();
        $this->assertEquals(true, isset($test->test));
        $this->assertEquals(false, isset($test->hoge));
    }

    public function testToString()
    {
        $test = new Test();

        $this->assertEquals('Test', (string)$test);
    }
}
