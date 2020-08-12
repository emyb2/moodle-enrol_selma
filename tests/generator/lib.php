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

defined('MOODLE_INTERNAL') || die();


/**
 * Plugin data generator class.
 *
 * @package
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_selma_generator extends testing_module_generator {

    /**
     * Enable the plugin.
     */
    public function enable_plugin() {
        $enabled = enrol_get_plugins(true);
        $enabled['selma'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    public function get_intake_data() : array {
        return [
            [
                'id' => 10612936,
                'programmeid' => 10479434,
                'programmetitle' => 'Adult and Tertiary Teaching 4',
                'code' => '2020-08-LW-ATT4-2018-1',
                'name' => 'Wintec August 2020',
                'startdate' => new DateTime('01-08-2020'),
                'enddate' => new DateTime('31-07-2021'),
            ],
            [
                'id' => 10612937,
                'programmeid' => 10557717,
                'programmetitle' => 'First Line Management 4',
                'code' => '2020-08-LW-FLM4 v1-1',
                'name' => 'Wintec August 2020',
                'startdate' => new DateTime('01-08-2020'),
                'enddate' => new DateTime('31-07-2021'),
            ],
            [
                'id' => 0,
                'programmeid' => 10579102,
                'programmetitle' => 'Introduction to Team Leadership 3',
                'code' => '2020-08-LW-ITL3 v1-1',
                'name' => 'Wintec August 2020',
                'startdate' => new DateTime('01-08-2020'),
                'enddate' => new DateTime('31-07-2021'),
            ],
        ];
    }

    public function generate_random_string(int $length) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomstring = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomstring .= $characters[$index];
        }
        return $randomstring;
    }

}