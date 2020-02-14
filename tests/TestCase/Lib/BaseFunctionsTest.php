<?php
declare(strict_types=1);

namespace Saito\Test\Lib;

use PHPUnit\Framework\TestCase;

class BaseFunctionsTest extends TestCase
{
    public function testDateToUnixIsUnix()
    {
        $time = time();
        $actual = dateToUnix($time);
        $this->assertEquals($time, $actual);
    }

    public function testDateToUnixDateTime()
    {
        $time = new \DateTime();
        $actual = dateToUnix($time);
        $this->assertEquals($time->getTimestamp(), $actual);
    }

    public function testDateToUnixStringValid()
    {
        $time = '2013-12-01 20:00:00';
        $actual = dateToUnix($time);
        $this->assertEquals(strtotime($time), $actual);
    }

    public function testDateToUnixStringInvalid()
    {
        $time = 'foo';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1524230476);

        dateToUnix($time);
    }
}
