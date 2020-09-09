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

/**
 * Basic property map.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_property_map extends property_map {

    public function define(): void {
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'firstname',
                get_string('firstname'),
                null,
                'firstname',
                true,
                'set_first_name'
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'lastname',
                get_string('lastname'),
                null,
                'lastname',
                true,
                'set_last_name'
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'email',
                get_string('email'),
                null,
                'email',
                true,
                'set_email'
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'idnumber',
                get_string('idnumber'),
                null,
                'studentid',
                true,
                'set_idnumber'
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'institution',
                get_string('institution'),
                null,
                null,
                false,
                null
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'department',
                get_string('department'),
                null,
                null,
                false,
                null
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'phone1',
                get_string('phone1'),
                null,
                null,
                false,
                'set_phone1'
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'phone2',
                get_string('phone2'),
                null,
                null,
                false,
                'set_phone2'
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'middlename',
                get_string('middlename'),
                null,
                null,
                false,
                null
            )
        );
        $this->add_mapped_property(
            new mapped_property(
                $this->object,
                'alternatename',
                get_string('alternatename'),
                null,
                null,
                false,
                null
            )
        );
        foreach (profile_get_custom_fields() as $customfield) {
            $name = 'profile_field_' . $customfield->shortname;
            $this->add_mapped_property(
                new mapped_property(
                    $this->object,
                    $name,
                    $customfield->name,
                    null,
                    null,
                    false,
                    null
                )
            );
        }
    }


    public function validate() {
        foreach ($this->properties as $property) {
            if ($property['required'] && trim($property['mappedproperty']) === '') {
                throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'user_property_map:' . $property['shortname']);
            }
        }
    }

}
