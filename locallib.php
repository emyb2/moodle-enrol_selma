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

use core_course\customfield\course_handler;
use core_customfield\api;
use enrol_selma\local\course;
use enrol_selma\local\factory\property_map_factory;
use enrol_selma\local\factory\entity_factory;
use enrol_selma\local\student;
use enrol_selma\local\teacher;
use enrol_selma\local\user;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');
require_once(dirname(__FILE__, 3) . '/admin/tool/uploaduser/locallib.php');
require_once(dirname(__FILE__, 3) . '/user/lib.php');
require_once(dirname(__FILE__, 3) . '/group/lib.php');

/**
 * Function to add this intake to that course.
 *
 * @param   int         $intakeid ID of intake to add to course.
 * @param   int         $courseid ID of course the intake should be added to.
 * @param   array       $customfields Intake custom fields to add the the group customtext.
 * @return  array       Array of success status & bool of true if success, along with message.
 * @throws  coding_exception
 * @throws  dml_exception|moodle_exception
 */
function enrol_selma_add_intake_to_course(int $intakeid, int $courseid, array $customfields) {
    global $DB, $USER;

    // Status tracker.
    $notok = false;

    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // Var 'added' of false means something didn't work. Changed if successfully added user to intake.
    $added = false;
    // Give more detailed response message to user.
    $message = get_string('status_other_message', 'enrol_selma');

    // TODO - Add intake to course.
    // Check if course exists.
    $courseexists = $DB->record_exists('course', array('id' => $courseid));

    if ($courseexists) {
        // Check if intake exists.
        $intakeexists = $DB->record_exists('enrol_selma_intake', array('id' => $intakeid));

        if ($intakeexists) {
            // Add enrol_selma instance to course, if none.
            // The course really should exist, as we check the DB for the ID above.
            $course = get_course($courseid);

            $enrolinstance = (new enrol_selma_plugin())->add_instance($course);

            // TODO - Do I even need to return that we created a instance/group or not, or do we just do it and return success/fail?
            // Could not add enrol_selma instance.
            if (is_null($enrolinstance)) {
                // Set status to 'forbidden'.
                $status = get_string('status_almostok', 'enrol_selma');
                // Give more detailed response message to user.
                $message = get_string('status_almostok_message', 'enrol_selma') .
                    get_string('forbidden_instance_add', 'enrol_selma', $courseid);
                // Status demoted to 'almost ok'.
                $notok = true;

                // We can continue - no 'return', as the instance already exists.
            }

            // If we successfully added enrolinstance.
            // Create group in course, if needed.
            $groupfound = groups_get_group_by_idnumber($courseid, $intakeid);
            $groupid = 0;

            if ($groupfound === false) {
                // Group not found, add one.

                // The intake really should exist, as we check the DB for the ID above.
                $intake = $DB->get_record('enrol_selma_intake', array('id' => $intakeid));

                $group = new stdClass();
                $group->name = $intake->name;
                $group->courseid = $courseid;
                $group->idnumber = $intakeid;

                // Add the custom fields if they are present.
                foreach ($customfields as $key => $value) {
                    $group->{$key} = $value;
                }

                // Create group.
                $newgroup = groups_create_group($group);

                // Set status if we could not create group for some reason.
                if (!isset($newgroup) || $newgroup === false) {
                    // Set status to 'forbidden'.
                    $status = get_string('status_almostok', 'enrol_selma');
                    // Give more detailed response message to user.
                    $message = get_string('status_almostok_message', 'enrol_selma') .
                        get_string('forbidden_group_add', 'enrol_selma', array('intake' => $intakeid, 'course' => $courseid));
                    // Status demoted to 'almost ok'.
                    $notok = true;

                    // We can continue - no 'return', as the process can technically continue without a group.
                } else {
                    // Group created.
                    $groupid = $newgroup;
                }
            } else {
                // Else, group exists already.
                $groupid = $groupfound->id;

                foreach ($customfields as $key => $value) {
                    $groupfound->{$key} = $value;
                }
                try {
                    groups_update_group($groupfound);
                } catch (\moodle_exception | \Exception $exception) {
                    // Ignore these so that the sync can carry on.
                    // Todo: these issues should be logged somewhere.
                }
            }

            // Build relationship - group, course, enrol instance, intake.
            // Create object to record relation between courses, intakes & groups.
            $relate = new stdClass();
            $relate->courseid = $courseid;
            $relate->intakeid = $intakeid;
            $relate->groupid = $groupid;
            $relate->usermodified = $USER->id;

            $exists = $DB->record_exists('enrol_selma_course_intake',
                array('courseid' => $relate->courseid, 'intakeid' => $relate->intakeid)
            );

            if ($exists) {
                // Set status to 'nothing new here'.
                $status = get_string('status_nonew', 'enrol_selma');
                // Give more detailed response message to user.
                $message = get_string('status_nonew_message', 'enrol_selma');

                $notok = true;
            } else {
                // Store relation to DB.
                $added = $DB->insert_record('enrol_selma_course_intake', $relate, false);
            }

            // If everything's gone perfectly so far, set the status as such.
            if ($added && !$notok) {
                // Set status to 'OK'.
                $status = get_string('status_ok', 'enrol_selma');
                // Give more detailed response message to user.
                $message = get_string('status_ok_message', 'enrol_selma');
            }

            // If the intake was not added to the course, we have ultimately failed...
            if (!$added && !$notok) {
                // Set status to 'fail'.
                $status = get_string('status_internalfail', 'enrol_selma');
                // Give more detailed response message to user.
                $message = get_string('status_internalfail_message', 'enrol_selma');
            }

            // Enrol users to course - use core functions, if possible. TODO - Queue?
            // Use scheduled task?

            // Returned details - success hopefully!
            return ['status' => $status, 'added' => $added, 'message' => $message];
        }

        // Set status to 'not found'.
        $status = get_string('status_notfound', 'enrol_selma');
        // Give more detailed response message to user.
        $message = get_string('status_notfound_message', 'enrol_selma') .
            get_string('status_notfound_detailed_message', 'enrol_selma',
                get_string('add_intake_to_course_parameters::intakeid', 'enrol_selma'));

        // Returned details - failed...
        return ['status' => $status, 'added' => $added, 'message' => $message];
    }

    // Set status to 'not found'.
    $status = get_string('status_notfound', 'enrol_selma');
    // Give more detailed response message to user.
    $message = get_string('status_notfound_message', 'enrol_selma') .
        get_string('status_notfound_detailed_message', 'enrol_selma', get_string('course'));

    // Returned details - failed...
    return ['status' => $status, 'added' => $added, 'message' => $message];
}

