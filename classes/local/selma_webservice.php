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
 * SELMA webservice account setup - helper methods.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use core_user;
use context_system;
use stdClass;
use progress_trace;
use null_progress_trace;
use moodle_exception;
use webservice;

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/webservice/lib.php');
require_once($CFG->libdir  . '/accesslib.php');

/**
 * Class to help setup of SELMA service role and account.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class selma_webservice {

    /** @var string SERVICE_SHORT_NAME Service short name, used to locate service details. */
    public const SERVICE_SHORT_NAME = 'enrol_selma';

    /**
     * Get service role based in shortname.
     *
     * @param string $shortname
     * @return false|mixed|stdClass
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function get_service_role(string $shortname) {
        global $DB;
        if (trim($shortname) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'shortname');
        }
        return $DB->get_record('role', ['shortname' => $shortname]);
    }

    /**
     * Get service account based on username.
     *
     * @param string $username
     * @return bool|stdClass
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function get_service_account(string $username) {
        if (trim($username) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'shortname');
        }
        return core_user::get_user_by_username($username);
    }

    /**
     * Create the service user account.
     *
     * @param string $username
     * @param string $email
     * @param progress_trace|null $trace
     * @return bool|stdClass
     * @throws \coding_exception
     * @throws moodle_exception
     */
    public static function setup_service_account(string $username, string $email, progress_trace $trace = null) {
        global $CFG;
        if (is_null($trace)) {
            $trace = new null_progress_trace();
        }
        if (clean_param($username, PARAM_USERNAME) === '') {
            throw new moodle_exception('invalidusername', 'error');
        }
        if (!validate_email($email)) {
            throw new moodle_exception('invalidemail', 'moodle');
        }
        $user = static::get_service_account($username);
        if (!$user) {
            $user = new stdClass();
            $user->username = $username;
            $user->password = generate_password();
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->firstname = get_string('serviceaccountfirstname', 'enrol_selma');
            $user->lastname = get_string('serviceaccountlastname', 'enrol_selma');
            $user->email = $email;
            $user->confirmed = 1;
            $user->policyagreed = 1;
            $user->firstnamephonetic = '';
            $user->lastnamephonetic = '';
            $user->middlename = '';
            $user->alternatename = '';
            $user->imagealt = '';
            $user->id = user_create_user($user, false);
            $trace->output("Service account `{$user->username}` created");
        } else {
            $user->password = '';
            $trace->output("Service account `{$user->username}` exists");
        }
        return $user;
    }

    /**
     * Create a service role with required capabilities that the service account
     * will be added to.
     *
     * @param string $shortname
     * @param progress_trace|null $trace
     * @return false|mixed|stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function setup_service_role(string $shortname, progress_trace $trace = null) {
        if (is_null($trace)) {
            $trace = new null_progress_trace();
        }
        $role = static::get_service_role($shortname);
        if (!$role) {
            $role = new stdClass();
            $role->name = get_string('servicerolename', 'enrol_selma');
            $role->shortname = $shortname;
            $role->description = get_string('serviceroledescription', 'enrol_selma');
            $role->archetype = 'user';
            $role->id = create_role(
                $role->name,
                $role->shortname,
                $role->description,
                $role->archetype
            );
            $trace->output("Service role `{$shortname}` created");
        } else {
            $trace->output("Service role `{$shortname}` already exists");
        }
        $defaultcaps = get_default_capabilities($role->archetype);
        $systemcontext = context_system::instance();
        foreach ($defaultcaps as $defaultcap => $permission) {
            assign_capability($defaultcap, $permission, $role->id, $systemcontext->id);
        }
        foreach (['assign', 'override', 'switch', 'view'] as $type) {
            $function = "core_role_set_{$type}_allowed";
            $allows = get_default_role_archetype_allows($type, $role->archetype);
            foreach ($allows as $allowid) {
                $function($role->id, $allowid);
            }
        }
        set_role_contextlevels($role->id, [CONTEXT_SYSTEM]);
        static::update_required_capabilities($role, $trace);
        return $role;
    }

    /**
     * Assign service account to the service role.
     *
     * @param stdClass $user
     * @param stdClass $role
     * @param progress_trace|null $trace
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function assign_account_to_role(stdClass $user, stdClass $role, progress_trace $trace = null) {
        if (is_null($trace)) {
            $trace = new null_progress_trace();
        }
        $systemcontext = context_system::instance();
        role_assign($role->id, $user->id, $systemcontext->id, 'enrol_selma');
        $trace->output("Account `$user->username` assigned to role `$role->shortname`");
    }

    /**
     * Create access token for service acccount.
     *
     * @param stdClass $user
     * @return string
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function authorise_account_and_create_token(stdClass $user) : string {
        global $DB;
        $systemcontext = context_system::instance();
        $webservicemanager = new webservice();
        $service = $webservicemanager->get_external_service_by_shortname(static::SERVICE_SHORT_NAME);
        if (!$service) {
            throw new coding_exception("Service not found.");
        }
        // Clear out previous service user and token records.
        $DB->delete_records(
            'external_services_users',
            ['externalserviceid' => $service->id, 'userid' => $user->id]
        );
        $DB->delete_records(
            'external_tokens',
            ['externalserviceid' => $service->id, 'userid' => $user->id]
        );
        // Authorise the user to use the service.
        $webservicemanager->add_ws_authorised_user(
            (object) [
                'externalserviceid' => $service->id,
                'userid' => $user->id
            ]
        );
        // Create a token for the user.
        $token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service, $user->id, $systemcontext);
        return $token;
    }

    /**
     * Add any missing required capabilities to the service role.
     *
     * @param stdClass $role
     * @param progress_trace|null $trace
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function update_required_capabilities(stdClass $role, progress_trace $trace = null) {
        if (is_null($trace)) {
            $trace = new null_progress_trace();
        }
        $systemcontext = context_system::instance();
        $webservicemanager = new webservice();
        $service = $webservicemanager->get_external_service_by_shortname(static::SERVICE_SHORT_NAME);
        if (!$service) {
            throw new coding_exception("Service not found.");
        }
        $capabilities = $webservicemanager->get_service_required_capabilities($service->id);
        foreach (array_unique($capabilities) as $capability) {
            assign_capability($capability, CAP_ALLOW, $role->id, $systemcontext->id, true);
            $trace->output("Allowing `{$capability}` for role `{$role->shortname}`");
        }
    }

    /**
     * Enable Moodle webservices and SELMA service, should already be enabled by default.
     *
     * @param progress_trace|null $trace
     */
    public static function enable_web_service(progress_trace $trace = null) {
        if (is_null($trace)) {
            $trace = new null_progress_trace();
        }
        set_config('enablewebservices', true);
        $webservicemanager = new webservice();
        $service = $webservicemanager->get_external_service_by_shortname(static::SERVICE_SHORT_NAME);
        if (!$service) {
            throw new coding_exception("Service not found.");
        }
        $service->enabled = true;
        $webservicemanager->update_external_service($service);
        $trace->output("SELMA web service enabled");
    }

    /**
     * Just enable REST protocol.
     *
     * @param progress_trace|null $trace
     */
    public static function enable_protocols(progress_trace $trace = null) {
        global $CFG;
        if (is_null($trace)) {
            $trace = new null_progress_trace();
        }
        $protocols = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);
        $protocols[] = 'rest';
        set_config('webserviceprotocols', implode(',', array_unique($protocols)));
        $trace->output("REST protocol enabled");
    }
}
