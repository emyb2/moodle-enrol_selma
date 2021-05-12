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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     enrol_selma
 * @category    upgrade
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_enrol_selma_install() {
    global $DB;
    $dbmanager = $DB->get_manager();

    // Add custom text fields to the group table.
    $table = new xmldb_table('groups');

    for ($i = 1; $i < 11; $i++) {
        $field = new xmldb_field("customtext{$i}", XMLDB_TYPE_TEXT);
        if (!$dbmanager->field_exists($table, $field)) {
            $dbmanager->add_field($table, $field);
        }
    }
    return true;
}
