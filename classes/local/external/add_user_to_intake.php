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
 * SELMA plugin 'add_user_to_intake' external file.
 *
 * @package    enrol_selma
 * @category   external
 * @copyright  2020 LearningWorks <selma@learningworks.co.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once(dirname(__FILE__, 4) . '/locallib.php');

use context_system;
use external_api;
use external_function_parameters;
use external_value;

/**
 * Class add_user_to_intake used to add a given user to a given intake.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_user_to_intake extends external_api {
    /**
     * Returns required parameters to add a user to an intake.
     *
     * @return external_function_parameters Description of parameters and expected type.
     */
    public static function add_user_to_intake_parameters() {
        // A 'FUNCTIONNAME_parameters()' always return an 'external_function_parameters()'.
        // The 'external_function_parameters' constructor expects an array of 'external_description'.
        return new external_function_parameters(
            // An 'external_description' can be 'external_value', 'external_single_structure' or 'external_multiple' structure.
            [
                'userid' => new external_value(PARAM_TEXT, get_string('add_user_to_intake_parameters::userid', 'enrol_selma')),
                'intakeid' => new external_value(PARAM_TEXT, get_string('add_user_to_intake_parameters::intakeid', 'enrol_selma')),
            ],
            get_string('add_user_to_intake_parameters', 'enrol_selma')
        );
    }

    /**
     * The constructor/function itself - let's add the user to the intake.
     *
     * @param   int     $userid SELMA ID of user to add to intake.
     * @param   int     $intakeid SELMA intake ID the user should be added to.
     * @return  array   Array of success status & bool if successful/not, message.
     */
    public function __construct(int $userid, int $intakeid) {
        return self::add_user_to_intake($userid, $intakeid);
    }

    /**
     * The function itself - let's add the user to the intake.
     *
     * @param   int     $userid SELMA ID of user to add to intake.
     * @param   int     $intakeid SELMA intake ID the user should be added to.
     * @return  array   Array of success status & bool if successful/not, message.
     */
    public static function add_user_to_intake(int $userid, int $intakeid) {
        // Validate parameters.
        $params = self::validate_parameters(self::add_user_to_intake_parameters(),
            [
                'userid' => $userid,
                'intakeid' => $intakeid
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_add_user_to_intake($params['userid'], $params['intakeid']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters Array of description of values returned by 'add_user_to_intake' function.
     */
    public static function add_user_to_intake_returns() {
        return new external_function_parameters(
            [
                'status' => new external_value(PARAM_TEXT, get_string('add_user_to_intake_returns::status', 'enrol_selma')),
                'added' => new external_value(PARAM_BOOL, get_string('add_user_to_intake_returns::added', 'enrol_selma')),
                'message' => new external_value(PARAM_TEXT, get_string('add_user_to_intake_returns::message', 'enrol_selma')),
            ],
            get_string('add_user_to_intake_returns', 'enrol_selma')
        );
    }
}