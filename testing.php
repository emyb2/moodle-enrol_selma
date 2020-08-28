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

use enrol_selma\local\user;

require_once(dirname(__FILE__, 3) . '/config.php');

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once(dirname(__FILE__, 3) . '/admin/tool/uploaduser/locallib.php');
require_once(dirname(__FILE__, 3) . '/user/lib.php');
require_once(dirname(__FILE__, 3) . '/group/lib.php');

$selmauser = new user();

echo "Hello World!<br><hr><br>";
echo "<br>SELMA User<hr>";
print_object(array_keys(get_object_vars($selmauser)));

echo "<br>Profile Mapping<hr>";
print_object(enrol_selma_get_profile_mapping());

echo "<br>Update user<hr>";

$selmauserdata['dob'] =             '18/12/2020';
$selmauserdata['email1'] =          'user@email.invalid';
$selmauserdata['forename'] =        'User';
$selmauserdata['gender'] =          'Male';
$selmauserdata['id'] =              '123456789101';
$selmauserdata['lastname'] =        'Lastname';
$selmauserdata['mobilephone'] =     '021021021';
$selmauserdata['nsn'] =             '123123123';
$selmauserdata['preferredname'] =   'zpottie';
$selmauserdata['secondaryphone'] =  '120120120';
$selmauserdata['status'] =          'active';
$selmauserdata['username'] =        'testingfile';

$selmauser->update_user_from_selma_data($selmauserdata);
print_object($selmauser);

echo "<br>Save user<hr>";
$selmauser->save();

die();