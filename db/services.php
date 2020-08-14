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

$namespace = 'enrol_selma\local\external\\';

$functions = [
    'enrol_selma_create_course' => [                        // Name of the web service function that the client will call.
        'classname'     => $namespace . 'create_course',    // Namespaced class in classes/external/external.php.
        'methodname'    => 'create_course',                 // Implement this function into the above class.
        'description'   => new lang_string(
                'create_course::description',
                'enrol_selma'
        ),                                                  // Human-readable description displayed in generated API documentation.
        'type'          => 'write',                         // Is 'write' if function does any database change, otherwise 'read'.
        'ajax'          => true                             // If web service function callable via AJAX = true, otherwise false.
    ],
    'enrol_selma_get_all_courses' => [
        'classname'     => $namespace . 'get_all_courses',
        'methodname'    => 'get_all_courses',
        'description'   => new lang_string(
                'get_all_courses::description',
                'enrol_selma'
        ),
        'type'          => 'read',
        'ajax'          => true
    ],
    'enrol_selma_create_users' => [
        'classname'     => $namespace . 'create_users',
        'methodname'    => 'create_users',
        'description'   => new lang_string(
            'create_users::description',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => true
    ],
    'enrol_selma_add_user_to_intake' => [
        'classname'     => $namespace . 'add_user_to_intake',
        'methodname'    => 'add_user_to_intake',
        'description'   => new lang_string(
            'add_user_to_intake::description',
            'enrol_selma'
        ),
        'type'          => 'write',
        'ajax'          => true
    ]
];

// OPTIONAL
// During the plugin installation/upgrade, Moodle installs these services as pre-built services.
// A pre-built service is not editable by administrator.
$services = [
    'enrol_selma_webservice' => [                // The name of the web service.
        'functions' => [                         // Web service functions of this service.
            'enrol_selma_create_course',
            'enrol_selma_get_all_courses',
            'enrol_selma_create_users',
            'enrol_selma_add_user_to_intake'
        ],
            'requiredcapability' =>
                    'enrol/selma:manage',   // Web service user needs this capability to access any function of this service.
        'restrictedusers' => 0,
        'enabled' => 1,                     // If enabled, the service can be reachable on a default installation.
        'shortname' => 'selmawebservice'    // Optional – but needed if restrictedusers is set so as to allow logins.
    ]
];