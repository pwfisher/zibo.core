<?php

namespace zibo\library;

use \DateTime as PhpDateTime;

/**
 * Common date time functions
 */
class DateTime extends PhpDateTime {

    /**
     * An hour in seconds
     * @var integer
     */
    const HOUR = 3600;

    /**
     * A day in seconds
     * @var integer
     */
    const DAY = 86400;

    /**
     * Rounds the provided time in seconds to the provided date at 00:00:00
     * @param integer $time Timestamp to round
     * @return integer Timestamp of the rounded time
     */
    public static function roundTimeToDay($time = null) {
        if (!$time) {
            $time = time();
        }

        return mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
    }

    /**
     * Formats the provided number of seconds in the HH:MM format
     * @param integer $seconds The number of seconds
     * @return string The provided number of seconds in the HH:MM format
     */
    public static function formatSeconds($seconds) {
        $hours = floor($seconds / 3600) % 24;
        $minutes = floor(($seconds % 3600) / 60) % 60;

        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

}