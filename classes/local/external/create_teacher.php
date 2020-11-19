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
 * SELMA plugin 'create_teacher' external file.
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
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use moodle_exception;
use dml_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/enrol/selma/locallib.php');

/**
 * SELMA plugin 'create_teacher' external file.
 *
 * @package    enrol_selma
 * @category   external
 * @copyright  2020 LearningWorks <selma@learningworks.co.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_teacher extends external_api {

    /**
     * Returns required parameters to create a teacher.
     *
     * @return external_function_parameters Description of parameters and expected type.
     */
    public static function create_teacher_parameters() {
        return new external_function_parameters(
            [
                'teacher' => new external_single_structure(
                    external_structure::get_teacher_structure()
                )
            ], get_string('create_teacher_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's create a teacher.
     *
     * @param array $teacher Teacher user's details (SELMA fields).
     * @return  array   Array of created user ID and warning messages, if any.
     */
    public static function create_teacher(array $teacher) {
        // Validate parameters.
        $params = self::validate_parameters(self::create_teacher_parameters(), ['teacher' => $teacher]);

        $context = context_system::instance();

        // Validate context and check capabilities.
        self::validate_context($context);

        // Check if ws account can create users.
        require_capability('moodle/user:create', $context);

        // Create teacher and return.
        return enrol_selma_create_teacher_from_selma($params['teacher'], get_config('enrol_selma'));
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure Array of expected course structure parameters and their description.
     */
    public static function create_teacher_returns() {
        return new external_single_structure(
            [
                'userid' => new external_value(PARAM_INT, get_string('create_teacher_returns::userid', 'enrol_selma')),
                'warnings' => new external_warnings()
            ], get_string('create_teacher_returns', 'enrol_selma')
        );
    }

}
