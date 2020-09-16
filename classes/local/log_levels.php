<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Log levels as described by RFC 5424.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Log level enum as described by RFC 5424.
 *
 * DEBUG (100): Detailed debug information.
 * INFO (200): Interesting events. Examples: User logs in, SQL logs.
 * NOTICE (250): Normal but significant events.
 * WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API,
 * undesirable things that are not necessarily wrong.
 * ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
 * CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.
 * ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc.
 * This should trigger the SMS alerts and wake you up.
 *
 * EMERGENCY (600): Emergency: system is unusable.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_levels {

    /**
     * Log level DEBUG.
     */
    public const DEBUG     = 100;

    /**
     * Log level INFO.
     */
    public const INFO      = 200;

    /**
     * Log level NOTICE.
     */
    public const NOTICE    = 250;

    /**
     * Log level WARNING.
     */
    public const WARNING   = 300;

    /**
     * Log level ERROR.
     */
    public const ERROR     = 400;

    /**
     * Log level CRITICAL.
     */
    public const CRITICAL  = 500;

    /**
     * Log level ALERT.
     */
    public const ALERT     = 550;

    /**
     * Log level EMERGENCY.
     */
    public const EMERGENCY = 600;

    /**
     * All log levels and textual representations.
     *
     * @return string[] Array of levels.
     */
    public static function all() {
        return [
            100 => 'DEBUG',
            200 => 'INFO',
            250 => 'NOTICE',
            300 => 'WARNING',
            400 => 'ERROR',
            500 => 'CRITICAL',
            550 => 'ALERT',
            600 => 'EMERGENCY'
        ];
    }
}
