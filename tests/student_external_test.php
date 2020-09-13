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
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

use enrol_selma\local\external\create_student;
use enrol_selma\local\external\update_student;

/**
 * Class student_external_testcase
 */
class student_external_testcase extends externallib_advanced_testcase {

    /**
     *  @var enrol_selma_generator $plugingenerator handle to plugin generator.
     */
    protected $plugingenerator;

    protected function setUp() {
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest(true);
    }

    public function test_create_student_no_cap_failure() {
        $this->expectException(required_capability_exception::class);
        $context = context_system::instance();
        $params = [
            'firstname' => 'Jack',
            'lastname' => 'Tors',
            'email' => 'jack.tors@jerkyboys.net',
            'studentid' => '1000000',
        ];
        $returnvalue = create_student::create_student($params);
    }

    public function test_create_student_success() {
        global $DB, $USER;
        // Set the required capabilities by the external function
        $context = context_system::instance();
        $roleid = $this->assignUserCapability('moodle/user:create', $context->id);
        $params = [
            'firstname' => 'Jack',
            'lastname' => 'Tors',
            'email' => 'jack.tors@jerkyboys.net',
            'studentid' => '1000000',
        ];
        $returnvalue = create_student::create_student($params);
        // We need to execute the return values cleaning process to simulate the web service server
        $returnvalue = external_api::clean_returnvalue(create_student::create_student_returns(), $returnvalue);
        $this->assertEquals(
            $returnvalue['moodleuserid'],
            $DB->get_field('user', 'id', ['id' => $returnvalue['moodleuserid']])
        );
        $this->assertTrue($DB->record_exists('user', ['idnumber' => '1000000']));

    }
    public function test_create_student_failure() {
        global $DB, $USER;
        // Set the required capabilities by the external function
        $context = context_system::instance();
        $roleid = $this->assignUserCapability('moodle/user:create', $context->id);
        $params = [
            'firstname' => $this->plugingenerator->generate_random_string(500),
            'lastname' => 'The best last name in Cambridge',
            'email' => 'jack.tors@jerkyboys.net',
            'studentid' => '55555555'
        ];
        $returnvalue = create_student::create_student($params);
        // We need to execute the return values cleaning process to simulate the web service server
        $returnvalue = external_api::clean_returnvalue(create_student::create_student_returns(), $returnvalue);
        $this->assertEquals($returnvalue['moodleuserid'], 0);
    }

    public function test_update_student_success() {
        global $DB, $USER;
        // Set the required capabilities by the external function
        $context = context_system::instance();
        $roleid = $this->assignUserCapability('moodle/user:update', $context->id);
        $params = [
            'firstname' => 'Firsty',
            'lastname' => 'The best last name in Cambridge',
            'email' => 'jack.tors@jerkyboys.net',
            'idnumber' => '55555555'
        ];
        $user = $this->getDataGenerator()->create_user($params);
        unset($params);
        $params = [
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'email' => 'jack.tors@jerkyboys.net',
            'studentid' => '55555555'
        ];
        $returnvalue = update_student::update_student($params);
        $returnvalue = external_api::clean_returnvalue(update_student::update_student_returns(), $returnvalue);
        $this->assertEquals(
            $user->id,
            $DB->get_field('user', 'id', ['id' => $returnvalue['moodleuserid']])
        );
        $this->assertTrue($DB->record_exists('user', ['lastname' => 'Lasty']));
    }

}