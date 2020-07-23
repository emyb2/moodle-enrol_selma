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
use core\plugininfo\customfield;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

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
                'name' => new external_value(PARAM_TEXT, get_string('create_course_parameters::coursename', 'enrol_selma')),
                'intakeid' => new external_value(PARAM_INT, get_string('create_course_parameters::intakeid', 'enrol_selma')),
                'customfields' => self::get_customfields_structure()
            ],
            get_string('create_course_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's create a course.
     *
     * @param   string  $name Name of course to create.
     * @param   int     $intakeid ID of intake from which this course was created.
     * @param   array   $customfields string Name of course to create.
     * @return  array   Array of success status & created course_id, if any.
     */
    public static function create_course($name, $intakeid, $customfields = []) {
        // TODO - Any sanitisation here? Validation happens anyway below.

        // Validate parameters.
        $params = self::validate_parameters(self::create_course_parameters(),
            [
                'name' => $name,
                'intakeid' => $intakeid,
                'customfields' => $customfields
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
        $status = get_string('status_other', 'enrol_selma');
        // Courseid of -1 means something didn't work. Changed if successfully created a course.
        $courseid = -1;
        // Use to give more detailed response message to user.
        $message = get_string('status_other_message', 'enrol_selma');

        // Prep tags - find & replace text and convert to array.
        $tags = get_config('enrol_selma', 'selmacoursetags');
        // Course name.
        $tags = str_replace('{{name}}', $params['name'], $tags);
        $tags = explode(',', $tags);

        // Construct course object.
        $coursedata = new stdClass();
        $coursedata->category = get_config('enrol_selma', 'newcoursecat');  // The default category to put the course in. TODO - what if the setting has not been configured yet, but we get a call to create_course or if it's been deleted?
        $coursedata->fullname = $params['name'];                            // Generated? Remember - visible to users.
        $coursedata->shortname = strtolower($params['name']);               // Generated? Remember - visible to users.
        $coursedata->idnumber = $params['intakeid'] . time();               // Generated?
        $coursedata->visible = get_config('moodlecourse', 'visible');       // Optional - based on category if not set.
        $coursedata->summary = '<strong>WIP</strong>';                      // Course summary - usually html. Parameter?
        $coursedata->summary_format = FORMAT_HTML;                          // Not sure if this even works. Check course/lib.php:2363-2383.
        $coursedata->tags = $tags;                                          // Add the user specified in 'selmacoursetags' setting.

        // Custom course fields.
        // For performance - check if we have customfields.
        if (isset($params['customfields']) && !empty($params['customfields'])) {
            // Format and add to customfields to add.
            foreach ($params['customfields'] as $key => $value) {
                $field = 'customfield_' . $key;
                $coursedata->$field = $value;
            }
        }

        //course_updated() in lib.php? Check out lib/enrollib.php:409.
        $course = \create_course($coursedata);
        // Check out course/externallib.php:831.

        // TODO - proper check/message?
        // Check if course created successfully.
        if ($course->id > 1) {
            $status = get_string('status_ok', 'enrol_selma');
            $message = get_string('status_ok_message', 'enrol_selma');
            $courseid = $course->id;
        }

        // Returned details.
        return ['status' => $status, 'courseid' => $courseid, 'message' => $message];
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
                'message' => new external_value(PARAM_TEXT, get_string('create_course_returns::message', 'enrol_selma')),
            ],
            get_string('create_course_returns', 'enrol_selma')
        );
    }

    /**
     * Creates expected structure used to accept customfields as passed parameters.
     *
     * @return external_single_structure Used to build structure of expected parameters.
     */
    private static function get_customfields_structure() {
        // TODO - Handle customfields that's passed, but the site does not have...
        global $DB;

        $structurearray = [];

        // Get all the types of fields.
        $fieldtypes = array_keys(customfield::get_enabled_plugins());

        // For each field type, get customfield_field shortname
        foreach ($fieldtypes as $fieldtype) {
            // Get all the course customfields that's on the site.
            $fields = $DB->get_records('customfield_field', ['type' => $fieldtype], null, 'shortname');

            // Save time by checking if we actually found something. If we did, add it to the structure.
            if (isset($fields) && !empty($fields)) {
                // For each field, add as expected (toptional) parameter.
                foreach ($fields as $field) {
                    $structurearray[$field->shortname] = new external_value(PARAM_RAW_TRIMMED,
                        get_string('customfield', 'enrol_selma', $field->shortname), VALUE_OPTIONAL);
                }
            }
        }

        // Return all possible accepted fields.
        return new external_single_structure(
            $structurearray, get_string('customfields', 'enrol_selma'), VALUE_OPTIONAL
        );
    }
}