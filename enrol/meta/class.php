<?php

// This file is used to generate a list of courses to add and remove meta links from based on a
// template used by SBHS. Template is "M-<year><Subject>#" where # is a variable
// operation(add|del), parent_course, child_course

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

// First get a list of meta courses matching "M-"
$searchparam = 'M-%';

$select = $DB->sql_like('idnumber', '?', $casesensitive = true, false);
$rs = $DB->get_recordset_select('course', $select, array($searchparam), 'shortname ASC', 'id, fullname, shortname, idnumber, visible', 0, 0);

foreach ($rs as $c) {
    // Only add to visible Meta Courses
    if (!$c->visible) {
        continue;
    }
    // For each meta course match courses using "<year>-metaname
    $currentyear = date("Y");
    $idnumber = substr ($c->idnumber,2);
    $idpattern = strtr($idnumber,"#","_"); // Underscore represents a single character
    $classParam = $currentyear . "-" . $idpattern;
    //print (format_string($c->fullname) . ' ['.$c->shortname.']' . ' ['.$c->id.']'. ' ['.$c->idnumber.']' . ' ['.$classParam.']' ."<br>\n");

    // Find existing meta links
    $existing = $DB->get_records('enrol', array('enrol'=>'meta', 'courseid'=>$c->id), '', 'customint1, id');

    // Find matching courses
    $cs = $DB->get_recordset_select('course', $select, array($classParam), 'shortname ASC', 'id, fullname, shortname, idnumber, visible', 0, 0);

    foreach ($cs as $m) {
      if (isset($existing[$m->id])) {
        continue;
      }
      //print ("\t&nbsp;&nbsp;&nbsp;" . format_string($m->fullname) . ' ['.$m->shortname.']' . ' ['.$m->idnumber.']' ."<br>\n");
      print ("add," .$c->idnumber . "," . $m->idnumber."<br>\n"); // Use idnumber
    }
    $cs->close();

    // Remove old meta links

    foreach ($existing as $m) {
      // Lookup customint1 to get course details
      $course = $DB->get_record('course', array('id'=>$m->customint1), 'id, fullname, shortname, idnumber, visible', MUST_EXIST);
      // Skip current year entries
      if (substr($course->idnumber, 0, 4)==$currentyear){
	continue;
      }
      print ("del," .$c->idnumber . "," . $course->idnumber."<br>\n"); // Use idnumber
    }

}
$rs->close();