/**
 * The function to add the specified user to an intake.
 *
 * @param   string  $studentid SELMA ID of student to add to intake.
 * @param   int     $intakeid  SELMA intake ID the user should be added to.
 * @return  array   Array of success status & bool if successful/not, message.
 * @throws  coding_exception|dml_exception
 */
function enrol_selma_add_student_to_intake(string $studentid, int $intakeid) {
    global $DB;

    // Track any warning messages.
    $warnings = [];

    // Get real Moodle user ID.
    $muser = $DB->get_record('user', array('idnumber' => $studentid), 'id');

    // If user doesn't exist yet (or they have not been 'linked' to SELMA yet).
    if (!$muser) {
        $warnings[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $studentid)
        ];

        // Return 'not found' status.
        return ['warnings' => $warnings];
    }

    return enrol_selma_add_user_to_intake($muser->id, $intakeid, $studentid);
}

/**
 * The function to add the specified user to an intake.
 *
 * @param   string  $teacherid SELMA ID of teacher to add to intake.
 * @param   int     $intakeid  SELMA intake ID the user should be added to.
 * @return  array   Array of success status & bool if successful/not, message.
 * @throws  coding_exception|dml_exception
 */
function enrol_selma_add_teacher_to_intake(string $teacherid, int $intakeid) {
    // Track any warning messages.
    $warnings = [];

    // Get real Moodle user ID.
    $muser = enrol_selma_get_teacher($teacherid);

    // If user doesn't exist yet (or they have not been 'linked' to SELMA yet).
    if (!$muser) {
        $warnings[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $teacherid)
        ];

        // Return 'not found' status.
        return ['warnings' => $warnings];
    }

    return enrol_selma_add_user_to_intake($muser['id'], $intakeid, $teacherid, 'teacher');
}

/**
 * The function to add the specified user to an intake.
 *
 * @param   int         $muserid Moodle user ID - to add to intake.
 * @param   int         $intakeid SELMA intake ID the user should be added to.
 * @param   int         $selmaid SELMA user ID being added to intake.
 * @param   string      $type Type of user - teacher or student.
 * @return  array       Array of success status & bool if successful/not, message.
 * @throws  coding_exception|dml_exception
 */
function enrol_selma_add_user_to_intake(int $muserid, int $intakeid, int $selmaid, string $type = 'student') {
    // Var 'added' of false means something didn't work. Changed further down if successfully added user to intake.
    $added = false;
    // Track any warning messages.
    $warnings = [];

    // Check if they've already been linked?
    $linked = enrol_selma_user_is_in_intake($muserid, $intakeid, $type);

    // If user's been linked before. TODO - do we care/just do nothing then?
    if ($linked) {
        $warnings[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_exists', 'enrol_selma'),
            'message' => get_string('warning_message_exists', 'enrol_selma', $selmaid)
        ];

        // Return 'already exists' status.
        return ['warnings' => $warnings];
    }

    // TODO - also eventually check if we need to enrol user into anything once we have all the necessary functions.
    // If added successfully, return success message.
    if (enrol_selma_relate_user_to_intake($muserid, $intakeid, $type)) {
        // User added to intake.
        $added = true;
    }

    $enrolled = false;
    // If user has been added, we need to enrol them to the intake's associated course(s).
    if ($added) {
        $enrolled = enrol_selma_enrol_user($muserid, $intakeid, $type);
    }

    if ($enrolled !== false) {
        // TODO - Send back warnings from enrol attempt(s)?
        return ['courses' => $enrolled];
    }

    $warnings[] = [
        'item' => get_string('pluginname', 'enrol_selma'),
        'itemid' => 1,
        'warningcode' => get_string('warning_code_unknown', 'enrol_selma'),
        'message' => get_string('warning_message_unknown', 'enrol_selma', $selmaid)
    ];

    // Returned details - failed (probably)...
    return ['warnings' => $warnings];
}

/**
 * Creates the course based on details provided.
 *
 * @param   array   $course Array of course details to create course.
 * @return  array   Array containing the status of the request, created course's ID, and appropriate message.
 */
function enrol_selma_create_course(array $course) {
    global $CFG;

    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // Courseid of null means something didn't work. Changed if successfully created a course.
    $courseid = null;
    // Set to give more detailed response message to user.
    $message = get_string('status_other_message', 'enrol_selma');

    // Check and validate everything that's needed (as minimum) by this function.
    $warnings = [];

    $context = context_system::instance();

    // Check if user has permission to create a course.
    require_capability('moodle/course:create', $context);

    // Check if we have a place to put the course.
    if (get_config('enrol_selma', 'newcoursecat') === false) {
        throw new moodle_exception('error_noconfig',
            'enrol_selma',
            $CFG->wwwroot . '/admin/settings.php?section=usersettingsselma',
            'newcoursecat'
        );
    }

    // Check if config(s) we use later have been set. These are optional, so just warn.
    if (get_config('enrol_selma', 'selmacoursetags') === false) {
        $warnings[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_noconfig', 'enrol_selma'),
            'message' => get_string('warning_message_noconfig', 'enrol_selma', 'selmacoursetags')
        ];
        // Not essential, so can continue - but warn.
    }

    // Prep tags - find & replace text and convert to array.
    $tags = get_config('enrol_selma', 'selmacoursetags');

    if ($tags !== false) {
        $tags = str_replace(['{{fullname}}', '{{shortname}}'], [$course['fullname'], $course['shortname']], $tags);
        $tags = explode(',', $tags);
    } else {
        $tags = '';
    }

    // Construct course object.
    $coursedata = new stdClass();
    // TODO - what if the setting has not been configured yet, but we get a call to create_course or if it's been deleted?
    // The default category to put the course in.
    $coursedata->category = get_config('enrol_selma', 'newcoursecat');
    // Generated? Remember - visible to users.
    $coursedata->fullname = $course['fullname'];
    // Generated? Remember - visible to users.
    $coursedata->shortname = $course['shortname'];
    // Generated?
    $coursedata->idnumber = $course['idnumber'];
    // Optional - based on category if not set.
    $coursedata->visible = get_config('moodlecourse', 'visible');
    // Add the user specified in 'selmacoursetags' setting.
    $coursedata->tags = $tags;

    // Consider course_updated() in lib.php? Check out lib/enrollib.php:409.
    $coursecreated = create_course($coursedata);
    // Check out course/externallib.php:831.

    // TODO - Add enrol_selma to course. Is this enough? What to do if false is returned?
    // Instantiate & add SELMA enrolment instance to course.
    (new enrol_selma_plugin)->add_instance($coursecreated);

    // TODO - proper check/message?
    // Check if course created successfully.
    if (isset($coursecreated->id) && $coursecreated->id > 1) {
        $status = get_string('status_ok', 'enrol_selma');
        $courseid = $coursecreated->id;
        $message = get_string('status_ok_message', 'enrol_selma');

        // Returned details - success!
        if (empty($warnings)) {
            return ['status' => $status, 'courseid' => $courseid, 'message' => $message];
        } else {
            return ['status' => $status, 'courseid' => $courseid, 'message' => $message, 'warnings' => $warnings];
        }
    }

    $status = get_string('status_internalfail', 'enrol_selma');
    $message = get_string('status_internalfail_message', 'enrol_selma');

    // Returned details - failed...
    if (empty($warnings)) {
        return ['status' => $status, 'courseid' => $courseid, 'message' => $message];
    } else {
        return ['status' => $status, 'courseid' => $courseid, 'message' => $message, 'warnings' => $warnings];
    }
}

