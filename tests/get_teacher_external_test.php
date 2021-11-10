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
 * Testing for the external (web-services) enrol_selma 'get_teacher' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For namespaces - look at https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.

use enrol_selma\local\external\create_teacher;
use enrol_selma\local\external\get_teacher;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the external enrol_selma 'get_teacher' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_teacher_external_testcase extends externallib_advanced_testcase {

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
     * Tests if exception is thrown when trying to get teacher - when none exist.
     */
    public function test_no_teacher_get_teacher() {
        // Set up custom profilefield needed for teacher user tracking.
        $category = $this->plugingenerator->create_profile_field_category('other');
        $this->plugingenerator->create_profile_field(
            'text',
            [
                'categoryid' => $category->id,
                'shortname' => 'teacherid',
                'name' => 'Teacher ID',
                'locked' => 1
            ]
        );

        // Get test teacher data.
        $record = $this->plugingenerator->get_selma_teacher_data()['valid'];

        // Should get warning about missing teacher returned.
        $result = get_teacher::get_teacher($record['teacherid']);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_teacher::get_teacher_returns(), $result);

        $warning[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $record['teacherid'])
        ];

        $warning[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', '')
        ];

        $expectedvalue = [
            'warnings' => $warning
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests if (SELMA) teacher can be retrieved.
     */
    public function test_get_teacher() {
        // Set up custom profilefield needed for teacher user tracking.
        $category = $this->plugingenerator->create_profile_field_category('other');
        $this->plugingenerator->create_profile_field(
            'text',
            [
                'categoryid' => $category->id,
                'shortname' => 'teacherid',
                'name' => 'Teacher ID',
                'locked' => 1
            ]
        );

        // Set the required capabilities by the external function.
        $context = context_system::instance();
        $this->assignUserCapability('moodle/user:create', $context->id);

        // Create teacher.
        $record = $this->plugingenerator->get_selma_teacher_data()['valid'];
        $teacher = create_teacher::create_teacher($record);

        // Should get teacher details returned.
        $result = get_teacher::get_teacher($record['teacherid']);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_teacher::get_teacher_returns(), $result);

        // Returned details (expected).
        $expectedvalue = [
            'id' => $teacher['userid'],
            'firstname' => $record['firstname'],
            'lastname' => $record['lastname'],
            'email' => $record['email'],
            'teacherid' => (int) $record['teacherid']
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }
}