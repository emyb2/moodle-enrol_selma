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
 * Deals with Moodle events that affect the plugin.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core\event\course_deleted;
use core\event\group_deleted;
use dml_exception;

/**
 * Class to deal with Moodle events that affect the plugin.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_observer {

    /**
     * Performs plugin-specific tasks when a course is deleted.
     *
     * @param   course_deleted      $event Course deleted event.
     * @return  bool                Returns true.
     * @throws  coding_exception
     * @throws  dml_exception
     */
    public static function course_deleted(course_deleted $event) {
        global $DB;
        $course = $event->get_record_snapshot($event->objecttable, $event->objectid);
        // Delete course intake(s).
        $DB->delete_records(
            'enrol_selma_course_intake',
            ['courseid' => $course->id]
        );
        return true;
    }

    /**
     * Performs plugin-specific tasks when a course is deleted.
     *
     * @param   group_deleted       $event Group deleted event.
     * @return  bool                Returns true.
     * @throws  coding_exception
     * @throws  dml_exception
     */
    public static function group_deleted(group_deleted $event) {
        global $DB;
        $group = $event->get_record_snapshot($event->objecttable, $event->objectid);
        // Reset groupid to zero.
        $DB->set_field(
            'enrol_selma_course_intake',
            'groupid',
            0,
            ['groupid' => $group->id]
        );
        return true;
    }

}
