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

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use enrol_selma\local\user;

require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Factory to build user, course, and intake entities.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity_factory {

    public function build_user_from_stdclass(stdClass $record) {
        $user = new user();
        foreach (get_object_vars($record) as $propertyname => $value) {
            $user->{$propertyname} = $value;
        }
        profile_load_data($user);
        return $user;
    }

}
