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
 * Rule manager class.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Rule manager class.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_manager {

    /**
     * Create a new rule.
     *
     * @param \stdClass $ruledata data to insert as new rule entry.
     *
     * @return rule An instance of rule class.
     */
    public static function add_rule($ruledata) {
        global $DB;

        $now = time();
        $ruledata->timecreated = $now;
        $ruledata->timemodified = $now;

        $ruledata->id = $DB->insert_record('tool_monitor_rules', $ruledata);
        return new rule($ruledata);
    }

    /**
     * Clean data submitted by mform.
     *
     * @param \stdClass $mformdata data to insert as new rule entry.
     *
     * @return \stdClass Cleaned rule data.
     */
    public static function clean_ruledata_form($mformdata) {
        global $USER;

        $rule = new \stdClass();
        if (!empty($mformdata->ruleid)) {
            $rule->id = $mformdata->ruleid;
        }
        $rule->userid = empty($mformdata->userid) ? $USER->id : $mformdata->userid;
        $rule->courseid = $mformdata->courseid;
        $rule->name = $mformdata->name;
        $rule->plugin = $mformdata->plugin;
        $rule->eventname = $mformdata->eventname;
        $rule->description = $mformdata->description['text'];
        $rule->descriptionformat = $mformdata->description['format'];
        $rule->frequency = $mformdata->frequency;
        $rule->timewindow = $mformdata->minutes * MINSECS;
        $rule->template = $mformdata->template['text'];
        $rule->templateformat = $mformdata->template['format'];

        return $rule;
    }

    /**
     * Delete a rule and associated subscriptions, by rule id.
     *
     * @param int $ruleid id of rule to be deleted.
     *
     * @return bool
     */
    public static function delete_rule($ruleid) {
        global $DB;

        subscription_manager::remove_all_subscriptions_for_rule($ruleid);
        return $DB->delete_records('tool_monitor_rules', array('id' => $ruleid));
    }

    /**
     * Get an instance of rule class.
     *
     * @param \stdClass|int $ruleorid A rule object from database or rule id.
     *
     * @return rule object with rule id.
     */
    public static function get_rule($ruleorid) {
        global $DB;
        if (!is_object($ruleorid)) {
            $rule = $DB->get_record('tool_monitor_rules', array('id' => $ruleorid), '*', MUST_EXIST);
        } else {
            $rule = $ruleorid;
        }

        return new rule($rule);
    }

    /**
     * Update rule data.
     *
     * @throws \coding_exception if $record->ruleid is invalid.
     * @param object $ruledata rule data to be updated.
     *
     * @return bool
     */
    public static function update_rule($ruledata) {
        global $DB;
        if (!self::get_rule($ruledata->id)) {
            throw new \coding_exception('Invalid rule ID.');
        }
        $ruledata->timemodified = time();
        return $DB->update_record('tool_monitor_rules', $ruledata);
    }

    /**
     * Get rules by course id.
     *
     * @param int $courseid course id of the rule.
     * @param int $limitfrom Limit from which to fetch rules.
     * @param int $limitto  Limit to which rules need to be fetched.
     *
     * @return array List of rules for the given course id, also includes system wide rules.
     */
    public static function get_rules_by_courseid($courseid, $limitfrom = 0, $limitto = 0) {
        global $DB;
        $select = "courseid = ? OR courseid = ?";
        return self::get_instances($DB->get_records_select('tool_monitor_rules', $select, array(0, $courseid), null, '*',
                $limitfrom, $limitto));
    }

    /**
     * Get rule count by course id.
     *
     * @param int $courseid course id of the rule.
     *
     * @return int count of rules present in system visible in the given course id.
     */
    public static function count_rules_by_courseid($courseid) {
        global $DB;
        $select = "courseid = ? OR courseid = ?";
        return $DB->count_records_select('tool_monitor_rules', $select, array(0, $courseid));
    }

    /**
     * Get rules by plugin name.
     *
     * @param string $plugin plugin name of the rule.
     *
     * @return array List of rules for the given plugin name.
     */
    public static function get_rules_by_plugin($plugin) {
        global $DB;
        return self::get_instances($DB->get_records('tool_monitor_rules', array('plugin' => $plugin)));
    }

    /**
     * Get rules by event name.
     *
     * @param string $eventname event name of the rule.
     *
     * @return array List of rules for the given event.
     */
    public static function get_rules_by_event($eventname) {
        global $DB;
        return self::get_instances($DB->get_records('tool_monitor_rules', array('eventname' => $eventname)));
    }

    /**
     * Helper method to convert db records to instances.
     *
     * @param array $arr of rules.
     *
     * @return array of rules as instances.
     */
    protected static function get_instances($arr) {
        $result = array();
        foreach ($arr as $key => $sub) {
            $result[$key] = new rule($sub);
        }
        return $result;
    }
}
