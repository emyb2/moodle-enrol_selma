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
 * Events this plugin needs to observe.
 *
 * @package     enrol_selma
 * @category    event
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    // Watch for course deletion.
    [
        'eventname'   => '\core\event\course_deleted',
        'callback'    => '\enrol_selma\local\event_observer::course_deleted',
        'includefile' => null,
        'internal'    => false,
        'priority'    => 9999
    ],
    // Watch for group deletion.
    [
        'eventname'   => '\core\event\group_deleted',
        'callback'    => '\enrol_selma\local\event_observer::group_deleted',
        'includefile' => null,
        'internal'    => false,
        'priority'    => 9999
    ],
];
