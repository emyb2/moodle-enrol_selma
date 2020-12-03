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
 * Plugin version and other meta-data are defined here.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component  = 'enrol_selma';        // Recommended since 2.0.2 (MDL-26035). Required since 3.0 (MDL-48494).
$plugin->release    = '0.5.0 (MOOMA)';      // Human-readable release version.
$plugin->version    = 2020120316;           // YYYYMMDDHH (year, month, day, 24-hr format hour).
$plugin->requires   = 2018051703;           // YYYYMMDDHH (Version number for Moodle 3.5.3).
$plugin->maturity   = MATURITY_ALPHA;       // Code maturity/stability.
