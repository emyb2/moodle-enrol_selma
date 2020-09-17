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
 * Class to represent a Moodle Course.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use moodle_exception;
use profile_field_base;
use stdClass;
use enrol_selma\local\utilities;

require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/enrol/selma/locallib.php');

/**
 * Class to represent a Moodle Course. Extends stdClass and has public properties, but use setters to enforce type.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course extends stdClass {

    /** @var int $id Course ID. */
    public $id = 0;

    /** @var int $category Category ID where course is/will be. */
    public $category;

    /** @var string $fullname Course fullname. */
    public $fullname;

    /** @var string $shortname Course shortname (unique). */
    public $shortname;

    /** @var string $idnumber Course idnumber (unique). */
    public $idnumber;

    /** @var string $summary Course description. */
    public $summary;

    /** @var int $timecreated Course creation epoch. */
    public $timecreated;

    /** @var int $timemodified Course last modified epoch. */
    public $timemodified;

    /**
     * Set (any) course field.
     *
     * @param   string              $name   Name of property.
     * @param   mixed               $value  Value of property.
     * @throws  moodle_exception
     */
    public function __set(string $name, $value) {
        if (strpos($name, 'profile_field_') === 0) {
            $this->set_profile_field($name, $value);
        }
    }

    /**
     * Set id property. Auto-increments, so length should be fine.
     *
     * @param   int     $id ID value.
     * @return  course
     */
    public function set_id(int $id) : self {
        $this->id = $id;
        return $this;
    }

    /**
     * Set ID of course's parent category.
     *
     * @param   int                 $category ID course's parent category.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_category(int $category): self {
        (new utilities)->check_length('course', 'category', $category);
        $this->category = $category;
        return $this;
    }

    /**
     * Set course's fullname.
     *
     * @param   string              $fullname Course's full textual name.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_fullname(string $fullname): self {
        (new utilities)->check_length('course', 'fullname', $fullname);
        $this->fullname = $fullname;
        return $this;
    }

    /**
     * Set course's shortname (unique).
     *
     * @param   string              $shortname Course's shortname - must be unique.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_shortname(string $shortname): self {
        (new utilities)->check_length('course', 'shortname', $shortname);
        $this->shortname = $shortname;
        return $this;
    }

    /**
     * Set course's idnumber property (unique).
     *
     * @param   string              $idnumber Course's ID number - must be unique.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_idnumber(string $idnumber): self {
        (new utilities)->check_length('course', 'idnumber', $idnumber);
        $this->idnumber = $idnumber;
        return $this;
    }

    /**
     * Set course's description/summary property.
     *
     * @param   string              $summary Description for course.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_summary(string $summary): self {
        (new utilities)->check_length('course', 'summary', $summary);
        $this->summary = $summary;
        return $this;
    }

    /**
     * Set course's timecreated property.
     *
     * @param   int                 $timecreated Epoch of course's creation.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_timecreated(int $timecreated): self {
        (new utilities)->check_length('course', 'timecreated', $timecreated);
        $this->timecreated = $timecreated;
        return $this;
    }

    /**
     * Set course's timemodified property.
     *
     * @param   int                 $timemodified Epoch of last modification to course.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_timemodified(int $timemodified): self {
        (new utilities)->check_length('course', 'timemodified', $timemodified);
        $this->timemodified = $timemodified;
        return $this;
    }

    /**
     * Set a user profile field. Use the fields preprocess method if available.
     *
     * @param string $name
     * @param $value
     * @return $this
     * @throws moodle_exception
     */
    public function set_profile_field(string $name, $value) {
        global $CFG;
        static $customprofilefields = null;
        if (is_null($customprofilefields)) {
            $customprofilefields = [];
            foreach (profile_get_custom_fields() as $profilefield) {
                $customprofilefields[$profilefield->shortname] = $profilefield;
            }
        }
        $shortname = str_replace('profile_field_', '', $name);
        if (!in_array($shortname, array_keys($customprofilefields))) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'name');
        }
        $field = $customprofilefields[$shortname];
        $classfilepath = $CFG->dirroot . '/user/profile/field/' . $field->datatype . '/field.class.php';
        if (file_exists($classfilepath)) {
            require_once($classfilepath);
            $profilefieldname = 'profile_field_' . $field->shortname;
            $classname = 'profile_field_' . $field->datatype;
            /** @var profile_field_base $profilefield */
            $profilefield = new $classname($field->id, $this->id, $field);
            if (method_exists($profilefield, 'edit_save_data_preprocess')) {
                $this->{$profilefieldname} = $profilefield->edit_save_data_preprocess($value, null);
            } else {
                $this->{$profilefieldname} = $value;
            }
        }
        return $this;
    }

    /**
     * Method for saving user to database. Provides extra property checks and then hands off to core user_create_user
     * and user_update_user functions. Also saves user profile field data.
     *
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public function save() {
        global $CFG, $DB;
        // Check minimum required properties have a value.
        if (trim($this->firstname) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'firstname');
        }
        if (trim($this->lastname) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'lastname');
        }
        if (trim($this->email) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'email');
        }
        if (trim($this->idnumber) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'idnumber');
        }
        $this->password = $this->newpassword;
        if ($this->id <= 0) {
            // Email duplicates check.
            $allowaccountssameemail = $CFG->allowaccountssameemail ?? 0;
            if (!$allowaccountssameemail) {
                // Make a case-insensitive query for the given email address.
                $select = $DB->sql_equal('email', ':email', false) .
                    ' AND mnethostid = :mnethostid AND deleted <> :deleted';
                $params = array(
                    'email' => $this->email,
                    'mnethostid' => $CFG->mnet_localhost_id,
                    'deleted' => 1
                );
                if ($DB->record_exists_select('user', $select, $params)) {
                    throw new moodle_exception('duplicateemailaddressesnotallowed', 'enrol_selma');
                }
            }
            // Unique ID number check.
            $exists = $DB->record_exists('user', ['idnumber' => $this->idnumber, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0]);
            if ($exists) {
                throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'idnumber'); // @todo Provide a better explaination in exception.
            }
            if (empty($this->username)) {
                $username = utilities::generate_username($this->firstname, $this->lastname);
                $this->set_username($username);
            }
            $this->mnethostid = $CFG->mnet_localhost_id; // Always set to local for a new user.
            $this->id = user_create_user($this, false, true);
            set_user_preference('enrol_selma_new_student_create_password', 1, $this);
        } else {
            user_update_user($this);
            if ($this->newpassword) {
                set_user_preference('auth_forcepasswordchange', 1, $this);
            }
        }
        profile_save_data($this);
        return true;
    }
}
