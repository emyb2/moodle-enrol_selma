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
 * SELMA plugin 'create_course' external file.
 *
 * @package    enrol_selma
 * @category   external
 * @copyright  2020 LearningWorks <selma@learningworks.co.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local\external;

use context_system;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once(dirname(__FILE__, 4) . '/locallib.php');

/**
 * Class create_course used to create course from SELMA.
 *
 * @package enrol_selma\local\external
 */
class create_course extends external_api {
    /**
     * Returns required parameters to create a course.
     *
     * @return external_function_parameters Description of parameters and expected type.
     */
    public static function create_course_parameters() {
        // 'FUNCTIONNAME_parameters()' always return an 'external_function_parameters()'.
        // The 'external_function_parameters' constructor expects an array of 'external_description'.
        return new external_function_parameters(
            // An 'external_description' can be 'external_value', 'external_single_structure' or 'external_multiple' structure
            [
                'course' => new external_single_structure(
                    [
                        'fullname' => new external_value(PARAM_TEXT, get_string('create_course_parameters::fullname', 'enrol_selma')),
                        'shortname' => new external_value(PARAM_TEXT, get_string('create_course_parameters::shortname', 'enrol_selma')),
                        'idnumber' => new external_value(PARAM_TEXT, get_string('create_course_parameters::idnumber', 'enrol_selma')),
                    ], get_string('create_course_parameters::course', 'enrol_selma')
                )
            ],
            get_string('create_course_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's create a course.
     *
     * @param   array   $course Course object and required details to create a course.
     * @return  array   Array of success status & created course_id, if any.
     */
    public static function create_course(array $course) {
        // Validate parameters.
        $params = self::validate_parameters(self::create_course_parameters(),
            [
                'course' => $course
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_create_course($params['course']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters Array of description of values returned by 'create_course' function.
     */
    public static function create_course_returns() {
        return new external_function_parameters(
            [
                'status' => new external_value(PARAM_TEXT, get_string('create_course_returns::status', 'enrol_selma')),
                'courseid' => new external_value(PARAM_INT, get_string('create_course_returns::courseid', 'enrol_selma')),
                'message' => new external_value(PARAM_TEXT, get_string('create_course_returns::message', 'enrol_selma'))
                // TODO - Maybe we should be returning 'warning' values, instead of in the message.
                // As per - https://docs.moodle.org/dev/Errors_handling_in_web_services#Warning_messages
                // For example, refer to mod/assign/externallib.php:614.
            ],
            get_string('create_course_returns', 'enrol_selma')
        );
    }
}