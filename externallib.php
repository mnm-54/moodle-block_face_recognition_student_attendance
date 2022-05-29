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
                    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
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

    /**
     * calling api using curl
     */

    public static function call_face_recog_api_parameters()
    {
        return new external_function_parameters(
            array(
                'studentimg' => new external_value(PARAM_RAW, "Student id"),
                'webcampicture' => new external_value(PARAM_RAW, "Student id"),
            )
        );
    }
    public static function call_face_recog_api($studentimg, $webcampicture)
    {
        global $CFG;

        $data = array(
            'original_img' => $studentimg,
            'face_img' => $webcampicture,
            'threshold' => 60
        );

        $ch = curl_init();
        $token = 'JULptUQb3X2K4iKS4PiF';
        $authorization = "Authorization: Bearer " . $token;
        $options = array(
            CURLOPT_URL => 'http://13.215.160.155:5000/verify',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => 1
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization));
        curl_setopt_array($ch, $options);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $results = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($results);

        return $results;
    }
    public static function call_face_recog_api_returns()
    {
        return new external_value(PARAM_RAW, 'human description of the returned value');
        return new external_single_structure(
            array(
                'confidence' => new external_value(PARAM_RAW, 'upadated or failed', VALUE_OPTIONAL)
            )
        );
    }
}
