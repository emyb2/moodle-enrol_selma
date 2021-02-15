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

namespace enrol_selma\local\external;

use coding_exception;
use external_value;

defined('MOODLE_INTERNAL') || die();

class external_structure {
    /**
     * Single course structure that can be reused in multiple service functions.
     *
     * @return array|external_value[]
     * @throws coding_exception
     */
    public static function get_course_structure() : array {
        return [
            'fullname' => new external_value(PARAM_TEXT,
                get_string('create_course_parameters::fullname', 'enrol_selma')
            ),
            'shortname' => new external_value(PARAM_TEXT,
                get_string('create_course_parameters::shortname', 'enrol_selma')
            ),
            'idnumber' => new external_value(PARAM_TEXT,
                get_string('create_course_parameters::idnumber', 'enrol_selma')
            )
        ];
    }

    /**
     * Single student structure that can be reused in multiple service functions.
     *
     * @return array|external_value[]
     * @throws coding_exception
     */
    public static function get_student_structure() : array {
        return [
            'firstname' => new external_value(
                PARAM_TEXT,
                get_string('firstname')
            ),
            'lastname' => new external_value(
                PARAM_TEXT,
                get_string('lastname')
            ),
            'middlename' => new external_value(
                PARAM_TEXT,
                get_string('middlename', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'preferredname' => new external_value(
                PARAM_TEXT,
                get_string('preferredname', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'email' => new external_value(
                PARAM_EMAIL,
                get_string('email', 'enrol_selma')
            ),
            'studentid' => new external_value(
                PARAM_TEXT,
                get_string('studentid', 'enrol_selma')
            ),
            'mobilephone' => new external_value(
                PARAM_TEXT,
                get_string('mobilephone', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'secondaryphone' => new external_value(
                PARAM_TEXT, get_string('secondaryphone', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'dateofbirth' => new external_value(
                PARAM_TEXT,
                get_string('dateofbirth', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'gender' => new external_value(
                PARAM_TEXT,
                get_string('gender', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'ethnicity' => new external_value(
                PARAM_TEXT,
                get_string('ethnicity', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'nsn' => new external_value(
                PARAM_INT,
                get_string('nsn', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'otherid' => new external_value(
                PARAM_INT,
                get_string('otherid', 'enrol_selma'),
                VALUE_OPTIONAL
            ),
            'moodleuserid' => new external_value(
                PARAM_INT,
                get_string('moodleuserid', 'enrol_selma'),
                VALUE_OPTIONAL
            )
        ];
    }

    /**
     * Single teacher structure that can be reused in multiple service functions.
     *
     * @return array|external_value[]
     * @throws coding_exception
     */
    public static function get_teacher_structure() : array {
        // Same as student structure, but add teacher ID.
        $ts = self::get_student_structure();

        // Set student ID to optional.
        $ts['studentid'] = new external_value(
            PARAM_TEXT,
            get_string('studentid', 'enrol_selma'),
            VALUE_OPTIONAL
        );

        // Add teacher ID requirement.
        $ts['teacherid'] = new external_value(
            PARAM_TEXT,
            get_string('create_teacher_parameters::teacherid', 'enrol_selma')
        );

        return $ts;
    }
}
