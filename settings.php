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

use enrol_selma\local\mapped_property;

defined('MOODLE_INTERNAL') || die();

global $ADMIN;

require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/settingslib.php');

if ($hassiteconfig) {
    // Check https://docs.moodle.org/dev/Admin_settings for more info.
    $component = 'enrol_selma';

    // SELMA settings category setup.
    $ADMIN->add('enrolments',
        new admin_category('enrolselmacategory',
            new lang_string('pluginname', $component)));

    // Enrolment settings page.
    $enrolsettings = new admin_settingpage(
        'enrolsettingsselma',
        new lang_string('settings', $component),
        'moodle/site:config'
    );

    $setting = new admin_setting_heading(
        "{$component}/selmasettings",
        new lang_string('settingsheading', $component),
        new lang_string('settingsheading::description', $component)
    );
    $enrolsettings->add($setting);

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar.
    // It describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );

    // What to do when 'unenrol user' event occurs.
    $setting = new admin_setting_configselect(
        "{$component}/unenrolaction",
        new lang_string('unenrolaction', $component),
        new lang_string('unenrolaction::description', $component),
        ENROL_EXT_REMOVED_SUSPENDNOROLES,
        $options

    );
    $enrolsettings->add($setting);

    $setting = new admin_setting_heading(
        "{$component}/instancedefaults",
        new lang_string('instancedefaults', $component),
        new lang_string('instancedefaults::description', $component)
    );
    $enrolsettings->add($setting);

    // We can't get roles if Moodle's still being set up - don't show setting yet.
    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $setting = new admin_setting_configselect(
            "{$component}/defaultrole",
            new lang_string('defaultrole', $component),
            new lang_string('defaultrole::description', $component),
            $student->id,
            $options
        );
        $enrolsettings->add($setting);
    }

    $ADMIN->add(
        'enrolselmacategory',
        $enrolsettings
    );

    // User settings page.
    $usersettings = new admin_settingpage(
        'usersettingsselma',
        new lang_string('usersettings', $component),
        'moodle/site:config'
    );

    $setting = new admin_setting_heading(
        "{$component}/userdefaults",
        new lang_string('userdefaultsheading', $component),
        new lang_string('userdefaultsheading::description', $component)
    );
    $usersettings->add($setting);

    // Profile field mapping - SELMA > Moodle.
    // Mapping SELMA user fields to Moodle profile fields.
    $setting = new admin_setting_heading(
        "{$component}/profilemapheading",
        new lang_string('profilemapheading', $component),
        new lang_string('profilemapheading::description', $component)
    );
    $usersettings->add($setting);

    // Duplicates checking.
    $duplicates = enrol_selma_validate_profile_mapping();
    $duplist = implode(', ', $duplicates);

    // Only add if duplicates detected.
    if (!empty($duplicates)) {
        // Alert of duplicates.
        $setting = new admin_setting_heading(
            "{$component}/profilemap_duplicatewarning",
            new lang_string('profilemap_duplicatewarning', $component),
            new lang_string('profilemap_duplicatewarning::description', $component, $duplist)
        );

        $usersettings->add($setting);
    }

    $user = new enrol_selma\local\user();
    $propertymapfactory = new enrol_selma\local\factory\property_map_factory();
    $userpropertymap = $propertymapfactory->build_user_property_map($user);
    foreach ($userpropertymap as $propertyname => $mappedproperty) {
        /** @var mapped_property $mappedproperty */
        $setting = new admin_setting_configtext(
            "{$component}/{$userpropertymap->get_config_name_grouping_prefix()}{$mappedproperty->get_name()}",
            $mappedproperty->get_human_friendly_name(),
            null,
            $mappedproperty->get_default_mapped_property_name()
        );

        // Add some text to description if setting value is a duplicate.
        if (in_array($setting->get_setting(), $duplicates)) {
            $setting->description = new lang_string('profilemapduplicate', 'enrol_selma', $setting->get_setting()) . $setting->description;
        }

        $usersettings->add($setting);
    }

    // Welcome Email.
    $setting = new admin_setting_heading(
        "{$component}/welcomeheading",
        new lang_string('welcomeheading', $component),
        new lang_string('welcomeheading::description', $component)
    );
    $usersettings->add($setting);

    // Welcome email subject line.
    $setting = new admin_setting_configtext(
        "{$component}/welcomesubject",
        new lang_string('welcomesubject', $component),
        new lang_string('welcomesubject::description', $component),
        '',
        PARAM_TEXT
    );
    $usersettings->add($setting);

    // Welcome email body message.
    $setting = new admin_setting_confightmleditor(
        "{$component}/welcomebody",
        new lang_string('welcomebody', $component),
        new lang_string('welcomebody::description', $component),
        '',
        PARAM_CLEANHTML
    );
    $usersettings->add($setting);

    $ADMIN->add(
        'enrolselmacategory',
        $usersettings
    );

    // Course settings.
    $coursesettings = new admin_settingpage(
        'selmacoursesettings',
        new lang_string('coursesettingsheading', $component),
        'moodle/site:config'
    );

    $setting = new admin_setting_heading(
        "{$component}/coursedefaults",
        new lang_string('coursedefaultsheading', $component),
        new lang_string('coursedefaultsheading::description', $component)
    );
    $coursesettings->add($setting);

    $setting = new admin_setting_heading(
        "{$component}/coursesettings",
        new lang_string('selmacoursesettingsheading', $component),
        new lang_string('selmacoursesettingsheading::description', $component)
    );
    $coursesettings->add($setting);

    // Use new method for Moodle 3.6+.
    if (class_exists('core_course_category')) {
        $options = core_course_category::make_categories_list();
    } else {
        $options = coursecat::make_categories_list();
    }

    // Where new courses are created.
    $setting = new admin_setting_configselect(
        "{$component}/newcoursecat",
        new lang_string('newcoursecat', $component),
        new lang_string('newcoursecat::description', $component),
        key($options),
        $options
    );
    $coursesettings->add($setting);

    // Use $options while we have it... Which categories should SELMA ignore?
    $setting = new admin_setting_configmultiselect(
        "{$component}/excludecoursecat",
        new lang_string('excludecoursecat', $component),
        new lang_string('excludecoursecat::description', $component),
        null,
        $options
    );
    $coursesettings->add($setting);

    // Create groups based on SELMA intakes.
    $setting = new admin_setting_configcheckbox(
        "{$component}/creategroups",
        new lang_string('creategroups', $component),
        new lang_string('creategroups::description', $component),
        1
    );
    $coursesettings->add($setting);

    // Add these tags to a course created using the plugin.
    $setting = new admin_setting_configtext(
        "{$component}/selmacoursetags",
        new lang_string('selmacoursetags', $component),
        new lang_string('selmacoursetags::description', $component),
        null
    );
    $coursesettings->add($setting);

    $ADMIN->add(
        'enrolselmacategory',
        $coursesettings
    );

    // Logging settings.
    $logsettings = new admin_settingpage(
        'selmalogging',
        new lang_string('logging', $component),
        'moodle/site:config'
    );

    $setting = new admin_setting_heading(
        "{$component}/loggingheading",
        new lang_string('logging', $component),
        null
    );
    $logsettings->add($setting);

    $setting = new admin_setting_configselect(
        "{$component}/loglevel",
        new lang_string('loglevel', $component),
        new lang_string('loglevel::description', $component),
        enrol_selma\local\log_levels::ERROR,
        enrol_selma\local\log_levels::all()

    );
    $logsettings->add($setting);

    $setting = new admin_setting_configcheckbox(
        "{$component}/logemailcritical",
        new lang_string('logemailcritical', $component),
        new lang_string('logemailcritical::description', $component),
        1
    );
    $logsettings->add($setting);

    $setting = new admin_setting_configtext(
        "{$component}/logemailcriticalrecipients",
        new lang_string('logemailcriticalrecipients', $component),
        new lang_string('logemailcriticalrecipients::description', $component),
        get_admin()->email
    );
    $logsettings->add($setting);

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
    $logsettings->add($setting);

    $ADMIN->add('enrolselmacategory', $logsettings);

    // View application log - external page.
    $pluginlogexternalpage = new moodle_url('/enrol/selma/log.php');
    $ADMIN->add(
        'enrolselmacategory',
        new admin_externalpage(
            "{$component}/log",
            new lang_string('applicationlog', $component),
            $pluginlogexternalpage->out(false)
        )
    );

    // Tell core we already added the settings structure.
    $settings = null;
}
