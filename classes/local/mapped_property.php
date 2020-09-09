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

use coding_exception;

/**
 * A mapped property.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mapped_property {

    private $object;
    private $name;
    private $humanfriendlyname;
    private $mappedpropertyname;
    private $defaultmappedpropertyname;
    private $required;
    private $mutatorproperty;
    private $mutatormethod;

    public function __construct(
        &$object,
        string $name,
        ? string $humanfriendlyname = null,
        ? string $mappedpropertyname = null,
        ? string $defaultmappedpropertyname = null,
        bool $required = false,
        ? string $mutatormethod = null
    ) {
        $this->object = $object;
        $this->name = $name;
        $this->humanfriendlyname = $humanfriendlyname;
        $this->mappedpropertyname = $mappedpropertyname;
        $this->defaultmappedpropertyname = $defaultmappedpropertyname;
        $this->required = $required;
        $this->mutatormethod = $mutatormethod;
    }

    public function get_name() : string {
        return $this->name;
    }

    public function get_human_friendly_name() : ? string {
        return $this->humanfriendlyname;
    }

    public function get_mapped_property_name() : ? string {
        return $this->mappedpropertyname ?? $this->defaultmappedpropertyname;
    }

    public function get_default_mapped_property_name() : ? string {
        return $this->defaultmappedpropertyname;
    }

    public function is_required() : bool {
        return $this->required;
    }

    public function is_valid() : bool {
        if ($this->required && !is_null($this->get_mapped_property_name())) {
            return true;
        }
        return false;
    }

    public function set_mapped_property_name(string $mappedpropertyname) : self {
        $this->mappedpropertyname = $mappedpropertyname;
        return $this;
    }

    public function set_value($value) {
        if (!is_null($this->mutatormethod) && trim($this->mutatormethod) !== '') {
            $object->{$this->mutatormethod}($value);
        } else if (property_exists($object, $this->get_name())) {
            $object->{$this->name} = $value;
        }
        throw new moodle_exception(
            'noclasspropertymutator', 'enrol_selma', null,
            ['property' => $this->name, 'class' => get_class($object)]
        );
    }

}