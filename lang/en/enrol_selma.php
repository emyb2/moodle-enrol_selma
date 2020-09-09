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
 * Plugin strings are defined here.
 *
 * @package     enrol_selma
 * @category    string
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'SELMA';

$string['applicationlog'] = 'Application log';
$string['configuration'] = 'Configuration';
$string['keep'] = 'Keep';
$string['sevendays'] = '7 days';
$string['thirtydays'] = '30 days';
$string['sixtydays'] = '60 days';
$string['ninetydays'] = '90 days';
$string['levelname'] = 'Level';
$string['message'] = 'Message';
$string['time'] = 'Time';
$string['level'] = 'Level';
$string['filters'] = 'Filter table data';
$string['before'] = 'Before';
$string['after'] = 'After';
$string['apply'] = 'Apply';
$string['clear'] = 'Clear';
$string['component'] = 'Component';
$string['disabled'] = 'Disabled';
$string['enabled'] = 'Enabled';
$string['logging'] = 'Logging settings';
$string['loglevel'] = 'Log level';
$string['loglevel::description'] = '
<strong>DEBUG</strong> (100): Detailed debug information.

<strong>INFO</strong> (200): Interesting events. Examples: User logs in, SQL logs.

<strong>NOTICE</strong> (250): Normal but significant events.

