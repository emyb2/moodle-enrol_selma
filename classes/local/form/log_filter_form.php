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

namespace enrol_selma\local\form;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use moodleform;
use enrol_selma\local\log_levels;

/**
 * Form for fields used for filtering the log.
 *
 * @package     enrol_selma
 * @subpackage  form
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_filter_form extends moodleform {

    public function definition() {
        $form = $this->_form;
        $form->addElement(
            'header', 'filters', get_string('filters', 'enrol_selma')
        );
        $options[0] = get_string('all');
        $options += log_levels::all();
        $form->addElement(
            'select', 'level', get_string('level', 'enrol_selma'), $options, 0
        );
        $form->addElement(
            'date_time_selector', 'after', get_string('after', 'enrol_selma')
        );
        $form->addElement('advcheckbox', 'enabledafter', get_string('enabled', 'enrol_selma'));
        $form->disabledIf('after', 'enabledafter', 'notchecked');
        $form->addElement(
            'date_time_selector', 'before', get_string('before', 'enrol_selma')
        );
        $form->addElement('advcheckbox', 'enabledbefore', get_string('enabled', 'enrol_selma'));
        $form->disabledIf('before', 'enabledbefore', 'notchecked');

        $actionbuttongroup = [];
        $actionbuttongroup[] =& $form->createElement(
            'submit', 'applyfilteractionbutton', get_string('apply', 'enrol_selma')
        );
        $actionbuttongroup[] =& $form->createElement(
            'submit', 'clearfilteractionbutton', get_string('clear', 'enrol_selma')
        );
        $form->addGroup(
            $actionbuttongroup, 'actionbuttongroup', '', ' ', false
        );
    }

    public function get_data() {
        $data = parent::get_data();
        if ($data) {
            foreach (get_object_vars($data) as $key => $value) {
                if (strpos($key, 'actionbutton') !== false) {
                    $action = strstr($key, 'actionbutton', true);
                    $data->action = $action;
                }
            }
        }
        return $data;
    }

    public function apply_filters(array $filters) {
        $data = [];
        $data['before'] = $filters['filterbefore'] ?? 0;
        $data['enabledbefore'] = ($filters['filterbefore']) ? 1 : 0;
        $data['after'] = $filters['filterafter'] ?? 0;
        $data['enabledafter'] = ($filters['filterafter']) ? 1 : 0;
        parent::set_data($data);
    }

}