/**
 * Creates the intake record based on details provided.
 *
 * @param   array   $intake Array of intake details, used to create intake.
 * @return  array   Array containing the status of the request, the created intake's ID, and appropriate message.
 */
function enrol_selma_create_intake(array $intake) {
    global $USER, $DB;
    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // Intakeid of null means something didn't work. Changed if successfully created the intake record.
    $intakeid = null;
    // Set to give more detailed response message to user.
    $message = get_string('status_other_message', 'enrol_selma');

    // TODO - Any additional checks - as we're inserting to DB?

    // TODO - Handle date values, time seems to be set to current time.
    $intake['intakestartdate'] = DateTime::createFromFormat('Y-m-d', $intake['intakestartdate']);
    $intake['intakeenddate'] = DateTime::createFromFormat('Y-m-d', $intake['intakeenddate']);

    // Build record.
    $data = new stdClass();
    $data->id = $intake['intakeid'];
    $data->programmeid = $intake['programmeid'];
    $data->code = $intake['intakecode'];
    $data->name = $intake['intakename'];
    $data->startdate = $intake['intakestartdate']->getTimestamp();
    $data->enddate = $intake['intakeenddate']->getTimestamp();
    $data->usermodified = $USER->id;
    $data->timecreated = time();
    $data->timemodified = time();

    // Check if record exists before inserting.
    if (!$DB->record_exists('enrol_selma_intake', array('id' => $data->id))) {
        // TODO - use raw insert? No safety checks.
        // Try to insert to DB, Moodle will throw exception, if necessary.
        $DB->insert_record_raw('enrol_selma_intake', $data, null, null, true);
        // Set status to 'OK'.
        $status = get_string('status_ok', 'enrol_selma');
        // Set intakeid to the one we just created.
        $intakeid = $data->id;
        // Use to give more detailed response message to user.
        $message = get_string('status_ok_message', 'enrol_selma');
    } else {
        // Record could not be created - probably because it already exists.
        // Set status to 'Already Reported'.
        $status = get_string('status_nonew', 'enrol_selma');
        // Give more detailed response message to user.
        $message = get_string('status_nonew_message', 'enrol_selma');
    }

    // Returned details - failed if not changed above...
    return ['status' => $status, 'intakeid' => $intakeid, 'message' => $message];
}

/**
 * Creates users with details provided.
 *
 * @param   array|null  $users Array of users' details required to create an account for them.
 * @return  array       Array containing the status of the request, userid of users created, and appropriate message.
 */
function enrol_selma_create_users(array $users) {
    $existinguser = [];

    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // If $users = null, then it means we didn't find anything/something went wrong. Changed if successfully created a user(s).
    $userids = [];
    // Set to give more detailed response message to user.
    $message = get_string('status_other_message', 'enrol_selma');

    // For each user received, process...
    foreach ($users as $user) {
        // If no username set, set to firstname.lastname format.
        if (!isset($user['username']) || empty($user['username'])) {
            $user['username'] = strtolower($user['forename'] . '.' . $user['lastname']);
        }

        $createduser = enrol_selma_user_from_selma_data($user);
        $createduser->save();

        // Add to list of created userids to be returned.
        $userids[] = $createduser->id;
    }

    // Check if existing users were found & update status/message.
    if (isset($existinguser) && !empty($existinguser)) {
        $status = get_string('status_almostok', 'enrol_selma');
        $message = get_string('status_almostok_message', 'enrol_selma') .
            ' ' .
            get_string('status_almostok_existing_message', 'enrol_selma', implode(', ', $existinguser));

        // Above message okay for if we managed to create some users alongside some duplicates. Below is if only duplicates found,
        // But no new accounts created.
        if (empty($userids) || !isset($userids)) {
            $status = get_string('status_nonew', 'enrol_selma');
            $message = get_string('status_nonew_message', 'enrol_selma');
        }
    } else {
        // If we have no duplicates & created some users - best type of success.
        if (isset($userids) && !empty($userids)) {
            $status = get_string('status_ok', 'enrol_selma');
            $message = get_string('status_ok_message', 'enrol_selma');
        } else {
            // No duplicates & no users created - fail/nothing done.
            $status = get_string('status_nocontent', 'enrol_selma');
            $message = get_string('status_nocontent_message', 'enrol_selma');
        }
    }

    // Returned details - failed...
    return ['status' => $status, 'userids' => $userids, 'message' => $message];
}

/**
 * Get all the courses that's not in any excluded category - excludecoursecat setting.
 *
 * @param   int                 $amount Number of records to retrieve - get all by default.
 * @param   int                 $page   Which 'page' to retrieve from the DB - works in conjunction with $amount.
 * @return  array               Array containing the status of the request, courses found, and appropriate message.
 * @throws  moodle_exception    Exception thrown when invalid/negative params are given.
 */
