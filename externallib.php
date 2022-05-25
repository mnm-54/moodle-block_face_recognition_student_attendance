<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * externallib file for sevices functions
 *
 * @package    block_face_recognition_student_attendance
 * @copyright  2022 munem
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/externallib.php");

class block_face_recognition_student_attendance_student_image extends external_api
{
    public static function get_student_course_image_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, "Course id"),
                'studentid' => new external_value(PARAM_INT, "Student id")
            )
        );
    }

    public static function get_student_course_image($courseid, $studentid)
    {
        die(var_dump("inside the api"));
    }

    public static function get_student_course_image_returns()
    {
        return new external_single_structure(
            array(
                'image_url' => new external_value(PARAM_URL, 'Url of student image')
            )
        );
    }
}
