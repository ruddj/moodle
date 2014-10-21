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
 * Renderable class to display a set of subscriptions in the manage subscriptions page.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_monitor\output\managesubs;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Renderable class to display a set of subscriptions in the manage subscriptions page.
 *
 * @since      Moodle 2.8
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subs extends \table_sql implements \renderable {

    /**
     * @var int course id.
     */
    public $courseid;

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

        $this->set_attribute('class', 'toolmonitor subscriptions generaltable generalbox');
        $this->define_columns(array('name', 'course', 'instance', 'unsubscribe', 'editrule'));
        $this->define_headers(array(
                get_string('name'),
                get_string('course'),
                get_string('moduleinstance', 'tool_monitor'),
                get_string('unsubscribe', 'tool_monitor'),
                get_string('editrule', 'tool_monitor')
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
    }

    /**
     * Generate content for name column.
     *
     * @param \tool_monitor\subscription $sub subscription object
     *
     * @return string html used to display the column field.
     */
    public function col_name(\tool_monitor\subscription $sub) {
        $name = $sub->get_name($this->context);
        $helpicon = new \tool_monitor\output\helpicon\renderable('subscription', $sub->id);
        $helpicon = $this->helpiconrenderer->render($helpicon);

        return $name . $helpicon;
    }

    /**
     * Generate content for course column.
     *
     * @param \tool_monitor\subscription $sub subscription object
     *
     * @return string html used to display the column field.
     */
    public function col_course(\tool_monitor\subscription $sub) {
       return $sub->get_course_name($this->context);
    }

    /**
     * Generate content for description column.
     *
     * @param \tool_monitor\subscription $sub subscription object
     *
     * @return string html used to display the column field.
     */
    public function col_instance(\tool_monitor\subscription $sub) {
        return $sub->get_instance_name();
    }

    /**
     * Generate content for manage column.
     *
     * @param \tool_monitor\subscription $sub subscription object
     *
     * @return string html used to display the column field.
     */
    public function col_unsubscribe(\tool_monitor\subscription $sub) {
        global $OUTPUT, $CFG;

        $a = $sub->get_name($this->context);
        $deleteurl = new \moodle_url($CFG->wwwroot. '/admin/tool/monitor/index.php', array('subscriptionid' => $sub->id,
                'action' => 'unsubscribe', 'courseid' => $this->courseid, 'sesskey' => sesskey()));
        $action = new \component_action('click', 'M.util.show_confirm_dialog', array('message' => get_string('subareyousure',
            'tool_monitor', $a)));
        $icon = $OUTPUT->action_link($deleteurl,
                new \pix_icon('t/delete', get_string('deletesubscription', 'tool_monitor')), $action);
        return $icon;
    }

    /**
     * Generate content for edit rule column.
     *
     * @param \tool_monitor\subscription $sub subscription object
     *
     * @return string html used to display the column field.
     */
    public function col_editrule(\tool_monitor\subscription $sub) {
        if ($sub->can_manage_rule()) {
            // User can manage rule.
            $editurl = new \moodle_url('/admin/tool/monitor/edit.php', array('ruleid' => $sub->ruleid,
                    'courseid' => $sub->rulecourseid));
            return \html_writer::link($editurl, get_string('editrule', 'tool_monitor'));
        }
        return '-';
    }

    /**
     * Query the reader. Store results in the object for use by build_table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {

        $total = \tool_monitor\subscription_manager::count_user_subscriptions();
        $this->pagesize($pagesize, $total);
        $subs = \tool_monitor\subscription_manager::get_user_subscriptions($this->get_page_start(), $this->get_page_size());
        $this->rawdata = $subs;
        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }
}
