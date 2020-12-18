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
 * SELMA Renderer class.
 *
 * @package     enrol_selma
 * @copyright   2020 LearningWorks <selma@learningworks.co.nz>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Manages rendering certain pages (e.g. clarity.php).
 *
 * @copyright  2020 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_selma_renderer extends plugin_renderer_base {
    /**
     * Renders overview of intakes.
     *
     * @param   array       $intakes Parameters to inform rendering.
     * @return  string      Returns HTML to render page.
     */
    public function overview(array $intakes) {
        global $PAGE;

        // Start accordion.
        $html = '<div id="accordion">';


        // Add a card for each intake.
        foreach ($intakes as $intake) {
            $html .= '
                <div class="card">
                    <button id="heading' . $intake->id . '" class="card-header btn btn-block bg-primary text-white" data-toggle="collapse" data-target="#collapse' . $intake->id . '" aria-expanded="true" aria-controls="collapse' . $intake->id . '">
                        <h5 class="mb-0">' . $intake->name . ' (' . $intake->id . ')</h5>
                    </button>
                    <div id="collapse' . $intake->id . '" class="collapse" aria-labelledby="heading' . $intake->id . '" data-parent="#accordion">
                        <div class="card-body table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">id</th>
                                        <th scope="col">name</th>
                                        <th scope="col">code</th>
                                        <th scope="col">startdate</th>
                                        <th scope="col">enddate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>' . $intake->id . '</td>
                                        <td>' . $intake->name . '</td>
                                        <td>' . $intake->code . '</td>
                                        <td>' . date('d/m/Y', $intake->startdate) . '</td>
                                        <td>' . date('d/m/Y', $intake->enddate) . '</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <a href="' .
                                        new moodle_url($PAGE->url, array('scope' => 'student', 'intake' => $intake->id)) .
                                        '" class="btn btn-outline-primary col">' . get_string('claritystudent', 'enrol_selma') .
                                    ' <span class="badge badge-pill badge-primary">' . $intake->numstudents . '</span>' .
                                '</a>
                                <a href="' .
                                        new moodle_url($PAGE->url, array('scope' => 'teacher', 'intake' => $intake->id)) .
                                        '" class="btn btn-outline-primary col">' . get_string('clarityteacher', 'enrol_selma') .
                                    ' <span class="badge badge-pill badge-primary">' . $intake->numteachers . '</span>' .
                                '</a>
                                <a href="' .
                                        new moodle_url($PAGE->url, array('scope' => 'course', 'intake' => $intake->id)) .
                                        '" class="btn btn-outline-primary col">' . get_string('claritycourse', 'enrol_selma') .
                                    ' <span class="badge badge-pill badge-primary">' . $intake->numcourses . '</span>' .
                                '</a>
                            </div>
                        </div>
                    </div>
                </div>';
        }

        // End accordion.
        $html .= '</div>';

        return $html;
    }

    /**
     * Renders list of users (students) in intake.
     *
     * @param   array       $users Array of Moodle users objects.
     * @return  string      Returns HTML to render page.
     */
    public function student(array $users) {
        global $CFG, $OUTPUT;

        // Table of list of students.
        $html = '<div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">id</th>
                                <th scope="col">fullname</th>
                                <th scope="col">email</th>
                                <th scope="col">idnumber</th>
                                <th scope="col">timecreated</th>
                                <th scope="col">lastlogin</th>
                            </tr>
                        </thead>
                        <tbody>';

        // Print a row for each user.
        foreach ($users as $user) {
            $html .= '
                            <tr>
                                <td>' . $user->id . '</td>
                                <td>' . $OUTPUT->user_picture($user, array('popup'=>true)) .
                                    ' <a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '">' . fullname($user) . '</a>
                                </td>
                                <td>' . $user->email . '</td>
                                <td>' . $user->idnumber . '</td>
                                <td>' . date('d/m/Y', $user->timecreated) . '</td>
                                <td>' . date('d/m/Y', $user->lastlogin) . '</td>
                            </tr>';
        }

        $html .= '
                        </tbody>
                    </table>
                </div>';

        return $html;
    }

    /**
     * Renders list of users (teachers) in intake.
     *
     * @param   array       $users Array of Moodle users objects.
     * @return  string      Returns HTML to render page.
     */
    public function teacher(array $users) {
        global $CFG, $OUTPUT;

        // Table of list of teachers.
        $html = '<div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">id</th>
                                <th scope="col">fullname</th>
                                <th scope="col">email</th>
                                <th scope="col">idnumber</th>
                                <th scope="col">timecreated</th>
                                <th scope="col">lastlogin</th>
                            </tr>
                        </thead>
                        <tbody>';

        // Print a row for each user.
        foreach ($users as $user) {
            $html .= '
                            <tr>
                                <td>' . $user->id . '</td>
                                <td>' . $OUTPUT->user_picture($user, array('popup'=>true)) .
                                    ' <a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '">' . fullname($user) . '</a>
                                </td>
                                <td>' . $user->email . '</td>
                                <td>' . $user->idnumber . '</td>
                                <td>' . date('d/m/Y', $user->timecreated) . '</td>
                                <td>' . date('d/m/Y', $user->lastlogin) . '</td>
                            </tr>';
        }

        $html .= '
                        </tbody>
                    </table>
                </div>';

        return $html;
    }

    /**
     * Renders list of courses associated to intake.
     *
     * @param   array       $courses Array of Moodle course objects.
     * @return  string      Returns HTML to render page.
     */
    public function course(array $courses) {
        global $CFG, $OUTPUT;

        // Table of list of teachers.
        $html = '<div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">id</th>
                                <th scope="col">fullname</th>
                                <th scope="col">shortname</th>
                                <th scope="col">idnumber</th>
                                <th scope="col">view</th>
                            </tr>
                        </thead>
                        <tbody>';

        // Print a row for each course.
        foreach ($courses as $course) {
            $html .= '
                            <tr>
                                <td>' . $course->id . '</td>
                                <td>' . $course->fullname . '</td>
                                <td>' . $course->shortname . '</td>
                                <td>' . $course->idnumber . '</td>
                                <td><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '" class="btn btn-outline-primary">View</a></td>
                            </tr>';
        }

        $html .= '
                        </tbody>
                    </table>
                </div>';

        return $html;
    }
}
