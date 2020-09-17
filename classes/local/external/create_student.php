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
 * SELMA plugin 'create_student' external file.
 *
 * @package    enrol_selma
 * @category   external
 * @copyright  2020 LearningWorks <selma@learningworks.co.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_student extends external_api {

    public static function create_student_parameters() {
        return new external_function_parameters(
            [
                'student' => new external_single_structure(
                    external_structure::get_student_structure()
                )
            ]
        );
    }

    public static function create_student($student) {
        $context = context_system::instance();
        require_capability('moodle/user:create', $context);
        self::validate_context($context);
        $params = self::validate_parameters(self::create_student_parameters(), ['student' => $student]);
        $status = 1;
        $moodleuserid = 0;
        $warnings = [];
        $selmadata = $params['student'];
        try {
            $user = enrol_selma_create_student_from_selma($selmadata, get_config('enrol_selma'));
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
        } catch (\Exception $exception) {
            $warnings[] = ['warningcode' => $status, 'message' => $exception->getMessage()];
        }
        return [
            'status' => $status,
            'moodleuserid' => $moodleuserid,
            'warnings' => $warnings
        ];
    }

    public static function create_student_returns() {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_TEXT, get_string('status', 'enrol_selma')),
                'moodleuserid' => new external_value(PARAM_INT, get_string('moodleuserid', 'enrol_selma')),
                'warnings' => new external_warnings()
            ]
        );
    }

}
