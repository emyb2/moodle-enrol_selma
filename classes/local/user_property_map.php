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
use moodle_exception;

require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Basic property map.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_property_map {

    private $properties;

    private $propertynameprefix;

    public function __construct(string $propertynameprefix = 'upm_') {
        $this->propertynameprefix = $propertynameprefix;
        $this->properties = [
            'firstname' => [
                'shortname' => 'firstname',
                'name' => get_string('firstname'),
                'required' => 1,
                'mappedproperty' => null,
                'default' => 'forename'
            ],
            'lastname' => [
                'shortname' => 'lastname',
                'name' => get_string('lastname'),
                'required' => 1,
                'mappedproperty' => null,
                'default' => 'lastname'
            ],
            'email' => [
                'shortname' => 'email',
                'name' => get_string('email'),
                'required' => 1,
                'mappedproperty' => null,
                'default' => 'email1'
            ],
            'idnumber' => [
                'shortname' => 'idnumber',
                'name' => get_string('idnumber'),
                'required' => 1,
                'mappedproperty' => null,
                'default' => 'id'
            ],
            'institution' => [
                'shortname' => 'institution',
                'name' => get_string('institution'),
                'required' => 0,
                'mappedproperty' => null,
                'default' => null
            ],
            'department' => [
                'shortname' => 'department',
                'name' => get_string('department'),
                'required' => 0,
                'mappedproperty' => null,
                'default' => null
            ],
            'phone1' => [
                'shortname' => 'phone1',
                'name' => get_string('phone1'),
                'required' => 0,
                'mappedproperty' => null,
                'default' => null
            ],
            'phone2' => [
                'shortname' => 'phone2',
                'name' => get_string('phone2'),
                'required' => 0,
                'mappedproperty' => null,
                'default' => null
            ],
            'middlename' => [
                'shortname' => 'middlename',
                'name' => get_string('middlename'),
                'required' => 0,
                'mappedproperty' => null,
                'default' => null
            ],
            'alternatename' => [
                'shortname' => 'alternatename',
                'name' => get_string('alternatename'),
                'required' => 0,
                'mappedproperty' => null,
                'default' => null
            ]
        ];
        foreach (profile_get_custom_fields() as $customfield) {
            $fieldname = 'profile_field_' . $customfield->shortname;
            $this->properties[$fieldname] = [
                'shortname' => $customfield->shortname,
                'name' => $customfield->name,
                'required' => $customfield->required,
                'mappedproperty' => null,
                'default' => null
            ];
        }
        foreach ($this->properties as $propertyname => $options) {
            $this->properties[$propertyname]['configname'] = $this->propertynameprefix . $propertyname;
        }
    }

    public function is_required(string $propertyname) : bool {
        if (!isset($this->properties[$propertyname])) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'propertyname');
        }
        return (bool) $this->properties[$propertyname]['required'] ?? 0;
    }

    public function load_from_config(stdClass $config) {
        foreach (get_object_vars($config) as $userproperty => $mappedproperty) {
            $pattern = '/^' . $this->propertynameprefix . '/';
            $userproperty = preg_replace($pattern, '', $userproperty, 1);
            if (isset($this->properties[$userproperty])) {
                $this->properties[$userproperty]['mappedproperty'] = $mappedproperty;
            }
        }
        return $this;
    }

    public function get_map() {
        return $this->properties;
    }

    public function validate() {
        foreach ($this->properties as $property) {
            if ($property['required'] && trim($property['mappedproperty']) === '') {
                throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'user_property_map:' . $property['shortname']);
            }
        }
    }

}
