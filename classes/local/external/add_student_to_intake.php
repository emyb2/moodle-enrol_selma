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
 * SELMA plugin 'add_student_to_intake' external file.
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
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;

/**
 * Class add_student_to_intake adds a given user to a given intake.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_student_to_intake extends external_api {
    /**
     * Returns required parameters to add a user to an intake.
     *
     * @return external_function_parameters Description of parameters and expected type.
     */
    public static function add_student_to_intake_parameters() {
        // A 'FUNCTIONNAME_parameters()' always return an 'external_function_parameters()'.
        // The 'external_function_parameters' constructor expects an array of 'external_description'.
        return new external_function_parameters(
            // An 'external_description' can be 'external_value', 'external_single_structure' or 'external_multiple' structure.
            [
                'studentid' => new external_value(PARAM_TEXT, get_string('add_student_to_intake_parameters::studentid', 'enrol_selma')),
                'intakeid' => new external_value(PARAM_TEXT, get_string('add_student_to_intake_parameters::intakeid', 'enrol_selma')),
            ],
            get_string('add_student_to_intake_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's add the user to the intake.
     *
     * @param   int     $studentid SELMA ID of student to add to intake.
     * @param   int     $intakeid SELMA intake ID the student should be added to.
     * @return  array   Array of success status & bool if successful/not, message.
     */
    public static function add_student_to_intake(int $studentid, int $intakeid) {
        // Validate parameters.
        $params = self::validate_parameters(self::add_student_to_intake_parameters(),
            [
                'studentid' => $studentid,
                'intakeid' => $intakeid
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_add_student_to_intake($params['studentid'], $params['intakeid']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure Array of description of values returned by 'add_student_to_intake' function.
     */
    public static function add_student_to_intake_returns() {
        return new external_single_structure(
            [
                'courses' => new external_multiple_structure(new external_single_structure(
                    [
                        'courseid' => new external_value(PARAM_INT,
                            get_string('add_student_to_intake_returns::courseid', 'enrol_selma')),
                        'userenrolid' => new external_value(PARAM_INT,
                            get_string('add_student_to_intake_returns::userenrolid', 'enrol_selma')),
                    ]
                ), get_string('add_student_to_intake_returns::courses', 'enrol_selma'), VALUE_OPTIONAL),
                // TODO - Maybe we should be returning 'warning' values, instead of in the message.
                // As per - https://docs.moodle.org/dev/Errors_handling_in_web_services#Warning_messages
                // For example, refer to mod/assign/externallib.php:614.
                'warnings' => new external_warnings()
            ],
            get_string('add_student_to_intake_returns', 'enrol_selma')
        );
    }
}
