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
 * Setup SELMA web service role and account.
 *
 * @package     enrol_selma
 * @category    cli
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/adminlib.php');

list($options, $unrecognized) = cli_get_params(
    [
        'roleshortname' => '',
        'accountusername' => '',
        'accountemail' => '',
        'non-interactive' => false,
        'debug' => false,
        'verbose' => false,
        'help' => false
    ],
    [
        'v' => 'verbose',
        'h' => 'help'
    ]
);

$help = "
Setup SELMA web service role and account.

Options:
--roleshortname             Service role shortname.
--accountusername           Service account username.
--accountemail              Service account email address.
--non-interactive           No interactive questions or confirmations.
--debug                     Developer debugging switched on.
-v, --verbose               Print out verbose messages.
-h, --help                  Print out this help.

Example:
\$ sudo -u www-data /usr/bin/php enrol/selma/setup_service.php -h
";

cron_setup_user();

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
$roleshortname = clean_param($options['roleshortname'], PARAM_ALPHAEXT);
$accountusername= clean_param($options['accountusername'], PARAM_USERNAME);
$accountemail = clean_param($options['accountemail'], PARAM_EMAIL);
$runsetup = false;
$interactive = empty($options['non-interactive']);
if ($interactive) {
    if (empty($roleshortname)) {
        $error = '';
        do {
            cli_writeln($error);
            $input = cli_input('Web service role shortname (default : webservice)', 'webservice');
            $input = clean_param($input, PARAM_ALPHAEXT);
            if (empty($input)) {
                $error = get_string('cliincorrectvalueretry', 'admin');
            } else {
                $error = '';
            }
        } while ($error !== '');
        $roleshortname = $input;
        cli_writeln("Service role shortname set to `$roleshortname`");
    }
    if (empty($accountusername)) {
        $error = '';
        do {
            cli_writeln($error);
            $input = cli_input('Web service account username (default : selmaserviceaccount)', 'selmaserviceaccount');
            $input = clean_param($input, PARAM_USERNAME);
            if (empty($input)) {
                $error = get_string('cliincorrectvalueretry', 'admin');
            } else {
                $error = '';
            }
        } while ($error !== '');
        $accountusername = $input;
        cli_writeln("Service account username set to `$accountusername`");
    }
    if (empty($accountemail)) {
        $error = '';
        do {
            cli_writeln($error);
            $input = cli_input('Web service account email');
            $input = clean_param($input, PARAM_EMAIL);
            if (empty($input)) {
                $error = get_string('cliincorrectvalueretry', 'admin');
            } else {
                $error = '';
            }
        } while ($error !== '');
        $accountemail = $input;
        cli_writeln("Service account email set to `$accountemail`");
    }
    cli_writeln('Setup SELMA web service role and service acccount?');
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
    if ($input == get_string('cliansweryes', 'admin')) {
        $runsetup = true;
    }
} else {
    if (empty($roleshortname)) {
        cli_error("Incorrect value for `roleshortname`");
    }
    if (empty($accountusername)) {
        cli_error("Incorrect value for `accountusername`");
    }
    if (empty($accountemail)) {
        cli_error("Incorrect value for `accountemail`");
    }
    $runsetup = true;
}
if ($runsetup) {
    // Enable web services.
    enrol_selma\local\selma_webservice::enable_web_service($trace);
    // Enable protocols.
    enrol_selma\local\selma_webservice::enable_protocols($trace);
    // Create service role.
    $role = enrol_selma\local\selma_webservice::setup_service_role($roleshortname, $trace);
    // Create service account
    $account = enrol_selma\local\selma_webservice::setup_service_account($accountusername, $accountemail, $trace);
    // Add service account to role.
    enrol_selma\local\selma_webservice::assign_account_to_role($account, $role, $trace);
    // Authorise service account and create token for service account.
    $token = enrol_selma\local\selma_webservice::authorise_account_and_create_token($account, $trace);

    $a = new stdClass();
    $a->username = $account->username;
    $a->password = $account->password;
    $a->token = $token;
    $a->site = (new moodle_url('/login/index.php'))->out(false);

    $subject = get_string('serviceconnectiondetails::subject', 'enrol_selma');
    $message = get_string('serviceconnectiondetails::message', 'enrol_selma', $a);

    email_to_user($account, core_user::get_noreply_user(), $subject, $message);
    cli_write($message);
}
exit(0);
