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
 * Library of functions to help with testing.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Plugin data generator class.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_selma_generator extends testing_module_generator {

    /**
     * Enable the plugin.
     */
    public function enable_plugin() {
        $enabled = enrol_get_plugins(true);
        $enabled['selma'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    /**
     * Disable the plugin.
     */
    public function disable_plugin() {
        $enabled = enrol_get_plugins(true);
        unset($enabled['selma']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    /**
     * Prepared intake data to test with.
     *
     * @return  array[] Array with test intake data.
     */
    public function get_intake_data() : array {
        return [
            [
                'id' => 10612936,
                'programmeid' => 10479434,
                'programmetitle' => 'Adult and Tertiary Teaching 4',
                'code' => '2020-08-LW-ATT4-2018-1',
                'name' => 'Wintec August 2020',
                'startdate' => (new DateTime('01-08-2020'))->format('d-m-Y'),
                'enddate' => (new DateTime('31-07-2021'))->format('d-m-Y'),
            ],
            [
                'id' => 10612937,
                'programmeid' => 10557717,
                'programmetitle' => 'First Line Management 4',
                'code' => '2020-08-LW-FLM4 v1-1',
                'name' => 'Wintec August 2020',
                'startdate' => new DateTime('01-08-2020'),
                'enddate' => new DateTime('31-07-2021'),
            ],
            [
                'id' => 0,
                'programmeid' => 10579102,
                'programmetitle' => 'Introduction to Team Leadership 3',
                'code' => '2020-08-LW-ITL3 v1-1',
                'name' => 'Wintec August 2020',
                'startdate' => new DateTime('01-08-2020'),
                'enddate' => new DateTime('31-07-2021'),
            ],
        ];
    }

    /**
     * Prepared course data to test with.
     *
     * @return  array[] Array with most basic course data we need.
     */
    public function get_selma_course_data() : array {
        return [
            'valid' => [
                'fullname' => 'Adult and Tertiary Teaching 4',
                'shortname' => 'ATT4',
                'idnumber' => 10479434
            ],
            'invalid' => [
                'fullname' => 'First Line Management 4',
                'shortname' => 'FLM4',
                'idnumber' => ['invalid' => '<strong>~!@#$%^&*()_+</strong>']
            ],
            'complete' => [
                'fullname' => 'Introduction to Team Leadership 3',
                'shortname' => 'ITL3',
                'idnumber' => 10479435
            ]
        ];
    }

    /**
     * Prepared teacher data to test with.
     *
     * @return  array[] Array with most basic teacher/user data we need.
     */
    public function get_selma_teacher_data() : array {
        return [
            'valid' => [
                'firstname' => 'Teacher',
                'lastname' => 'User',
                'email' => 'teacher@school.invalid',
                'teacherid' => '12344321'
            ],
            'invalid' => [
                'firstname' => 'Invalid Teacher',
                'lastname' => 'User',
                'email' => 34154576,
                'teacherid' => '!@#$%^&*()'
            ],
            // Add all other fields too.
            'complete' => [
                'firstname' => 'Full Teacher',
                'lastname' => 'User',
                'email' => 'fullteacher@school.invalid',
                'teacherid' => 12345678
            ]
        ];
    }

    /**
     * Prepared 'get_all_courses' webservice call data to help with testing.
     *
     * @return  array[] Array with most basic course data we need.
     */
    public function get_selma_get_course_data() : array {
        return [
            'valid' => [
                'amount' => 10,
                'page' => 1,
            ],
            'invalid' => [
                'amount' => -2,
                'page' => -5,
            ]
        ];
    }

    /**
     * Generates a random string of a given length.
     *
     * @param   int     $length The number of characters the random string should have.
     * @return  string  Generated random string.
     */
    public function generate_random_string(int $length) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomstring = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomstring .= $characters[$index];
        }
        return $randomstring;
    }

    public function create_profile_field_category(string $name) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/profile/definelib.php');
        $id = $DB->insert_record('user_info_category', ['name' => $name]);
        return $DB->get_record('user_info_category', ['id' => $id], '*', MUST_EXIST);
    }

    public function create_profile_field(string $datatype, array $data) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/profile/definelib.php');
        $datatypes = profile_list_datatypes();
        if (!isset($datatypes[$datatype])) {
            throw new coding_exception('invalidvalue:datatype');
        }
        $data['datatype'] = $datatype;
        require_once($CFG->dirroot . '/user/profile/field/' . $datatype . '/define.class.php');
        $newfield = 'profile_define_' . $datatype;
        /** @var profile_define_base $formfield */
        $formfield = new $newfield();
        $formfield->define_save((object) $data);
    }

}