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
 * Moodle Email Handler for Monolog.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local\monolog;

defined('MOODLE_INTERNAL') || die();

use ddl_exception;
use dml_exception;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\MailHandler;
use stdClass;

/**
 * Moodle Email Monolog Handler.
 *
 * @package     enrol_selma
 * @copyright   2020 Troy Williams <troy.williams@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleEmailHandler extends MailHandler {
    /**
     * Moodle user from which the message will be sent.
     * @var array
     */
    protected $from;

    /**
     * Moodle users to which the message will be sent.
     * @var array
     */
    protected $to;

    /**
     * The subject of the email.
     * @var string
     */
    protected $subject;

    /**
     * Constructs instance of MailHandler.
     *
     * @param array        $to             The receivers of the mail.
     * @param string       $subject        The subject of the mail.
     * @param stdClass     $from           The sender of the mail.
     * @param string|int   $level          The minimum logging level at which this handler will be triggered.
     * @param bool         $bubble         Whether the messages that are handled can bubble up the stack or not.
     */
    public function __construct(
        array $to,
        string $subject,
        stdClass $from,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->to = $to;
        $this->subject = $subject;
        $this->from = $from;
    }

    /**
     * Send a mail with the given content.
     *
     * @param   string  $content Formatted email body to be sent.
     * @param   array   $records The array of log records that formed this content.
     */
    protected function send($content, array $records) : void {
        $messagetext = '';
        $messagehtml = '';
        if ($this->isHtmlBody($content)) {
            $messagehtml = $content;
        } else {
            $messagetext = $content;
        }
        $subject = $this->subject;
        if ($records) {
            $subjectFormatter = new LineFormatter($this->subject);
            $subject = $subjectFormatter->format($this->getHighestRecord($records));
        }
        foreach ($this->to as $to) {
            if ($to instanceof stdClass) {
                email_to_user($to, $this->from, $subject, $messagetext, $messagehtml);
            }
        }
    }
}
