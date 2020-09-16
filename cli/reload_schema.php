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
 * Drops all plugin tables and re-creates plugin schema based on plugin's XMLDB file.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/adminlib.php');

list($options, $unrecognized) = cli_get_params(
    [
        'non-interactive'   => false,
        'debug'             => false,
        'verbose'           => false,
        'help'              => false
    ],
    [
        'v' => 'verbose',
        'h' => 'help'
    ]
);

$help = "
Drops plugin all tables and re-creates plugin schema based off plugins XMLDB file.

Options:
--non-interactive           No interactive questions or confirmations
--debug                     Developer debugging switched on.
-v, --verbose               Print out verbose messages.
-h, --help                  Print out this help.

Example:
\$ sudo -u www-data /usr/bin/php enrol/selma/reload_schema.php -h
";

$warning = '
█     █  ███  ████  ██    █ █ ██    █  ████  █
█     █ █   █ █   █ ███   █ █ ███   █ █      █
█  █  █ █████ ████  █ ██  █ █ █ ██  █ █   ██ █
█  █  █ █   █ █   █ █  ██ █ █ █  ██ █ █    █  
 ██ ██  █   █ █   █ █   ███ █ █   ███  ████  █
';

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
if ($options['help']) {
    echo $help;
    die;
}
if ($options['debug']) {
    set_debugging(DEBUG_DEVELOPER, true);
}
$trace = new null_progress_trace();
if ($options['verbose']) {
    $trace = new text_progress_trace();
}
$interactive = empty($options['non-interactive']);
if ($interactive) {
    cli_writeln('About to drop plugin tables. Will recreate schema based off plugins XMLDB file.');
    cli_write($warning);
    cli_writeln('Do you really want to do this?');
    $prompt = get_string('cliyesnoprompt', 'admin');
    $input = cli_input($prompt, '',
        [
            get_string('clianswerno', 'admin'),
            get_string('cliansweryes', 'admin')
        ]
    );
    if ($input == get_string('clianswerno', 'admin')) {
        exit(1);
    }
}
$component = 'enrol_selma';
$pluginmanager = core_plugin_manager::instance();
$information = $pluginmanager->get_plugin_info($component);
$xmldbfilepath = $information->rootdir . '/db/install.xml';
drop_plugin_tables($component, $xmldbfilepath, false);
purge_all_caches();
$dbmanager = $DB->get_manager();
$dbmanager->install_from_xmldb_file($xmldbfilepath);
purge_all_caches();
exit(0);
