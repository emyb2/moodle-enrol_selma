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
 * Table SQL overridden class for plugin log table.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local\table;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core_text;
use moodle_url;
use stdClass;
use table_sql;
use enrol_selma\local\log_levels;

/**
 * Table SQL overridden class for plugin log table.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_table extends table_sql {

    use add_column_header;

    /**
     * Component being dealt with.
     */
    public const COMPONENT = 'enrol_selma';

    /**
     * Default page size.
     */
    const DEFAULT_PAGE_SIZE = 50;

    /**
     * This - 'log_table' constructor.
     *
     * @param   string              $uniqueid
     * @param   moodle_url          $baseurl
     * @param   array               $filters
     * @throws  coding_exception
     */
    public function __construct($uniqueid, moodle_url $baseurl, array $filters = []) {
        parent::__construct($uniqueid);
        $this->add_column_header('levelname', get_string('levelname', static::COMPONENT), true, 'text-nowrap');
        $this->add_column_header('message', get_string('message', static::COMPONENT));
        $this->add_column_header('timestamp', get_string('time', static::COMPONENT), true, 'text-nowrap');
        $this->is_collapsible = false;
        $this->sort_default_column = 'timestamp';
        $this->sort_default_order  = SORT_DESC;
        $this->pageable(true);
        $this->define_baseurl($baseurl);
        $this->build_sql($filters); // Finally build the required SQL.
    }

    /**
     * Systematically build the SQL needed.
     *
     * @param array $filters The filters used in SQL.
     */
    protected function build_sql(array $filters) {
        $fields = "l.*";
        $from = "{enrol_selma_log} l";
        $level = $filters['filterlevel'] ?? 0;
        $before = $filters['filterbefore'] ?? 0;
        $after = $filters['filterafter'] ?? 0;
        $wheres = [];
        $params = [];
        if (!$level) {
            $wheres[] = "l.level >= :level";
        } else {
            $wheres[] = "l.level = :level";
        }
        $params['level'] = $level;
        if ($before) {
            $wheres[] = "l.timestamp < :before";
            $params['before'] = $before;
        }
        if ($after) {
            $wheres[] = "l.timestamp > :after";
            $params['after'] = $after;
        }
        $where = join(' AND ', $wheres);
        $sql = new stdClass();
        $sql->fields = $fields;
        $sql->from = $from;
        $sql->where = $where;
        $sql->params = $params;
        $this->sql = $sql;
    }

    /**
     * Build content for levelname column.
     *
     * @param   mixed   $values Value object passed to process.
     * @return  string  HTML returned.
     */
    public function col_levelname($values) {
        $awesomeiconname = '';
        $loglevelselector = core_text::strtolower("log-level-{$values->levelname}");
        switch ($values->level) {
            case log_levels::DEBUG:
                $awesomeiconname = 'fa-bug';
                break;
            case log_levels::INFO:
                $awesomeiconname = 'fa-info-circle';
                break;
            case log_levels::NOTICE:
                $awesomeiconname = 'fa-sticky-note';
                break;
            case log_levels::WARNING:
                $awesomeiconname = 'fa-exclamation-triangle';
                break;
            case log_levels::ERROR:
                $awesomeiconname = 'fa-times-circle';
                break;
            case log_levels::CRITICAL:
                $awesomeiconname = 'fa-heartbeat';
                break;
            case log_levels::ALERT:
                $awesomeiconname = 'fa-bell';
                break;
            case log_levels::EMERGENCY:
                $awesomeiconname = 'fa-ambulance';
                break;
        }
        return '<div class="log-level '. $loglevelselector . '"><i class="fa '. $awesomeiconname .'" aria-hidden="true"></i>'.
            '<span>' . $values->levelname . '</span></div>';
    }

    /**
     * Build content for timestamp column.
     *
     * @param   mixed   $values Value object passed to process.
     * @return  string  HTML returned.
     */
    public function col_timestamp($values) {
        return userdate($values->timestamp);
    }

}