function enrol_selma_get_all_courses(int $amount = 0, int $page = 1) {
    global $DB;

    // TODO - $amount & $page needs to be positive values.
    if ($amount < 0 || $page < 0) {
        throw new moodle_exception('exception_bepositive', 'enrol_selma');
    }

    // To keep track of which DB 'page' to look on.
    $dbpage = $page;

    $nextpage = -1;

    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // If courses = null, then it means we didn't find anything/something went wrong. Changed if successfully found a course(s).
    $courses = null;
    // Set to give more detailed response message to user.
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
        return ['status' => $status, 'courses' => $courses, 'nextpage' => $nextpage, 'message' => $message];
    }

    // The next page the requester should request.
    if ($amount !== 0 && count($courses) == $amount) {
        $nextpage = $page + 1;
    }

    // Courses retrieved successfully, set statusses, messages, vars appropriately.
    $status = get_string('status_ok', 'enrol_selma');
    // Var $courses already set.
    $message = get_string('status_ok_message', 'enrol_selma');

    // Returned details.
    return ['status' => $status, 'courses' => $courses, 'nextpage' => $nextpage, 'message' => $message];
}

/**
 * Validates and checks if profilemapping is in order.
 *
 * @return  array   Returns array of the duplicated values used for profile field mapping.
 */
function enrol_selma_validate_profile_mapping() {
    // TODO - Get all and filter dupes or be specific?
    // TODO - Also check the types match. e.g. NSN -> Integer.
    // Get all the plugin's configs.
    $selmasettings = (array) get_config('enrol_selma');

    // Check each setting if profilemap.
    foreach ($selmasettings as $key => $value) {
        // We only check if profilemaps have duplicates.
        if (stripos($key, 'upm_') === false || empty($value)) {
            // Not profilemap - remove.
            unset($selmasettings[$key]);
        }
    }

    // Count how many times each value shows up.
    $duplicatesfound = array_count_values($selmasettings);

    // Remove if it's only turned up once, and then return array's keys as values instead.
    $duplicatesfound = array_keys(array_diff($duplicatesfound, [1]));

    // Return all duplicates found, if any.
    return $duplicatesfound;
}

/**
 * Finds which SELMA student fields are mapped to which Moodle profile fields.
 *
 * @return  array   Returns array of which Moodle fields the SELMA fields are mapped to.
 */
function enrol_selma_get_profile_mapping() {
    $searchstring = 'upm_';

    // TODO - Get all and filter dupes or be specific?
    // Get all the plugin's configs.
    $profilemap = (array) get_config('enrol_selma');

    // Check each setting if profilemap.
    foreach ($profilemap as $key => $value) {
        // Check if a profilemapping config.
        if (stripos($key, $searchstring) === false) {
            // Not profilemap - remove.
            unset($profilemap[$key]);
        }
    }

    // Remove prefix from fields.
    $profilemap = enrol_selma_remove_arrkey_substr($profilemap, $searchstring);

    // Return all profilemaps found, if any.
    return $profilemap;
}

/**
 * Loops through an array's keys and removes any occurrence of the given substring.
 *
 * @param   array   $checkarray The array to search through.
 * @param   string  $substring  The substring to search for.
 * @return  array   Returns array with the substring removed from its keys.
 */
function enrol_selma_remove_arrkey_substr(array $checkarray, string $substring) {
    // Note - can't use array_walk as we'll be updating the array structure (not only it's values).
    // See https://www.php.net/manual/en/function.array-walk.php#refsect1-function.array-walk-parameters.
    // Loop through the array to manually update the keys.
    foreach ($checkarray as $key => $value) {
        // Check if the key contains the substring.
        if (stripos($key, $substring) !== false) {
            // Found a match! Add an entry to the array with the updated key and same value, then remove the old entry.
            $newkey = str_replace($substring, '', $key);
            $checkarray[$newkey] = $value;
            unset($checkarray[$key]);
        }
    }

    // Return array with updated keys, if any.
    return $checkarray;
}

/**
 * Loops through an array's keys and prepends any occurrence of the given substring.
 *
 * @param   array   $checkarray The array to search through.
 * @param   string  $substring  The substring to search for.
 * @return  array   Returns array with the substring prepended to its keys.
 */
function enrol_selma_prepend_arrkey_substr(array $checkarray, string $substring) {
    // Note - can't use array_walk as we'll be updating the array structure (not only it's values).
    // See https://www.php.net/manual/en/function.array-walk.php#refsect1-function.array-walk-parameters.
    // Loop through the array to manually update the keys.
    foreach ($checkarray as $key => $value) {
        // Prepend substring to each key.
        $newkey = $substring . $key;

        // Set new key.
        $checkarray[$newkey] = $value;
        // Unset old key.
        unset($checkarray[$key]);
    }

    // Return array with updated keys, if any.
    return $checkarray;
}

/**
 *  Array of user profile fields we don't want to write to - for data integrity and security.
 *
 * @return  array   $keys Returns array of blacklisted user fields.
 */
function enrol_selma_get_blacklisted_user_fields() {
    $keys = [
        'id',
        'auth',
        'confirmed',
        'policyagreed',
        'deleted',
        'suspended',
        'mnethostid',
        'password',
        'emailstop',
        'icq',
        'skype',
        'yahoo',
        'aim',
        'msn',
        'lang',
        'calendartype',
        'theme',
        'timezone',
        'firstaccess',
        'lastaccess',
        'lastlogin',
        'currentlogin',
        'lastip',
        'secret',
        'picture',
        'url',
        'imagealt',
        'lastnamephonetic',
        'firstnamephonetic',
        'moodlenetprofile',
        'descriptionformat',
        'mailformat',
        'maildigest',
        'maildisplay',
        'autosubscribe',
        'trackforums',
        'timecreated',
        'timemodified',
        'trustbitmask'
    ];

    return $keys;
}

/**
 *  Array of all custom user profile fields on the site.
 *
 * @return  array   $customoptions Returns array of custom profile fields.
 */
function enrol_selma_get_custom_profile_fields() {
    global $DB;
    $customoptions = [];

    // Get custom fields.
    $customfields = $DB->get_records('user_info_field', [], null, 'shortname');

    // If we found customprofile fields, we need to include those.
    if (!empty($customfields) && isset($customfields)) {
        // Prepend with 'profile_field_' to make identifiable as custom user field.
        $customoptions = preg_filter('/^/', 'profile_field_', array_keys($customfields));
    }

    return $customoptions;
}

/**
 *  Load all custom profile fields on the site into user object as properties.
 *
 * @param   user    $user User object to load fields into.
 * @return  user    $user Returns array of custom profile fields.
 */
