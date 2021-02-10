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
 * Testing for the external (web-services) enrol_selma 'get_gradebook_items' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For namespaces - look at https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.

use enrol_selma\local\external\get_gradebook_items;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the external enrol_selma 'get_gradebook_items' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_gradebook_items_external_testcase extends externallib_advanced_testcase {

    /**
     * @var enrol_selma_generator $plugingenerator handle to plugin generator.
     */
    protected $plugingenerator;

    /**
     * Prepares for the test.
     */
    protected function setUp() {
        // Run parent setup first, if any.
        parent::setUp();

        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest();
    }

    /**
     * Tests if exception is thrown when trying to get gradebook items when none exist.
     */
    public function test_no_items_get_gradebook_items() {
        // Create course to have gradebook items to retrieve.
        $courserecord = $this->plugingenerator->get_selma_course_data()['valid'];
        $course = $this->getDataGenerator()->create_course($courserecord);

        // Should get a warning returned, as there are no gradebook items yet..
        $result = get_gradebook_items::get_gradebook_items($course->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_gradebook_items::get_gradebook_items_returns(), $result);

        // What we expect back - course has no gradebook items.
        $warning[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $course->id)
        ];

        // Returned details (expected).
        $expectedvalue = [
            'warnings' => $warning
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests if exception is thrown when trying to get gradebook items.
     */
    public function test_get_gradebook_items() {
        // Create course to have gradebook items to retrieve.
        $courserecord = $this->plugingenerator->get_selma_course_data()['valid'];
        $course = $this->getDataGenerator()->create_course($courserecord);

        // The course needs a mod/activity to have a gradebook item too.
        $record = new stdClass();
        $record->course = $course;
        $mod = $this->getDataGenerator()->create_module('assign', $record);

        // Should get all the course's gradebook items returned.
        $result = get_gradebook_items::get_gradebook_items($course->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_gradebook_items::get_gradebook_items_returns(), $result);

        // What we expect back - the course and mod should be gradebook items.
        // Course.
        $item[] = [
            'itemname' => $courserecord['fullname'],
            'itemtype' => 'course'
        ];

        // Assign(ment) mod.
        $item[] = [
            'itemname' => $mod->name,
            'itemtype' => 'mod'
        ];

        // Returned details (expected).
        $expectedvalue = [
            'items' => $item
        ];

        // If 'items' index doesn't exist, the assertion below will fail anyway.
        if (isset($returnedvalue['items'])) {
            // Remove the id from the returned value, we're not testing that - and it could be different from test to test.
            foreach ($returnedvalue['items'] as &$rv) {
                unset($rv['id']);
            }
        }

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }
}