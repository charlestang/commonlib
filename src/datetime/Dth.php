<?php

namespace charlestang\commonlib\datetime;

defined('DTH_TIMEZONE') or define('DTH_TIMEZONE', 'Asia/Shanghai');

if (empty(ini_get('date.timezone'))) {
    date_default_timezone_set(DTH_TIMEZONE);
}

/**
 * Dth stands for "date time helper", the compact form of spelling can
 * save a lot of type when programming.
 *
 * (PHP 5 >= 5.3.0)
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class Dth
{

    /**
     * Period
     */
    const PERIOD_MINUTE = 60;
    const PERIOD_HOUR   = 3600;
    const PERIOD_DAY    = 86400;

    /**
     * Format string
     */
    const FORMAT_MYSQL_DATETIME = 'Y-m-d H:i:s'; //MySQL accepted datetime format
    const FORMAT_MYSQL_DATE     = 'Y-m-d'; //MySQL accepted date format

    /**
     * Template string
     */
    const TPL_MONTH_START = 'Y-m-01 00:00:00';

    /**
     * Return style
     */
    const BY_UNIX_TIMESTAMP = 'timestamp'; //Unix timestamp
    const BY_FORMATTED_DATE = 'formatted';  //formatted datetime

    /**
     * Interval type
     */
    const INTERVAL_CLOSED      = 'closed';      //closed interval, e.g. [x, y]
    const INTERVAL_HALF_CLOSED = 'half-closed'; //half-closed interval on left, e.g. [x, y)

    /**
     * Week day type
     */
    const FIRST_DAY_SUNDAY = 'w';
    const FIRST_DAY_MONDAY = 'N';

    //above constant definition

    /**
     * This is a low level api, which can translate a string protentially represented a date or time to
     * some specific format date time string or integer.
     * @param string $string date time string
     * @param string $by     in which way to translate
     * @param string $format format string
     * @return mixed
     */
    public static function translate($string, $by = self::BY_FORMATTED_DATE, $format = self::FORMAT_MYSQL_DATETIME)
    {
        $time = $string === 'now' ? time() : strtotime($string);
        if ($by == self::BY_FORMATTED_DATE) {
            $time = date($format, $time);
        }
        return $time;
    }

    /**
     * Get the date of today
     * @param string $by
     * @param string $format
     * @return mixed
     */
    public static function getTodayDate($by = self::BY_FORMATTED_DATE, $format = self::FORMAT_MYSQL_DATE)
    {
        return self::translate('today', $by, $format);
    }

    /**
     * Get the next date of specified date
     * @param string $string date time string
     * @param string $by     in which way to translate
     * @param string $format format string
     * @return mixed
     */
    public static function getNextDate($string, $by = self::BY_FORMATTED_DATE, $format = self::FORMAT_MYSQL_DATE)
    {
        return self::translate($string . ' +1 day', $by, $format);
    }

    /**
     * Get the date of tomorrow
     * @param string $by
     * @param string $format
     * @return mixed
     */
    public static function getTomorrowDate($by = self::BY_FORMATTED_DATE, $format = self::FORMAT_MYSQL_DATE)
    {
        return self::translate('tomorrow', $by, $format);
    }

    public static function getYesterdayDate($by = self::BY_FORMATTED_DATE, $format = self::FORMAT_MYSQL_DATE)
    {
        return self::translate('yesterday', $by, $format);
    }

    public static function getNow($by = self::BY_FORMATTED_DATE, $format = self::FORMAT_MYSQL_DATETIME)
    {
        return self::translate('now', $by, $format);
    }

    public static function translateInterval(
        $start,
        $end,
        $style = self::INTERVAL_HALF_CLOSED,
        $by = self::BY_UNIX_TIMESTAMP,
        $format = self::FORMAT_MYSQL_DATETIME
    ) {
        $start_time = self::translate($start, self::BY_UNIX_TIMESTAMP);
        $end_time   = self::translate($end, self::BY_UNIX_TIMESTAMP);
        if ($style == self::INTERVAL_CLOSED) {
            $end_time --;
        }
        if ($by != self::BY_UNIX_TIMESTAMP) {
            $start_time = date($format, $start_time);
            $end_time   = date($format, $end_time);
        }
        return [$start_time, $end_time];
    }

    public static function getTodayInterval(
        $style = self::INTERVAL_HALF_CLOSED,
        $by = self::BY_UNIX_TIMESTAMP,
        $format = self::FORMAT_MYSQL_DATETIME
    ) {
        return self::translateInterval('today', 'tomorrow', $style, $by, $format);
    }

    public static function getYesterdayInterval(
        $style = self::INTERVAL_HALF_CLOSED,
        $by = self::BY_UNIX_TIMESTAMP,
        $format = self::FORMAT_MYSQL_DATETIME
    ) {
        return self::translateInterval('yesterday', 'today', $style, $by, $format);
    }

    public static function getTomorrowInterval(
        $style = self::INTERVAL_HALF_CLOSED,
        $by = self::BY_UNIX_TIMESTAMP,
        $format = self::FORMAT_MYSQL_DATETIME
    ) {
        return self::translateInterval('tomorrow', 'tomorrow +1 day', $style, $by, $format);
    }

    public static function getThisMonthInterval(
        $style = self::INTERVAL_HALF_CLOSED,
        $by = self::BY_UNIX_TIMESTAMP,
        $format = self::FORMAT_MYSQL_DATETIME
    ) {
        $month_start = date(self::TPL_MONTH_START, self::translate('this month', self::BY_UNIX_TIMESTAMP));
        return self::translateInterval($month_start, $month_start . ' +1 month', $style, $by, $format);
    }

    public static function getNextMonthInterval(
        $style = self::INTERVAL_HALF_CLOSED,
        $by = self::BY_UNIX_TIMESTAMP,
        $format = self::FORMAT_MYSQL_DATETIME
    ) {
        $month_start      = date(self::TPL_MONTH_START, self::translate('this month', self::BY_UNIX_TIMESTAMP));
        $next_month_start = date(self::TPL_MONTH_START, self::translate($month_start . ' +1 month', self::BY_UNIX_TIMESTAMP));
        return self::translateInterval($next_month_start, $next_month_start . ' +1 month', $style, $by, $format);
    }

    public static function getLastMonthInterval(
        $style = self::INTERVAL_HALF_CLOSED,
        $by = self::BY_UNIX_TIMESTAMP,
        $format = self::FORMAT_MYSQL_DATETIME
    ) {
        $month_start      = date(self::TPL_MONTH_START, self::translate('this month', self::BY_UNIX_TIMESTAMP));
        $last_month_start = date(self::TPL_MONTH_START, self::translate($month_start . ' -1 month', self::BY_UNIX_TIMESTAMP));
        return self::translateInterval($last_month_start, $last_month_start . ' +1 month', $style, $by, $format);
    }

    public static function getDateStart($string, $by = self::BY_FORMATTED_DATE, $format = self::FORMAT_MYSQL_DATETIME)
    {
        return self::translate(self::translate($string, self::BY_FORMATTED_DATE, self::FORMAT_MYSQL_DATE), $by, $format);
    }

    public static function getDateEnd($string, $by = self::BY_FORMATTED_DATE, $format = self::FORMAT_MYSQL_DATETIME)
    {
        return self::translate(
            self::translate($string, self::BY_FORMATTED_DATE, self::FORMAT_MYSQL_DATE) . ' 23:59:59',
            $by,
            $format
        );
    }

    /**
     * This method is used to calculate an interval of some specific date time string.
     *
     * @param string $string date time string
     * @param string $style  what the style of the interval, HALF_CLOSED, e.g. [x,y) , CLOSED, e.g. [x,y] .
     * @param string $by     in which way to translate
     * @param string $format format string
     * @return array
     */
    public static function getDateInterval(
        $string,
        $style = self::INTERVAL_CLOSED,
        $by = self::BY_FORMATTED_DATE,
        $format = self::FORMAT_MYSQL_DATETIME
    ) {
        $start = self::getDateStart($string);
        return self::translateInterval($start, $start . ' +1 day', $style, $by, $format);
    }

    /**
     * Calculate days between two date string, the first parameter is bigger in unix timestamp format, that means the
     * corresponding time is a forward time of the second parameter
     *
     * This method will always return an SIGNED integer number, if $big is not bigger than $small, the result will be minus
     *
     * @param string $big
     * @param string $small
     * @return int
     */
    public static function countDays($big, $small)
    {
        $minus = false;
        $time1 = strtotime($big);
        $time2 = strtotime($small);
        if ($time1 < $time2) {
            $minus = true;
        }
        $interval = date_diff(date_create(date('Y-m-d', strtotime($big))), date_create($small));
        return $minus ? 0 - $interval->format('%a') : intval($interval->format('%a'));
    }

    public static function getDayOrderOfWeek($string, $in = self::FIRST_DAY_SUNDAY)
    {
        return date($in, self::translate($string, self::BY_UNIX_TIMESTAMP));
    }
}
