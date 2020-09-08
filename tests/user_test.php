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

defined('MOODLE_INTERNAL') || die();

use enrol_selma\local\user;
use enrol_selma\local\external;

/**
 * Testing for the enrol_selma 'user' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_testcase extends externallib_advanced_testcase {

    /**
     *  @var enrol_selma_generator $plugingenerator handle to plugin generator.
     */
    protected $plugingenerator;

    public function setUp() {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/selma/lib.php');
        require_once($CFG->dirroot . '/enrol/selma/locallib.php');
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest();
    }

    public function test_user_idnumber_length_exceeded() {
        $this->expectException(moodle_exception::class);
        $code = $this->plugingenerator->generate_random_string(1000);
        $intake = new user();
        $intake->set_property('idnumber', $code);
    }

    // Size of known DB fields.
    // Var types inserted to DB.
    // Customfield - mapping & types - handling.
    // New vs existing user.
    // Saving
}
