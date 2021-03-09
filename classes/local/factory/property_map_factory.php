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

use coding_exception;
use enrol_selma\local\course;
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

    /**
     * Map course property based on plugin settings.
     *
     * @param   course              $course Plugin's course object.
     * @param   stdClass|null       $config Plugin's settings.
     * @return  property_map        Properties mapped to SELMA fields.
     * @throws  coding_exception
     */
    public function build_course_property_map(course $course, stdClass $config = null) : property_map {
        $propertymap = new property_map($course);
        $propertymap->set_config_name_grouping_prefix('cfm_');
        $propertymap->add_mapped_property(
            new mapped_property(
                $course,
                'fullname',
                get_string('course_fullname', 'enrol_selma'),
                null,
                'fullname',
                true
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $course,
                'shortname',
                get_string('course_shortname', 'enrol_selma'),
                null,
                'shortname',
                true
            )
        );
        $propertymap->add_mapped_property(
            new mapped_property(
                $course,
                'idnumber',
                get_string('course_idnumber', 'enrol_selma'),
                null,
                'idnumber',
                true
            )
        );

        $propertymap->add_mapped_property(
            new mapped_property(
                $course,
                'summary',
                get_string('course_summary', 'enrol_selma'),
                null,
                'summary',
                false
            )
        );

        // Map custom course fields.
        foreach (enrol_selma_get_custom_course_fields() as $field) {
            $name = 'customfield_' . $field->get('shortname');
            $propertymap->add_mapped_property(
                new mapped_property(
                    $course,
                    $name,
                    $field->get('name'),
                    null,
                    null,
                    false
                )
            );
        }

        // Map if plugin configured.
        if (!is_null($config)) {
            $propertymap->set_mapped_properties_from_config($config);
        }
        return $propertymap;
    }

    public function build_user_property_map(user $user, stdClass $config = null) : property_map {
        $propertymap = new property_map($user);
        $propertymap->set_config_name_grouping_prefix('upm_');
        $propertymap->add_mapped_property(
            new mapped_property($user, 'firstname', get_string('firstname'), null, 'firstname', true)
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'lastname', get_string('lastname'), null, 'lastname', true)
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'email', get_string('email'), null, 'email', true)
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'idnumber', get_string('idnumber'), null, 'studentid', true)
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'institution', get_string('institution'))
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'department', get_string('department'))
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'phone1', get_string('phone1'))
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'phone2', get_string('phone2'))
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'middlename', get_string('middlename'))
        );
        $propertymap->add_mapped_property(
            new mapped_property($user, 'alternatename', get_string('alternatename'))
        );
        foreach (profile_get_custom_fields() as $customfield) {
            $name = 'profile_field_' . $customfield->shortname;
            $defaultmapping = null;

            // Handle default values (or anything) for expected custom profile fields.
            if ($customfield->shortname === 'teacherid') {
                $defaultmapping = $customfield->shortname;

                // If we're dealing with a teacher, a student ID should not be required.
                $studentid = $propertymap->get_property('idnumber');
                $studentid->set_required(false);
            }

            $propertymap->add_mapped_property(
                new mapped_property($user, $name, $customfield->name, null, $defaultmapping)
            );
        }
        if (!is_null($config)) {
            $propertymap->set_mapped_properties_from_config($config);
        }

        return $propertymap;
    }
}
