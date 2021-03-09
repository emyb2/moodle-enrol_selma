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
 * Class to represent a User.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use enrol_selma\local\utilities;
use moodle_exception;
use profile_field_base;
use stdClass;

require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/enrol/selma/locallib.php');

/**
 * Class to represent a User. Extends stdClass and has public properties, but use setters to enforce type.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user extends stdClass {

    /** @var int $id User ID. */
    public $id = 0;

    /** @var string $auth Authentication method. */
    public $auth = 'manual';

    public $confirmed = 1;

    public $mnethostid = 0;


    public $newpassword;

    /** @var string $username User username. */
    public $username;

    /** @var string $firstname User first name. */
    public $firstname;

    /** @var string $lastname User last name. */
    public $lastname;

    /** @var string $email User email address. */
    public $email;

    /** @var int $idnumber User SELMA ID number. */
    public $idnumber = '';

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

    /** @var string $lang User language. */
    public $lang;

    /** @var  */
    public $calendartype;

    /** @var string $middlename User middle name. */
    public $middlename = '';

    /** @var string $alternatename User alternate name. */
    public $alternatename = '';

    public $mailformat;

    public $maildigest;

    public $maildisplay;

    public $autosubscribe;

    public $trackforums;

    public $timecreated;

    public $timemodified;

    public function __set($name, $value) {
        if (strpos($name, 'profile_field_') === 0) {
            $this->set_profile_field($name, $value);
        }
    }

    public function set_id(int $id) : self {
        utilities::check_length('user', 'id', $id);
        $this->id = $id;
        return $this;
    }

    public function set_username(string $username) : self {
        if ($username !== clean_param($username, PARAM_USERNAME)) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'username');
        }
        utilities::check_length('user', 'username', $username);
        $this->username = $username;
        return $this;
    }

    public function set_firstname(string $firstname) : self {
        utilities::check_length('user', 'firstname', $firstname);
        $this->firstname = $firstname;
        return $this;
    }

    public function set_lastname(string $lastname) : self {
        utilities::check_length('user', 'lastname', $lastname);
        $this->lastname = $lastname;
        return $this;
    }

    public function set_email(string $email) : self {
        if (!validate_email($email)) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'email');
        }
        utilities::check_length('user', 'email', $email);
        $this->email = $email;
        return $this;
    }

    public function set_idnumber(string $idnumber) : self {
        utilities::check_length('user', 'idnumber', $idnumber);
        $this->idnumber = $idnumber;
        return $this;
    }

    public function set_phone1(string $phone1) : self {
        utilities::check_length('user', 'phone1', $phone1);
        $this->phone1 = $phone1;
        return $this;
    }

    public function set_phone2(string $phone2) : self {
        utilities::check_length('user', 'phone2', $phone2);
        $this->phone2 = $phone2;
        return $this;
    }

    /**
     * Set a user profile field. Use the fields preprocess method if available.
     *
     * @param   string              $name Name of property.
     * @param   mixed               $value Property's value.
     * @return  $this               This user object.
     * @throws  moodle_exception
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
