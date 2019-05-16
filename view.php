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
 * My students block.
 *
 * @package    block_mystudents
 * @copyright  2018 Namur University
 * @author     Laurence Dumortier <laurence.dumortier@unamur.be>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/user/lib.php');

require_login();

$PAGE->set_url(new moodle_url('/blocks/mystudents/view.php'));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('mystudents', 'block_mystudents'));
$PAGE->set_title(get_string('mystudents', 'block_mystudents'));

echo $OUTPUT->header();

echo $OUTPUT->box_start();

$content = '';

// Get course list.
$mycourses = enrol_get_my_courses(null, 'fullname ASC');
$hassiteconfig = has_capability('moodle/site:config', $context);
$students = array();
foreach ($mycourses as $course) {
    $coursecontext = context_course::instance($course->id);
    if ($hassiteconfig || has_capability('moodle/course:update', $coursecontext)) {
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        // For each course get all the users.
        list ($select, $from, $where, $params) = user_get_participants_sql($course->id, 0, 0, $studentrole->id);
        $list = $DB->get_recordset_sql("$select $from $where", $params);
        foreach ($list as $student) {
            $key = str_replace('', '_', $student->lastname) . '_' . str_replace('', '_', $student->firstname);
            if (!array_key_exists($key, $students)) {
                $students[$key] = array('lastname' => $student->lastname, 'firstname' => $student->firstname,
                    'email' => $student->email, 'userid' => $student->id, 'courses' => array());
            }
            $students[$key]['courses'][] = $course->shortname;
        }
    }
}
ksort($students);

$table = new html_table();
$table->head = array(get_string('lastname'), get_string('firstname'), get_string('email'), get_string('courses'));

foreach ($students as $student => $info) {
    $courses = $info['courses'];
    $row = new html_table_row();
    $row->cells[] = html_writer::link($CFG->wwwroot .'/user/profile.php?id='. $info['userid'], $info['lastname']);
    $row->cells[] = html_writer::link($CFG->wwwroot .'/user/profile.php?id='. $info['userid'], $info['firstname']);
    $row->cells[] = $info['email'];
    $row->cells[] = implode(', ', $courses);
    $table->data[] = $row;
}
$content .= html_writer::table($table);

echo $content;

echo $OUTPUT->box_end();

// Finish the page.
echo $OUTPUT->footer();