function enrol_selma_load_custom_profile_fields(user $user) {
    $allcustomfields = enrol_selma_get_custom_profile_fields();

    foreach ($allcustomfields as $field) {
        // TODO - set to string by default for now - add checks for type.
        $user->$field = '';
    }

    return $user;
}

/**
 *  Array of all allowed user profile fields - including custom fields and excluding blacklisted fields.
 *
 * @return  array   $keys Returns array of all allowed user fields.
 */
function enrol_selma_get_allowed_user_fields() {
    // Get core fields.
    $alloptions = get_user_fieldnames();

    // List of user profile fields we don't want to write to - for data integrity and security.
    $blacklistkeys = enrol_selma_get_blacklisted_user_fields();

    // Get custom fields.
    $customoptions = enrol_selma_get_custom_profile_fields();

    $alloptions = array_merge($alloptions, $customoptions);

    // TODO - Need to re-create index with array_combine() - it sets each key to its value, to get shortname easier?
    // Remove any blacklisted profile fields from the list of options.
    $allowed = array_values(array_diff($alloptions, $blacklistkeys));
    $allowed = array_combine($allowed, $allowed);

    return $allowed;
}

/**
 * Loads user based on given (Moodle) ID.
 *
 * @param   int $id User's Moodle ID value.
 */
function enrol_selma_user_from_id(int $id) {
    global $DB;

    $user = new user();

    // Set id, as it's on the blacklisted fields - we don't want the user to set the user's id.
    $user->id = $id;

    // Should only be one, so we use get_record. Also, only the allowed fields.
    $dbuser = (array) $DB->get_record('user', array('id' => $user->id));

    // Set core fields/properties.
    $user->set_properties($dbuser);

    // Get custom profile fields.
    $customfields = enrol_selma_get_user_custom_field_data($user->id);

    // Set custom profile fields.
    if (isset($customfields)) {
        $customfields = enrol_selma_prepend_arrkey_substr($customfields, 'profile_field_');
        $user->set_properties($customfields);
    }

    return $user;
}

/**
 * Update the user's properties with the SELMA data.
 *
 * @param   array   $selmauser SELMA user data to be transcribed to Moodle user data.
 */
function enrol_selma_user_from_selma_data($selmauser) {
    $user = new user();

    // Use profile field mapping to capture user data.
    $profilemapping = enrol_selma_get_profile_mapping();

    // Assign each SELMA user profile field to the Moodle equivalent.
    foreach ($selmauser as $field => $value) {
        // Translate to Moodle field.
        $element = $profilemapping[$field];

        // Set field to value.
        $user->set_property($element, $value);
    }

    return $user;
}

/**
 * Creates record in DB of relationship between user & intake.
 *
 * @param   int         $userid User we're adding to an intake.
 * @param   int         $intakeid Intake ID user should be added to.
 * @param   string      $type Type of user - student or teacher.
 * @return  bool        $inserted Bool indicating success/failure of inserting record to DB.
 * @throws  dml_exception
 */
function enrol_selma_relate_user_to_intake(int $userid, int $intakeid, string $type = 'student') {
    global $DB, $USER;

    // Todo - Should we be able to add users to an intake before the intake exists in Moodle (pre-create)?
    // Check if intake exists.
    if ($DB->record_exists('enrol_selma_intake', array('id' => $intakeid)) === false) {
        return false;
    }

    // Construct data object for DB.
    $data = new stdClass();
    $data->userid = $userid;
    $data->intakeid = $intakeid;
    $data->usermodified = $USER->id;
    $data->timecreated = time();
    $data->timemodified = time();

    // Add to student or teacher table.
    $table = 'enrol_selma_student_intake';

    if ($type === 'teacher') {
        $table = 'enrol_selma_teacher_intake';
    }

    return $DB->insert_record($table, $data, false);
}

/**
 * Get all of a user's custom profile field data.
 *
 * @param   int     $id User's Moodle ID.
 * @return  array   $customfields Associative array with customfield's shortname as key and user's data as value.
 * @throws  dml_exception
 */
function enrol_selma_get_user_custom_field_data(int $id) {
    global $DB;

    // Keep track of given user's data.
    $userdata = [];

    // Get the fields and data for the user.
    $customfields = profile_get_custom_fields();
    $fielddata = $DB->get_records('user_info_data', array('userid' => $id), null, 'id, fieldid, data');

    // Map the user's data to the corresponding customfield shortname.
    foreach ($fielddata as $data) {
        $userdata[$customfields[$data->fieldid]->shortname] = $data->data;
    }

    return $userdata;
}

/**
 * Checks if a user is associated to an intake.
 *
 * @param   int     $userid   User we're checking.
 * @param   int     $intakeid Intake ID user should be checked against.
 * @param   string  $type Type of user - student or teacher.
 * @return  bool    Bool indicating if user is in intake.
 * @throws  dml_exception
 */
function enrol_selma_user_is_in_intake(int $userid, int $intakeid, string $type = 'student') {
    global $DB;

    $table = 'enrol_selma_student_intake';

    if ($type === 'teacher') {
        $table = 'enrol_selma_teacher_intake';
    }

    return $DB->record_exists($table, array('userid' => $userid, 'intakeid' => $intakeid));
}

/**
 * Create a Moodle course based on SELMA component data.
 *
 * @param   array                           $selmadata Data received from SELMA (most likely).
 * @return  course                          Return course object.
 * @throws  dml_exception
 * @throws  moodle_exception
 * @throws  required_capability_exception
 */
function enrol_selma_create_course_from_selma(array $selmadata) {

    require_capability('moodle/course:create', context_system::instance());
    $course = new course();
    $propertymapfactory = new property_map_factory();
    $coursepropertymap = $propertymapfactory->build_course_property_map($course, get_config('enrol_selma'));
    if (!$coursepropertymap->is_valid()) {
        throw new moodle_exception($coursepropertymap->get_last_error());
    }
    $coursepropertymap->write_data($selmadata);
    $course->save();
    return $course;
}


/**
 * Updates a Moodle course based on new SELMA component data.
 *
 * @param   array               $selmadata
 * @param   stdClass            $config
 * @param   string              $courselinkfield
 * @return  course
 * @throws  coding_exception
 * @throws  dml_exception
 * @throws  moodle_exception
 */
