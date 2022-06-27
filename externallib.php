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
        global $DB;
        $coursename = $DB->get_record_select('course', "id = :id", array('id' => $courseid));

        // $context = context_course::instance($courseid);
        $context = context_system::instance();

        $fs = get_file_storage();
        if ($files = $fs->get_area_files($context->id, 'local_participant_image_upload', 'student_photo')) {
            foreach ($files as $file) {
                if ($studentid == $file->get_itemid() && $file->get_filename() != '.') {
                    // Build the File URL. Long process! But extremely accurate.
                    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                    // Display the image
                    $download_url = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();

                    $return_value = [
                        'image_url' => $download_url,
                        'course_name' => $coursename->fullname
                    ];

                    return $return_value;
                }
            }
        }
        return [
            'image_url' => false,
            'course_name' => $coursename->fullname
        ];
    }

    public static function get_student_course_image_returns()
    {
        return new external_single_structure(
            array(
                'image_url' => new external_value(PARAM_URL, 'Url of student image'),
                'course_name' => new external_value(PARAM_TEXT, 'Course name')

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
                'sessionid' => new external_value(PARAM_INT, "Session id"),
            )
        );
    }
    public static function student_attendance_update($courseid, $studentid, $sessionid)
    {
        global $DB;

        $record = $DB->get_record('block_face_recog_attendance', array(
                        'course_id' => $courseid,
                        'student_id' => $studentid,
                        'session_id' => $sessionid
                    ));
        if(empty($record)) {
            $record = new stdClass();
            $record->student_id = $studentid;
            $record->course_id = $courseid;
            $record->session_id = $sessionid;
            $record->time = time();
            
            $DB->insert_record('block_face_recog_attendance', $record);
        } else {
            $record->time = time();
            
            $DB->update_record('block_face_recog_attendance', $record);
        }
        

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
        $url = get_config('block_face_recognition_student_attendance', 'endpoint');

        $token = self::get_bearer_token($url);
        $token = $token->token;

        $studentimg = str_replace('data:image/png;base64,', '', $studentimg);
        $webcampicture = str_replace('data:image/png;base64,', '', $webcampicture);

        $delimiter = '-------------' . uniqid();
        // file upload fields: name => array(type=>'mime/type',content=>'raw data')
        $fileFields = array(
            'original_img' => array(
                'type' => 'image/png',
                'filename' => 'image1.png',
                'transfer-type' => 'binary',
                'content' => $studentimg
            ),
            'face_img' => array(
                'type' => 'image/png',
                'filename' => 'image2.png',
                'transfer-type' => 'binary',
                'content' => $webcampicture
            ), /* ... */
        );
        // all other fields (not file upload): name => value
        $postFields = array(
            'threshold' => 60,
            /* ... */
        );



        $data = '';



        // populate normal fields first (simpler)
        foreach ($postFields as $name => $content) {
            $data .= "--" . $delimiter . "\r\n";
            $data .= 'Content-Disposition: form-data; name="' . $name . '"';
            // note: double endline
            $data .= "\r\n\r\n";
            $data .= $content;
            $data .= "\r\n";
        }
        // populate file fields
        foreach ($fileFields as $name => $file) {
            $data .= "--" . $delimiter . "\r\n";
            // "filename" attribute is not essential; server-side scripts may use it
            $data .= 'Content-Disposition: form-data; name="' . $name . '";' .
                ' filename="' . $file['filename'] . '"' . "\r\n";
            // this is, again, informative only; good practice to include though
            $data .= 'Content-Type: ' . $file['type'] . "\r\n";
            // this endline must be here to indicate end of headers
            $data .= "\r\n";
            // $data .= 'Content-Transfer-Encoding: ' . $file['transfer-type'] . "\r\n";
            // // this endline must be here to indicate end of headers
            // $data .= "\r\n";
            // the file itself (note: there's no encoding of any kind)
            $data .= $file['content'] . "\r\n";
        }
        // last delimiter
        $data .= "--" . $delimiter . "--\r\n";

        // echo $data;
        // var_dump($delimiter);
        $url .= '/verify';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array(
            'Content-Type: multipart/form-data; boundary=' . $delimiter,
            'Content-Length: ' . strlen($data),
            'Authorization: Bearer ' . $token,
        ));

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        $results = curl_exec($handle);
        curl_close($handle);
        $results = json_decode($results);

        return $results;
    }
    public static function call_face_recog_api_returns()
    {
        return new external_single_structure(
            array(
                'confidence' => new external_value(PARAM_RAW, 'upadated or failed', VALUE_OPTIONAL)
            )
        );
    }

    private static function get_bearer_token($endpoint)
    {
        $username = get_config('block_face_recognition_student_attendance', 'username');
        $paswd = get_config('block_face_recognition_student_attendance', 'password');

        $delimiter = '-------------' . uniqid();
        // post fields 
        $postFields = array(
            'username' => $username,
            'password' =>  $paswd,
        );

        $data = '';
        // populate normal fields first (simpler)
        foreach ($postFields as $name => $content) {
            $data .= "--" . $delimiter . "\r\n";
            $data .= 'Content-Disposition: form-data; name="' . $name . '"';
            // note: double endline
            $data .= "\r\n\r\n";
            $data .= $content;
            $data .= "\r\n";
        }
        // last delimiter
        $data .= "--" . $delimiter . "--\r\n";

        $endpoint .= '/get_token';

        $handle = curl_init($endpoint);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array(
            'Content-Type: multipart/form-data; boundary=' . $delimiter,
            'Content-Length: ' . strlen($data)
        ));

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        $token = curl_exec($handle);
        curl_close($handle);
        $token = json_decode($token);

        return $token;
    }

    public static function check_active_window_parameters() 
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, "Course id"),
            )
        );
    }
    public static function check_active_window($courseid) 
    {
        global $DB;
        $course = $DB->get_record('local_piu_window', array('course_id' => $courseid, 'active' => 1));

        return [
            'active' => $course->active,
            'sessionid' => $course->session_id,
        ];
    }
    public static function check_active_window_returns() 
    {
        return new external_single_structure(
            array(
                'active' => new external_value(PARAM_INT, 'Return active 0 or 1'),
                'sessionid' => new external_value(PARAM_INT, 'Return session id')
            )
        );
    }
}
