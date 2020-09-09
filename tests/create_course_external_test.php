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

// As per https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.
//namespace enrol_selma;

use enrol_selma\local\external\create_course;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the enrol_selma 'user' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_course_external_testcase extends externallib_advanced_testcase {

    /**
     *  @var enrol_selma_generator $plugingenerator handle to plugin generator.
     */
    protected $plugingenerator;

    /**
     * Prepares for the test.
     */
    protected function setUp() {
        //global $CFG;
        //require_once($CFG->dirroot . '/enrol/selma/locallib.php');

        // Run parent setup first, if any.
        parent::setUp();

        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest();
    }

    /**
     * Tests if exception is thrown when trying to create course without capability to do so.
     */
    public function test_missing_required_capability() {
        // Get context to assign capability.
        $contextid = context_system::instance()->id;

        // We give this user capability to 'view' but not 'create' (yet).
        $roleid = $this->assignUserCapability('moodle/course:view', $contextid);

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];

        // We expect to be thrown a 'required capability' exception.
        $this->expectException(required_capability_exception::class);

        // User should not be able to create yet..
        $result = create_course::create_course($params);

        }

    /**
     * Tests if exception is thrown when required configs have not been set up yet.
     */
    public function test_missing_required_config() {
        // Get context to assign capability.
        $contextid = context_system::instance()->id;

        // Give user ability to create courses.
        $roleid = $this->assignUserCapability('moodle/course:create', $contextid);

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];

        // We expect to be thrown a 'required capability' exception.
        $this->expectException(moodle_exception::class);

        // Plugin configs not set up at this point yet.
        $result = create_course::create_course($params);
    }

    /**
     * Tests if warning is returned when optional configs have not been set up yet.
     */
    public function test_missing_optional_config() {
        // Get context to assign capability.
        $contextid = context_system::instance()->id;

        // Give user ability to create courses.
        $roleid = $this->assignUserCapability('moodle/course:create', $contextid);

        // TODO - make this a custom/new category, so we can test if the config was respected later. Hard to know if default used.
        // Now 'fix' the previous issue(s) to continue. 1 = Miscellaneous (default category).
        set_config('newcoursecat', 1,'enrol_selma');

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];


        // Plugin configs not set up at this point yet.
        $result = create_course::create_course($params);

        // We need to execute the return values cleaning process to simulate the web service server
        $returnedvalue = external_api::clean_returnvalue(create_course::create_course_returns(), $result);

        $expectedvalue = [
            'status' => get_string('status_ok', 'enrol_selma'),
            'courseid' => $returnedvalue['courseid'], // Slight cheat here, but the course ID will be generated.
            'message' => get_string('status_ok_message', 'enrol_selma'),
            'warnings' => array(
                0 => [
                    'item' => get_string('pluginname', 'enrol_selma'),
                    'itemid' => 1,
                    'warningcode' => get_string('warning_code_noconfig', 'enrol_selma'),
                    'message' => get_string('warning_message_noconfig', 'enrol_selma', 'selmacoursetags')
                ]
            )
        ];

        // Assert we got warnings, as expected.
        $this->assertArrayHasKey('warnings', $returnedvalue);
        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests the most simple (working) setup of a course.
     */
    public function test_simple_working_course() {
        // Get context to assign capability.
        $contextid = context_system::instance()->id;

        // Give user ability to create courses.
        $roleid = $this->assignUserCapability('moodle/course:create', $contextid);

        // TODO - make this a custom/new category, so we can test if the config was respected later. Hard to know if default used.
        // Now 'fix' the previous issue(s) to continue. 1 = Miscellaneous (default category).
        set_config('newcoursecat', 1,'enrol_selma');

        // Now 'fix' the previous issue(s) to continue. Set some tags.
        set_config('selmacoursetags', '{{name}},selma','enrol_selma');

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];

        // Plugin configs not set up at this point yet.
        $result = create_course::create_course($params);

        // We need to execute the return values cleaning process to simulate the web service server
        $returnedvalue = external_api::clean_returnvalue(create_course::create_course_returns(), $result);

        $expectedvalue = [
            'status' => get_string('status_ok', 'enrol_selma'),
            'courseid' => $returnedvalue['courseid'], // Slight cheat here, but the course ID will be generated.
            'message' => get_string('status_ok_message', 'enrol_selma'),
        ];

        // Assert we don't have any warnings.
        $this->assertArrayNotHasKey('warnings', $returnedvalue);
        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }
}
