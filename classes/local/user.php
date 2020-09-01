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

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use stdClass;

require_once(dirname(__FILE__, 5) . '/user/profile/lib.php');
require_once(dirname(__FILE__, 3) . '/locallib.php');

/**
 * Class to represent a User. Extends stdClass and has public properties but
 * use setters to enforce type.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user extends stdClass {

    /** @var int $id User ID. */
    public $id;

    /**
     * Class 'user' constructor. Sets up class with user profile fields as properties.
     *
     * @param   int $id User's Moodle id.
     */
    function __construct(int $id = 0) {
        // Set id so we know if we're creating a new user (0) or editing an existing user (>0)
        $this->id = $id;

        // TODO - Better checking if user exists - check email ('allowaccountssameemail'), Moodle ID & SELMA ID.

        // If Moodle ID is given, we're to load an existing user.
        if ($id !== 0) {
            enrol_selma_user_from_id($id);
        } else {
            // Get all the user fields we deal with.
            $properties = enrol_selma_get_allowed_user_fields();

            // Set up all properties dynamically (All strings currently).
            foreach ($properties as $property) {
                $this->$property = '';
            }
        }
    }

    /**
     * Set a given property.
     *
     * @param   string  $property User's property - DB field.
     * @param   string  $value Value property should be set to.
     * @return  user    $this Return the user object.
     */
    public function set_property(string $property, string $value) : self {
        // Limit properties to only allowed fields.
        if (!in_array($property, enrol_selma_get_blacklisted_user_fields())) {
            $this->$property = $value;
        }
        return $this;
    }

    /**
     * Set given properties.
     *
     * @param   array   $properties Associative array of user's property - DB field - as key and it's intended value as the value.
     * @return  user    $this Return the user object.
     */
    public function set_properties(array $properties) : self {
        foreach ($properties as $property => $value) {
            // We don't accept null values.
            if ($value !== null) {
                $this->set_property($property, $value);
            }
        }
        return $this;
    }

    /**
     * Update the DB with the current properties & values.
     *
     * @return bool $saved.
     */
    public function save() :self {
        global $DB, $CFG;

        // TODO - Should we remove any entries with empty value? I think so.

        // If id is 0, we're adding a new user.
        if ($this->id === 0) {
            // We only support local accounts.
            $this->mnethostid = $CFG->mnet_localhost_id;

            // TODO - increment username?
            // Check if username exists and increment, if necessary.
            if ($DB->get_record('user', array('username' => $this->username)) !== false) {
                $this->username = uu_increment_username($this->username);
            }

            // TODO - check for duplicate emails - respect setting 'allowaccountssameemail'.

            $saved = user_create_user($this);
            $this->id = $saved;
        } else {
            // Anything else means we're updating (or trying to, at least).
            // TODO - check for duplicate emails - respect setting 'allowaccountssameemail'. Can also update if existing is found.
            // Function returns nothing, so update and set $saved for later use.
            user_update_user($this);
            $saved = $this->id;
        }

        // Add custom profilefields for user.
        $customfields = enrol_selma_get_custom_profile_fields();
        $customproperties = [];

        foreach ($customfields as $customfield) {
            // We just need the shortname.
            $shortname = str_replace('profile_field_', '', $customfield);

            $customproperties[$shortname] = $this->$customfield;
        }

        // Save the custom fields.
        profile_save_custom_fields($saved, $customproperties);

        // throw new dml_read_exception();

        return $this;
    }

    /**
     * Get all of a user's custom profile field data.
     *
     * @param   int     $id User's Moodle ID.
     * @return  array   $customfields Associative array with customfield's shortname as key and user's data as value.
     */
    public function get_user_custom_field_data($id) {
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
     * Creates record in DB of relationship between user & intake.
     *
     * @param   int     $intakeid Intake ID user should be added to.
     * @return  bool    $inserted Bool indicating success/failure of inserting record to DB.
     */
    public function add_user_to_intake($intakeid) {
        global $DB, $USER;

        // Contruct data object for DB.
        $data = new stdClass();
        $data->userid = $this->id;
        $data->intakeid = $intakeid;
        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();

        $inserted = $DB->insert_record('enrol_selma_user_intake', $data, false);

        return $inserted;
    }
}
