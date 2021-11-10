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
 * Testing for the external (web-services) enrol_selma 'get_student' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For namespaces - look at https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.

use enrol_selma\local\external\create_student;
use enrol_selma\local\external\get_student;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the external enrol_selma 'get_student' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_student_external_testcase extends externallib_advanced_testcase {

    /**
     * @var enrol_selma_generator $plugingenerator handle to plugin generator.
     */
    protected $plugingenerator;

    /**
     * Prepares for the test.
     */
    protected function setUp() : void {
        // Run parent setup first, if any.
        parent::setUp();

        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest();
    }

    /**
     * Tests if exception is thrown when trying to get student - when none exist.
     */
    public function test_no_student_get_student() {
        // Get test student data.
        $record = $this->plugingenerator->get_selma_student_data()['valid'];

        // Should get warning about missing student returned.
        $result = get_student::get_student($record['studentid']);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_student::get_student_returns(), $result);

        $warning[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $record['studentid'])
        ];

        $expectedvalue = [
            'warnings' => $warning
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests if (SELMA) student can be retrieved.
     */
    public function test_get_student() {
        // Set the required capabilities by the external function.
        $context = context_system::instance();
        $this->assignUserCapability('moodle/user:create', $context->id);

        // Create student.
        $record = $this->plugingenerator->get_selma_student_data()['valid'];
        $student = create_student::create_student($record);

        // Should get student details returned.
        $result = get_student::get_student($record['studentid']);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_student::get_student_returns(), $result);

        // Returned details (expected).
        $expectedvalue = [
            'id' => $student['moodleuserid'],
            'firstname' => $record['firstname'],
            'lastname' => $record['lastname'],
            'email' => $record['email'],
            'idnumber' => (int) $record['studentid']
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }
}