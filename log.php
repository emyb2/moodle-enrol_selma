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
 * SELMA Logs page.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/formslib.php');

$filterlevel = optional_param('filterlevel', 0, PARAM_INT);
$filterbefore = optional_param('filterbefore', 0, PARAM_INT);
$filterafter = optional_param('filterafter', 0, PARAM_INT);
$filters = [
    'filterlevel' => $filterlevel,
    'filterbefore' => $filterbefore,
    'filterafter' => $filterafter
];
admin_externalpage_setup('enrol_selma/log', '', $filters); // Set-up must come first.
$baseurl = $PAGE->url;
$filterform = new enrol_selma\local\form\log_filter_form();
$data = $filterform->get_data();
if ($data) {
    if ($data->action == 'clearfilter') {
        $baseurl->remove_params($filters);
        redirect($baseurl);
    }
    if ($data->action == 'applyfilter') {
        $filters['filterlevel'] = $data->level ?? 0;
        $filters['filterbefore'] = ($data->enabledbefore) ? $data->before :  0;
        $filters['filterafter'] = ($data->enabledafter) ? $data->after : 0;
        $baseurl->params($filters);
    }
}
$filterform->apply_filters($filters);
$report = new enrol_selma\local\table\log_table('enrol_selma/log', $baseurl, $filters);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('applicationlog', 'enrol_selma'));
$report->out(enrol_selma\local\table\log_table::DEFAULT_PAGE_SIZE, false);
$filterform->display();
echo $OUTPUT->footer();
