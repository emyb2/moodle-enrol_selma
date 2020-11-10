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
 * SELMA plugin 'get_gradebook_items' external file.
 *
 * @package    enrol_selma
 * @category   external
 * @copyright  2020 LearningWorks <selma@learningworks.co.nz>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_selma\local\external;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__, 4) . '/locallib.php');

use coding_exception;
use context_system;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;

/**
 * Class get_gradebook_items used to get a Moodle course's gradebook items (most likely for listing).
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_gradebook_items extends external_api {
    /**
     * Returns required parameters to retrieve a course's gradebook items.
     *
     * @return external_function_parameters Description of parameters and expected type.
     * @throws coding_exception
     */
    public static function get_gradebook_items_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT,
                    get_string('get_gradebook_items_parameters::courseid', 'enrol_selma')
                )
            ],
            get_string('get_gradebook_items_parameters', 'enrol_selma')
        );
    }

    /**
     * The function itself - let's get that course's gradebook items.
     *
     * @param   int     $courseid Course ID of course to get gradebook items of.
     * @return  array   Course's gradebook item(s), if any.
     */
    public function __construct(int $courseid) {
        return self::get_gradebook_items($courseid);
    }

    /**
     * The function itself - let's get that course's gradebook items.
     *
     * @param   int     $courseid Course ID of course to get gradebook items of.
     * @return  array   Course's gradebook item(s), if any.
     */
    public static function get_gradebook_items(int $courseid) {
        // Validate parameters.
        $params = self::validate_parameters(self::get_gradebook_items_parameters(),
            [
                'courseid' => $courseid
            ]
        );

        // Validate context and check capabilities.
        self::validate_context(context_system::instance());

        // Returned details.
        return enrol_selma_get_gradebook_items($params['courseid']);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure Array of description of values returned by 'get_gradebook_items' function.
     */
    public static function get_gradebook_items_returns() {
        return new external_single_structure(
            [
                'items' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT,
                                get_string('get_gradebook_items_returns::itemid', 'enrol_selma')),
                            'itemname' => new external_value(PARAM_TEXT,
                                get_string('get_gradebook_items_returns::name', 'enrol_selma'))
                        ]
                    ),
                    get_string('get_gradebook_items_returns::items', 'enrol_selma'),
                    VALUE_OPTIONAL
                ),
                // TODO - Maybe we should be returning 'warning' values, instead of in the message.
                // As per - https://docs.moodle.org/dev/Errors_handling_in_web_services#Warning_messages
                // For example, refer to mod/assign/externallib.php:614.
                'warnings' => new external_warnings()
            ],
            get_string('get_gradebook_items_returns', 'enrol_selma')
        );
    }
}
