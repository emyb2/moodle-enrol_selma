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

namespace enrol_selma\local\monolog;

defined('MOODLE_INTERNAL') || die();

use ddl_exception;
use dml_exception;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use stdClass;

/**
 * Moodle DB Table Monolog Handler.
 *
 * @package     Monolog
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleDBTableHandler extends AbstractProcessingHandler {

    /**
     * @var string Moodle component name.
     */
    private $component;

    /**
     * @var string Name of Moodle table to log to.
     */
    private $tablename;

    /**
     * Constructor sets the table to log to in Moodle.
     *
     * @param string $tablename
     * @param string $component
     * @param int $level
     * @param bool $bubble
     * @throws ddl_exception
     */
    public function __construct(string $tablename, string $component = '', $level = Logger::DEBUG, bool $bubble = true) {
        global $DB;

        $dbmanager = $DB->get_manager();
        if (!$dbmanager->table_exists($tablename)) {
            throw new ddl_exception("Moodle table {$tablename} does not exist");
        } else {
            $this->tablename = $tablename;
        }
        $this->component = $component;
        parent::__construct($level, $bubble);
    }

    /**
     * Get table name.
     *
     * @return string|null
     */
    public function get_tablename() : ? string  {
        return $this->tablename;
    }

    /**
     * Writes the record down to passed in table.
     *
     * @param array $record
     * @throws dml_exception
     * @return void
     */
    protected function write(array $record) : void {
        global $DB;
        if ($this->tablename) {
            $item               = new stdClass();
            $item->message      = $record['message'];
            $item->level        = $record['level'];
            $item->levelname    = $record['level_name'];
            $item->context      = json_encode((object) $record['context']);
            $item->channel      = $record['channel'];
            $item->component    = $record['context']['component'] ?? $this->component;
            $item->timestamp    = $record['datetime']->getTimestamp();
            $DB->insert_record($this->tablename, $item);
        }
    }

}
