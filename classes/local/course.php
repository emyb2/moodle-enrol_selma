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
 * Class to represent a Moodle Course.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use core_course_category;
use dml_exception;
use moodle_exception;
use stdClass;

require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/enrol/selma/locallib.php');

/**
 * Class to represent a Moodle Course. Extends stdClass and has public properties, but use setters to enforce type.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course extends stdClass {

    /** @var int $id Course ID. */
    public $id = 0;

    /** @var int $category Category ID where course is/will be. */
    public $category;

    /** @var string $fullname Course fullname. */
    public $fullname;

    /** @var string $shortname Course shortname (unique). */
    public $shortname;

    /** @var string $idnumber Course idnumber (unique). */
    public $idnumber;

    /** @var string $summary Course description. */
    public $summary;

    /** @var int $visible Course visibility. */
    public $visible;

    /** @var int $timecreated Course creation epoch. */
    public $timecreated;

    /** @var int $timemodified Course last modified epoch. */
    public $timemodified;

    /**
     * Set custom course fields - no others without setters accepted.
     *
     * @param   string              $name   Name of property.
     * @param   mixed               $value  Value of property.
     * @throws  moodle_exception
     */
    public function __set(string $name, $value) {
        if (strpos($name, 'customfield_') === 0) {
            $this->set_custom_course_field($name, $value);
        }
    }

    /**
     * Set id property. Auto-increments, so length should be fine.
     *
     * @param   int     $id ID value.
     * @return  course
     */
    public function set_id(int $id) : self {
        utilities::check_length('course', 'id', $id);
        $this->id = $id;
        return $this;
    }

    /**
     * Set ID of course's parent category.
     *
     * @param   int                 $category ID course's parent category.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_category(int $category): self {
        utilities::check_length('course', 'category', $category);
        $this->category = $category;
        return $this;
    }

    /**
     * Set course's fullname.
     *
     * @param   string              $fullname Course's full textual name.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_fullname(string $fullname): self {
        utilities::check_length('course', 'fullname', $fullname);
        $this->fullname = $fullname;
        return $this;
    }

    /**
     * Set course's shortname (unique).
     *
     * @param   string              $shortname Course's shortname - must be unique.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_shortname(string $shortname): self {
        utilities::check_length('course', 'shortname', $shortname);
        $this->shortname = $shortname;
        return $this;
    }

    /**
     * Set course's idnumber property (unique).
     *
     * @param   string              $idnumber Course's ID number - must be unique.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_idnumber(string $idnumber): self {
        utilities::check_length('course', 'idnumber', $idnumber);
        $this->idnumber = $idnumber;
        return $this;
    }

    /**
     * Set course's description/summary property.
     *
     * @param   string              $summary Description for course.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_summary(string $summary): self {
        utilities::check_length('course', 'summary', $summary);
        $this->summary = $summary;
        return $this;
    }

    /**
     * Set course's timecreated property.
     *
     * @param   int                 $timecreated Epoch of course's creation.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_timecreated(int $timecreated): self {
        utilities::check_length('course', 'timecreated', $timecreated);
        $this->timecreated = $timecreated;
        return $this;
    }

    /**
     * Set course's timemodified property.
     *
     * @param   int                 $timemodified Epoch of last modification to course.
     * @return  course
     * @throws  moodle_exception
     */
    public function set_timemodified(int $timemodified): self {
        utilities::check_length('course', 'timemodified', $timemodified);
        $this->timemodified = $timemodified;
        return $this;
    }

    /**
     * Set a custom course field.
     *
     * @param   string              $name Name of property.
     * @param   mixed               $value Property's value.
     * @return  $this               This course object.
     * @throws  moodle_exception
     */
    public function set_custom_course_field(string $name, $value) {
        static $customcoursefields = null;
        // Get all possible fields.
        if (is_null($customcoursefields)) {
            $customcoursefields = [];
            foreach (enrol_selma_get_custom_course_fields() as $field) {
                $customcoursefields[$field->get('shortname')] = $field->get_formatted_name();
            }
        }
        // Remove custom course field identifier (customfield_).
        $shortname = str_replace('customfield_', '', $name);

        // Check if it's a real field.
        if (!in_array($shortname, array_keys($customcoursefields))) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'shortname');
        }

        // TODO - Validate value?
        // Set property on class.
        $this->{$name} = $value;

        return $this;
    }

    /**
     * Method for saving course to database.
     *
     * Provides extra property checks and then hands off to core create_course() and update_course() functions.
     * Also saves custom course field data.
     *
     * @return bool
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function save() {
        global $DB;

        // Check minimum required properties have a value.
        if (trim($this->fullname) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'fullname');
        }
        if (trim($this->shortname) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'shortname');
        }
        if (trim($this->idnumber) === '') {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'idnumber');
        }
        if (!isset($this->category)) {
            // Set to config-specified category or set to default.
            $this->category = get_config('enrol_selma', 'newcoursecat') ?: core_course_category::get_default();
        }

        // Create or update.
        if ($this->id <= 0) {
            // TODO - Any other checks?
            // Set time properties
            if (!isset($this->timecreated) || !isset($this->timemodified)) {
                $this->timecreated = time();
                $this->timemodified = $this->timecreated;
            }
            $this->id = create_course($this)->id;
        } else {
            // Get the time_created if updating - column 'time_created' can not be null, apparently...
            if (!isset($this->timecreated) || !isset($this->timemodified)) {
                $this->timecreated = $DB->get_field('course', 'timecreated', array('id' => $this->id));
                $this->timemodified = time();
            }
            update_course($this);
        }

        // Save custom course fields.
        enrol_selma_save_custom_course_fields($this);

        return true;
    }
}
