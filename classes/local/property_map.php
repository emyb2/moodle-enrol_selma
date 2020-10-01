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

use ArrayIterator;
use IteratorAggregate;
use moodle_exception;
use stdClass;

/**
 * A collection of mapped properties.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class property_map implements IteratorAggregate {

    protected $object;

    protected $confignamegroupingprefix;

    protected $errors;

    /** @var mapped_property[] $mappedproperties Array of mapped properties. */
    protected $mappedproperties;

    public function __construct(&$object) {
        $this->object = $object;
        $this->confignamegroupingprefix = '';
        $this->mappedproperties = [];
        $this->errors = [];
    }

    public function add_mapped_property(mapped_property $mappedproperty) : self {
        $this->mappedproperties[$mappedproperty->get_name()] = $mappedproperty;
        return $this;
    }

    public function get_config_name_grouping_prefix() {
        return $this->confignamegroupingprefix;
    }

    public function get_last_error() {
        return end($this->errors);
    }

    public function get_object() {
        return $this->object;
    }

    public function get_property(string $name) : mapped_property {
        if (!isset($this->mappedproperties[$name])) {
            throw new moodle_exception('unexpectedvalue', 'enrol_selma', null, 'name');
        }
        return $this->mappedproperties[$name];
    }

    public function get_mapped_properties() {
        return $this->mappedproperties;
    }

    public function getIterator() {
        return new ArrayIterator($this->mappedproperties);
    }

    public function set_mapped_properties_from_config(stdClass $config) {
        foreach (get_object_vars($config) as $configproperty => $mappedproperty) {
            $pattern = '/^' . $this->confignamegroupingprefix . '/';
            $property = preg_replace($pattern, '', $configproperty, 1);
            if (!is_null($property) && $property != $configproperty && isset($this->mappedproperties[$property])) {
                $this->mappedproperties[$property]->set_mapped_property_name($mappedproperty);
            }
        }
        return $this;
    }

    public function set_config_name_grouping_prefix(string $confignamegroupingprefix) {
        $this->confignamegroupingprefix = $confignamegroupingprefix;
        return $this;
    }

    public function write_data(array $data) {
       foreach ($this->mappedproperties as $property) {
           if ($property->is_required() && !isset($data[$property->get_mapped_property_name()])) {
               throw new moodle_exception('exception_valuerequired', 'enrol_selma', null, $property->get_name());
           }
           if (!is_null($property->get_mapped_property_name()) && isset($data[$property->get_mapped_property_name()])) {
               $value = $data[$property->get_mapped_property_name()];
               $property->set_value($value);
           }
       }
    }

    public function is_valid() : bool {
        foreach ($this->mappedproperties as $mappedproperty) {
            if (!$mappedproperty->is_valid()) {
                $this->errors[] = get_string('mappedpropertybadsetup', 'enrol_selma', $mappedproperty->get_name());
                return false;
            }
        }
        return true;
    }

}
