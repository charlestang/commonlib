<?php

use \charlestang\commonlib\datetime\Dth;

/**
 * Test cases of Dth class.
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class DthTest extends PHPUnit_Framework_TestCase
{

    public function testGetTomorrowDate()
    {
        $expected = [
            strtotime(date('Y-m-d', strtotime('+1 day'))),
            date('Y-m-d', strtotime('+1 day')),
            date('Y-m-d 00:00:00', strtotime('+1 day')),
        ];
        $actual   = [
            Dth::getTomorrowDate(Dth::BY_UNIX_TIMESTAMP),
            Dth::getTomorrowDate(),
            Dth::getTomorrowDate(Dth::BY_FORMATTED_DATE, Dth::FORMAT_MYSQL_DATETIME),
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGetTodayDate()
    {
        $expected = [
            date('Y-m-d', time()),
            date('Y-m-d 00:00:00', time()),
            strtotime(date('Y-m-d', time())),
        ];
        $actual   = [
            Dth::getTodayDate(),
            Dth::getTodayDate(Dth::BY_FORMATTED_DATE, Dth::FORMAT_MYSQL_DATETIME),
            Dth::getTodayDate(Dth::BY_UNIX_TIMESTAMP),
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGetYesterdayDate()
    {
        $expected = [
            date('Y-m-d', strtotime('-1 day')),
            date('Y-m-d 00:00:00', strtotime('-1 day')),
            strtotime(date('Y-m-d', strtotime('-1 day'))),
        ];
        $actual   = [
            Dth::getYesterdayDate(),
            Dth::getYesterdayDate(Dth::BY_FORMATTED_DATE, Dth::FORMAT_MYSQL_DATETIME),
            Dth::getYesterdayDate(Dth::BY_UNIX_TIMESTAMP),
        ];
        $this->assertEquals($expected, $actual);
    }

}
