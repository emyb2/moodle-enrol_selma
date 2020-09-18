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
 * The plugin-specific library of functions used for testing.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use enrol_selma\local\course;
use enrol_selma\local\user;

require_once(dirname(__FILE__, 3) . '/config.php');
require_once(dirname(__FILE__, 3) . '/user/profile/lib.php');

require_login();

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once(dirname(__FILE__, 3) . '/admin/tool/uploaduser/locallib.php');
require_once(dirname(__FILE__, 3) . '/user/lib.php');
require_once(dirname(__FILE__, 3) . '/group/lib.php');

global $DB;

$course = new course();
$course->course_field_test = 5;

$course->save();

print_object($course);
die();

// Configs.
var_dump(get_config('enrol_selma', 'faake'));

$selmauser = new user();

echo "Hello World!<br><hr><br>";
echo "<br>SELMA User<hr>";
var_dump(array_keys(get_object_vars($selmauser)));

echo "<br>Profile Mapping<hr>";
var_dump(enrol_selma_get_profile_mapping());

echo "<br>User object from selma data<hr>";

$selmauserdata['id'] = '9010';
$selmauserdata['dob'] = '19/12/2020';
$selmauserdata['email1'] = 'user3@email.invalid';
$selmauserdata['forename'] = 'Users';
$selmauserdata['gender'] = 'Males';
$selmauserdata['id'] = '12345678910';
$selmauserdata['lastname'] = 'Lastnames';
$selmauserdata['mobilephone'] = '9999999999';
$selmauserdata['nsn'] = '1231231231';
$selmauserdata['preferredname'] = 'zpottie2';
$selmauserdata['secondaryphone'] = '1201201201';
$selmauserdata['status'] = 'active2';
$selmauserdata['username'] = 'testingfile1';

$selmauser = enrol_selma_user_from_selma_data($selmauserdata);
var_dump($selmauser);

echo "<br>Get user<hr>";
$user = enrol_selma_user_from_id(34);
var_dump($user->lastname);
var_dump($user);
$user->lastname = "Ted";
var_dump($user);

echo "<br>Save user<hr>";
$newuser = $user->save();
var_dump($DB->get_record('user', array('id' => $newuser->id)));

echo "<br>Add user to intake<hr>";
$user = enrol_selma_add_user_to_intake(654321, 12312313);
var_dump($user);

die();