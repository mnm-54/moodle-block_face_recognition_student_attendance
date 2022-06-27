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
 * This is a one-line short description of the file.
 *
 * @package    block_face_recognition_student_attendance
 * @copyright  2022 munem
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');


class block_face_recognition_student_attendance extends block_base
{
    function init()
    {
        $this->title = get_string('Attendance', 'block_face_recognition_student_attendance');
    }


    function has_config()
    {
        return true;
    }

    function get_content()
    {
        global $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        global $USER, $CFG, $PAGE;

        $courses = $this->get_enrolled_courselist_with_active_window($USER->id);
        $attendancedonetxt = get_string('attendance_done', 'block_face_recognition_student_attendance');
        $attendancebuttontxt = get_string('attendance_button', 'block_face_recognition_student_attendance');
        $attendancebuttontitle = get_string('attendance_button_title', 'block_face_recognition_student_attendance');
        
        $this->content = new stdClass;
        $this->content->text = '<hr>';

        $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        foreach ($courses as $course) {
            $done = $DB->count_records("block_face_recog_attendance", array('student_id' => $USER->id, 'course_id' => $course->cid, 'time' => $today));
            if ($done) {
                $this->content->text .= "
                <div class='d-flex justify-content-between mb-3'>
                    <div class='d-flex align-items-center'>" . $course->fullname . "</div>
                    <div class='d-flex align-items-center'>
                        <p class='text-success m-0' title='" . $attendancedonetxt . ", date:" . date("d.m.y") . "'>" . $attendancedonetxt . "</p>                       
                    </div>
                </div>
                <hr>
                ";
            } else {
                $this->content->text .= "
                <div class='d-flex justify-content-between mb-3'>
                    <div class='d-flex align-items-center'>" . $course->fullname . "</div>
                    <div>
                        <button 
                            type='button' 
                            id='" . $course->cid . "' 
                            class='action-modal btn btn-primary' 
                            title='". $attendancebuttontitle . "'>
                            ". $attendancebuttontxt ."
                        </button>
                    </div>
                </div>
                <hr>
                ";
            }
        }
        $successmessage = get_config('block_face_recognition_student_attendance', 'successmessage');
        $failedmessage = get_config('block_face_recognition_student_attendance', 'failedmessage');
        $this->page->requires->js_call_amd('block_face_recognition_student_attendance/attendance_modal', 'init', array($USER->id, $successmessage, $failedmessage));

        return $this->content;
    }

    /**
     * 
     * Allow the block to have multiple instance
     * 
     * @return bool
     */
    function instance_allow_multiple()
    {
        return false;
    }

    /**
     * @param int $courseid course id
     * @param int $studentid student id
     * 
     * @return image instance if available
     */
    function fn_get_block_image_url($courseid, $studentid)
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

                    // return '<a href="' . $download_url . '">' . $file->get_filename() . '</a><br/>';
                    return '<img src="' . $download_url . '" width="auto" height="100"/><br/>';
                }
            }
        }
        return '<p>Please upload an image first</p>';
    }

    function get_enrolled_courselist_with_active_window($userid)
    {
        global $DB;
        $sql = "SELECT c.fullname 'fullname', c.id 'cid'
                FROM {role_assignments} r
                JOIN {user} u on r.userid = u.id
                JOIN {role} rn on r.roleid = rn.id
                JOIN {context} ctx on r.contextid = ctx.id
                JOIN {course} c on ctx.instanceid = c.id
                JOIN {local_piu_window} lpiu on c.id = lpiu.course_id
                WHERE rn.shortname = 'student'  and lpiu.active = 1 and u.id=" . $userid;
        $courselist = $DB->get_records_sql($sql);
        return $courselist;
    }
}
