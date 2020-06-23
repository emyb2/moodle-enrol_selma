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

namespace enrol_selma\local\table;

defined('MOODLE_INTERNAL') || die();

/**
 * Trait for adding add column header functionality.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait add_column_header {

    /**
     * Allows to add only one column name and header to the table (parent class methods only allow to set all).
     *
     * @param string $key
     * @param string $label
     * @param bool $sortable
     * @param string $columnclass
     */
    protected function add_column_header($key, $label, $sortable = true, $columnclass = '') {
        if (empty($this->columns)) {
            $this->define_columns([$key]);
            $this->define_headers([$label]);
        } else {
            $this->columns[$key] = count($this->columns);
            $this->column_style[$key] = array();
            $this->column_class[$key] = $columnclass;
            $this->column_suppress[$key] = false;
            $this->headers[] = $label;
        }
        if ($columnclass !== null) {
            $this->column_class($key, $columnclass);
        }
        if (!$sortable) {
            $this->no_sorting($key);
        }
    }
}
