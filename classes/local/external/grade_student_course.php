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
 * Webservice function for selma to grade a students course for enrol_selma.
 *
 * @package     enrol_selma
 * @author      Donald Barrett <donald.barrett@learningworks.co.nz>
 * @copyright   2021 onwards, LearningWorks ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local\external;

// No direct access.
defined('MOODLE_INTERNAL') || die();

use external_api;
use external_single_structure;
use external_value;
use external_warnings;
use external_function_parameters;
use coding_exception;
use dml_exception;
use invalid_parameter_exception;
use context_course;

/**
 * Class grade_student_course.
 *
 * The webservice function definitions for marking a students course from an external source.
 *
 * @package enrol_selma\local\external
 */
class grade_student_course extends external_api {

    /**
     * Grade a students course.
     *
     * @param string $studentid
     * @param int $courseid
     * @param int $grade
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws coding_exception
     */
    public static function grade_student_course(string $studentid, int $courseid, int $grade) : array {
        global $CFG, $DB;

        // Load the grade libs and classes.
        require_once("{$CFG->libdir}/gradelib.php");
        require_once("{$CFG->libdir}/grade/grade_item.php");
        require_once("{$CFG->libdir}/grade/grade_grade.php");
        require_once("{$CFG->libdir}/grade/grade_category.php");
        require_once("{$CFG->libdir}/grade/constants.php");
        require_once("{$CFG->libdir}/enrollib.php");

        $params = self::validate_parameters(
            self::grade_student_course_parameters(),
            ['studentid' => $studentid, 'courseid' => $courseid, 'grade' => $grade]
        );
        $warnings = [];
        $status = 200;
        $graded = true;

        $user = $DB->get_record('user', ['idnumber' => $params['studentid']]);
        if (!$user) {
            $status = 404;
            $graded = false;
            $a = "User with idnumber {$params['studentid']}";
            $warnings[] = [
                'item' => 'user',
                'itemid' => -1,
                'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
                'message' => get_string('status_notfound_detailed_message', 'enrol_selma', $a)
            ];
        }

        if (!$DB->record_exists('course', ['id' => $params['courseid']])) {
            $status = 404;
            $graded = false;
            $a = "Course with id {$params['courseid']}";
            $warnings[] = [
                'item' => 'course',
                'itemid' => $params['courseid'],
                'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
                'message' => get_string('status_notfound_detailed_message', 'enrol_selma', $a)
            ];
        } else {
            $coursecontext = context_course::instance($params['courseid']);
            if (!is_enrolled($coursecontext, $user)) {
                $status = 404;
                $graded = false;
                $a = "User with idnumber {$params['studentid']} is not enrolled into course with id {$params['courseid']}";
                $warnings[] = [
                    'item' => 'userenrolment',
                    'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
                    'message' => get_string('status_notfound_detailed_message', 'enrol_selma', $a)
                ];
            }
        }

        if (empty($warnings)) {
            // Get the grade item.
            $gradeitem = \grade_item::fetch(['courseid' => $params['courseid'], 'itemtype' => 'course']);
            if ($gradeitem === false) {
                $status = 404;
                $graded = false;
                $a = "Grade item of type course for the course with id {$params['courseid']}";
                $warnings[] = [
                    'item' => 'grade_item',
                    'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
                    'message' => get_string('status_notfound_detailed_message', 'enrol_selma', $a)
                ];
            } else if ($gradeitem->grademax == 0) {
                $status = 500;
                $graded = false;
                $a = ['courseid' => $params['courseid'], 'message' => 'Grade max value is 0'];
                $warnings[] = [
                    'item' => 'grade_item',
                    'warningcode' => get_string('status_internalfail', 'enrol_selma'),
                    'message' => get_string('warning_message_gradebooknotconfigured', 'enrol_selma', $a)
                ];
            } else if ($gradeitem->gradetype != GRADE_TYPE_VALUE) {
                $status = 500;
                $graded = false;
                $a = ['courseid' => $params['courseid'], 'message' => 'Only value type grades are supported'];
                $warnings[] = [
                    'item' => 'grade_item',
                    'warningcode' => get_string('status_internalfail', 'enrol_selma'),
                    'message' => get_string('warning_message_gradebooknotconfigured', 'enrol_selma', $a)
                ];
            } else {
                // Manually override the grade item for the user.
                $finalgrade = $gradeitem->get_final($user->id);
                if (!is_null($finalgrade) && $finalgrade->finalgrade == $params['grade']) {
                    $status = get_string('status_nonew', 'enrol_selma');
                    $graded = false;
                    $warnings[] = [
                        'item' => 'finalgrade',
                        'warningcode' => $status,
                        'message' => get_string('status_nonew_message', 'enrol_selma')
                    ];
                } else {
                    // Adjust the grade to be not higher than grademax.
                    $newgrade = $params['grade'] > $gradeitem->grademax ? $gradeitem->grademax : $params['grade'];
                    if ($params['grade'] > $gradeitem->grademax) {
                        $a = ['grade' => $params['grade'], 'grademax' => $gradeitem->grademax];
                        $warnings[] = [
                            'item' => 'grade',
                            'warningcode' => $status,
                            'message' => get_string('gradetruncated', 'enrol_selma', $a)
                        ];
                    }
                    $gradeitem->update_final_grade($user->id, $newgrade);
                }

                // Conditionally set overriden flag.
                $gradegrade = new \grade_grade(['userid' => $user->id, 'itemid' => $gradeitem->id]);
                if ($gradegrade === false) {
                    $status = 404;
                    $graded = false;
                    $a = "Grade object for user with idnumber {$user->idnumber} and grade item id {$gradeitem->id}";
                    $warnings[] = [
                        'item' => 'grade_grade',
                        'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
                        'message' => get_string('status_notfound_detailed_message', 'enrol_selma', $a)
                    ];
                } else if (!$gradegrade->is_overridden()) {
                    $gradegrade->set_overridden(true);
                }
            }
        }

        return ['status' => $status, 'graded' => $graded, 'warnings' => $warnings];
    }

    /**
     * The parameters expected for calling the grade_student_course function.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function grade_student_course_parameters() : external_function_parameters {
        return new external_function_parameters([
            'studentid' => new external_value(PARAM_ALPHANUMEXT, get_string('grade_student_course_parameters::studentid', 'enrol_selma')),
            'courseid' => new external_value(PARAM_INT, get_string('grade_student_course_parameters::courseid', 'enrol_selma')),
            'grade' => new external_value(PARAM_INT, get_string('grade_student_course_parameters::grade', 'enrol_selma'))
        ]);
    }

    /**
     * The expected return value and structure when calling the grade_student_course function.
     *
     * @return external_single_structure
     * @throws coding_exception
     */
    public static function grade_student_course_returns() : external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, get_string('grade_student_course_returns::status', 'enrol_selma')),
            'graded' => new external_value(PARAM_BOOL, get_string('grade_student_course_returns::graded', 'enrol_selma')),
            'warnings' => new external_warnings()
        ]);
    }
}