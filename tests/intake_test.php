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
 * Testing for the local enrol_selma 'intake' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/phpunit/classes/advanced_testcase.php');

/**
 * Testing for the local enrol_selma 'intake' class.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class intake_testcase extends advanced_testcase {

    /**
     * @var enrol_selma_generator $plugingenerator handle to plugin generator.
     */
    protected $plugingenerator;

    public function setUp() : void {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/selma/lib.php');
        require_once($CFG->dirroot . '/enrol/selma/locallib.php');
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest();
    }

    public function test_intake_param_length_exceeded() {
        $this->expectException(moodle_exception::class);
        $code = $this->plugingenerator->generate_random_string(1000);
        $intake = new enrol_selma\local\intake();
        $intake->set_code($code);
    }

    public function test_intake_date_type_datestring() {
        $intake = new enrol_selma\local\intake();
        $datestring = '01/08/2020';
        $date = new DateTime($datestring);
        $intake->set_start_date($datestring);
        $this->assertTrue(($intake->startdate == $date->getTimestamp()));
    }

    public function test_intake_date_type_epoch() {
        $intake = new enrol_selma\local\intake();
        $date = new DateTime('01/08/2020');
        $intake->set_start_date($date->getTimestamp());
        $this->assertTrue(($intake->startdate == $date->getTimestamp()));
    }

    public function test_intake_date_type_datetime() {
        $intake = new enrol_selma\local\intake();
        $date = new DateTime('01/08/2020');
        $intake->set_start_date($date);
        $this->assertTrue(($intake->startdate == $date->getTimestamp()));
    }

    public function test_intake_date_type_fail() {
        $this->expectException(moodle_exception::class);
        $intake = new enrol_selma\local\intake();
        $intake->set_end_date(00.0000);
    }
}
