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
 * Web service description
 *
 * @package    block_face_recognition_student_attendance
 * @copyright  2022 munem
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'block_face_recognition_student_attendance_image_api' => array(
        'classname' => 'block_face_recognition_student_attendance_student_image',
        'methodname'  => 'get_student_course_image',
        'classpath'   => 'blocks/face_recognition_student_attendance/externallib.php',
        'description' => 'Returns the student image saved in the course for attendance',
        'type'        => 'write',
        'ajax' => true,
    ),
    'block_face_recognition_student_attendance_update_db' => array(
        'classname' => 'block_face_recognition_student_attendance_student_image',
        'methodname'  => 'student_attendance_update',
        'classpath'   => 'blocks/face_recognition_student_attendance/externallib.php',
        'description' => 'Saves student data to attendance table after completing attendance',
        'type'        => 'write',
        'ajax' => true,
    ),
    'block_face_recognition_student_attendance_face_recog_api' => array(
        'classname' => 'block_face_recognition_student_attendance_student_image',
        'methodname'  => 'call_face_recog_api',
        'classpath'   => 'blocks/face_recognition_student_attendance/externallib.php',
        'description' => 'Calling the api for face recog',
        'type'        => 'write',
        'ajax' => true,
    ),
    'block_face_recognition_student_attendance_check_active_window' => array(
        'classname' => 'block_face_recognition_student_attendance_student_image',
        'methodname'  => 'check_active_window',
        'classpath'   => 'blocks/face_recognition_student_attendance/externallib.php',
        'description' => 'Calling the api for checking active window for attendance of a particular course',
        'type'        => 'write',
        'ajax' => true,
    )
);

$services = array(
    'block_face_recognition_student_attendance_services' => array(
        'functions' => array(
            'block_face_recognition_student_attendance_image_api',
            'block_face_recognition_student_attendance_update_db',
            'block_face_recognition_student_attendance_face_recog_api',
            'block_face_recognition_student_attendance_check_active_window'
        ),
        'restrictedusers' => 0,
        // into the administration
        'enabled' => 1,
        'shortname' =>  'bfrs_image_api',
    )
);
