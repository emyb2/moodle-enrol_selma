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
 * Testing for the external (web-services) enrol_selma 'get_all_courses' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// As per https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.
//namespace enrol_selma;

use enrol_selma\local\external\get_all_courses;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the external enrol_selma 'get_all_courses' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_all_courses_external_testcase extends externallib_advanced_testcase {

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
     * Tests if exception is thrown when trying to pass invalid parameters.
     */
    public function test_negative_params_get_all_courses() {
        // Get test course data.
        $params = $this->plugingenerator->get_selma_get_course_data()['invalid'];

        $this->expectException(moodle_exception::class);

        // User should get all course returned.
        $result = get_all_courses::get_all_courses($params['amount'], $params['page']);
        // We need to execute the return values cleaning process to simulate the web service server
        $returnedvalue = external_api::clean_returnvalue(get_all_courses::get_all_courses_returns(), $result);
    }

    /**
     * Tests if exception is thrown when trying to get courses when none exist.
     */
    public function test_no_courses_get_all_courses() {
        // Get test course data.
        $params = $this->plugingenerator->get_selma_get_course_data()['valid'];

        // User should get all course returned.
        $result = get_all_courses::get_all_courses($params['amount'], $params['page']);
        // We need to execute the return values cleaning process to simulate the web service server
        $returnedvalue = external_api::clean_returnvalue(get_all_courses::get_all_courses_returns(), $result);

        $expectedvalue = [
            'status' => get_string('status_notfound', 'enrol_selma'),
            'courses' => [],
            'nextpage' => -1,
            'message' => get_string('status_notfound_message', 'enrol_selma'),
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests if exception is thrown when trying to create course without capability to do so.
     */
    //public function test_working_get_all_courses() {
    //    // Get test course data.
    //    $params = $this->plugingenerator->get_selma_get_course_data()['valid'];
    //
    //    // TODO - Create some courses.
    //    //create_course();
    //
    //    // User should get all course returned.
    //    $result = get_all_courses::get_all_courses($params['amount'], $params['page']);
    //
    //    // We need to execute the return values cleaning process to simulate the web service server
    //    $returnedvalue = external_api::clean_returnvalue(get_all_courses::get_all_courses_returns(), $result);
    //
    //    // What's expected?
    //    $expectedvalue = [
    //        'status' => get_string('status_notfound', 'enrol_selma'),
    //        'courses' => [],
    //        'nextpage' => -1,
    //        'message' => get_string('status_notfound_message', 'enrol_selma'),
    //    ];
    //
    //    var_dump($returnedvalue);
    //    var_dump($expectedvalue);
    //
    //    // Assert we don't have any warnings.
    //    $this->assertArrayNotHasKey('warnings', $returnedvalue);
    //    // Assert we got what we expected.
    //    $this->assertEquals($expectedvalue, $returnedvalue);
    //}
}