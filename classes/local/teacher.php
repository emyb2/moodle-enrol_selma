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
 * Class to represent a teacher.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use moodle_exception;
use enrol_selma\local\user;

require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/enrol/selma/locallib.php');

/**
 * Class to represent a SELMA teacher. Extends user and has public properties, but use setters to enforce type.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class teacher extends user {
    /**
     * Method for saving teacher to database. Provides extra teacher-specific property checks and then hands off to 'user' class.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public function save() {
        global $CFG, $DB;
        // Check minimum required properties exists and have a value.
        if (!property_exists($this, 'profile_field_teacherid') || trim($this->profile_field_teacherid) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'profile_field_teacherid');
        }

        // If trying to create a user.
        if ($this->id <= 0) {
            // Unique ID number check.
            // Get the ID of the 'teacherid' field and then check if the user exists already.
            $fieldid = $DB->get_record('user_info_field', array('shortname' => 'teacherid'), 'id');

            // The 'teacherid' custom profile field needs to exist if you want to create teachers.
            if ($fieldid === false) {
                throw new moodle_exception('exception_fieldnotexist', 'enrol_selma', null, 'profile_field_teacherid');
            }

            $select = sprintf("fieldid = :fieldid AND %s = :data", $DB->sql_compare_text('data'));

            // Check if a user already has this 'teacherid'.
            $teacheruser = $DB->get_record_select(
                'user_info_data',
                $select,
                array('fieldid' => $fieldid->id, 'data' => $this->profile_field_teacherid)
            );

            // Make sure the user still exists and has not been deleted.
            if ($teacheruser !== false) {
                $exists = $DB->record_exists('user',
                    ['id' => $teacheruser->userid, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0]
                );

                if ($exists) {
                    // TODO Provide a better explanation in exception.
                    throw new moodle_exception(
                        'exception_existsalready',
                        'enrol_selma',
                        null,
                        'profile_field_teacherid = ' . $this->profile_field_teacherid
                    );
                }
            }
        }

        // If they don't exist, continue with creation.
        parent::save();
    }
}
