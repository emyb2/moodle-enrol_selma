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
 * Plugin administration pages are defined here.
 *
 * @package     enrol_selma
 * @category    admin
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $ADMIN;
if ($hassiteconfig) {
    // https://docs.moodle.org/dev/Admin_settings
    $component = 'enrol_selma';

    // Main plugin settings configuration page.
    $settings = new admin_settingpage(
        'enrolsettingsselma',
        new lang_string('configuration', $component),
        'moodle/site:config'
    );

    // Logging section.
    $setting = new admin_setting_heading(
        "{$component}/loggingheading",
        new lang_string('logging', $component),
        null
    );
    $settings->add($setting);

    $setting = new admin_setting_configselect(
        "{$component}/loglevel",
        new lang_string('loglevel', $component),
        new lang_string('loglevel::description', $component),
        enrol_selma\local\log_levels::ERROR,
        enrol_selma\local\log_levels::all()

    );
    $settings->add($setting);

    $setting = new admin_setting_configcheckbox(
        "{$component}/logemailcritical",
        new lang_string('logemailcritical', $component),
        new lang_string('logemailcritical::description', $component),
        1
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        "{$component}/logemailcriticalrecipients",
        new lang_string('logemailcriticalrecipients', $component),
        new lang_string('logemailcriticalrecipients::description', $component),
        get_admin()->email
    );
    $settings->add($setting);

    $options = [
        0 => new lang_string('keep', $component),
        7 => new lang_string('sevendays', $component),
        30 => new lang_string('thirtydays', $component),
        60 => new lang_string('sixtydays', $component),
        90 => new lang_string('ninetydays', $component)
    ];

    $setting = new admin_setting_configselect(
        "{$component}/logretention",
        new lang_string('logretention', $component),
        new lang_string('logretention::description', $component),
        0,
        $options

    );
    $settings->add($setting);

    // Create a category to house any external pages we may require.
    $ADMIN->add(
        'enrolments',
        new admin_category(
            "{$component}_folder",
            new lang_string('pluginname', $component)
        )
    );

    // Log view page.
    $pluginlogexternalpage = new moodle_url('/enrol/selma/log.php');
    $ADMIN->add(
        "{$component}_folder",
        new admin_externalpage(
            "{$component}/log",
            new lang_string('applicationlog', $component),
            $pluginlogexternalpage->out(false)
        )
    );
}
