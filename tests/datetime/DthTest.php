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

    public function testGetDateInterval()
    {
        $expected = [
            ['2015-01-01 00:00:00', '2015-01-01 23:59:59'],
            ['2014-12-31 00:00:00', '2015-01-01 00:00:00'],
        ];
        $actual   = [
            Dth::getDateInterval('2015-01-01 12:34:56'),
            Dth::getDateInterval('2014-12-31 12:34:56', Dth::INTERVAL_HALF_CLOSED),
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testCountDays()
    {
        $expected = [
            0,
            1,
            0,
            -1,
        ];
        $actual   = [
            Dth::countDays('2015-01-01', '2015-01-01'),
            Dth::countDays('2015-03-01', '2015-02-28'),
            Dth::countDays('2015-01-01 23:59:59', '2015-01-01 00:00:00'),
            Dth::countDays('2014-12-31', '2015-01-01'),
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGetDayOrderOfWeek()
    {
        $expected = [
            6,
        ];
        $actual   = [
            Dth::getDayOrderOfWeek('2015-08-08 01:41:00'),
        ];
        $this->assertEquals($expected, $actual);
    }

}
