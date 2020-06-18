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

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use core_user;
use DateInterval;
use DateTime;
use Monolog\Logger;
use Monolog\Handler\NativeMailerHandler;
use tool_aap\local\monolog\MoodleDBTableHandler;
use tool_aap\local\monolog\MoodleEmailHandler;

/**
 * Singleton class for get Monolog logger.
 *
 * @package     Logging
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_logger {

    public const COMPONENT = 'enrol_selma';

    /** @var string LOG_TABLE_NAME Name of Moodle table to store log entries. */
    public const LOG_TABLE_NAME = 'enrol_selma_log';

    /**
     * @var Logger $logger Handle to a Monolog logger instance.
     */
    private static $logger;

    /**
     * Return singleton.
     *
     * @return Logger
     * @throws \coding_exception
     * @throws \ddl_exception
     * @throws \dml_exception
     */
    public static function get_logger() {
        global $DB;
        if (is_null(static::$logger)) {
            $config = get_config(static::COMPONENT);
            $logger = new Logger(static::COMPONENT);
            $loglevel = empty($config->loglevel) ? Logger::ERROR : (int) $config->loglevel;
            $logger->pushHandler(
                new MoodleDBTableHandler(static::LOG_TABLE_NAME, static::COMPONENT, $loglevel)
            );
            // Conditionally add email handler.
            if (!empty($config->logemailcritical) && !empty($config->logemailcriticalrecipients)) {
                $to = [];
                $recipients = preg_split("/[\s,;]+/", $config->logemailcriticalrecipients);
                foreach ($recipients as $recipient) {
                    $email = clean_param($recipient, PARAM_EMAIL);
                    if (!empty($email)) {
                        $recipient = $DB->get_record('user', ['email' => $email]);
                        if ($recipient) {
                            $to[] = $recipient;
                        }
                    }
                }
                if (!empty($to)) {
                    $subject = get_string('pluginname','tool_aap');
                    $from = core_user::get_noreply_user();
                    $logger->pushHandler(
                        new MoodleEmailHandler($to, $subject, $from, Logger::CRITICAL)
                    );
                }
            }
            static::$logger = $logger;
        }
        return static::$logger;
    }

    /**
     * Purge log entries older than certain amount of days.
     *
     * @param int $days
     * @throws \coding_exception
     * @throws \ddl_exception
     * @throws \dml_exception
     */
    public static function purge_log_table_by_days(int $days) {
        global $DB;
        if ($days > 0) {
            $date = new DateTime();
            $date->sub(new DateInterval('P' . $days . 'D'));
            $timeinterval =  $date->getTimestamp();
            $count = $DB->count_records_select(
                self::LOG_TABLE_NAME,
                "time < :time",
                ['time' => $timeinterval]
            );
            if ($count) {
                $DB->delete_records_select(
                    self::LOG_TABLE_NAME,
                    "time < :time",
                    ['time' => $timeinterval]
                );
                static::get_logger()->info("Purged {$count} log items older than {$days} day(s)");
            }
        }
    }

}
