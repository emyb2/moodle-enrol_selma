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
 * SELMA plugin 'get_all_courses' external file.
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

/**
 * Class get_all_courses used to get all active courses in Moodle.
 *
 * @package enrol_selma\local\external
 */
class get_all_courses extends external_api {
    /**
     * Returns required parameters to get courses - none required, but can be paginated.
     *
     * @return null No parameters expected.
     */
    public static function get_all_courses_parameters() {
        return new external_function_parameters(
            [
                'amount' => new external_value(PARAM_INT, get_string('get_all_courses_parameters::amount', 'enrol_selma')),
                'page' => new external_value(PARAM_INT, get_string('get_all_courses_parameters::page', 'enrol_selma'))
            ], get_string('get_all_courses_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's get those courses.
     *
     * @param   int     $amount How many records to return - by default we get them all.
     * @param   int     $page Which page to start on - default, we start on 1.
     * @return  array   Array of success status & all available courses.
     */
    public static function get_all_courses(int $amount = 0, int $page = 1) {
        // Validate parameters.
        $params = self::validate_parameters(self::get_all_courses_parameters(),
            [
                'amount' => $amount,
                'page' => $page
            ]);

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Get all courses & return.
        return enrol_selma_get_all_courses($params['amount'], $params['page']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters Array of description of values returned by 'get_all_courses' function.
     */
    public static function get_all_courses_returns() {
        return new external_function_parameters(
            [
                'status' => new external_value(PARAM_TEXT, get_string('get_all_courses_returns::status', 'enrol_selma')),
                'courses' => new external_multiple_structure(self::get_course_structure(), get_string('get_all_courses_returns::courses', 'enrol_selma')),
                'nextpage' => new external_value(PARAM_INT, get_string('get_all_courses_returns::nextpage', 'enrol_selma')),
                'message' => new external_value(PARAM_TEXT, get_string('get_all_courses_returns::message', 'enrol_selma'))
            ], get_string('get_all_courses_returns', 'enrol_selma')
        );
    }

    /**
     * Helps generate the course structure expected as a parameter above.
     *
     * @return external_single_structure Array of expected course structure parameters and their description.
     */
    public static function get_course_structure() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, get_string('courses::id', 'enrol_selma')),
                'shortname' => new external_value(PARAM_TEXT, get_string('courses::shortname', 'enrol_selma')),
                'fullname' => new external_value(PARAM_TEXT, get_string('courses::fullname', 'enrol_selma')),
                'idnumber' => new external_value(PARAM_TEXT, get_string('courses::idnumber', 'enrol_selma')),
            ], get_string('courses::course_structure', 'enrol_selma')
        );
    }
}