function enrol_selma_update_course_from_selma(array $selmadata, stdClass $config, $courselinkfield = 'idnumber') {
    global $DB;
    require_capability('moodle/course:update', context_system::instance());
    if (trim($courselinkfield) === '') {
        throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, $courselinkfield);
    }
    $propertymapfactory = new enrol_selma\local\factory\property_map_factory();
    // Just want the map for now.
    $coursepropertymap = $propertymapfactory->build_course_property_map(new enrol_selma\local\course(), $config);
    if (!$coursepropertymap->is_valid()) {
        throw new moodle_exception($coursepropertymap->get_last_error());
    }
    $selmalinkfield = $coursepropertymap->get_property($courselinkfield)->get_default_mapped_property_name();
    if (!isset($selmadata[$selmalinkfield])) {
        throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, $selmadata);
    }
    $record = $DB->get_record('course', [$courselinkfield => $selmadata[$selmalinkfield]], '*', MUST_EXIST);
    $entityfactory = new entity_factory();
    $course = $entityfactory->build_course_from_stdclass($record);
    $coursepropertymap = $propertymapfactory->build_course_property_map($course, $config);
    $coursepropertymap->write_data($selmadata);
    $course->save();
    return $course;
}

/**
 * Create a Moodle user record based on SELMA student data.
 *
 * @param   array       $selmadata
 * @return  user
 * @throws  dml_exception
 * @throws  moodle_exception
 * @throws  required_capability_exception
 */
function enrol_selma_create_student_from_selma(array $selmadata) {
    $student = new student();
    $student = enrol_selma_map_and_create_user($student, $selmadata);
    return $student;
}

/**
 * Update a Moodle user record based on SELMA student data.
 *
 * @param   array       $selmadata
 * @param   stdClass    $config
 * @param   string      $userlinkfield
 * @return  user
 * @throws  coding_exception
 * @throws  dml_exception
 * @throws  moodle_exception
 */
function enrol_selma_update_student_from_selma(array $selmadata, stdClass $config, $userlinkfield = 'idnumber') {
    global $DB;
    require_capability('moodle/user:update', context_system::instance());
    if (trim($userlinkfield) === '') {
        throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'userlinkfield');
    }
    $propertymapfactory = new enrol_selma\local\factory\property_map_factory();
    // Just want the map for now.
    $userpropertymap = $propertymapfactory->build_user_property_map(new enrol_selma\local\student(), $config);
    if (!$userpropertymap->is_valid()) {
        throw new moodle_exception($userpropertymap->get_last_error());
    }
    $selmalinkfield = $userpropertymap->get_property($userlinkfield)->get_default_mapped_property_name();
    if (!isset($selmadata[$selmalinkfield])) {
        throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'selmadata');
    }
    // Find by moodle user id first, if supplied, otherwise, try find by selmaid.
    if (isset($selmadata['moodleuserid']) && !empty($selmadata['moodleuserid'])) {
        $record = $DB->get_record('user', array('id' => $selmadata['moodleuserid']), '*', MUST_EXIST);
    } else {
        $record = $DB->get_record('user', [$userlinkfield => $selmadata[$selmalinkfield]], '*', MUST_EXIST);
    }
    $user = entity_factory::build_student_from_stdclass($record);
    $userpropertymap = $propertymapfactory->build_user_property_map($user, $config);
    $userpropertymap->write_data($selmadata);
    $user->save();
    return $user;
}

/**
 * Create a Moodle user record based on SELMA teacher data.
 *
 * @param   array               $selmadata Data from SELMA - teacher user info.
 * @return  array               Created user ID and/or waarning(s).
 * @throws  moodle_exception
 */
function enrol_selma_create_teacher_from_selma(array $selmadata) {
    // We can do teacher-specific things here.
    $moodleuserid = -1;
    // TODO - check if 'teacherid' custom profile field exists, otherwise create it.

    $teacher = new teacher();
    $teacher = enrol_selma_map_and_create_user($teacher, $selmadata);

    // Only return the created user's ID.
    if (isset($teacher) && !empty($teacher->id)) {
        $moodleuserid = $teacher->id;
    }

    // TODO - Handle/create appropriate warning messages.
    return [
        'userid' => $moodleuserid
    ];
}

/**
 * Update a Moodle user record based on SELMA teacher data.
 *
 * @param   array       $selmadata
 * @param   stdClass    $config
 * @param   string      $userlinkfield
 * @return  user
 * @throws  coding_exception
 * @throws  dml_exception
 * @throws  moodle_exception
 */
function enrol_selma_update_teacher_from_selma(array $selmadata, stdClass $config, $userlinkfield = 'teacherid') {
    global $DB;
    require_capability('moodle/user:update', context_system::instance());
    if (trim($userlinkfield) === '') {
        throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'userlinkfield');
    }
    $propertymapfactory = new enrol_selma\local\factory\property_map_factory();
    // Just want the map for now.
    $userpropertymap = $propertymapfactory->build_user_property_map(new enrol_selma\local\teacher(), $config);
    if (!$userpropertymap->is_valid()) {
        throw new moodle_exception($userpropertymap->get_last_error());
    }
    if (!isset($selmadata[$userlinkfield])) {
        throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'selmadata');
    }
    // Find by moodle user id first, if supplied, otherwise, try find by selma tutorid.
    if (isset($selmadata['moodleuserid']) && !empty($selmadata['moodleuserid'])) {
        $record = $DB->get_record('user', array('id' => $selmadata['moodleuserid']), '*', MUST_EXIST);
    } else {
        // Needs fine-tuning if wanting to update by selmaID.
        // $record = enrol_selma_get_teacher($selmadata[$userlinkfield], $selmadata['email']);
        throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, $userlinkfield);
    }
    $teacher = entity_factory::build_teacher_from_stdclass((object) $record);
    $userpropertymap = $propertymapfactory->build_user_property_map($teacher, $config);
    $userpropertymap->write_data($selmadata);

    $teacher->save();
    return $teacher;
}

/**
 * Maps given SELMA data to given user object and saves it (to the DB).
 *
 * @param   user        $user User object - teacher or student (or user).
 * @param   array       $selmadata Data received from SELMA.
 * @return  user        $user User object.
 * @throws  required_capability_exception
 * @throws  dml_exception
 * @throws  moodle_exception
 */
