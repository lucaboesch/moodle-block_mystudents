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
 * Display profile for a particular user
 *
 * @package core_user
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/user/lib.php');
require_once('./locallib.php');

require_login();

   
$PAGE->set_url(new moodle_url('/blocks/mystudents/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('mystudents', 'block_mystudents'));

echo $OUTPUT->header();

echo $OUTPUT->box_start();

$content = '';

// obtenir la liste des cours
$mycourses = enrol_get_my_courses(null, 'fullname ASC');
$students = array();
foreach ($mycourses as $course) {
    $courseid = $course->id;
    if (is_allowed_to_display_students($courseid)) {
        // pour chaque cours, obtenir la liste des participants
        $studentlist = get_student_list_fo_course ($courseid);
        foreach ($studentlist as $student) {
            $key = $student['lastname'] . '_' . $student['firstname'];
            if (!array_key_exists($key, $students)) {
                $students[$key] = array('lastname' => $student['lastname'], 'firstname' => $student['firstname'], 'userid' => $student['id'], 'courses' => array());
            }
            $students[$key]['courses'][] = $course->shortname;
        }
    }
}
ksort($students);
$table = new html_table();
$table->head = array(get_string('lastname'), get_string('firstname'), get_string('progcode', 'block_mystudents'), get_string('courses'));

foreach ($students as $student => $info) {
    $courses = $info['courses'];
    $row = new html_table_row();
    $row->cells[] = $info['lastname'];
    $row->cells[] = $info['firstname'];
    $progcode = get_ldap_prog_code_for_user($info['userid']);
    $row->cells[] = implode(', ', $progcode);
    $row->cells[] = implode(', ', $courses);
    $table->data[] = $row;
}
$content .= html_writer::table($table);



echo $content;

echo $OUTPUT->box_end();

// Finish the page.
echo $OUTPUT->footer();