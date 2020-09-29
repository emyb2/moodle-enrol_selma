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
 * Utility-type methods to help throughout rest of code.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

use __PHP_Incomplete_Class;
use coding_exception;
use database_column_info;
use dml_exception;
use moodle_exception;
use core_text;

defined('MOODLE_INTERNAL') || die();


global $CFG;
require_once($CFG->libdir . '/weblib.php');

/**
 * Utility helper methods to help in other parts of codebase.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class utilities {

    /**
     * Basic unique username generator that uses first and last names as seeds.
     *
     * @param string $firstname
     * @param string $lastname
     * @return string
     * @throws coding_exception
     * @throws dml_exception|moodle_exception
     */
    public static function generate_username(string $firstname, string $lastname) : string {
        global $CFG, $DB;
        if (trim($firstname) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'firstname');
        }
        if (trim($lastname) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'lasstname');
        }
        $username = clean_param(core_text::strtolower("{$firstname}.{$lastname}"), PARAM_USERNAME);
        if (trim($username) === '') {
            throw new moodle_exception('invalidusername');
        }
        if ($DB->sql_regex_supported()) {
            $REGEXP = $DB->sql_regex(true);
            $select = "username $REGEXP :usernamepattern AND mnethostid = :mnethostid AND deleted <> :deleted";
            $usernamepattern = "$username([0-9]+)?";
            $params = ['usernamepattern' => $usernamepattern, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 1];
        } else {
            $select = "username = :username AND mnethostid = :mnethostid AND deleted <> :deleted";
            $params = ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 1];
        }
        $existingusers = $DB->get_records_select(
            'user',
            $select,
            $params,
            'username DESC',
            'id, username',
            0,
            1
        );
        if ($existingusers) {
            $existinguser = reset($existingusers);
            $username = uu_increment_username($existinguser->username);
        }
        return $username;
    }

    /**
     * Borrowed from Symfony's PHP 8 polyfill.
     *
     * @link            https://github.com/symfony/polyfill/tree/master/src/Php80
     * @param   mixed   $value Some type of object to identify type of.
     * @return  string  Type of object in string format.
     */
    public static function get_debug_type($value) : string {
        switch (true) {
            case null === $value:
                return 'null';
            case is_bool($value):
                return 'bool';
            case is_string($value):
                return 'string';
            case is_array($value):
                return 'array';
            case is_int($value):
                return 'int';
            case is_float($value):
                return 'float';
            case is_object($value):
                break;
            case $value instanceof __PHP_Incomplete_Class:
                return '__PHP_Incomplete_Class';
            default:
                if (null === $type = @get_resource_type($value)) {
                    return 'unknown';
                }

                if ('Unknown' === $type) {
                    $type = 'closed';
                }

                return "resource ($type)";
        }
        $class = get_class($value);
        if (false === strpos($class, '@')) {
            return $class;
        }
        return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class').'@anonymous';
    }

    /**
     * Get information about a specific column.
     *
     * @param   string                  $table Name of table.
     * @param   string                  $name Name of column.
     * @return  database_column_info    Array of database_column_info objects indexed with column names.
     * @throws  moodle_exception
     */
    public static function get_column_information(string $table, string $name) : database_column_info {
        global $DB;
        $columns = $DB->get_columns($table); // Using cache.
        if (!isset($columns[$name])) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'name');
        }
        return $columns[$name];
    }

    /**
     * Utility method to check a property length against associated varchar database column.
     *
     * @param string $table Which table to lookup.
     * @param string $name  Name of column to check.
     * @param string $value Value being checked.
     * @throws moodle_exception
     */
    public static function check_length(string $table, string $name, string $value) {
        $column = self::get_column_information($table, $name);
        if (core_text::strlen($value) > $column->max_length) {
            throw new moodle_exception('maximumcharacterlengthforexceeded',
                'enrol_selma',
                null,
                array(
                    'name' => $name,
                    'expected' => $column->max_length
                ));
        }
    }
}
