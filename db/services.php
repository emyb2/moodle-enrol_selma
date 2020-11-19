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
 * SELMA enrolment plugin external functions and service definitions.
 *
 * @package     enrol_selma
 * @category    webservice
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'enrol_selma_create_course' => [
        'classname'     => 'enrol_selma\local\external\create_course',
        'methodname'    => 'create_course',
        'description'   => new lang_string(
            'create_course::description',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => false,
        'capabilities'  => 'moodle/course:create'
    ],
    'enrol_selma_get_gradebook_items' => [
        'classname'     => 'enrol_selma\local\external\get_gradebook_items',
        'methodname'    => 'get_gradebook_items',
        'description'   => new lang_string(
            'get_gradebook_items::description',
            'enrol_selma'
        ),
        'type'          => 'read',
        'ajax'          => false,
        'capabilities'  => ''
    ],
    'enrol_selma_get_all_courses' => [
        'classname'     => 'enrol_selma\local\external\get_all_courses',
        'methodname'    => 'get_all_courses',
        'description'   => new lang_string(
                'get_all_courses::description',
                'enrol_selma'
        ),
        'type'          => 'read',
        'ajax'          => false,
        'capabilities'  => ''
    ],
    'enrol_selma_create_users' => [
        'classname'     => 'enrol_selma\local\external\create_users',
        'methodname'    => 'create_users',
        'description'   => new lang_string(
            'create_users::description',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => false,
        'capabilities'  => 'moodle/user:create'
    ],
    'enrol_selma_create_intake' => [
        'classname'     => 'enrol_selma\local\external\create_intake',
        'methodname'    => 'create_intake',
        'description'   => new lang_string(
            'create_intake::description',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => false,
        'capabilities'  => ''
    ],
    'enrol_selma_get_intake' => [
        'classname'     => 'enrol_selma\local\external\get_intake',
        'methodname'    => 'get_intake',
        'description'   => new lang_string(
            'get_intake::description',
            'enrol_selma'
        ),
        'type'          => 'read',
        'ajax'          => false,
        'capabilities'  => ''
    ],
    'enrol_selma_get_intake_courses' => [
        'classname'     => 'enrol_selma\local\external\get_intake_courses',
        'methodname'    => 'get_intake_courses',
        'description'   => new lang_string(
            'get_intake_courses::description',
            'enrol_selma'
        ),
        'type'          => 'read',
        'ajax'          => false,
        'capabilities'  => ''
    ],
    'enrol_selma_add_intake_to_course' => [
        'classname'     => 'enrol_selma\local\external\add_intake_to_course',
        'methodname'    => 'add_intake_to_course',
        'description'   => new lang_string(
            'add_intake_to_course::description',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => false,
        'capabilities'  => ''
    ],
    'enrol_selma_add_user_to_intake' => [
        'classname'     => 'enrol_selma\local\external\add_user_to_intake',
        'methodname'    => 'add_user_to_intake',
        'description'   => new lang_string(
            'add_user_to_intake::description',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => false,
        'capabilities'  => ''
    ],
    'enrol_selma_create_student' => [
        'classname'     => 'enrol_selma\local\external\create_student',
        'methodname'    => 'create_student',
        'description'   => new lang_string(
            'createstudent::servicedescription',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => false,
        'capabilities'  => 'moodle/user:create'
    ],
    'enrol_selma_update_student' => [
        'classname'     => 'enrol_selma\local\external\update_student',
        'methodname'    => 'update_student',
        'description'   => new lang_string(
            'updatestudent::servicedescription',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => false,
        'capabilities'  => 'moodle/user:update'
    ],
    'enrol_selma_get_student' => [
        'classname'     => 'enrol_selma\local\external\get_student',
        'methodname'    => 'get_student',
        'description'   => new lang_string(
            'get_student::description',
            'enrol_selma'
        ),
        'type'          => 'read',
        'ajax'          => false,
        'capabilities'  => ''
    ]
];
$services = [
    'enrol_selma' => [
        'shortname' => 'enrol_selma',
        'enabled' => 1,
        'functions' => [
            'enrol_selma_create_course',
            'enrol_selma_get_gradebook_items',
            'enrol_selma_get_all_courses',
            'enrol_selma_create_users',
            'enrol_selma_create_intake',
            'enrol_selma_get_intake',
            'enrol_selma_get_intake_courses',
            'enrol_selma_add_intake_to_course',
            'enrol_selma_add_user_to_intake',
            'enrol_selma_create_student',
            'enrol_selma_update_student',
            'enrol_selma_get_student'
        ],
        'restrictedusers' => 0
    ]
];
