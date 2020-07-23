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
 * Refresh WS functions CLI script for enrol_selma.
 * Inspired by https://github.com/learningworks/moodle-local_ws_enrolcohort/blob/master/cli/upgrade.php
 *
 * @package     enrol_selma
 * @subpackage  cli
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This is a CLI script.
define('CLI_SCRIPT', true);

// Config file.
require_once(dirname(__FILE__, 4) . '/config.php');

global $CFG;

// Other things to require.
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/cronlib.php');
require_once($CFG->libdir . '/upgradelib.php');

// We may need a lot of memory here.
@set_time_limit(0);
raise_memory_limit(MEMORY_HUGE);

// CLI options.
list($options, $unrecognized) = cli_get_params(
    // Long names.
    [
        'help' => false,
        'no-verbose' => false,
        'print-logo' => false
    ],
    // Short names.
    [
        'h' => 'help',
        'nv' => 'no-verbose',
        'pl' => 'print-logo'
    ]
);

if (function_exists('cli_logo') && $options['print-logo']) {
    // Show a logo because we can...
    cli_logo();
    echo PHP_EOL;
}

if ($unrecognized) {
    $unrecognized = implode("\n ", $unrecognized);
    cli_error(new lang_string('cliunknowoption', 'admin', $unrecognized));
}

// Show help.
if ($options['help']) {
    echo new lang_string('clihelp', 'enrol_selma');
    die;
}

// Start output log.
$trace = new text_progress_trace();
$trace->output(new lang_string('cliheading', 'enrol_selma', new lang_string('pluginname', 'enrol_selma')));
echo PHP_EOL;

// Set verbosity and output verbosity state.
if ($options['no-verbose']) {
    $trace->output(new lang_string('noverbose', 'enrol_selma'));
    $trace = new null_progress_trace();
} else {
    $trace->output(new lang_string('verbose', 'enrol_selma'));
}

// Time the process.
$timenow = time();
$trace->output(new lang_string('servertime', 'enrol_selma', date('r', $timenow)));
$starttime = microtime();

$trace->output(new lang_string('updatefunctions', 'enrol_selma'));
external_update_descriptions('enrol_selma');
$pluginman = core_plugin_manager::instance();
upgrade_noncore(true);
$trace->output(new lang_string('updatefunctionsdone', 'enrol_selma'));

// Finish timing.
$difftime = microtime_diff($starttime, microtime());
$trace->output(new lang_string('executiontime', 'enrol_selma', $difftime));
