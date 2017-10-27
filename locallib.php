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

function is_allowed_to_display_students($courseid) {
    global $USER;
    if (is_admin()) {
        return true;
    }
    $teacherslist = get_teachers_list_for_course ($courseid);
    foreach ($teacherslist as $teacher) {
        if ($teacher['userid'] == $USER->id) {
            return true;
        }
    }
    return false;
}