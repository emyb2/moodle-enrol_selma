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

use core_text;
use database_column_info;

global $CFG;
require_once($CFG->libdir . '/weblib.php');

/**
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
     * @throws \coding_exception
     * @throws \dml_exception
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
            $select = "username $REGEXP :usernamepattern AND mnethostid = :mnethostid";
            $usernamepattern = "$username([0-9]+)?";
            $params = ['usernamepattern' => $usernamepattern, 'mnethostid' => $CFG->mnet_localhost_id];
        } else {
            $select = "username = :username AND mnethostid = :mnethostid";
            $params = ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id];
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
     * @link https://github.com/symfony/polyfill/tree/master/src/Php80
     * @param $value
     * @return string
     */
    public static function get_debug_type($value) : string {
        switch (true) {
            case null === $value: return 'null';
            case \is_bool($value): return 'bool';
            case \is_string($value): return 'string';
            case \is_array($value): return 'array';
            case \is_int($value): return 'int';
            case \is_float($value): return 'float';
            case \is_object($value): break;
            case $value instanceof \__PHP_Incomplete_Class: return '__PHP_Incomplete_Class';
            default:
                if (null === $type = @get_resource_type($value)) {
                    return 'unknown';
                }

                if ('Unknown' === $type) {
                    $type = 'closed';
                }

                return "resource ($type)";
        }
        $class = \get_class($value);
        if (false === strpos($class, '@')) {
            return $class;
        }
        return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class').'@anonymous';
    }

    /**
     * Get information about a specific column.
     *
     * @param string $name
     * @return mixed
     * @throws moodle_exception
     */
    public static function get_column_information(string $table, string $name) : database_column_info  {
        global $DB;
        $columns = $DB->get_columns($table); // Using cache.
        if (!isset($columns[$name])) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'name');
        }
        return $columns[$name];
    }

}
