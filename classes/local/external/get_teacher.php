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
 * SELMA plugin 'get_teacher' external file.
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
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use restricted_context_exception;

/**
 * Class get_teacher used to get a SELMA/Moodle teacher based on SELMA teacher/tutor ID.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_teacher extends external_api {
    /**
     * Returns required parameters to retrieve a user.
     *
     * @return external_function_parameters Description of parameters and expected type.
     * @throws coding_exception
     */
    public static function get_teacher_parameters() {
        return new external_function_parameters(
            [
                'teacherid' => new external_value(PARAM_TEXT,
                    get_string('get_teacher_parameters::teacherid', 'enrol_selma')
                ),
                'email' => new external_value(PARAM_TEXT,
                    get_string('get_teacher_parameters::email', 'enrol_selma'), VALUE_DEFAULT, ''
                )
            ],
            get_string('get_teacher_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's get that user.
     *
     * @param   string                 $teacherid Teacher/Tutor (SELMA) ID used to look up a user in Moodle.
     * @param   string              $email Teacher (SELMA) email address used to look up a user in Moodle.
     * @return  array               Array of user details, if any.
     * @throws  coding_exception
     * @throws  dml_exception
     * @throws  invalid_parameter_exception|restricted_context_exception
     */
    public static function get_teacher(string $teacherid, string $email = '') {
        // Validate parameters.
        $params = self::validate_parameters(self::get_teacher_parameters(),
            [
                'teacherid' => $teacherid,
                'email' => $email
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_get_teacher($params['teacherid'], $params['email']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure Array of description of values returned by 'get_teacher' function.
     * @throws coding_exception
     */
    public static function get_teacher_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT,
                    get_string('get_teacher_returns::id', 'enrol_selma'),
                    VALUE_OPTIONAL
                ),
                'firstname' => new external_value(PARAM_TEXT,
                    get_string('get_teacher_returns::firstname', 'enrol_selma'),
                    VALUE_OPTIONAL
                ),
                'lastname' => new external_value(PARAM_TEXT,
                    get_string('get_teacher_returns::lastname', 'enrol_selma'),
                    VALUE_OPTIONAL
                ),
                'email' => new external_value(PARAM_TEXT,
                    get_string('get_teacher_returns::email', 'enrol_selma'),
                    VALUE_OPTIONAL
                ),
                'idnumber' => new external_value(PARAM_TEXT,
                    get_string('get_teacher_returns::idnumber', 'enrol_selma'),
                    VALUE_OPTIONAL
                ),
                'teacherid' => new external_value(PARAM_TEXT,
                    get_string('get_teacher_returns::teacherid', 'enrol_selma'),
                    VALUE_OPTIONAL
                ),
                // TODO - Maybe we should be returning 'warning' values, instead of in the message.
                // As per - https://docs.moodle.org/dev/Errors_handling_in_web_services#Warning_messages
                // For example, refer to mod/assign/externallib.php:614.
                'warnings' => new external_warnings()
            ],
            get_string('get_teacher_returns', 'enrol_selma')
        );

    }
}
