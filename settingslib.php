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
 * Plugin administration settings are refined here.
 *
 * @package     enrol_selma
 * @category    admin
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends the core select setting to allow specifying whether the field should be enabled or not.
 */
class admin_setting_configselect_with_enabled extends admin_setting_configselect {
    /**
     * @var bool Whether or not the select field is considered enabled or disabled.
     */
    private $enabled;

    /**
     * Constructor: uses parent::__construct, but add 'enabled' flag.
     *
     * @param string        $name Unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string        $visiblename Localised.
     * @param string        $description Long localised info.
     * @param string|int    $defaultsetting Default value for the setting.
     * @param array         $choices array of $value=>$label for each selection.
     * @param boolean       $enabled If true, the input field is enabled, otherwise it's disabled.
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $choices, $enabled = true) {
        $this->enabled = $enabled;
        // TODO - Should we check if the setting has been set first? Otherwise it may never be set to what we want to force it to?
        // TODO - We can assign forced config values at plugin install?
        // We should not save changes to the disabled elements.
        $this->nosave = !$enabled;
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices);
    }

    /**
     * Returns XHTML field(s) as required by choices.
     *
     * @param   string  $data Selected value of element.
     * @param   string  $query Used to highlight when searching for setting.
     * @return  string  XHTML Renders the setting.
     */
    public function output_html($data, $query='') {
        // If select enabled, continue as normal.
        if ($this->enabled !== false) {
            return parent::output_html($data, $query);
        }

        // Otherwise, disable element, add hidden element so the value is still passed.
        $element = '<select class="custom-select" disabled tabindex="-1">
                        <option selected>' . $this->get_defaultsetting() . '</option>
                    </select>';

        return format_admin_setting($this, $this->visiblename, $element, $this->description, null, null, null, $query);
    }
}