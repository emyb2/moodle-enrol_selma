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
 * Testing for the external (web-services) enrol_selma 'get_intake' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For namespaces - look at https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.

use enrol_selma\local\external\create_intake;
use enrol_selma\local\external\get_intake;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the external enrol_selma 'get_intake' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_intake_external_testcase extends externallib_advanced_testcase {

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
     * Tests if exception is thrown when trying to get intake - when none exist.
     */
    public function test_no_intake_get_intake() {
        // Get test intake data.
        $params = $this->plugingenerator->get_intake_data()[0];

        // Should get intake returned.
        $result = get_intake::get_intake($params['id']);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_intake::get_intake_returns(), $result);

        $warning[] = [
            'item' => get_string('pluginname', 'enrol_selma'),
            'itemid' => 1,
            'warningcode' => get_string('warning_code_notfound', 'enrol_selma'),
            'message' => get_string('warning_message_notfound', 'enrol_selma', $params['id'])
        ];

        $expectedvalue = [
            'warnings' => $warning
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests if exception is thrown when trying to get courses when none exist.
     */
    public function test_get_intake() {
        global $USER;
        // Get test intake data.
        $params = $this->plugingenerator->get_intake_data()[0];

        $createparams = [
            'intakeid' => $params['id'],
            'programmeid' => $params['programmeid'],
            'intakecode' => $params['code'],
            'intakename' => $params['name'],
            'intakestartdate' => $params['startdate'],
            'intakeenddate' => $params['enddate']
        ];

        // Create intake.
        $intake = create_intake::create_intake($createparams);

        // Should get intake returned.
        $result = get_intake::get_intake($params['id']);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(get_intake::get_intake_returns(), $result);

        // Returned details (expected).
        $expectedvalue = [
            'id' => $params['id'],
            'programmeid' => $params['programmeid'],
            'code' => $params['code'],
            'name' => $params['name'],
            'startdate' => date_create_from_format('Y-m-d', $params['startdate'])->getTimestamp(),
            'enddate' => date_create_from_format('Y-m-d', $params['enddate'])->getTimestamp(),
            'usermodified' => (int) $USER->id
        ];

        unset($returnedvalue['timecreated']);
        unset($returnedvalue['timemodified']);

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }
}