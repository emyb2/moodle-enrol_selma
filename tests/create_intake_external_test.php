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
 * Testing for the external (web-services) enrol_selma 'create_intake' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For namespaces - look at https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.

use enrol_selma\local\intake;
use enrol_selma\local\external\create_intake;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the external enrol_selma 'create_intake' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_intake_external_testcase extends externallib_advanced_testcase {

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

        // Set up current user.
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest();
    }

    /**
     * Tests if expected result is given when trying to create intake.
     */
    public function test_create_intake() {
        // Get test course data.
        $intakeobj = $this->plugingenerator->get_intake_data()[0];

        $createparams = [
            'intakeid' => $intakeobj['id'],
            'programmeid' => $intakeobj['programmeid'],
            'intakecode' => $intakeobj['code'],
            'intakename' => $intakeobj['name'],
            'intakestartdate' => $intakeobj['startdate'],
            'intakeenddate' => $intakeobj['enddate']
        ];

        // Create intake.
        $result = create_intake::create_intake($createparams);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(create_intake::create_intake_returns(), $result);

        // What we expect in the results.
        // Set status to 'OK'.
        $status = get_string('status_ok', 'enrol_selma');
        // Intake added bool status - we need it to be true.
        $intakeid = $intakeobj['id'];
        // Give more detailed response message to user.
        $message = get_string('status_ok_message', 'enrol_selma');

        $expectedvalue = [
            'status' => $status,
            'intakeid' => $intakeid,
            'message' => $message
        ];

        // Assert we got what we expected.
        $this->assertEquals($expectedvalue, $returnedvalue);
    }
}