<strong>WARNING</strong> (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.

<strong>ERROR</strong> (400): Runtime errors that do not require immediate action but should typically be logged and monitored.

<strong>CRITICAL</strong> (500): Critical conditions. Example: Application component unavailable, unexpected exception.

<strong>ALERT</strong> (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.

<strong>EMERGENCY</strong> (600): Emergency: system is unusable.
';
$string['logemailcritical'] = 'Email critical errors';
$string['logemailcritical::description'] = 'Email any errors that are logged at CRITICAL, ALERT, or EMERGENCY levels.';
$string['logemailcriticalrecipients'] = 'Email recipients';
$string['logemailcriticalrecipients::description'] = 'One or multiple mail addresses separated by commas. The email address must be associated to a Moodle user account.';
$string['logretention'] = 'Log retention';
$string['logretention::description'] = 'Days to keep log entries in main logging table. Log entries purged based off log entries timestamp.';

// Settings.
$string['selmasettingcategories'] = 'SELMA enrolment setting categories';
$string['settings'] = 'Enrolment settings';
$string['settingsheading'] = 'SELMA enrolment settings';
$string['settingsheading::description'] = 'The SELMA->Moodle enrolment plugin *(MOOMA)* allows admins to manage user enrolments from SELMA SMS.
Internally, the enrolment is done via the Manual enrolment plugin - which **must** to be enabled in the same course.';
$string['unenrolaction'] = 'External unenrol action';
$string['unenrolaction::description'] = 'Select action to carry out when Moodle receives \'unenrol\' request from SELMA. Please note: some user data purged from course during unenrolment.';
$string['instancedefaults'] = 'Enrolment instance defaults';
$string['instancedefaults::description'] = 'Default settings applied to a new SELMA enrolment instance in a course.';
$string['defaultrole'] = 'Default role';
$string['defaultrole::description'] = 'Default role assigned to user when enrolled to course.';
$string['welcomeheading'] = 'Welcome email';
$string['welcomeheading::description'] = 'Template of the welcome email sent to new users created via SELMA.';
$string['welcomesubject'] = 'Email subject';
$string['welcomesubject::description'] = 'Welcome email subject line.';
$string['welcomebody'] = 'Email body';
$string['welcomebody::description'] = '<strong>Description</strong><br>
                                        Message in welcome email body.<br>
                                        Use keywords below to build a customised message:<br><br>
                                        <strong>Keywords:</strong>
                                        <ul>
                                            <li>{sitename} - Website\'s Full Name.</li>
                                            <li>{firstname} - User\'s First Name.</li>
                                            <li>{lastname} - User\'s Last Name.</li>
                                            <li>{course} - Course Full Name.</li>
                                            <li>{courselink} - Clickable Course Full Name - links to appropriate course.</li>
                                            <li>{date} - Date in the format of dd/mm/yyyy.</li>
                                        </ul><hr>';
$string['coursesettingsheading'] = 'Course settings';
$string['coursedefaultsheading'] = 'Default course settings';
$string['coursedefaultsheading::description'] = 'This plugin uses the \'Default course settings\' as set-up in Moodle when creating new courses.<br><br>
                                                You can find the \'Default course settings\' here:<br>
                                                <a href="' . $CFG->wwwroot . '/admin/settings.php?section=coursesettings">Default course settings</a>';
$string['selmacoursesettingsheading'] = 'SELMA course settings';
$string['selmacoursesettingsheading::description'] = 'SELMA settings relating to course creation.';
$string['newcoursecat'] = 'New course category';
$string['newcoursecat::description'] = 'Which category a new course is placed in when created via SELMA.';
$string['excludecoursecat'] = 'Exclude Categories';
$string['excludecoursecat::description'] = 'Which categories should be ignored by SELMA. Child categories do <strong>not</strong> inherit parent\'s exclusion status.<br>
                                            For example, this is used for the list of courses shown in SELMA that\'s retieved from this site.<br>
                                            Use Ctrl (Windows) or ⌘ (Mac) to select multiple categories.';
$string['creategroups'] = 'Create \'groups\' from \'intakes\'';
$string['creategroups::description'] = 'If enabled, the plugin will put users into the respective course group based on which intake they belong to in SELMA.
                                        Otherwise, users are simply enrolled into the course (no groups).';
$string['selmacoursetags'] = 'Default course tags';
$string['selmacoursetags::description'] = 'Comma-separated text to include as tags in courses created via this plugin.<br>
                                            Select placeholders are also available that will be dynamically replaced with real content.<br>
                                            <strong>e.g.</strong><br>
                                            "{{fullname}},{{shortname}},selma,course"<br>Converted to individual tags: "Course Name", "courseshortname", "selma" & "course"';
$string['usersettings'] = 'User settings';
$string['userdefaultsheading'] = 'Default user preferences';
$string['userdefaultsheading::description'] = 'Default user preferences as set-up in Moodle is used by this plugin when creating new users.<br><br>
                                                You can find the \'User default preferences\' here:<br>
                                                <a href="' . $CFG->wwwroot . '/admin/settings.php?section=userdefaultpreferences">User default preferences</a>';
$string['profilemapheading'] = 'User profile field mapping';
$string['profilemapheading::description'] = 'Below you can specify which SELMA user profile field is mapped to which Moodle profile field. Custom profile fields are also supported.';
$string['profilemap_duplicatewarning'] = '<span class="settingwarning">WARNING - Duplicates detected!</span>';
$string['profilemap_duplicatewarning::description'] = '<span class="settingwarning">Duplicates values detected: {$a}</span><hr>';
$string['profilemapduplicate'] = '<div class="alert alert-danger">Duplicate value!</div>';
$string['profilemap_username'] = 'SELMA username';
$string['profilemap_username::description'] = '<sup class="settingwarning">Enforced to preserve data integrity.</sup>';
$string['profilemap_forename'] = 'SELMA forename';
$string['profilemap_forename::description'] = '<sup class="settingwarning">Enforced to preserve data integrity.</sup>';
$string['profilemap_lastname'] = 'SELMA lastname';
$string['profilemap_lastname::description'] = '<sup class="settingwarning">Enforced to preserve data integrity.</sup>';
$string['profilemap_email1'] = 'SELMA email1';
$string['profilemap_email1::description'] = '<sup class="settingwarning">Enforced to preserve data integrity.</sup>';
$string['profilemap_id'] = 'SELMA ID';
$string['profilemap_id::description'] = '<sup class="settingwarning">Enforced to preserve data integrity.</sup>';
$string['profilemap_mobilephone'] = 'SELMA mobilephone';
$string['profilemap_mobilephone::description'] = 'Which Moodle field the SELMA mobilephone field can be mapped to.';
$string['profilemap_secondaryphone'] = 'SELMA secondaryphone';
$string['profilemap_secondaryphone::description'] = 'Which Moodle field the SELMA secondaryphone field can be mapped to.';
$string['profilemap_gender'] = 'SELMA gender';
$string['profilemap_gender::description'] = 'Which Moodle field the SELMA gender field can be mapped to.';
$string['profilemap_dob'] = 'SELMA dob';
$string['profilemap_dob::description'] = 'Which Moodle field the SELMA dob field can be mapped to.';
$string['profilemap_nsn'] = 'SELMA nsn';
$string['profilemap_nsn::description'] = 'Which Moodle field the SELMA nsn field can be mapped to.';
$string['profilemap_status'] = 'SELMA status';
$string['profilemap_status::description'] = 'Which Moodle field the SELMA status field can be mapped to.';
$string['profilemap_preferredname'] = 'SELMA preferredname';
$string['profilemap_preferredname::description'] = 'Which Moodle field the SELMA preferredname field can be mapped to.';


// Capabilities.
$string['selma:config'] = "Configure SELMA";
$string['selma:manage'] = "Manage SELMA";

// Web services.
$string['create_course::description'] = '***WIP - Creates a single course.***';
$string['create_course_parameters'] = 'Expected parameters to create a course.';
$string['create_course_parameters::course'] = 'Object of course to create.';
$string['create_course_parameters::fullname'] = 'Name of course to create.';
$string['create_course_parameters::shortname'] = 'Short (unique) name of course to create - also visible to users.';
$string['create_course_parameters::idnumber'] = 'Additional (unique) identifier for course - usually used for reports/integrations.';
$string['create_course_returns'] = 'The returned values after attempting to create course.';
$string['create_course_returns::status'] = 'Returns success status code.';
$string['create_course_returns::courseid'] = 'Returns the created course ID.';
$string['create_course_returns::message'] = 'Message to return along with the response.';
$string['get_all_courses::description'] = '***WIP - Get a list of active courses in Moodle.***';
$string['get_all_courses_parameters'] = 'Expected parameters to get active course.';
$string['get_all_courses_parameters::amount'] = 'Expected parameters to get active course.';
$string['get_all_courses_parameters::page'] = 'Expected parameters to get active course.';
$string['get_all_courses_returns'] = 'Returned values for requesting all active courses (ignores excluded categories - setting).';
$string['get_all_courses_returns::status'] = 'Returns success status code.';
$string['get_all_courses_returns::courses'] = 'Array of courses and each\'s details.';
$string['courses::course_structure'] = 'Course details.';
$string['courses::id'] = 'Course id (in DB).';
$string['courses::shortname'] = 'Course shortname - must be unique.';
$string['courses::fullname'] = 'Full name of course.';
$string['courses::idnumber'] = 'Course idnumber - usually used in reports, etc.';
$string['get_all_courses_returns::nextpage'] = 'The next page to be requested (if any).';
$string['get_all_courses_returns::message'] = 'Message to return along with the response.';
$string['create_users::description'] = '***WIP - Creates an array of users.***';
$string['create_users_parameters'] = 'Expected parameters to create users.';
$string['create_users_parameters::users'] = 'List of all users\' details - to be created.';
$string['create_users_parameters::user'] = 'A user\'s details - needed to create.';
$string['create_users_parameters::username'] = 'Username of user. Uses incremental \'firstname.lastname\' format, if none provided.';
$string['create_users_parameters::forename'] = 'First name of user.';
$string['create_users_parameters::lastname'] = 'Last name of user.';
$string['create_users_parameters::preferredname'] = 'Preferred name/Nickname of user.';
$string['create_users_parameters::email1'] = 'Email address of user.';
$string['create_users_parameters::mobilephone'] = 'Mobile phone number of user.';
$string['create_users_parameters::secondaryphone'] = 'Phone number of user.';
$string['create_users_parameters::gender'] = 'Gender of user.';
$string['create_users_parameters::dob'] = 'Date of Birth of user.';
$string['create_users_parameters::nsn'] = 'NSN of user.';
$string['create_users_parameters::id'] = 'ID number (SELMA) of user.';
$string['create_users_parameters::status'] = 'Status of user.';
$string['create_users_returns'] = 'The returned values after attempting to create user(s).';
$string['create_users_returns::status'] = 'Returns success status code.';
$string['create_users_returns::userids'] = 'Returns the user Moodle IDs of the users that were created.';
$string['create_users_returns::userid'] = 'Moodle ID of a user that was created.';
$string['create_users_returns::message'] = 'Message to return along with the response. Lists duplicate SELMA IDs or emails found.';
$string['create_intake::description'] = '***WIP - Expected parameters to create an intake record.***';
$string['create_intake_parameters'] = 'Expected parameters to create an intake record.';
$string['create_intake_parameters::intake'] = 'Intake details.';
$string['create_intake_parameters::intakeid'] = 'Intake SELMA ID.';
$string['create_intake_parameters::programmeid'] = 'Programme ID that intake is associated to.';
$string['create_intake_parameters::intakecode'] = 'Intake code.';
$string['create_intake_parameters::intakename'] = 'Intake name.';
$string['create_intake_parameters::intakestartdate'] = 'Intake\'s start date.';
$string['create_intake_parameters::intakeenddate'] = 'Intake\'s end date.';
$string['create_intake_returns'] = 'Returned values when calling this function.';
$string['create_intake_returns::status'] = 'Returns success status code.';
$string['create_intake_returns::intakeid'] = 'Returns created intake\'s ID.';
$string['create_intake_returns::message'] = 'Message to return along with the response.';
$string['add_intake_to_course::description'] = '***WIP - Expected parameters to add a SELMA intake to a Moodle course.***';
$string['add_intake_to_course_parameters'] = 'Expected parameters to add an intake to a course.';
$string['add_intake_to_course_parameters::intakeid'] = 'SELMA intake ID to be added to course.';
$string['add_intake_to_course_parameters::courseid'] = 'Moodle course ID the intake should be added to.';
$string['add_intake_to_course_returns'] = 'Returned values when calling this function.';
$string['add_intake_to_course_returns::status'] = 'Returns success status code.';
$string['add_intake_to_course_returns::added'] = 'Boolean of whether the intake was added or not';
$string['add_intake_to_course_returns::message'] = 'Message returned along with the response.';
$string['add_user_to_intake::description'] = '***WIP - Adds a user to an intake in Moodle.***';
$string['add_user_to_intake_parameters'] = 'Parameters required to add a user to an intake.';
$string['add_user_to_intake_parameters::userid'] = 'SELMA user ID.';
$string['add_user_to_intake_parameters::intakeid'] = 'SELMA intake ID.';
$string['add_user_to_intake_returns'] = 'The returned values after attempting to a user to an intake.';
$string['add_user_to_intake_returns::status'] = 'Returns success status code.';
$string['add_user_to_intake_returns::added'] = 'Bool of whether the user could be added or not.';
$string['add_user_to_intake_returns::message'] = 'Message returned along with the response.';

// Web services warnings.
$string['warning_code_notcapable'] = 'notcapable';
$string['warning_message_notcapable'] = 'This user does not have the following capability: \'{$a}\'.';
$string['warning_code_noconfig'] = 'noconfig';
$string['warning_message_noconfig'] = 'This config has not been set yet: \'{$a}\'.';
$string['error_noconfig'] = 'This config has not been set yet: \'{$a}\'.';

// Web services statuses.
$string['status_ok'] = '200';
$string['status_ok_message'] = 'OK - Expected response received.';
$string['status_nocontent'] = '204';
$string['status_nocontent_message'] = 'No content to return - processed successfully, but intended result not achieved.';
$string['status_almostok'] = '206';
$string['status_almostok_message'] = 'Partially OK - Some part of the operation could not be completed.';
$string['status_almostok_existing_message'] = 'Found existing records for: {$a}';
$string['status_nonew'] = '208';
$string['status_nonew_message'] = 'Already Reported - Nothing inherently wrong with request, but all this has been processed before.';
$string['status_forbidden'] = '403';
$string['status_forbidden_message'] = 'Forbidden - That\'s not allowed.';
$string['status_notfound'] = '404';
$string['status_notfound_message'] = 'Not Found - 0 Records found.';
$string['status_notfound_detailed_message'] = ' Not found: \'{$a}\'.';
$string['status_internalfail'] = '500';
$string['status_internalfail_message'] = 'Internal Failure - Failed to complete operation.';
$string['status_other'] = '303';
$string['status_other_message'] = 'Other - Unexpected error/result received.';
$string['nomessage'] = 'No response message.';

// Custom additional detailed messages for status messages.
$string['forbidden_instance_add'] = ' Can not add more than one enrol_selma instance to course \'{$a}\'.';
$string['forbidden_group_add'] = ' Failed to create group for intake \'{$a->intake}\' in course \'{$a->course}\'.';

// CLI.
$string['clihelp'] = 'Enrol SELMA plugin CLI script to update webservice functions without full Moodle upgrade.
Please note you must execute this script with the same uid as apache!
Options:
-nv, --no-verbose       Disables output to the console.
-h, --help              Print out this help.
-pl, --print-logo       Prints a cool CLI logo if available.
Example:
Run script with default parameters  - \$sudo -u www-data /usr/bin/php upgrade.php
';
$string['cliheading'] = '{$a} - CLI script that refreshes/add new webservice functions.';
$string['noverbose'] = '  Verbose output has been disabled.';
$string['verbose'] = '  Verbose output has been enabled.';
$string['servertime'] = '  Server Time: {$a}';
$string['updatefunctions'] = '    Updating the SELMA webservices functions without going through whole Moodle upgrade process.';
$string['updatefunctionsdone'] = '    The new webservice functions should be visible.';
$string['executiontime'] = '  Script execution took {$a} seconds.';

$string['characterlengthexceeded'] = 'Character length exceeded expected \'{$a-expected}\' received \'{$a->recieved}\'';
$string['maximumcharacterlengthexceeded'] = 'Maximum character length exceeded';
$string['maximumcharacterlengthforexceeded'] = 'Maximum character length for \'{$a}\' exceeded';
$string['unexpectedvalue'] = 'Unexpected value for \'{$a}\'';
$string['invalidargument'] = 'Invalid argument for \'{$a}\'';
$string['invalidargumentexpects'] = 'Invalid argument for \'{$a->name}\' expects type \'{$a->type}\'';

$string['servicerolename'] = 'Web service';
$string['serviceroledescription'] = 'Used by external systems to communicate with Moodle.';
$string['serviceaccountfirstname'] = 'SELMA';
$string['serviceaccountlastname'] = 'Service Account';
$string['serviceconnectiondetails::subject'] = 'SELMA service connection setup details';
$string['serviceconnectiondetails::message'] = '
SELMA service connection setup details

Service account username: {$a->username}
Service account password: {$a->password}
Service account token:    {$a->token}

Please login {$a->site} and accept any privacy policies and reset password.
';

// Exceptions.
$string['exception_bepositive'] = "Parameters must be positive values";
$string['duplicateemailaddressesnotallowed'] = 'Duplicate email addresses not allowed by configuration';
$string['studentsinglestructure'] = 'External data structure for a student.';
$string['middlename'] = 'Middle name';
$string['preferredname'] = 'Preferred name';
$string['email'] = 'Email address';
$string['mobilephone'] = 'Mobile phone number';
$string['secondaryphone'] = 'Phone number';
$string['gender'] = 'Gender of user';
$string['dateofbirth'] = 'Date of Birth';
$string['nsn'] = 'National Student Number';
$string['studentid'] = 'Student ID number';
$string['otherid'] = 'Other ID number';
$string['moodleuserid'] = 'Moodle User ID';
$string['noclasspropertymutator'] = 'No class property mutator for property {$a->propertyt} in class {$a->class}.';
