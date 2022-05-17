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
        $this->title = 'attendance';
    }


    function has_config()
    {
        return true;
    }

    function get_content()
    {
        if ($this->content !== NULL) {
            return $this->content;
        }

        global $USER, $CFG;

        $courses = enrol_get_my_courses();

        $this->content = new stdClass;
        $this->content->text = $USER->username . '<br><hr>';

        $this->content->text .= $this->fn_get_block_image_url(4, $USER->id);

        foreach ($courses as $course) {
            $editurl = $CFG->wwwroot;
            $this->content->text .= $course->fullname . '<button type="button" style="float: right;" onclick="location.href=\'' . $editurl . '\'">Give attandance</button>'
                . '<br>' . '<br>';
        }

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
}
