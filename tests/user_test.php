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
 * Testing for the local enrol_selma 'user' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/phpunit/classes/advanced_testcase.php');

use enrol_selma\local\user;

/**
 * Testing for the enrol_selma 'user' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_testcase extends advanced_testcase {

    /**
     * @var enrol_selma_generator $plugingenerator handle to plugin generator.
     */
    protected $plugingenerator;

    public function setUp() : void {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/selma/lib.php');
        require_once($CFG->dirroot . '/enrol/selma/locallib.php');
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest();
    }

    public function test_create_student_setter_exception() {
        $this->expectException(moodle_exception::class);
        $user = new user();
        $user->set_firstname($this->plugingenerator->generate_random_string(500));
    }

    public function test_create_student_ok() {
        global $CFG, $DB;
        $this->setAdminUser();
        $category1 = $this->plugingenerator->create_profile_field_category('other');
        $this->plugingenerator->create_profile_field(
            'datetime',
            [
                'categoryid' => $category1->id,
                'shortname' => 'dob',
                'name' => 'Date of birth',
                'startday' => 1,
                'startmonth' => 1,
                'startyear' => '1970',
                'param1' => '1970',
                'endday' => 1,
                'endmonth' => 1,
                'endyear' => '2050',
                'param2' => '2050'
            ]
        );
        $config = new stdClass();
        $config->upm_firstname = 'firstname';
        $config->upm_lastname = 'lastname';
        $config->upm_email = 'email';
        $config->upm_idnumber = 'studentid';
        $config->upm_profile_field_dob = 'dateofbirth';
        $selmadata = [
            'firstname' => 'Jack',
            'lastname' => 'Tors',
            'email' => 'jack.tors@jerkyboys.net',
            'studentid' => '0000',
            'dateofbirth' => '15-10-1976'
        ];
        $user = enrol_selma_create_student_from_selma($selmadata, $config);
        $this->assertTrue($DB->record_exists('user', ['id' => $user->id]));
    }

    public function test_update_student_ok() {
        global $CFG, $DB;
        $this->setAdminUser();
        $category1 = $this->plugingenerator->create_profile_field_category('other');
        $this->plugingenerator->create_profile_field(
            'datetime',
            [
                'categoryid' => $category1->id,
                'shortname' => 'dob',
                'name' => 'Date of birth',
                'startday' => 1,
                'startmonth' => 1,
                'startyear' => '1970',
                'param1' => '1970',
                'endday' => 1,
                'endmonth' => 1,
                'endyear' => '2050',
                'param2' => '2050'
            ]
        );

        $user = $this->getDataGenerator()->create_user(
            [
                'firstname' => 'Jack',
                'lastname' => 'Tors',
                'email' => 'jack.tors@noemail.net',
                'idnumber' => '0000'
            ]
        );
        $this->assertTrue($DB->record_exists('user', ['idnumber' => '0000']));
        $config = new stdClass();
        $config->upm_firstname = 'firstname';
        $config->upm_lastname = 'lastname';
        $config->upm_email = 'email';
        $config->upm_idnumber = 'studentid';
        $config->upm_profile_field_dob = 'dateofbirth';
        $selmadata = [
            'firstname' => 'Jacky',
            'lastname' => 'Tors',
            'email' => 'jack.tors@jerkyboys.net',
            'studentid' => '0000',
            'gender' => 'male',
        ];
        $user = enrol_selma_update_student_from_selma($selmadata, $config);
        $this->assertTrue($DB->record_exists('user', ['idnumber' => '0000']));
        $this->assertTrue($DB->record_exists('user', ['firstname' => 'Jacky']));
        $this->assertTrue($DB->record_exists('user', ['email' => 'jack.tors@jerkyboys.net']));
    }
}
