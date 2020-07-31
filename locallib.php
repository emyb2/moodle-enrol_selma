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
 * The plugin-specific library of functions. Used for testing too.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');

/**
 * Creates the course based on details provided.
 *
 * @param array     $course Array of course details to create course.
 * @return array    Array containing the status of the request, created course's ID, and appropriate message.
 */
function enrol_selma_create_course(array $course) {
    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // Courseid of null means something didn't work. Changed if successfully created a course.
    $courseid = null;
    // Use to give more detailed response message to user.
    $message = get_string('status_other_message', 'enrol_selma');

    // Prep tags - find & replace text and convert to array.
    $tags = get_config('enrol_selma', 'selmacoursetags');
    // Course name.
    $tags = str_replace(['{{fullname}}', '{{shortname}}'], [$course['fullname'], $course['shortname']], $tags);
    $tags = explode(',', $tags);

    // Construct course object.
    $coursedata = new stdClass();
    $coursedata->category = get_config('enrol_selma', 'newcoursecat');  // The default category to put the course in. TODO - what if the setting has not been configured yet, but we get a call to create_course or if it's been deleted?
    $coursedata->fullname = $course['fullname'];                        // Generated? Remember - visible to users.
    $coursedata->shortname = $course['shortname'];                      // Generated? Remember - visible to users.
    $coursedata->idnumber = $course['idnumber'];                        // Generated?
    $coursedata->visible = get_config('moodlecourse', 'visible');       // Optional - based on category if not set.
    $coursedata->tags = $tags;                                          // Add the user specified in 'selmacoursetags' setting.

    // Consider course_updated() in lib.php? Check out lib/enrollib.php:409.
    $coursecreated = \create_course($coursedata);
    // Check out course/externallib.php:831.

    // TODO - Add enrol_selma to course. Is this enough? What to do if false is returned?
    // Instantiate & add SELMA enrolment instance to course.
    (new enrol_selma_plugin)->add_instance($coursecreated);

    // TODO - proper check/message?
    // Check if course created successfully.
    if (isset($coursecreated->id) && $coursecreated->id > 1) {
        $status = get_string('status_ok', 'enrol_selma');
        $message = get_string('status_ok_message', 'enrol_selma');
        $courseid = $coursecreated->id;

        // Returned details - success!
        return ['status' => $status, 'courseid' => $courseid, 'message' => $message];
    }

    $status = get_string('status_internalfail', 'enrol_selma');
    $message = get_string('status_internalfail_message', 'enrol_selma');

    // Returned details - failed...
    return ['status' => $status, 'courseid' => $courseid, 'message' => $message];
}

/**
 * Get all the courses that's not in any excluded category - excludecoursecat setting.
 *
 * @param   int     $amount Number of records to retrieve - get all by default.
 * @param   int     $page Which 'page' to retrieve from the DB - works in conjunction with $amount.
 * @return  array   Array containing the status of the request, courses found, and appropriate message.
 */
function enrol_selma_get_all_courses(int $amount = 0, int $page = 1) {
    global $DB;

    // TODO - $amount & $page needs to be positive values.

    // To keep track of which DB 'page' to look on.
    $dbpage = $page;

    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // If courses = null, then it means we didn't find anything/something went wrong. Changed if successfully found a course(s).
    $courses = null;
    // Use to give more detailed response message to user.
    $message = get_string('status_other_message', 'enrol_selma');

    // Used to calculate the right place to start from. Page index starts at 0.
    if ($page > 0) {
        $dbpage = $page - 1;
    }

    // Vars to keep track of which page/amount of courses to get.
    $limitfrom = $amount * $dbpage;
    $limitnum = $amount;

    // If amount is zero, we get all the courses.
    if ($amount === 0) {
        $limitfrom = 0;
    }

    // Let's check if any categories need to be excluded.
    $excats = get_config('enrol_selma', 'excludecoursecat');

    if (isset($excats) && !empty($excats)) {
        // Found category to exclude.
        $excats = explode(',', $excats);
    }

    // Exclude 'site' course category.
    $excats[] = '0';

    // Create SQL to exclude 'excluded' categories.
    list($sqlfragment, $params) = $DB->get_in_or_equal($excats, SQL_PARAMS_NAMED, null, false);

    // Get those courses.
    $courses = $DB->get_records_select(
        'course',
        "category $sqlfragment",
        $params,
        null,
        'id,fullname,shortname,idnumber',
        $limitfrom,
        $limitnum
    );

    // Check if we found anything.
    if (empty($courses) || !isset($courses)) {
        // No courses found, update status/message.
        $status = get_string('status_notfound', 'enrol_selma');
        $message = get_string('status_notfound_message', 'enrol_selma');

        // Return status.
        return ['status' => $status, 'courses' => $courses, 'message' => $message];
    }

    // The next page the requester should request.
    if ($amount !== 0 && count($courses) == $amount) {
        $nextpage = $page + 1;
    }

    // Courses retrieved successfully, set statusses, messages, vars appropriately.
    $status = get_string('status_ok', 'enrol_selma');
    // Var $courses already set.
    $message = get_string('status_ok_message', 'enrol_selma');
    // Check if nextpage needs to be sent.
    if (isset($nextpage)) {
        // Returned details (incl. nextpage).
        return ['status' => $status, 'courses' => $courses, 'nextpage' => $nextpage, 'message' => $message];
    }

    // Returned details (excl. nextpage).
    return ['status' => $status, 'courses' => $courses, 'message' => $message];
}