function enrol_selma_map_and_create_user(user $user, array $selmadata) {
    // Check if user has capability to do so.
    require_capability('moodle/user:create', context_system::instance());

    // Get plugin configs.
    $config = get_config('enrol_selma');

    // Map & prep object.
    $propertymapfactory = new property_map_factory();
    $userpropertymap = $propertymapfactory->build_user_property_map($user, $config);
    if (!$userpropertymap->is_valid()) {
        throw new moodle_exception($userpropertymap->get_last_error());
    }
    $userpropertymap->write_data($selmadata);

    // Create user (in DB).
    $user->save();

    return $user;
}

/**
 * Retrieve all available custom course fields.
 *
 * @return  array   $fields Return all the custom course fields with shortname as key & fullname as value.
 */
function enrol_selma_get_custom_course_fields() {
    // Course's itemid is always 0 - https://docs.moodle.org/dev/Custom_fields_API#Custom_fields_API_overview.
    $categories = api::get_categories_with_fields('core_course', 'course', 0);

    // Get all fields from all custom course field categories.
    $fields = array();
    foreach ($categories as $category) {
        foreach ($category->get_fields() as $field) {
            // Return array of fields, so we can leverage helper functions.
            $fields[] = $field;
        }
    }

    return $fields;
}

/**
 * Updates and/or saves a course's custom fields.
 *
 * @param   course  $course The course to update (including 'customfield_fields').
 * @return  course  Return updated course.
 */
function enrol_selma_save_custom_course_fields(course $course) {
    // Course object should have an ID at this point.
    $handler = course_handler::create($course->id);
    // Get datacontroller so we can manipulate the data.
    $datacontrollers = $handler->get_instance_data($course->id);

    // For each field, update the value (if any).
    foreach ($datacontrollers as $datacontroller) {
        $field = $datacontroller->get_field();
        $property = $field->get('shortname');
        $customfield = null;

        // Only set and save if we have those fields.
        if (isset($property) && isset($course->{'customfield_' . $property})) {
            $customfield = $course->{'customfield_' . $property};
            // Set value for type of field.
            $datacontroller->set($datacontroller->datafield(), $customfield);
            // Set value field.
            $datacontroller->set('value', $customfield);
            $datacontroller->save();
        }
    }

    return $course;
}

/**
 * Get a SELMA intake's details from Moodle's DB (if any).
 *
 * @param   int                 $intakeid The SELMA intake ID to retrieve.
 * @return  array               $intake The intake details, or warning if none found.
 * @throws  coding_exception
 * @throws  dml_exception
 */
function enrol_selma_get_intake(int $intakeid) {
    global $DB;

    // Check the DB for intake.
    $intake = $DB->get_record('enrol_selma_intake', array('id' => $intakeid));

    // Return 'not found' if a record could not be found.
    if ($intake === false) {
        $intake['warnings'][] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $intakeid)
        ];

        // Return warning.
        return $intake;
    }

    // Cast object to array.
    $intake = (array)$intake;

    // Return array of intake details.
    return $intake;
}

/**
 * Get a SELMA intake's associated courses from Moodle's DB (if any).
 *
 * @param   int                 $intakeid The SELMA intake ID to retrieve course for.
 * @return  array               $intake The intake's courses, or warning if none found.
 * @throws  coding_exception
 * @throws  dml_exception
 */
function enrol_selma_get_intake_courses(int $intakeid) {
    global $DB;

    // Check the DB for intake.
    $intakecourses = $DB->get_records('enrol_selma_course_intake', array('intakeid' => $intakeid), null, 'courseid');

    // Return 'not found' if a record could not be found.
    if (empty($intakecourses)) {
        $intakecourses['warnings'][] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $intakeid)
        ];

        // Return warning.
        return $intakecourses;
    }

    // Return array of intake's course IDs.
    return ['courseids' => array_keys($intakecourses)];
}

/**
 * Retrieves gradebook items for a given course.
 *
 * @param   int     $courseid Course ID for which to retrieve gradebook items.
 * @return  array   $items The gradebook items found.
 * @throws  dml_exception|coding_exception
 */
function enrol_selma_get_gradebook_items(int $courseid) {
    global $DB;

    // Check the DB for gradebook items.
    $items = $DB->get_records('grade_items', array('courseid' => $courseid), null, 'id, itemname, itemtype');

    // Return 'not found' if a record could not be found.
    if (empty($items)) {
        $items['warnings'][] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $courseid)
        ];

        // Return warning.
        return $items;
    }

    // Courses' gradebook items has no 'itemname'. We have to add it...
    foreach ($items as $item) {
        // If item is a course, look for course name.
        if ($item->itemname === null && $item->itemtype === 'course') {
            $itemname = $DB->get_record('course', array('id' => $courseid), 'fullname');

            // If found, update item's name to course name.
            if ($itemname !== false) {
                $item->itemname = $itemname->fullname;
            }
        }
    }

    // Return array of course's gradebook item details.
    return ['items' => $items];
}

/**
 * Get a SELMA student's details (if any).
 *
 * @param   int                 $studentid The SELMA student ID to retrieve details of.
 * @param   string              $email The SELMA student email address to retrieve details of.
 * @return  array               $student The student's details, or warning if none found.
 * @throws  coding_exception
 * @throws  dml_exception
 */
function enrol_selma_get_student(int $studentid, string $email) {
    global $DB;
    $warnings = [];
    $student = false;

    // Lookup using SELMA student ID first.
    if (isset($studentid)) {
        $student = $DB->get_record('user', array('idnumber' => $studentid), 'id, firstname, lastname, email, idnumber');
    }

    // If not found, try looking up by email.
    if ($student === false) {
        // TODO - improve warning message - user not found by idnumber.
        $warnings[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $studentid)
        ];

        if (!empty($email)) {
            $student = $DB->get_record('user', array('email' => $email), 'id, firstname, lastname, email, idnumber');

            // Return 'not found' if email record could not be found.
            if ($student === false) {
                // TODO - improve warning message - user not found by email.
                $warnings[] = [
                    'item' => get_string('pluginname', 'enrol_selma'),
                    'itemid' => 1,
                    'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
                    'message' => get_string('warning_message_notfound', 'enrol_selma', $email)
                ];

                $student['warnings'] = $warnings;

                // Return warning - and ONLY warning if both student ID & email lookup failed.
                return $student;
            }
        }
    }

    // Handle the scenario when a user has no idnumber yet (it's an optional field in Moodle).
    if (empty($student->idnumber)) {
        unset($student->idnumber);
    }

    // Cast object to array.
    $student = (array) $student;

    $dupeemails = get_config('core', 'allowaccountssameemail');

    // Warn if duplicate emails are allowed.
    if ($dupeemails !== false && $dupeemails === '1') {
        $warnings[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_duplicatesallowed', 'enrol_selma'),
            'message' => get_string('warning_message_duplicatesallowed', 'enrol_selma')
        ];
    }

    if (!empty($warnings)) {
        $student['warnings'] = $warnings;
    }

    return $student;
}

