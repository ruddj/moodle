<?php

/**

* SBHS Library Search
* Written by James Rudd, 08/05/2007
*
* Based on Wikipedia Search  v2006100700 Author: David Horat ( david.horat@gmail.com )
* based on work of Aggelos Panagiotakis( agelospanagiotakis@gmail.com )
* Also Based on simpledocssearch v0.1 Written by Darren Smith (with some help from Marky Clarky) on 21/3/2006
* Uses some code from Juilan Ridden, MonteNet
* License: GPL
 *
 * @package   block_library
 * @copyright 2011 James Rudd
*
*/


class block_library_enquiry extends block_base {

    function init() {
		$this->title = get_string('pluginname', 'block_library_enquiry');
    }

    function instance_allow_multiple() {
        return false;
    }

    function instance_allow_config() {
        return true;
    }

    function has_config() {
        return false;
    }

    function get_content() {
        global $CFG;

        // Check if we've already generated content
        if (!empty($this->content)) {
            return $this->content;
        }

        // Prep the content
        $this->content = new stdClass;

	/*if (!empty($this->config->teacheronly)) {
            $course = get_record('course', 'id', $this->instance->pageid);
            if (!isteacher($course->id)) {
		            $this->content->text = '';
		            $this->content->footer = '';
                return $this->content;
           }
        }
	*/
	$target = (empty($this->config->samewindow)) ? ' target="_blank"' : '';

		$sbhslogo = '<img src="http://www.sydneyboyshigh.com/images/stories/gproxy/logo_sm.png" border="0" alt="Library Enquiry" width="150" />'."\n";

                $form = '
  <table cellspacing="2" border="0">
   <form'.$target.' name="searchform" action="http://www.sydneyboyshigh.com/library/enquiry/search" id="searchform">
    <tr valign=top>
     <td colspan="2" align="left">
       <input id="searchInput" name="q" type="text" size="20" accesskey="f" value=""/>
     </td>
    </tr>
    <tr>
     <td align="left">
      <select name="type" style="vertical-align: top; padding: 0; " >
      <option value="any" selected >Search All</option>
      <option value="title" >Title</option>
      <option value="author" >Author</option>
      <option value="subject" >Subject</option>
      <option value="keyword" >Keyword</option>
      <option value="series" >Series</option>
      </select>
     </td>
     <td align="right">
      <input type="submit" class="searchButton" id="searchGoButton" value="Go!" />
     </td>
    </tr>
   </form>
   <tr><td colspan="2" align="right"><a href="http://www.sydneyboyshigh.com/library/enquiry/advanced">Advanced Search</a></td></tr>
  </table>';

        $this->content->text = "\n".'<center>'."\n".$sbhslogo."\n".$form."\n".'</center>'."\n";

        $this->content->footer = ""; //'<a href="http://www.sydneyboyshigh.com/library/enquiry/advanced">Advanced Search</a>';

        return $this->content;
    }



    function applicable_formats() {
        return array('site-index' => true,
                     'course-view' => true, 'course-view-social' => true,
                     'mod' => false, 'mod-quiz' => false);
    }

}

?>
