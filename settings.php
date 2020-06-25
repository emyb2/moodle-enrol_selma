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

    // Settings folder setup.
    $ADMIN->add('enrolments',
        new admin_category('enrolselmafolder',
            new lang_string('pluginname', $component)));

    // ENROLMENT SETTINGS.
    $addsettings = new admin_settingpage(
        'enrolsettingsselma',
        new lang_string('settings', $component),
        'moodle/site:config'
    );

    $setting = new admin_setting_heading(
        "{$component}/selmasettings",
        new lang_string('settingsheading', $component),
        new lang_string('settingsheading::description', $component)
    );
    $addsettings->add($setting);

    // Add instance to new courses.
    $setting = new admin_setting_configcheckbox(
        "{$component}/autoadd",
        new lang_string('autoadd', $component),
        new lang_string('autoadd::description', $component),
        1
    );
    $addsettings->add($setting);

    // FROM Manual Enrolment plugin settings.
    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    //       it describes what should happend when users are not supposed to be enerolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    // END FROM Manual Enrolment plugin settings.

    // What to do when user's enrolment expired.
    $setting = new admin_setting_configselect(
        "{$component}/expiredaction",
        new lang_string('expiredaction', $component),
        new lang_string('expiredaction::description', $component),
        ENROL_EXT_REMOVED_KEEP,
        $options

    );
    $addsettings->add($setting);

    // FROM Manual Enrolment plugin settings.
    $options = array();
    for ($i=0; $i<24; $i++) {
        $options[$i] = $i;
    }
    // END FROM Manual Enrolment plugin settings.

    // What hour of day to send the expiry reminder.
    $setting = new admin_setting_configselect(
        "{$component}/expirednotifhour",
        new lang_string('expirednotifhour', $component),
        new lang_string('expirednotifhour::description', $component),
        6,
        $options
    );
    $addsettings->add($setting);

    $setting = new admin_setting_heading(
        "{$component}/instancedefaults",
        new lang_string('instancedefaults', $component),
        new lang_string('instancedefaults::description', $component)
    );
    $addsettings->add($setting);

    // BASED ON Manual Enrolment plugin settings.
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
        $addsettings->add($setting);
    }
    // END BASED ON Manual Enrolment plugin settings.

    // Duration of enrolment.
    $setting = new admin_setting_configtext(
        "{$component}/enrollength",
        new lang_string('enrollength', $component),
        new lang_string('enrollength::description', $component),
        0,
        PARAM_INT
    );
    $addsettings->add($setting);

    $options = array();
    $options[] = new lang_string("no");
    $options[] = new lang_string("yes");

    // Notify user of looming expiry.
    $setting = new admin_setting_configselect(
        "{$component}/notifyexpiry",
        new lang_string('notifyexpiry', $component),
        new lang_string('notifyexpiry::description', $component),
        $options[0],
        $options
    );
    $addsettings->add($setting);

    // Pre-unenrolment notification buffer.
    $setting = new admin_setting_configtext(
        "{$component}/notifypreexpiry",
        new lang_string('notifypreexpiry', $component),
        new lang_string('notifypreexpiry::description', $component),
        0,
        PARAM_INT
    );
    $addsettings->add($setting);

    $setting = new admin_setting_heading(
        "{$component}/welcomeheading",
        new lang_string('welcomeheading', $component),
        new lang_string('welcomeheading::description', $component)
    );
    $addsettings->add($setting);

    // Welcome email subject line.
    $setting = new admin_setting_configtext(
        "{$component}/welcomesubject",
        new lang_string('welcomesubject', $component),
        new lang_string('welcomesubject::description', $component),
        '',
        PARAM_TEXT
    );
    $addsettings->add($setting);

    // Welcome email body message.
    $setting = new admin_setting_confightmleditor(
        "{$component}/welcomebody",
        new lang_string('welcomebody', $component),
        new lang_string('welcomebody::description', $component),
        '',
        PARAM_CLEANHTML
    );
    $addsettings->add($setting);

    $ADMIN->add(
        'enrolselmafolder',
        $addsettings
    );

    // COURSE SETTINGS.
    $cousettings = new admin_settingpage(
        'selmacoursesettings',
        new lang_string('selmacoursesettingsheading', $component),
        'moodle/site:config'
    );

    $setting = new admin_setting_heading(
        "{$component}/selmacoursesettings",
        new lang_string('selmacoursesettings', $component),
        new lang_string('selmacoursesettings::description', $component)
    );
    $cousettings->add($setting);

    $options = core_course_category::make_categories_list();

    // Where new courses are created.
    $setting = new admin_setting_configselect(
        "{$component}/newcoursecat",
        new lang_string('newcoursecat', $component),
        new lang_string('newcoursecat::description', $component),
        $options[1],
        $options
    );
    $cousettings->add($setting);

    // Create groups based on SELMA intakes.
    $setting = new admin_setting_configcheckbox(
        "{$component}/creategroups",
        new lang_string('creategroups', $component),
        new lang_string('creategroups::description', $component),
        1
    );
    $cousettings->add($setting);

    $ADMIN->add(
        'enrolselmafolder',
        $cousettings
    );

    // LOGGING SETTINGS.
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

    $ADMIN->add('enrolselmafolder', $logsettings);

    // VIEW LOGS - external page.
    $pluginlogexternalpage = new moodle_url('/enrol/selma/log.php');
    $ADMIN->add(
        'enrolselmafolder',
        new admin_externalpage(
            "{$component}/log",
            new lang_string('applicationlog', $component),
            $pluginlogexternalpage->out(false)
        )
    );

    // Tell core we already added the settings structure.
    $settings = null;
}
