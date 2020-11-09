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
 * Class to represent an intake.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local;

defined('MOODLE_INTERNAL') || die();

use DateTime;
use moodle_exception;
use stdClass;

/**
 * Class to represent an Intake extends stdClass and has public properties, but use setters to enforce type.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class intake extends stdClass {

    /** @var int $id Intake ID. */
    public $id = 0;

    /** @var int Programme ID Intake is related to. */
    public $programmeid = 0;

    /** @var string Intake code. */
    public $code = '';

    /** @var string Intake name. */
    public $name = '';

    /** @var int Intake start date stored as epoch. */
    public $startdate = 0;

    /** @var int Intake end date stored as epoch. */
    public $enddate = 0;

    /**
     * Set intake id.
     *
     * Note: Does not use auto increment is direct representation of value in SELMA.
     *
     * @param int $id
     * @return $this
     */
    public function set_id(int $id) : self {
        if ($id <= 0) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'id');
        }
        utilities::check_length('enrol_selma_intake', 'id', $id);
        $this->id = $id;
        return $this;
    }

    /**
     * Set the related programme id.
     *
     * @param int $programmeid
     * @return $this
     */
    public function set_programme_id(int $programmeid) : self {
        utilities::check_length('enrol_selma_intake', 'programmeid', $programmeid);
        $this->programmeid = $programmeid;
        return $this;
    }

    /**
     * Set intake code.
     *
     * @param string $code
     * @return $this
     */
    public function set_code(string $code) : self {
        utilities::check_length('enrol_selma_intake', 'code', $code);
        $this->code = $code;
        return $this;
    }

    /**
     * Set intake name.
     *
     * @param string $name
     * @return $this
     */
    public function set_name(string $name) : self {
        utilities::check_length('enrol_selma_intake', 'name', $name);
        $this->name = $name;
        return $this;
    }

    /**
     * Accepts string date, epoch, or DateTime.
     *
     * @param   mixed               $startdate Start date can be received in several formats.
     * @return  intake              $this Intake object.
     * @throws  moodle_exception
     */
    public function set_start_date($startdate) : self {
        $type = utilities::get_debug_type($startdate);
        switch (true) {
            case $type == 'int':
                $startdate = new DateTime('@' . $startdate);
                break;
            case $type == 'string':
                $startdate = new DateTime($startdate);
                break;
            case $type == 'DateTime':
                break;
            default:
                throw new moodle_exception('invalidargument', 'enrol_selma', null, 'startdate');
        }
        $this->startdate = $startdate->getTimestamp();
        return $this;
    }

    /**
     * Accepts string date, epoch, or DateTime.
     *
     * @param   mixed               $enddate End date can be received in several formats.
     * @return  intake              $this Intake object.
     * @throws  moodle_exception
     */
    public function set_end_date($enddate) : self {
        $type = utilities::get_debug_type($enddate);
        switch (true) {
            case $type == 'int':
                $enddate = new DateTime('@' . $enddate);
                break;
            case $type == 'string':
                $enddate = new DateTime($enddate);
                break;
            case $type == 'DateTime':
                break;
            default:
                throw new moodle_exception('invalidargument', 'enrol_selma', null, 'enddate');
        }
        $this->enddate = $enddate->getTimestamp();
        return $this;
    }

}
