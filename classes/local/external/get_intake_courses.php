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
 * SELMA plugin 'get_intake_courses' external file.
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
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;

/**
 * Class get_intake used to get a SELMA intake's data (most likely for checking/verifying).
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_intake_courses extends external_api {
    /**
     * Returns required parameters to retrieve an intake's associated courses.
     *
     * @return external_function_parameters Description of parameters and expected type.
     * @throws coding_exception
     */
    public static function get_intake_courses_parameters() {
        return new external_function_parameters(
            [
                'intakeid' => new external_value(PARAM_INT,
                    get_string('get_intake_courses_parameters::intakeid', 'enrol_selma')
                )
            ],
            get_string('get_intake_courses_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's get that intake's courses.
     *
     * @param   int     $intakeid Intake ID used to retrieve associated course(s) from Moodle.
     * @return  array   Intake's course(s), if any.
     */
    public function __construct(int $intakeid) {
        return self::get_intake_courses($intakeid);
    }

    /**
     * The function itself - let's get that intake's courses.
     *
     * @param   int     $intakeid Intake ID used to retrieve associated course(s) from Moodle.
     * @return  array   Intake's course(s), if any.
     */
    public static function get_intake_courses(int $intakeid) {
        // Validate parameters.
        $params = self::validate_parameters(self::get_intake_courses_parameters(),
            [
                'intakeid' => $intakeid
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_get_intake_courses($params['intakeid']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure Array of description of values returned by 'get_intake_courses' function.
     */
    public static function get_intake_courses_returns() {
        return new external_single_structure(
            [
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT,
                        get_string('get_intake_courses_returns::courseid', 'enrol_selma'),
                        VALUE_OPTIONAL),
                    get_string('get_intake_courses_returns::courseids', 'enrol_selma')
                ),
                    // TODO - Maybe we should be returning 'warning' values, instead of in the message.
                    // As per - https://docs.moodle.org/dev/Errors_handling_in_web_services#Warning_messages
                    // For example, refer to mod/assign/externallib.php:614.
                    'warnings' => new external_warnings(),
            ], get_string('get_intake_courses_returns', 'enrol_selma')
        );
    }
}
