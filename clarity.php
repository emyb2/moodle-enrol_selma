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
 * Let's clarify the setup and layout of the site - intakes, users & courses.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__, 3) . '/config.php');

defined('MOODLE_INTERNAL') || die();

require_login();

// Only allow admins to this page.
if (!is_siteadmin()) {
    redirect($CFG->wwwroot);
}

global $OUTPUT, $SITE, $PAGE, $DB;

// Retrieve/clean params.
$scope = optional_param('scope', 'overview', PARAM_TEXT);
$params['scope'] = $scope;

// We only accept 'student', 'teacher' or 'course'.
if ($scope === 'overview' || $scope === 'student' || $scope === 'course' || $scope === 'teacher') {
    // If not at the overview page, we need to know which intake's students/teachers/course to display.
    if ($scope !== 'overview') {
        $intake = required_param('intake', PARAM_TEXT);
        $params['intake'] = $intake;
    }
} else {
        throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'scope');
}

// Setup url & params.
$context = context_system::instance();
$url = new moodle_url($CFG->wwwroot . '/enrol/selma/clarity.php', $params);

// Set PAGE variables.
$PAGE->set_context($context);
$PAGE->set_url($url);

// Only print headers if not asked to download data.
// Print the page header.
$heading = get_string('clarity' . $scope, 'enrol_selma');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('enrol_selma/jssearch', 'init');

// Breadcrumbs.
$PAGE->navbar->add(get_string('enrol', 'enrol'));
$PAGE->navbar->add(get_string('pluginname', 'enrol_selma'));
$PAGE->navbar->add(get_string('clarityoverview', 'enrol_selma'), new moodle_url($CFG->wwwroot . '/enrol/selma/clarity.php'));
if ($scope !== 'overview') {
    $PAGE->navbar->add($heading);
}

$PAGE->set_pagelayout('standard');
$PAGE->add_body_class($scope);


// Get the renderer for this page.
$renderer = $PAGE->get_renderer('enrol_selma');

// Process params & generate HTML.
$html = get_string('nothingtosee', 'enrol_selma');
if ($scope === 'overview') {
    // Get overview of intake.
    $intakes = $DB->get_records('enrol_selma_intake');
    if (!empty($intakes)) {
        foreach ($intakes as $intake) {
            $students = $DB->count_records('enrol_selma_student_intake', array('intakeid' => $intake->id));
            $teachers = $DB->count_records('enrol_selma_teacher_intake', array('intakeid' => $intake->id));
            $courses = $DB->count_records('enrol_selma_course_intake', array('intakeid' => $intake->id));

            $intake->numstudents = $students;
            $intake->numteachers = $teachers;
            $intake->numcourses = $courses;
        }

        $html = $renderer->overview($intakes);
    }
} elseif ($scope === 'student') {
    // Get students of given intake.
    $students = $DB->get_records('enrol_selma_student_intake', array('intakeid' => $params['intake']));
    if (!empty($students)) {
        $userids = array_column($students, 'userid');

        // Get actual Moodle users.
        list($insql, $inparams) = $DB->get_in_or_equal($userids);
        $users = $DB->get_records_select('user', "id $insql", $inparams);
        $html = $renderer->student($users);
    }
} elseif ($scope === 'teacher') {
    // Get teacher of given intake.
    $teachers = $DB->get_records('enrol_selma_teacher_intake', array('intakeid' => $params['intake']));
    if (!empty($teachers)) {
        $userids = array_column($teachers, 'userid');

        // Get actual Moodle users.
        list($insql, $inparams) = $DB->get_in_or_equal($userids);
        $users = $DB->get_records_select('user', "id $insql", $inparams);

        $html = $renderer->teacher($users);
    }
} elseif ($scope === 'course') {
    // Get courses of given intake.
    $courseintakes = $DB->get_records('enrol_selma_course_intake', array('intakeid' => $params['intake']));
    if (!empty($courseintakes)) {
        $courseids = array_column($courseintakes, 'courseid');

        list($insql, $inparams) = $DB->get_in_or_equal($courseids);
        $courses = $DB->get_records_select('course', "id $insql", $inparams);

        $html = $renderer->course($courses);
    }
}

// Render the page.
echo $OUTPUT->header();
echo $OUTPUT->heading($heading);

// Output page's main HTML.
echo $html;

echo $OUTPUT->footer();