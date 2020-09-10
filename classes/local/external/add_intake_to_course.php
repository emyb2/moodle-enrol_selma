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
 * SELMA plugin 'add_intake_to_course' external file.
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
use external_value;

/**
 * Class add_intake_to_course used to add SELMA intake to Moodle course.
 *
 * @package enrol_selma\local\external
 */
class add_intake_to_course extends external_api {
    /**
     * Returns required parameters to add intake to course.
     *
     * @return external_function_parameters Description of parameters and expected type.
     */
    public static function add_intake_to_course_parameters() {
        return new external_function_parameters(
            [
                'intakeid' => new external_value(PARAM_INT, get_string('add_intake_to_course_parameters::intakeid', 'enrol_selma')),
                'courseid' => new external_value(PARAM_INT, get_string('add_intake_to_course_parameters::courseid', 'enrol_selma')),
            ],
            get_string('add_intake_to_course_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's add this intake to that course.
     *
     * @param   int     $intakeid ID of intake to add to course.
     * @param   int     $courseid ID of course the intake should be added to.
     * @return  array   Array of success status & bool of true if success, along with message.
     */
    public static function add_intake_to_course(int $intakeid, int $courseid) {
        // Validate parameters.
        $params = self::validate_parameters(self::add_intake_to_course_parameters(),
            [
                'intakeid' => $intakeid,
                'courseid' => $courseid
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_add_intake_to_course($params['intakeid'], $params['courseid']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters Array of description of values returned by 'add_intake_to_course' function.
     */
    public static function add_intake_to_course_returns() {
        return new external_function_parameters(
            [
                'status' => new external_value(PARAM_TEXT, get_string('add_intake_to_course_returns::status', 'enrol_selma')),
                'added' => new external_value(PARAM_BOOL, get_string('add_intake_to_course_returns::added', 'enrol_selma')),
                'message' => new external_value(PARAM_TEXT, get_string('add_intake_to_course_returns::message', 'enrol_selma'))
            ], get_string('add_intake_to_course_returns', 'enrol_selma')
        );
    }
}