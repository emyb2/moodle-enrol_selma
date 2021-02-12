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
 * Testing for the external (web-services) enrol_selma 'withdraw_student' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For namespaces - look at https://docs.moodle.org/dev/Coding_style#Namespaces_within_.2A.2A.2Ftests_directories.

use enrol_selma\local\external\add_intake_to_course;
use enrol_selma\local\external\add_student_to_intake;
use enrol_selma\local\external\create_intake;
use enrol_selma\local\external\create_student;
use enrol_selma\local\external\withdraw_student;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Testing for the external enrol_selma 'withdraw_student' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class withdraw_student_external_testcase extends externallib_advanced_testcase {

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
     * Tests if exception is thrown when trying to withdraw student with invalid ueid.
     */
    public function test_invalid_withdraw_student() {
        // Withdraw non-existent student/enrolment.
        $result = withdraw_student::withdraw_student(1234);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(withdraw_student::withdraw_student_returns(), $result);

        // Returned details (expected).
        $expectedvalue = [
            'withdrawn' => false
        ];

        // Assert we got what we expected.
        $this->assertFalse($returnedvalue['withdrawn']);
        $this->assertEquals($expectedvalue, $returnedvalue);
    }

    /**
     * Tests if (SELMA) student can be withdrawn from course.
     */
    public function test_withdraw_student() {
        // Create test intake.
        $intakerecord = $this->plugingenerator->get_intake_data()[0];
        $createparams = [
            'intakeid' => $intakerecord['id'],
            'programmeid' => $intakerecord['programmeid'],
            'intakecode' => $intakerecord['code'],
            'intakename' => $intakerecord['name'],
            'intakestartdate' => $intakerecord['startdate'],
            'intakeenddate' => $intakerecord['enddate']
        ];

        // Create intake.
        $intake = create_intake::create_intake($createparams);

        // Create test course
        // Create course to add intake to.
        $courserecord = $this->plugingenerator->get_selma_course_data()['valid'];
        $course = $this->getDataGenerator()->create_course($courserecord);

        // Create test student.
        // Set the required capabilities by the external function.
        $context = context_system::instance();
        $this->assignUserCapability('moodle/user:create', $context->id);
        $studentrecord = $this->plugingenerator->get_selma_student_data()['valid'];
        create_student::create_student($studentrecord);

        // Add intake to course.
        // Params for 'add_intake_to_course';
        $params = [
            'intakeid' => $intake['intakeid'],
            'courseid' => $course->id
        ];
        add_intake_to_course::add_intake_to_course($params['intakeid'], $params['courseid']);

        // Add student to intake.
        // Params for 'add_student_to_intake' - we need to pass SELMA studentrID.
        $params = [
            'studentid' => $studentrecord['studentid'],
            'intakeid' => $intake['intakeid']
        ];
        $enrolledcourses = add_student_to_intake::add_student_to_intake($params['studentid'], $params['intakeid']);
        // Just get one course/user enrolment to withdraw the student from.
        $ueid = $enrolledcourses['courses'][0]['userenrolid'];

        // Withdraw student.
        $result = withdraw_student::withdraw_student($ueid);
        // We need to execute the return values cleaning process to simulate the web service server.
        $returnedvalue = external_api::clean_returnvalue(withdraw_student::withdraw_student_returns(), $result);

        // Returned details (expected).
        $expectedvalue = [
            'withdrawn' => true
        ];

        // Assert we got what we expected.
        $this->assertTrue($returnedvalue['withdrawn']);
        $this->assertEquals($expectedvalue, $returnedvalue);
    }
}