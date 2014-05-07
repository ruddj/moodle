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
 * Adds new instance of enrol_meta to specified course.
 *
 * @package    enrol
 * @subpackage meta
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/enrol/meta/addmultiple_form.php");
require_once("$CFG->dirroot/enrol/meta/locallib.php");

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

$PAGE->set_url('/enrol/meta/addmultiple.php', array('id'=>$course->id));
$pageurl = new moodle_url('/enrol/meta/addmultiple.php', array('id'=>$course->id));

$PAGE->set_pagelayout('admin');

navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));

require_login($course);
require_capability('moodle/course:enrolconfig', $context);

$searchtext = optional_param('links_searchtext', '', PARAM_RAW);

if (optional_param('links_clearbutton', 0, PARAM_RAW) && confirm_sesskey()) {
    redirect($pageurl);
}


$enrol = enrol_get_plugin('meta');
if (!$enrol->get_newinstance_link($course->id)) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
}

//$mform = new enrol_meta_addmultiple_form(NULL, $course);

// row limit unlimited if not set in config
$rowlimit = $enrol->get_config('addmultiple_rowlimit', 0);

function get_valid_courses($courses, $course) {
    $available_courses = array();
    foreach ($courses as $c) {
        if ($c->id == SITEID or $c->id == $course->id or isset($existing[$c->id])) {
            continue;
        }
	$coursecontext = context_course::instance($c->id);
        if (!has_capability('enrol/meta:selectaslinked', $coursecontext)) {
            continue;
        }
        $available_courses[$c->id] = format_string($c->fullname) . ' ['.$c->shortname.']';
    }
    return $available_courses;
}

$existing = $DB->get_records('enrol', array('enrol'=>'meta', 'courseid'=>$course->id), '', 'customint1, id');
if (!empty($searchtext)) {
    $courses = get_courses_search(explode(" ", $searchtext), 'shortname ASC', 0, 99999, $rowlimit);
    $availablecourses = get_valid_courses($courses, $course);
} else {
    $rs = $DB->get_recordset('course', array('visible' => 1), 'shortname ASC', 'id, fullname, shortname', 0, $rowlimit);
    $availablecourses = get_valid_courses($rs, $course);
    $rs->close();
}

if (!$enrol->get_newinstance_link($course->id)) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id, '')));
}

$mform = new enrol_meta_addmultiple_form($pageurl->out(false), array('course'=>$course, 'availablecourses'=>$availablecourses));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));

} else if ($data = $mform->get_data()) {
    if (!empty($data->links)) { //todo
        foreach ($data->links as $link) {
            $eid = $enrol->add_instance($course, array('customint1'=>$link));
        }
        enrol_meta_sync($course->id);
    }
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_meta'));

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
