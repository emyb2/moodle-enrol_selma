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
 * The enrol plugin selma is defined here.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// The base class 'enrol_plugin' can be found at lib/enrollib.php. Override
// methods as necessary.

require_once(__DIR__ . '/vendor/autoload.php');
/**
 * Class enrol_selma_plugin.
 */
class enrol_selma_plugin extends enrol_plugin {

    /**
     * Does this plugin allow manual enrolments?
     *
     * All plugins allowing this must implement 'enrol/selma:enrol' capability.
     *
     * @param stdClass $instance Course enrol instance.
     * @return bool True means user with 'enrol/selma:enrol' may enrol others freely, false means nobody may add more enrolments manually.
     */
    public function allow_enrol($instance) {
        return false;
    }

    /**
     * Does this plugin allow manual unenrolment of all users?
     *
     * All plugins allowing this must implement 'enrol/selma:unenrol' capability.
     *
     * @param stdClass $instance Course enrol instance.
     * @return bool True means user with 'enrol/selma:unenrol' may unenrol others freely, false means nobody may touch user_enrolments.
     */
    public function allow_unenrol($instance) {
        return false;
    }

    /**
     * Does this plugin allow manual changes in user_enrolments table?
     *
     * All plugins allowing this must implement 'enrol/selma:manage' capability.
     *
     * @param stdClass $instance Course enrol instance.
     * @return bool True means it is possible to change enrol period and status in user_enrolments table.
     */
    public function allow_manage($instance) {
        return true;
    }

    /**
     * Does this plugin allow manual unenrolment of a specific user?
     *
     * All plugins allowing this must implement 'enrol/selma:unenrol' capability.
     *
     * This is useful especially for synchronisation plugins that
     * do suspend instead of full unenrolment.
     *
     * @param stdClass $instance Course enrol instance.
     * @param stdClass $ue Record from user_enrolments table, specifies user.
     * @return bool True means user with 'enrol/selma:unenrol' may unenrol this user, false means nobody may touch this user enrolment.
     */
    public function allow_unenrol_user($instance, $ue) {
        return false;
    }

    /**
     * Use the standard interface for adding/editing the form.
     *
     * @since Moodle 3.1.
     * @return bool.
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Adds form elements to add/edit instance form.
     *
     * @since Moodle 3.1.
     * @param object $instance Enrol instance or null if does not exist yet.
     * @param MoodleQuickForm $mform.
     * @param context $context.
     * @return void
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        // Do nothing by default.
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @since Moodle 3.1.
     * @param array $data Array of ("fieldname"=>value) of submitted data.
     * @param array $files Array of uploaded files "element_name"=>tmp_file_path.
     * @param object $instance The instance data loaded from the DB.
     * @param context $context The context of the instance we are editing.
     * @return array Array of "element_name"=>"error_description" if there are errors, empty otherwise.
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        // No errors by default.
        debugging('enrol_plugin::edit_instance_validation() is missing. This plugin has no validation!', DEBUG_DEVELOPER);
        return array();
    }

    /**
     * Single instance add programmatically. One instance per course.
     *
     * @param int $courseid.
     * @return bool.
     */
    public function can_add_instance($courseid) {
        return false;
    }

    /**
     * Add new instance of SELMA enrol plugin.
     * @param object $course
     * @param array $fields instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = null) {
        global $DB;
        // We can add to $fields here later, if needed.

        // Check if we can really add an instance to the course.
        $enrolinstance = $DB->record_exists('enrol', array('courseid' => $course->id, 'enrol' => $this->get_name()));

        // Return null if an instance already exists for the course.
        if ($enrolinstance) {
            return null;
        }

        // Otherwise, continue adding the instance to the course.

        return parent::add_instance($course, $fields);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        return false;
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass  $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        return true;
    }
}
