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

namespace enrol_selma\local\external;

use coding_exception;
use context_system;
use Exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use moodle_exception;
use dml_exception;
use required_capability_exception;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/enrol/selma/locallib.php');

/**
 * SELMA plugin 'update_teacher' external file.
 *
 * @package    enrol_selma
 * @category   external
 * @copyright  2020 LearningWorks <selma@learningworks.co.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_teacher extends external_api {
    /**
     * Returns required parameters to update a user/teacher account.
     *
     * @return external_function_parameters Description of parameters and expected type.
     * @throws coding_exception
     */
    public static function update_teacher_parameters() {
        return new external_function_parameters(
            [
                'teacher' => new external_single_structure(
                    external_structure::get_teacher_structure()
                )
            ]
        );
    }

    /**
     * The function itself - let's update that teacher account.
     *
     * @param   array   $teacher Teacher user's details (SELMA fields).
     * @return  array   Array of updated Moodle user ID and warning messages, if any.
     * @throws  coding_exception
     * @throws  invalid_parameter_exception
     * @throws  restricted_context_exception
     * @throws  required_capability_exception
     * @throws  dml_exception
     */
    public static function update_teacher(array $teacher) {
        $context = context_system::instance();
        require_capability('moodle/user:update', $context);
        self::validate_context($context);
        $params = self::validate_parameters(self::update_teacher_parameters(), ['teacher' => $teacher]);
        $status = 1;
        $userid = 0;
        $warnings = [];
        $selmadata = $params['teacher'];
        try {
            $user = enrol_selma_update_teacher_from_selma($selmadata, get_config('enrol_selma'));
            if ($user->id >= 1) {
                $status = 200;
                $moodleuserid = $user->id;
            }
        } catch (dml_exception $exception) {
            $status = 500;
            $warnings[] = ['warningcode' => $status, 'message' => shorten_text($exception->getMessage(), 100)];
        } catch (moodle_exception $exception) {
            $status = 400;
            $warnings[] = ['warningcode' => $status, 'message' => $exception->getMessage()];
        } catch (Exception $exception) {
            $warnings[] = ['warningcode' => $status, 'message' => $exception->getMessage()];
        }
        return [
            'status' => $status,
            'userid' => $moodleuserid,
            'warnings' => $warnings
        ];
    }

    /**
     * Returns description of method result value(s).
     *
     * @return external_single_structure Array of expected teacher/user structure parameters and their description.
     * @throws coding_exception
     */
    public static function update_teacher_returns() {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_TEXT, get_string('status', 'enrol_selma')),
                'userid' => new external_value(PARAM_INT, get_string('moodleuserid', 'enrol_selma')),
                'warnings' => new external_warnings()
            ]
        );
    }
}
