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

use core_text;
use moodle_exception;
use profile_field_base;
use stdClass;
use enrol_selma\local\utilities;

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

    public $auth;

    public $confirmed;

    public $mnethostid;

    /** @var string $username User username. */
    public $username;

    /** @var string $firstname User first name. */
    public $firstname;

    /** @var string $lastname User last name. */
    public $lastname;

    /** @var string $email User email address. */
    public $email;

    /** @var int $idnumber User SELMA ID number. */
    public $idnumber;

    /** @var string $phone1 User primary phone number. */
    public $phone1;

    /** @var string $phone2 User secondary phone number. */
    public $phone2;

    /** @var string $institution User institution. */
    public $institution;

    /** @var string $department User department. */
    public $department;

    /** @var string $address User address. */
    public $address;

    /** @var string $city User city. */
    public $city;

    /** @var string $country User country. */
    public $country;

    /** @var string $lang User language. */
    public $lang;

    public $calendartype;

    /** @var string $middlename User middle name. */
    public $middlename;

    /** @var string $alternatename User alternate name. */
    public $alternatename;

    public $mailformat;

    public $maildigest;

    public $maildisplay;

    public $autosubscribe;

    public $trackforums;

    private $customprofilefields;

    /**
     * user constructor.
     *
     * Others fields will be set for a new user by user_create_user function.
     *
     */
    public function __construct() {
        global $CFG;
        $this->id = 0;
        $this->auth = 'manual';
        $this->mnethostid = $CFG->mnet_localhost_id;
        $this->confirmed = 1;
        $this->phone1 = '';
        $this->phone2 = '';
        $this->institution = '';
        $this->department = '';
        $this->address = '';
        $this->city = '';
        $this->country = '';
        $this->middlename = '';
        $this->alternatename = '';
        $this->customprofilefields = [];
        foreach (profile_get_custom_fields() as $profilefield) {
            $this->customprofilefields[$profilefield->shortname] = $profilefield;
        }
    }

    protected function check_length($name, $value) {
        $column = utilities::get_column_information('user', $name);
        if (core_text::strlen($value) > $column->max_length) {
            throw new moodle_exception('maximumcharacterlengthforexceeded', 'enrol_selma', null, $name);
        }
    }

    public static function required_properties() : array {
        return [
            'firstname',
            'lastname',
            'email',
            'idnumber',
        ];
    }

    public function set_id(int $id) : self {
        $this->id = $id;
        return $this;
    }

    public function set_username(string $username) : self {
        if ($username !== clean_param($username, PARAM_USERNAME)) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'username');
        }
        $this->check_length('username', $username);
        $this->username = $username;
        return $this;
    }

    public function set_first_name(string $firstname) : self {
        $this->check_length('firstname', $firstname);
        $this->firstname = $firstname;
        return $this;
    }

    public function set_last_name(string $lastname) : self {
        $this->check_length('lastname', $lastname);
        $this->lastname = $lastname;
        return $this;
    }

    public function set_email(string $email) : self {
        global $CFG, $DB;
        if (!validate_email($email)) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'email');
        }
        $this->check_length('email', $email);
        $this->email = $email;
        return $this;
    }

    public function set_idnumber(string $idnumber) : self {
        $this->check_length('idnumber', $idnumber);
        $this->idnumber= $idnumber;
        return $this;
    }

    public function set_phone1(string $phone1) : self {
        $this->check_length('phone1', $phone1);
        $this->phone1 = $phone1;
        return $this;
    }

    public function set_phone2(string $phone2) : self {
        $this->check_length('phone2', $phone2);
        $this->phone2 = $phone2;
        return $this;
    }

    public function set_profile_field(string $name, $value) {
        global $CFG;
        $shortname = str_replace('profile_field_', '', $name);
        if (!in_array($shortname, array_keys($this->customprofilefields))) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'name');
        }
        $field = $this->customprofilefields[$shortname];
        require_once($CFG->dirroot . '/user/profile/field/' . $field->datatype . '/field.class.php');
        $profilefieldname = 'profile_field_' . $field->shortname;
        $classname = 'profile_field_' . $field->datatype;
        /** @var profile_field_base $profilefield */
        $profilefield = new $classname($field->id, $this->id, $field);
        if (method_exists($profilefield, 'edit_save_data_preprocess')) {
            $this->{$profilefieldname} = $profilefield->edit_save_data_preprocess($value, null);
        } else {
            $this->{$profilefieldname} = $value;
        }
        return $this;
    }

    public function save() {
        global $CFG, $DB;
        // General check that required properties are set.
        foreach (static::required_properties() as $property) {
            if (!isset($this->{$property}) || is_null($this->{$property})) {
                throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, $property);
            }
        }
        if ($this->id <= 0) {
            // Email duplicates check.
            $allowaccountssameemail = $CFG->allowaccountssameemail ?? 0;
            if (!$allowaccountssameemail) {
                // Make a case-insensitive query for the given email address.
                $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid';
                $params = array(
                    'email' => $this->email,
                    'mnethostid' => $CFG->mnet_localhost_id
                );
                if ($DB->record_exists_select('user', $select, $params)) {
                    throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'email'); // @todo Provide a better explaination in exception.
                }
            }
            // Unique ID number check.
            $exists = $DB->record_exists('user', ['idnumber' => $this->idnumber, 'mnethostid' => $CFG->mnet_localhost_id]);
            if ($exists) {
                throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'idnumber'); // @todo Provide a better explaination in exception.
            }
            if (empty($this->username)) {
                $username = utilities::generate_username($this->firstname, $this->lastname);
                $this->set_username($username);
            }
            $this->id = user_create_user($this, false, true);
            set_user_preference('enrol_selma_new_student_create_password', 1, $this);
        } else {
            user_update_user($this);
        }
        profile_save_data($this);
        return true;
    }

}
