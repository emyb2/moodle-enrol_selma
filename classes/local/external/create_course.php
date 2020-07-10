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
 * SELMA plugin 'create_course' external file
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
use external_value;
use lang_string;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class create_course extends external_api {

    /**
     * Returns required parameters to create a course.
     * @return external_function_parameters Description of parameters and expected type.
     */
    public static function create_course_parameters() {
        // 'FUNCTIONNAME_parameters()' always return an 'external_function_parameters()'.
        // The 'external_function_parameters' constructor expects an array of 'external_description'.
        return new external_function_parameters(
        // An 'external_description' can be 'external_value', 'external_single_structure' or 'external_multiple' structure
                array('name' => new external_value(PARAM_TEXT, new lang_string('course_name', 'enrol_selma')),
                        'intake_id' => new external_value(PARAM_INT, new lang_string('intake_id', 'enrol_selma')))
        );
    }

    /**
     * The function itself - let's create a course.
     *
     * @param $parameters array Parameters received from SELMA to create a course e.g. ['name' => 'FLM4', 'intake_id' => 1234].
     * @return array Array of success status & created course_id, if any.
     */
    public static function create_course($parameters) {
        // Validate parameters.
        $params = self::validate_parameters(self::create_course_parameters(), $parameters);

        $status = false;
        $courseid = 0;

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // TODO - How to determine if we need to create of update a course.
        $data = new stdClass();
        $data->category = 1;
        $data->shortname = 'shortname';             // Generated? Remember - visible to users.
        $data->idnumber = 'idnumber';               // Generated?
        $data->timecreated = time();                // Optional.
        $data->timemodified = $data->timecreated;   // Optional.
        $data->visible = '0';                       // Optional - based on category if not provided.
        $data->visibleold = $data->visible;
        $data->summary = '';                        // Not sure if this even works. Check course/lib.php:2363-2383.
        $data->summary_format = FORMAT_HTML;        // Not sure if this even works. Check course/lib.php:2363-2383.
        $data->format = 'topics';                   // Not sure if this works/necessary (use defaults). Check course/lib.php:2390.
        $data->numsections = '0';                   // Not sure if this is necessary (use defaults).
        $data->tags = 'selma';                      // Possibly add 'SELMA' tag? Setting?
        $data->customfield_field = 'customdata';    // Loop through customfield mapping to store SELMA data in Moodle.

        //course_updated() in lib.php? Check out lib/enrollib.php:409.

        $course = create_course($data);

        if ($course->id > 1) {
            $status = true;
        }

        return ['success' => $status, 'course_id' => $course->id];
    }

    /**
     * Returns description of method result value.
     * @return external_function_parameters Array of description of values returned by 'create_course' function.
     */
    public static function create_course_returns() {
        return new external_function_parameters(
            [
                'success' => new external_value(PARAM_BOOL, new lang_string('create_course_returns_success', 'enrol_selma')),
                'course_id' => new external_value(PARAM_INT, new lang_string('create_course_returns_course_id', 'enrol_selma'))
            ],
            new lang_string('create_course_returns', 'enrol_selma')
        );
    }
}