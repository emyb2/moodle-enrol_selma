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
 * SELMA plugin 'create_intake' external file.
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

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__, 4) . '/locallib.php');

/**
 * Class create_intake used to create a record of a SELMA intake and metadata.
 *
 * @package enrol_selma\local\external
 */
class create_intake extends external_api {
    /**
     * Returns required parameters to create an intake.
     *
     * @return external_function_parameters Description of parameters and expected type.
     */
    public static function create_intake_parameters() {
        return new external_function_parameters(
            [
                'intake' => new external_single_structure(
                    [
                        'intakeid' => new external_value(PARAM_INT, get_string('create_intake_parameters::intakeid', 'enrol_selma')),
                        'programmeid' => new external_value(PARAM_INT, get_string('create_intake_parameters::programmeid', 'enrol_selma')),
                        'intakecode' => new external_value(PARAM_TEXT, get_string('create_intake_parameters::intakecode', 'enrol_selma')),
                        'intakename' => new external_value(PARAM_TEXT, get_string('create_intake_parameters::intakename', 'enrol_selma')),
                        'intakestartdate' => new external_value(PARAM_TEXT, get_string('create_intake_parameters::intakestartdate', 'enrol_selma')),
                        'intakeenddate' => new external_value(PARAM_TEXT, get_string('create_intake_parameters::intakeenddate', 'enrol_selma')),
                    ], get_string('create_intake_parameters::intake', 'enrol_selma')
                )
            ],
            get_string('create_intake_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's create the intake.
     *
     * @param   array   $intake Intake details to record in Moodle.
     * @return  array   Array of success status & created intake, if any.
     */
    public static function create_intake(array $intake) {
        // Validate parameters.
        $params = self::validate_parameters(self::create_intake_parameters(),
            [
                'intake' => $intake
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_create_intake($params['intake']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters Array of description of values returned by 'create_intake' function.
     */
    public static function create_intake_returns() {
        return new external_function_parameters(
            [
                'status' => new external_value(PARAM_TEXT, get_string('create_intake_returns::status', 'enrol_selma')),
                'intakeid' => new external_value(PARAM_INT, get_string('create_intake_returns::intakeid', 'enrol_selma')),
                'message' => new external_value(PARAM_TEXT, get_string('create_intake_returns::message', 'enrol_selma')),
            ],
            get_string('create_intake_returns', 'enrol_selma')
        );
    }
}