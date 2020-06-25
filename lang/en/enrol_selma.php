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
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'SELMA';

$string['applicationlog'] = 'Application Logs';
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
$string['logging'] = 'Logging Settings';
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
$string['autoadd'] = 'Auto-add to new courses';
$string['autoadd::description'] = 'Automatically add the plugin to new courses.';
$string['expiredaction'] = 'Enrolment expired action';
$string['expiredaction::description'] = 'Select action to carry out when user enrolment expires. Please note: some user data purged from course during unenrolment.';
$string['expirednotifhour'] = 'Expiry notification sent at (hour)';
$string['expirednotifhour::description'] = 'The reminder sent to the user regarding enrolment expiry will be sent at this hour of the day.';
$string['instancedefaults'] = 'Enrolment instance defaults';
$string['instancedefaults::description'] = 'Default settings applied to a new selma enrolment instance in a course.';
$string['defaultrole'] = 'Default role';
$string['defaultrole::description'] = 'Default role assigned to user when enrolled to course.';
$string['enrollength'] = 'Default enrolment duration';
$string['enrollength::description'] = 'Number of **days** the user should be enrolled for. (0 = unlimited).';
$string['notifyexpiry'] = 'Notify pre-expiry?';
$string['notifyexpiry::description'] = 'Should a user be alerted of their upcoming enrolment expiry?';
$string['notifypreexpiry'] = 'Notification threshold';
$string['notifypreexpiry::description'] = 'How long beforehand should the user be notified? In number of **days**.';
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
$string['selmacoursesettingsheading'] = 'Course settings';
$string['selmacoursesettings'] = 'SELMA course settings';
$string['selmacoursesettings::description'] = 'Settings relating to courses created by SELMA.';
$string['newcoursecat'] = 'New course category';
$string['newcoursecat::description'] = 'Which category a new course is placed in when created via SELMA.';
$string['creategroups'] = 'Create \'groups\' from \'intakes\'';
$string['creategroups::description'] = 'If enabled, the plugin will put users into the respecttive course group based on which intake they belong to in SELMA.
                                        Otherwise, users are simply enrolled into the course (no groups).';
