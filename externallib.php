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

use core_calendar\local\event\forms\update;

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
        $context = context_course::instance($courseid);

        $fs = get_file_storage();
        if ($files = $fs->get_area_files($context->id, 'local_participant_image_upload', 'student_photo')) {
            foreach ($files as $file) {
                if ($studentid == $file->get_itemid() && $file->get_filename() != '.') {
                    // Build the File URL. Long process! But extremely accurate.
                    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), true);
                    // Display the image
                    $download_url = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();

                    $return_value = [
                        'image_url' => $download_url
                    ];

                    return $return_value;
                }
            }
        }
        return [
            'image_url' => false
        ];
    }

    public static function get_student_course_image_returns()
    {
        return new external_single_structure(
            array(
                'image_url' => new external_value(PARAM_URL, 'Url of student image')
            )
        );
    }

    /**
     * Update db for student attendance
     */
    public static function student_attendance_update_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, "Course id"),
                'studentid' => new external_value(PARAM_INT, "Student id"),
                'time' => new external_value(PARAM_INT, "Date & time of attendance")
            )
        );
    }
    public static function student_attendance_update($courseid, $studentid, $time)
    {
        global $DB;
        $record = new stdClass();
        $record->student_id = $studentid;
        $record->course_id = $courseid;
        $record->time = $time;
        $DB->insert_record('block_face_recog_attendance', $record);

        return ['status' => 'updated'];
    }
    public static function student_attendance_update_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'upadated or failed')
            )
        );
    }
}
