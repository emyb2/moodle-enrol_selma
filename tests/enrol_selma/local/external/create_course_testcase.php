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
namespace enrol_selma\local\external;

defined('MOODLE_INTERNAL') || die();

use enrol_selma\local\external;
use enrol_selma_generator;
use externallib_advanced_testcase;

/**
 * Testing for the enrol_selma 'user' class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_course_testcase extends externallib_advanced_testcase {

    /**
     *  @var enrol_selma_generator $plugingenerator handle to plugin generator.
     */
    protected $plugingenerator;

    protected function setUp() {
        parent::setUp();
        global $CFG;
        require_once($CFG->dirroot . '/enrol/selma/locallib.php');
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_selma');
        $this->plugingenerator->enable_plugin();
        $this->resetAfterTest();
    }

    // Call function.
}
