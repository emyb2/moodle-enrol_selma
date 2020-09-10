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

namespace enrol_selma\local\factory;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use enrol_selma\local\user;
use enrol_selma\local\property_map;
use enrol_selma\local\mapped_property;

/**
 * Factory to build different property maps.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class property_map_factory {

    public function build_user_property_map(user $user, stdClass $config = null) : property_map {
        $propertymap = new property_map($user);
        $propertymap->set_config_name_grouping_prefix('upm_');
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'firstname',
                get_string('firstname'),
                null,
                'firstname',
                true,
                'set_first_name'
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'lastname',
                get_string('lastname'),
                null,
                'lastname',
                true,
                'set_last_name'
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'email',
                get_string('email'),
                null,
                'email',
                true,
                'set_email'
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'idnumber',
                get_string('idnumber'),
                null,
                'studentid',
                true,
                'set_idnumber'
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'institution',
                get_string('institution'),
                null,
                null,
                false,
                null
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'department',
                get_string('department'),
                null,
                null,
                false,
                null
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'phone1',
                get_string('phone1'),
                null,
                null,
                false,
                'set_phone1'
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'phone2',
                get_string('phone2'),
                null,
                null,
                false,
                'set_phone2'
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
                'middlename',
                get_string('middlename'),
                null,
                null,
                false,
                null
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $user,
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
            $propertymap->add_mapped_property(
                new mapped_property(
                    $user,
                    $name,
                    $customfield->name,
                    null,
                    null,
                    false,
                    null
                )
            );
        }
        if (!is_null($config)) {
            $propertymap->set_mapped_properties_from_config($config);
        }
        return $propertymap;
    }
}
