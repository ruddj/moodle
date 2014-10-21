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
 * Renderable class to display a set of rules in the manage subscriptions page.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_monitor\output\managesubs;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Renderable class to display a set of rules in the manage subscriptions page.
 *
 * @since      Moodle 2.8
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rules extends \table_sql implements \renderable {

    /**
     * @var int course id.
     */
    public $courseid;

    /**
     * @var int total rules present.
     */
    public $totalcount = 0;

    /**
     * @var \context_course|\context_system context of the page to be rendered.
     */
    protected $context;

    /**
     * @var \tool_monitor\output\helpicon\renderer the help icon renderer.
     */
    protected $helpiconrenderer;

    /**
     * Sets up the table_log parameters.
     *
     * @param string $uniqueid unique id of form.
     * @param \moodle_url $url url where this table is displayed.
     * @param int $courseid course id.
     * @param int $perpage Number of rules to display per page.
     */
    public function __construct($uniqueid, \moodle_url $url, $courseid = 0, $perpage = 100) {
        global $PAGE;

        parent::__construct($uniqueid);

        $this->set_attribute('class', 'toolmonitor subscriberules generaltable generalbox');
        $this->define_columns(array('name', 'description', 'select'));
        $this->define_headers(array(
                get_string('name'),
                get_string('description'),
                get_string('select')
            )
        );
        $this->courseid = $courseid;
        $this->pagesize = $perpage;
        $systemcontext = \context_system::instance();
        $this->context = empty($courseid) ? $systemcontext : \context_course::instance($courseid);
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(true);
        $this->is_downloadable(false);
        $this->define_baseurl($url);
        $this->helpiconrenderer = $PAGE->get_renderer('tool_monitor', 'helpicon');
        $total = \tool_monitor\rule_manager::count_rules_by_courseid($this->courseid);
        $this->totalcount = $total;
    }

    /**
     * Generate content for name column.
     *
     * @param \tool_monitor\rule $rule rule object
     *
     * @return string html used to display the column field.
     */
    public function col_name(\tool_monitor\rule $rule) {
        $name = $rule->get_name($this->context);
        $helpicon = new \tool_monitor\output\helpicon\renderable('rule', $rule->id);
        $helpicon = $this->helpiconrenderer->render($helpicon);

        return $name . $helpicon;
    }

    /**
     * Generate content for description column.
     *
     * @param \tool_monitor\rule $rule rule object
     *
     * @return string html used to display the column field.
     */
    public function col_description(\tool_monitor\rule $rule) {
        return $rule->get_description($this->context);
    }

    /**
     * Generate content for plugin column.
     *
     * @param \tool_monitor\rule $rule rule object
     *
     * @return string html used to display the column field.
     */
    public function col_select(\tool_monitor\rule $rule) {
        global $OUTPUT;
        $select = $rule->get_module_select($this->courseid);
        return is_object($select) ? $OUTPUT->render($select) : $select;
    }

    /**
     * Query the reader. Store results in the object for use by build_table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {

        $total = \tool_monitor\rule_manager::count_rules_by_courseid($this->courseid);
        $this->pagesize($pagesize, $total);
        $rules = \tool_monitor\rule_manager::get_rules_by_courseid($this->courseid, $this->get_page_start(),
                $this->get_page_size());
        $this->rawdata = $rules;
        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * Gets a list of courses where the current user can subscribe to rules as a dropdown.
     *
     * @return \single_select list of courses.
     */
    public function get_user_courses_select() {
        $courses = get_user_capability_course('tool/monitor:subscribe', null, true, 'fullname');
        $options = array(0 => get_string('site'));
        $systemcontext = \context_system::instance();
        foreach ($courses as $course) {
            $options[$course->id] = format_text($course->fullname, array('context' => $systemcontext));
        }
        $url = new \moodle_url('/admin/tool/monitor/index.php');
        $select = new \single_select($url, 'courseid', $options, $this->courseid);
        $select->set_label(get_string('selectacourse', 'tool_monitor'));
        return $select;
    }
}
