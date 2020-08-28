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

    /**
     * Class 'user' constructor. Sets up class with user profile fields as properties.
     */
    function __construct() {
        $properties = enrol_selma_get_allowed_user_fields();

        foreach ($properties as $property) {
            $this->$property = '';
        }
    }

    /**
     * Set a given property.
     *
     * @param   string  $property User's property - DB field.
     * @param   int     $value Value property should be set to.
     * @return  user    $this Return the user object.
     */
    public function set_property(string $property, int $value) : self {
        $this->$property = $value;
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
            $this->set_property($property, $value);
        }
        return $this;
    }

    /**
     * Update the DB with the current properties & values.
     *
     * @return bool $saved.
     */
    public function save() :self {
        global $DB;

        // Insert the user with core data.
        $saved = $DB->insert_record('user', $this);

        // Add custom profilefields for user.
        $customfields = enrol_selma_get_custom_user_fields();
        $customproperties = [];

        foreach ($customfields as $customfield) {
            $customproperties[] = $this->$customfield;
        }

        profile_save_custom_fields($saved, $customproperties);

        // throw new dml_read_exception();

        return $this;
    }

    /**
     * Update the user's properties with the SELMA data.
     *
     * @param   array   $selmauser SELMA user data to be transcribed to Moodle user data.
     */
     public function  update_user_from_selma_data($selmauser) {
        // Use profile field mapping to capture user data.
        $profilemapping = enrol_selma_get_profile_mapping();

        // Assign each SELMA user profile field to the Moodle equivalent.
        foreach ($selmauser as $field => $value) {
            // Translate to Moodle field.
            $element = $profilemapping[$field];

            // Set field to value.
            $this->$element = $value;
        }
    }
}
