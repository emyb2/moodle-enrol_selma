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

$string['applicationlog'] = 'Application Log';
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
$string['logging'] = 'Logging';
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