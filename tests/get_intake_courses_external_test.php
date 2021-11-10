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
 * Testing for the external (web-services) enrol_selma 'get_intake_courses' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For namespaces - look at https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.

use enrol_selma\local\external\add_intake_to_course;
use enrol_selma\local\external\create_intake;
use enrol_selma\local\external\get_intake;
use enrol_selma\local\external\get_intake_courses;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the external enrol_selma 'get_intake_courses' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_intake_courses_external_testcase extends externallib_advanced_testcase {

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
     * Tests if exception is thrown when trying to get intake's courses - when none exist.
     */
    public function test_no_ic_get_intake_courses() {
        // Get test intake data.
        $intakerecord = $this->plugingenerator->get_intake_data()[0];

        // Should get a warning returned.
        $result = get_intake_courses::get_intake_courses($intakerecord['id']);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_intake_courses::get_intake_courses_returns(), $result);

        $warning[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $intakerecord['id'])
        ];

        $expectedvalue = [
            'warnings' => $warning
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests if any exceptions are thrown when trying to get intake's courses.
     */
    public function test_get_intake_courses() {
        // Get test intake data.
        $intakerecord = $this->plugingenerator->get_intake_data()[0];

        $createintake = [
            'intakeid' => $intakerecord['id'],
            'programmeid' => $intakerecord['programmeid'],
            'intakecode' => $intakerecord['code'],
            'intakename' => $intakerecord['name'],
            'intakestartdate' => $intakerecord['startdate'],
            'intakeenddate' => $intakerecord['enddate']
        ];

        // Create intake.
        $intake = create_intake::create_intake($createintake);

        // Create course to associate intake to.
        $course1record = $this->plugingenerator->get_selma_course_data()['valid'];
        $course1 = $this->getDataGenerator()->create_course($course1record);

        // Create another course to associate intake to.
        $course2record = $this->plugingenerator->get_selma_course_data()['complete'];
        $course2 = $this->getDataGenerator()->create_course($course2record);

        // Associate intake to courses.
        $relate1 = add_intake_to_course::add_intake_to_course($intakerecord['id'], $course1->id);
        $relate2 = add_intake_to_course::add_intake_to_course($intakerecord['id'], $course2->id);

        // Should get intake's related courses returned.
        $result = get_intake_courses::get_intake_courses($intakerecord['id']);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_intake_courses::get_intake_courses_returns(), $result);

        // Returned details (expected).
        $expectedvalue = [
            'courseids' => [(int) $course1->id, (int) $course2->id]
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }
}