/**
 * Get a SELMA teacher's details (if any).
 *
 * @param   string              $teacherid The SELMA teacher/tutor ID to retrieve details of.
 * @param   string              $email The SELMA teacher/tutor email address to retrieve details of.
 * @return  array               $teacher The teacher's details, or warning if none found.
 * @throws  coding_exception
 * @throws  dml_exception
 */
function enrol_selma_get_teacher(string $teacherid, string $email = '') {
    global $DB;
    $warnings = [];
    $teacher = false;

    // Lookup using SELMA teacher ID first.
    if (isset($teacherid)) {
        // Check if the 'teacherid' customfield exists.
        $fieldid = enrol_selma_get_teacherid_field();

        // Throw warning, if not, but we can still try and find by email if duplicates are not allowed.
        if ($fieldid === false) {
            // TODO - improve warning message - teacherid custom profile field not found.
            $warnings[] = [
                'item' => get_string('pluginname', 'enrol_selma'),
                'itemid' => 1,
                'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
                'message' => get_string('warning_message_notfound', 'enrol_selma', $fieldid)
            ];
        } else {
            // There should only be one user with that Teacher ID.
            $select = sprintf("fieldid = :fieldid AND %s = :data", $DB->sql_compare_text('data'));

            $idexists = $DB->get_record_select(
                'user_info_data',
                $select,
                array('fieldid' => $fieldid, 'data' => $teacherid),
                'userid'
            );

            // Now get the actual user record.
            if ($idexists !== false) {
                $teacher = $DB->get_record('user', array('id' => $idexists->userid), 'id, firstname, lastname, email, idnumber');
            }
        }
    }

    // If not found, try looking up by email.
    if ($teacher === false) {
        // TODO - improve warning message - user not found by id/teacherid.
        $warnings[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $teacherid)
        ];

        // Try finding by email.
        if (!empty($email)) {
            $teacher = $DB->get_record('user', array('email' => $email), 'id, firstname, lastname, email, idnumber');
        }

        // Return 'not found' if email record could not be found either.
        if ($teacher === false) {
            // TODO - improve warning message - user not found by email.
            $warnings[] = [
                'item' => get_string('pluginname', 'enrol_selma'),
                'itemid' => 1,
                'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
                'message' => get_string('warning_message_notfound', 'enrol_selma', $email)
            ];

            $teacher['warnings'] = $warnings;

            // Return warning - and ONLY warning if both teacher ID & email lookup failed.
            return $teacher;
        }
    }

    // Handle the scenario when a user has no idnumber yet (it's an optional field in Moodle).
    if (empty($teacher->idnumber)) {
        unset($teacher->idnumber);
    }

    // TODO - return actual teacherid, not the sent one (or return nothing).
    // Include teacherid in response.
    $teacher->teacherid = $teacherid;

    // Cast object to array.
    $teacher = (array) $teacher;

    $dupeemails = get_config('core', 'allowaccountssameemail');

    // Warn if duplicate emails are allowed.
    if ($dupeemails !== false && $dupeemails === '1') {
        $warnings[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_duplicatesallowed', 'enrol_selma'),
            'message' => get_string('warning_message_duplicatesallowed', 'enrol_selma')
        ];
    }

    if (!empty($warnings)) {
        $teacher['warnings'] = $warnings;
    }

    return $teacher;
}

/**
 * Get the 'teacherid' custom profile field (if any).
 *
 * @return  int|bool            ID of field, or false if not found.
 */
function enrol_selma_get_teacherid_field() {
    global $DB;

    // We expect a custom textfield with the shortname 'teacherid'.
    $fieldid = $DB->get_record('user_info_field', array('shortname' => 'teacherid', 'datatype' => 'text'), 'id');

    // Return false if not found.
    if ($fieldid === false) {
        return $fieldid;
    }

    // Otherwise, send the DB id back.
    return $fieldid->id;
}

/**
 * Enrol a user into course associated to the given intake.
 *
 * @param   int     $userid Moodle user ID to be enrolled.
 * @param   int     $intakeid ID of intake to inform course enrolments.
 * @param   string  $type Role to assign to user.
 * @return  mixed
 * @throws  coding_exception
 * @throws  dml_exception
 */
function enrol_selma_enrol_user(int $userid, int $intakeid, string $type = 'student') {
    // TODO - Should this move to `enrol_user()` in lib.php?
    global $DB;

    $enrolled = [];
    $roleid = null;

    // Get intake's courses & groups.
    $coursesgroups = $DB->get_records('enrol_selma_course_intake', array('intakeid' => $intakeid), null, 'courseid, groupid');

    // We only accept teacher/student as roles.
    if ($type === 'student' || $type === 'teacher') {
        // Get the role 'student' or 'teacher' maps to.
        $roleid = get_config('enrol_selma', $type . 'role');
    }

    // Enrol the user into each course found.
    foreach ($coursesgroups as $courseandgroup) {
        // Get the selma enrolment method instance for the course.
        $instance = $DB->get_record('enrol', array('courseid' => $courseandgroup->courseid, 'enrol' => 'selma'));
        // Get the enrol plugin to use for user enrolment.
        $enrolplugin = enrol_get_plugin('selma');

        // Fall back to manual if not found.
        if ($instance === false) {
            $instance = $DB->get_record('enrol', array('courseid' => $courseandgroup->courseid, 'enrol' => 'manual'));
            $enrolplugin = enrol_get_plugin('manual');
        }

        // This will enrol the user! Yay.
        $enrolplugin->enrol_user($instance, $userid, $roleid);

        // Get user enrolment ID.
        $ueid = $DB->get_record('user_enrolments', array('enrolid' => $instance->id, 'userid' => $userid), 'id');

        // Put them in the appropriate group.
        if (isset($courseandgroup->groupid) && !empty($courseandgroup->groupid)) {
            $addedtogroup = groups_add_member($courseandgroup->groupid, $userid);
        }

        // Log enrolment.
        $enrolled[] = [
            'courseid' => $instance->courseid,
            'userenrolid' => $ueid->id
        ];
    }

    return $enrolled;
}
