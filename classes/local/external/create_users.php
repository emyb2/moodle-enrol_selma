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
 * SELMA plugin 'create_users' external file.
 *
 * @package    enrol_selma
 * @category   external
 * @copyright  2020 LearningWorks <selma@learningworks.co.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local\external;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__, 4) . '/locallib.php');

use context_system;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

/**
 * Class create_users used to create users from SELMA.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_users extends external_api {
    /**
     * Returns required parameters to create a user(s).
     *
     * @return external_function_parameters Description of parameters and expected type.
     */
    public static function create_users_parameters() {
        return new external_function_parameters(
            [
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'username' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::username', 'enrol_selma'),
                                VALUE_OPTIONAL
                            ),
                            'forename' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::forename', 'enrol_selma')
                            ),
                            'lastname' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::lastname', 'enrol_selma')
                            ),
                            'email1' => new external_value(PARAM_EMAIL,
                                get_string('create_users_parameters::email1', 'enrol_selma')
                            ),
                            'id' => new external_value(PARAM_INT,
                                get_string('create_users_parameters::id', 'enrol_selma')
                            ),
                            'mobilephone' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::mobilephone', 'enrol_selma'),
                                VALUE_OPTIONAL
                            ),
                            'secondaryphone' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::secondaryphone', 'enrol_selma'),
                                VALUE_OPTIONAL
                            ),
                            'gender' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::gender', 'enrol_selma'),
                                VALUE_OPTIONAL
                            ),
                            'dob' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::dob', 'enrol_selma'),
                                VALUE_OPTIONAL
                            ),
                            'nsn' => new external_value(PARAM_INT,
                                get_string('create_users_parameters::nsn', 'enrol_selma'),
                                VALUE_OPTIONAL
                            ),
                            'status' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::status', 'enrol_selma'),
                                VALUE_OPTIONAL
                            ),
                            'preferredname' => new external_value(PARAM_TEXT,
                                get_string('create_users_parameters::preferredname', 'enrol_selma'),
                                VALUE_OPTIONAL
                            ),
                        ], get_string('create_users_parameters::user', 'enrol_selma')
                    ), get_string('create_users_parameters::users', 'enrol_selma')
                )
            ],
            get_string('create_users_parameters', 'enrol_selma')
        );
    }

    /**
     * The constructor/function itself - let's create the users.
     *
     * @param   array   $users Users object and required details to create users.
     * @return  array   Array of success status & created user IDs, if any.
     */
    public function __construct(array $users) {
        return self::create_users($users);
    }

    /**
     * The function itself - let's create the users.
     *
     * @param   array   $users Users object and required details to create users.
     * @return  array   Array of success status & created user IDs, if any.
     */
    public static function create_users(array $users) {
        // Validate parameters.
        $params = self::validate_parameters(self::create_users_parameters(),
            [
                'users' => $users
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_create_users($params['users']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters Array of description of values returned by 'create_users' function.
     */
    public static function create_users_returns() {
        return new external_function_parameters(
            [
                'status' => new external_value(PARAM_TEXT,
                    get_string('create_users_returns::status', 'enrol_selma')
                ),
                'userids' => new external_multiple_structure(
                    new external_value(PARAM_INT,
                        get_string('create_users_returns::userid', 'enrol_selma')
                    ),
                    get_string('create_users_returns::userids', 'enrol_selma')
                ),
                'message' => new external_value(PARAM_TEXT,
                    get_string('create_users_returns::message', 'enrol_selma')
                )
            ], get_string('create_users_returns', 'enrol_selma')
        );
    }
}