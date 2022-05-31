  
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
 * Settings student attendance plugin.
 *
 * @package    block_face_recognition_student_attendance
 * @copyright  2022 Shadman Ahmed
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_face_recognition_student_attendance/successmessage',
        get_string('successmessagetext', 'block_face_recognition_student_attendance'),
        get_string('successmessagelongtext', 'block_face_recognition_student_attendance'),
        ''));

    $settings->add(new admin_setting_configtext('block_face_recognition_student_attendance/failedmessage',
        get_string('failedmessagetext', 'block_face_recognition_student_attendance'),
        get_string('failedmessagelongtext', 'block_face_recognition_student_attendance'),
        ''));
    
}