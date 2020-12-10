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
 * SELMA plugin 'withdraw_student' external file.
 *
 * @package    enrol_selma
 * @category   external
 * @copyright  2020 LearningWorks <selma@learningworks.co.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local\external;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__, 4) . '/locallib.php');

use coding_exception;
use context_system;
use dml_exception;
use enrol_selma_plugin;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use restricted_context_exception;

/**
 * Class withdraw_student used to withdraw a SELMA/Moodle student based on user-enrolment ID.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class withdraw_student extends external_api {
    /**
     * Returns required parameters to withdraw a student.
     *
     * @return external_function_parameters Description of parameters and expected type.
     * @throws coding_exception
     */
    public static function withdraw_student_parameters() {
        return new external_function_parameters(
            [
                'ueid' => new external_value(PARAM_INT,
                    get_string('withdraw_student_parameters::ueid', 'enrol_selma')
                )
            ],
            get_string('withdraw_student_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's withdraw that student.
     *
     * @param   int                 $ueid Student (Moodle) user-enrolment ID used to withdraw from course.
     * @return  array               Array of withdrawn confirmation, if any.
     */
    public static function withdraw_student(int $ueid) {
        // Validate parameters.
        $params = self::validate_parameters(self::withdraw_student_parameters(),
            [
                'ueid' => $ueid
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        $withdrawn = (new enrol_selma_plugin())->unenrol_user_enrolment($params['ueid']);
        // Returned details.
        return ['withdrawn' => (bool) $withdrawn];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure Array of description of values returned by 'withdraw_student' function.
     * @throws coding_exception
     */
    public static function withdraw_student_returns() {
        return new external_single_structure(
            [
                'withdrawn' => new external_value(PARAM_BOOL,
                    get_string('withdraw_student_returns::withdrawn', 'enrol_selma')
                )
                // No warning for this? Only exceptions if something goes wrong, since 'unenrol' is a void-function.
            ],
            get_string('withdraw_student_returns', 'enrol_selma')
        );

    }
}
