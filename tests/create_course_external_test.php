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
 * Testing for the external (web-services) enrol_selma 'create_course' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For namespaces - look at https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.

use enrol_selma\local\external\create_course;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/customfield/classes/api.php');

/**
 * Testing for the enrol_selma 'user' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_course_external_testcase extends externallib_advanced_testcase {

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
     * Tests if exception is thrown when trying to create course without capability to do so.
     */
    public function test_missing_required_capability() {
        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];

        // We expect an exception to be thrown.
        $this->expectException(required_capability_exception::class);

        // User should not be able to create yet.
        create_course::create_course($params);
    }

    /**
     * Tests if exception is thrown when required configs have not been set up yet.
     */
    public function test_missing_required_config() {
        // Get context to assign capability.
        $contextid = context_system::instance()->id;

        // Give user ability to create courses.
        $this->assignUserCapability('moodle/course:create', $contextid);

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];

        // TODO - intentionally unset config? This test may be moot since the setting's given a default value .
        // We expect to be thrown a generic 'moodle' exception if setting not set.
        if (get_config('enrol_selma', 'newcoursecat') === false) {
            $this->expectException(moodle_exception::class);

            // Plugin configs not set up at this point yet.
            create_course::create_course($params);
        }
    }

    /**
     * Tests if warning is returned when optional configs have not been set up yet.
     */
    public function test_missing_optional_config() {
        // Get context to assign capability.
        $contextid = context_system::instance()->id;

        // Give user ability to create courses.
        $this->assignUserCapability('moodle/course:create', $contextid);

        // TODO - make this a custom/new category, so we can test if the config was respected later. Hard to know if default used.
        // Now 'fix' the previous issue(s) to continue. 1 = Miscellaneous (default category).
        set_config('newcoursecat', 1, 'enrol_selma');

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];

        // Plugin configs not set up at this point yet.
        $result = create_course::create_course($params);

        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(create_course::create_course_returns(), $result);

        $expectedvalue = [
            'courseid' => $returnedvalue['courseid'], // Slight cheat here, but the course ID will be generated.
            'warnings' => []
        ];

        // Assert we got empty warnings, as expected.
        $this->assertArrayHasKey('warnings', $returnedvalue);
        $this->assertEmpty($returnedvalue['warnings']);
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
        $this->assignUserCapability('moodle/course:create', $contextid);

        // TODO - make this a custom/new category, so we can test if the config was respected later. Hard to know if default used.
        // Now 'fix' the previous issue(s) to continue. 1 = Miscellaneous (default category).
        set_config('newcoursecat', 1, 'enrol_selma');

        // Now 'fix' the previous issue(s) to continue. Set some tags.
        set_config('selmacoursetags', '{{name}},selma', 'enrol_selma');

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];

        // Plugin configs set up at this point yet.
        $result = create_course::create_course($params);

        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(create_course::create_course_returns(), $result);

        $expectedvalue = [
            'courseid' => $returnedvalue['courseid'], // Slight cheat here, but the course ID will be generated.
            'warnings' => [],
        ];

        // Assert we got empty warnings, as expected.
        $this->assertArrayHasKey('warnings', $returnedvalue);
        $this->assertEmpty($returnedvalue['warnings']);
        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests the handling of a duplicate course being created.
     */
    public function test_simple_duplicate_course() {
        // Get context to assign capability.
        $contextid = context_system::instance()->id;

        // Give user ability to create courses.
        $this->assignUserCapability('moodle/course:create', $contextid);

        // TODO - make this a custom/new category, so we can test if the config was respected later. Hard to know if default used.
        // Now 'fix' the previous issue(s) to continue. 1 = Miscellaneous (default category).
        set_config('newcoursecat', 1, 'enrol_selma');

        // Now 'fix' the previous issue(s) to continue. Set some tags.
        set_config('selmacoursetags', '{{name}},selma', 'enrol_selma');

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['valid'];

        // Plugin configs not set up at this point yet.
        $result1 = create_course::create_course($params);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue1 = external_api::clean_returnvalue(create_course::create_course_returns(), $result1);

        $result2 = create_course::create_course($params);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue2 = external_api::clean_returnvalue(create_course::create_course_returns(), $result2);

        // Assert we got empty warnings, as expected.
        $this->assertArrayHasKey('warnings', $returnedvalue1);
        $this->assertEmpty($returnedvalue1['warnings']);

        // TODO - improve assertion to be more specific? How, as we catch it?
        // Assert we got warnings, as expected.
        $this->assertArrayHasKey('warnings', $returnedvalue2);
        $this->assertNotEmpty($returnedvalue2['warnings']);
    }

    /**
     * Tests the handling of invalid params received to create course.
     */
    public function test_simple_invalid_course() {
        // Get context to assign capability.
        $contextid = context_system::instance()->id;

        // Give user ability to create courses.
        $this->assignUserCapability('moodle/course:create', $contextid);

        // TODO - make this a custom/new category, so we can test if the config was respected later. Hard to know if default used.
        // Now 'fix' the previous issue(s) to continue. 1 = Miscellaneous (default category).
        set_config('newcoursecat', 1, 'enrol_selma');

        // Now 'fix' the previous issue(s) to continue. Set some tags.
        set_config('selmacoursetags', '{{name}},selma', 'enrol_selma');

        // Get test course data.
        $params = $this->plugingenerator->get_selma_course_data()['invalid'];

        // We expect to be thrown a generic 'moodle' exception.
        $this->expectException(invalid_parameter_exception::class);

        // Plugin configs not set up at this point yet.
        $result = create_course::create_course($params);
        // We need to execute the return values cleaning process to simulate the web service server.
        external_api::clean_returnvalue(create_course::create_course_returns(), $result);
    }
}
