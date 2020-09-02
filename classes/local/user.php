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
    public $id = 0;

    /** @var string $username User username. */
    public $username = '';

    /** @var string $firstname User first name. */
    public $firstname = '';

    /** @var string $lastname User last name. */
    public $lastname = '';

    /** @var string $email User email address. */
    public $email = '';

    /** @var int $idnumber User SELMA ID number. */
    public $idnumber = 0;

    /** @var string $phone1 User primary phone number. */
    public $phone1 = '';

    /** @var string $phone2 User secondary phone number. */
    public $phone2 = '';

    /** @var string $institution User institution. */
    public $institution = '';

    /** @var string $department User department. */
    public $department = '';

    /** @var string $address User address. */
    public $address = '';

    /** @var string $city User city. */
    public $city = '';

    /** @var string $country User country. */
    public $country = '';

    /** @var string $description User description. */
    public $description = '';

    /** @var string $middlename User middle name. */
    public $middlename = '';

    /** @var string $alternatename User alternate name. */
    public $alternatename = '';

    /**
     * Class 'user' constructor. Sets up class with user profile fields as properties.
     */
    function __construct() {

        // TODO - Better checking if user exists - check email ('allowaccountssameemail'), Moodle ID & SELMA ID?
        // TODO - Load custom profile fields?
    }

    /**
     * Set a given property.
     *
     * @param   string  $property User's property - DB field.
     * @param   mixed   $value Value property should be set to.
     * @return  user    $this Return the user object.
     */
    public function set_property(string $property, $value) : self {
        // TODO - check propety type.
        //utilities::get_debug_type();

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
}
