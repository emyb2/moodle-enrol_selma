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
require_once(dirname(__FILE__, 3) . '/admin/tool/uploaduser/locallib.php');
require_once(dirname(__FILE__, 3) . '/user/lib.php');

/**
 * The function to add the specified user to an intake.
 *
 * @param   int     $userid SELMA ID of user to add to intake.
 * @param   int     $intakeid SELMA intake ID the user should be added to.
 * @return  array   Array of success status & bool if successful/not, message.
 */
function enrol_selma_add_user_to_intake(int $userid, int $intakeid) {
    global $DB, $USER;

    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // Var 'added' of false means something didn't work. Changed if successfully added user to intake.
    $added = false;
    // Use to give more detailed response message to user.
    $message = get_string('status_other_message', 'enrol_selma');

    // Prep data to go into DB.
    // Check which Moodle field the SELMA ID is associated to.
    $idmapping = enrol_selma_get_profile_mapping()['id'];

    // Get real Moodle user ID.
    $muserid = $DB->get_record('user', array($idmapping => $userid), 'id');

    // If user doesn't exist yet (or they have not been 'linked' to SELMA yet).
    if (!$muserid) {
        // Set status to 'not found'.
        $status = get_string('status_notfound', 'enrol_selma');
        // Use to give more detailed response message to user.
        $message = get_string('status_notfound_message', 'enrol_selma');

        // Return 'not found' status.
        return ['status' => $status, 'added' => $added, 'message' => $message];
    }

    // Check if they've already been linked?
    $linked = $DB->record_exists('enrol_selma_user_intake', array('userid' => $muserid->id, 'intakeid' => $intakeid));

    // If user's been linked before.
    if ($linked) {
        // Set status to 'already reported'.
        $status = get_string('status_nonew', 'enrol_selma');
        // Set message to give more detailed response to user.
        $message = get_string('status_nonew_message', 'enrol_selma');

        // Return 'already reported' status.
        return ['status' => $status, 'added' => $added, 'message' => $message];
    }

    // Contruct data object for DB.
    $data = new stdClass();
    $data->userid = $muserid->id;
    $data->intakeid = $intakeid;
    $data->usermodified = $USER->id;
    $data->timecreated = time();
    $data->timemodified = time();

    // If added successfully, return success message.
    if ($DB->insert_record('enrol_selma_user_intake', $data, false)) {
        // Set status to 'OK'.
        $status = get_string('status_ok', 'enrol_selma');
        // User added to intake.
        $added = true;
        // Use to give more detailed response message to user.
        $message = get_string('status_ok_message', 'enrol_selma');

        // Return 'success' status.
        return ['status' => $status, 'added' => $added, 'message' => $message];
    }

    // Returned details - failed (probably)...
    return ['status' => $status, 'added' => $added, 'message' => $message];
}

/**
 * Creates the course based on details provided.
 *
 * @param   array   $course Array of course details to create course.
 * @return  array   Array containing the status of the request, created course's ID, and appropriate message.
 */
function enrol_selma_create_course(array $course) {
    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // Courseid of null means something didn't work. Changed if successfully created a course.
    $courseid = null;
    // Set to give more detailed response message to user.
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
    $intake['intakestartdate'] = DateTime::createFromFormat('d-m-Y', $intake['intakestartdate']);
    $intake['intakeenddate'] = DateTime::createFromFormat('d-m-Y', $intake['intakeenddate']);

    // Build record.
    $data = new stdClass();
    $data->id =             $intake['intakeid'];
    $data->programmeid =    $intake['programmeid'];
    $data->code =           $intake['intakecode'];
    $data->name =           $intake['intakename'];
    $data->startdate =      $intake['intakestartdate']->getTimestamp();
    $data->enddate =        $intake['intakeenddate']->getTimestamp();
    $data->usermodified =   $USER->id;
    $data->timecreated =    time();
    $data->timemodified =   time();

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
    global $DB, $CFG;
    $existinguser = [];

    // Set status to 'we don't know what went wrong'. We will set this to potential known causes further down.
    $status = get_string('status_other', 'enrol_selma');
    // If $users = null, then it means we didn't find anything/something went wrong. Changed if successfully created a user(s).
    $userids = [];
    // Set to give more detailed response message to user.
    $message = get_string('status_other_message', 'enrol_selma');

    // Use profile field mapping to capture user data.
    $profilemapping = enrol_selma_get_profile_mapping();

    // For each user received, process...
    foreach ($users as $user) {
        // Keep track of customfields & their values.
        $usercustomfields = [];

        // If no username set, set to firstname.lastname format.
        if (!isset($user['username']) || empty($user['username'])) {
            $user['username'] = strtolower($user['forename'] . '.' . $user['lastname']);
        }

        // TODO - If exist, update? Maybe check email too - respect `allowaccountssameemail` setting?
        // First, check if user exists by SELMA id.
        $tempuser = $DB->get_record('user', array($profilemapping['id'] => $user['id']));

        if ($tempuser !== false) {
            // Add to list of existing users found.
            $existinguser[] = $user['id'];
            continue;
        }

        // TODO - Different error, or just return the ID/email1? Any better way than 2 DB calls - $DB->get_records_select?
        // Then, check if user exists with email - respect `allowaccountssameemail`.
        $tempuser = $DB->get_record('user', array($profilemapping['email1'] => $user['email1']));
        if (get_config('moodle', 'allowaccountssameemail') === '0' && $tempuser->email === $user['email1']) {
            // Add to list of existing users found.
            $existinguser[] = $user['email1'];
            continue;
        }

        // Otherwise, create user.
        // TODO - Which is better `create_user_record();` or `user_create_user();` - the latter is more thorough.
        $newuser = new stdClass();

        // Assign each user profile fields to the Moodle equivalent.
        foreach ($user as $field => $value) {
            // Translate to Moodle field.
            $element = $profilemapping[$field];

            // If customfield, track it for later, otherwise, add to user object.
            if (preg_match('/^profile_field_/', $element)) {
                $usercustomfields[preg_replace('/^profile_field_/', '', $element)] = $value;
            } else {
                // Set field to value.
                $newuser->$element = $value;
            }
        }

        // We only support local accounts.
        $newuser->mnethostid = $CFG->mnet_localhost_id;

        // TODO - increment username?
        // Check if username exists and increment, if necessary.
        if ($DB->get_record('user', array('username' => $newuser->username)) !== false) {
            $newuser->username = uu_increment_username($newuser->username);
        }

        $createduserid = user_create_user($newuser);

        // Handle custom profile fields.
        profile_save_custom_fields($createduserid, $usercustomfields);

        // Add to list of created userids to be returned.
        $userids[] = $createduserid;
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

/**
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
        if (stripos($key, 'profilemap_') === false) {
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
 * @return  array   Returns array of which Moodle fields the SELMA fields are mapped to.
 */
function enrol_selma_get_profile_mapping() {
    $searchstring = 'profilemap_';

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
 * @return  array   Returns array of the duplicated values used for profile field mapping.
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