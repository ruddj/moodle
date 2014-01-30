<?

require_once("$CFG->libdir/externallib.php");
error_reporting(E_ALL ^ E_NOTICE);
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors', 'stdout');
//log_errors(true);

class local_shsmyhomework_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function calendar_get_parameters() {
        return new external_function_parameters(
            array(
                'username' => new external_value(PARAM_TEXT, 'username (not user id) being operated on'),
                'options' => new external_single_structure(
                            array(
                                    'eventids' => new external_multiple_structure(
                                            new external_value(PARAM_INT, 'event ids')
                                            , 'List of event ids',
                                            VALUE_DEFAULT, array(), NULL_ALLOWED
                                                ),
                                    'courseids' => new external_multiple_structure(
                                            new external_value(PARAM_INT, 'course ids')
                                            , 'List of course ids for which events will be returned',
                                            VALUE_DEFAULT, array(), NULL_ALLOWED
                                                ),
                                    'groupids' => new external_multiple_structure(
                                            new external_value(PARAM_INT, 'group ids')
                                            , 'List of group ids for which events should be returned',
                                            VALUE_DEFAULT, array(), NULL_ALLOWED
                                                ),

                                    'showcourse' => new external_value(PARAM_BOOL,
                                             "Set to true to return current user's user events",
                                             VALUE_DEFAULT, true, NULL_ALLOWED),
                                    'showsite' => new external_value(PARAM_BOOL,
                                             "Set to true to return global events",
                                             VALUE_DEFAULT, true, NULL_ALLOWED),
                                    'showuser' => new external_value(PARAM_BOOL,
                                             "Set to true to return personal events",
                                             VALUE_DEFAULT, true, NULL_ALLOWED),
                                    'showgroup' => new external_value(PARAM_BOOL,
                                             "Set to true to return group events",
                                             VALUE_DEFAULT, true, NULL_ALLOWED),

                                    'timestart' => new external_value(PARAM_INT,
                                             "Time from which events should be returned",
                                             VALUE_DEFAULT, 0, NULL_ALLOWED),
                                    'timeend' => new external_value(PARAM_INT,
                                             "Time to which the events should be returned",
                                             VALUE_DEFAULT, time(), NULL_ALLOWED),
                                    'ignorehidden' => new external_value(PARAM_BOOL,
                                             "Ignore hidden events or not",
                                             VALUE_DEFAULT, true, NULL_ALLOWED),


                            ), 'Options', VALUE_DEFAULT, array())
            )
        );
    }


    public static function calendar_get_returns()
    {
      return new external_single_structure(array(
                'events' => new external_multiple_structure( new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'event id'),
                            'name' => new external_value(PARAM_TEXT, 'event name'),
                            'description' => new external_value(PARAM_RAW, 'Description', VALUE_OPTIONAL, null, NULL_ALLOWED),
                            'format' => new external_format_value('description'),
                            'courseid' => new external_value(PARAM_INT, 'course id'),
                            'groupid' => new external_value(PARAM_INT, 'group id'),
                            'userid' => new external_value(PARAM_INT, 'user id'),
                            'repeatid' => new external_value(PARAM_INT, 'repeat id'),
                            'modulename' => new external_value(PARAM_TEXT, 'module name', VALUE_OPTIONAL, null, NULL_ALLOWED),
                            'instance' => new external_value(PARAM_INT, 'instance id'),
                            'eventtype' => new external_value(PARAM_TEXT, 'Event type'),
                            'timestart' => new external_value(PARAM_INT, 'timestart'),
                            'timeduration' => new external_value(PARAM_INT, 'time duration'),
                            'visible' => new external_value(PARAM_INT, 'visible'),
                            'uuid' => new external_value(PARAM_TEXT, 'unique id of ical events', VALUE_OPTIONAL, null, NULL_NOT_ALLOWED),
                            'sequence' => new external_value(PARAM_INT, 'sequence'),
                            'timemodified' => new external_value(PARAM_INT, 'time modified'),
                            'subscriptionid' => new external_value(PARAM_INT, 'Subscription id', VALUE_OPTIONAL, null, NULL_ALLOWED),

                             // extended details from calendar_add_event_metadata()
                             'icon' => new external_value(PARAM_RAW, 'Icon URL', VALUE_OPTIONAL, null, NULL_ALLOWED),
                             'referer' => new external_value(PARAM_RAW, 'Event URL', VALUE_OPTIONAL, null, NULL_ALLOWED),
                             'courselink' => new external_value(PARAM_RAW, 'Course URL', VALUE_OPTIONAL, null, NULL_ALLOWED),
                             'cmid' => new external_value(PARAM_INT, 'Module ID', VALUE_OPTIONAL, null, NULL_ALLOWED),
                             'cssclass' => new external_value(PARAM_ALPHANUMEXT, 'CSS Display Class', VALUE_OPTIONAL, null, NULL_ALLOWED),

                        ), 'event')
                 ),
                 'warnings' => new external_warnings()
                )
        );
    }




    public static function calendar_get($username, $options)
    {
        global $SITE, $DB, $USER, $CFG;
        require_once($CFG->dirroot."/calendar/lib.php");

        // Parameter validation.
        //$params = self::validate_parameters(self::get_calendar_events_parameters(), array('events' => $events, 'options' => $options));
        //$funcparam = array('courses' => array(), 'groups' => array());

        // find the user id of the username
        $users = $DB->get_record('user', array('username' => clean_param($username, PARAM_RAW)), 'id');
        if (count($users) != 1) {
          throw new invalid_parameter_exception('Unique username expected.');
        }

        $userid = reset($users);
        //$userid = $user->id;
        //die(print_r($users));
        $courses = array();
        $groups  = array();
        $courseids = $options['courseids'];
        $groupids  = $options['groupids'];

        $hassystemcap = has_capability('moodle/calendar:manageentries', context_system::instance());
        $warnings = array();

        // Let us findout courses that we can return events from.
        if (!$hassystemcap) {
          if (is_array($courseids) && count($courseids)) {
            $warnings[] = array('warningcode' => 'nopermissions',
                                'message' => 'you do not have permissions to access user\'s course events');
          }

          if (is_array($groupids) && count($groupids)) {
            $warnings[] = array('warningcode' => 'nopermissions',
                                'message' => 'you do not have permissions to access user\'s group events');
          }

        }
        else {
          if (!is_array($courseids) || !count($courseids)) {
            $courses = enrol_get_users_courses($userid, true, 'id, visible, shortname');

            foreach ($courses as $course) {
              $course_groups = groups_get_all_groups($course->id, $userid);
              $groups = array_merge($groups, array_keys($course_groups));


              $courseids[] = $course->id;
            }
          }

          if (!is_array($groupids) || !count($groupids)) {
            foreach ($groups as $group) {
              $groupids[] = $group->id;
            }
          }
        }

        $courseids = array_unique(array_values($courseids));
        $groupids  = array_unique(array_values($groupids));

        // options on what to show
        // (1) show course events?
        if (!$options['showcourse']) {
          $courseids = array();
        }
        if ($options['showsite']) {
          $courseids[] = $SITE->id;
        }
        if (!$options['showgroup']) {
          $groupids = array();
        }
        if (!$options['showuser']) {
          $userid = false;
        }

        $timestart = $options['timestart'];
        $timeend   = $options['timeend'];
        $ignorehidden = $options['ignorehidden'];

        $eventList = calendar_get_events($timestart, $timeend, $userid, $groupids, $courseids, true, $ignorehidden);

        $events = array();
        foreach ($eventList as $id => $event) {
            $event = calendar_add_event_metadata($event);

            $events[$id] = (array) $event;
        }



        // events asked for eventids.
        $eventids  = $options['eventids'];
        if (is_array($eventids) && count($eventids)) {
          $warnings[] = array('warningcode' => 'notimplemented',
                              'message' => 'retrieving events by id is not implemented');
        }
        /*$eventsbyid = calendar_get_events_by_id($params['events']['eventids']);
        foreach ($eventsbyid as $eventid => $eventobj) {
            $event = (array) $eventobj;
            if (isset($events[$eventid])) {
                   continue;
            }
            if ($hassystemcap) {
                // User can see everything, no further check is needed.
                $events[$eventid] = $event;
            } else if (!empty($eventobj->modulename)) {
                $cm = get_coursemodule_from_instance($eventobj->modulename, $eventobj->instance);
                if (groups_course_module_visible($cm)) {
                    $events[$eventid] = $event;
                }
            } else {
                // Can the user actually see this event?
                $eventobj = calendar_event::load($eventobj);
                if (($eventobj->courseid == $SITE->id) ||
                            (!empty($eventobj->groupid) && in_array($eventobj->groupid, $groups)) ||
                            (!empty($eventobj->courseid) && in_array($eventobj->courseid, $courses)) ||
                            ($USER->id == $eventobj->userid) ||
                            (calendar_edit_event_allowed($eventid))) {
                    $events[$eventid] = $event;
                } else {
                    $warnings[] = array('item' => $eventid, 'warningcode' => 'nopermissions', 'message' => 'you do not have permissions to view this event');
                }
            }
        }*/


        return array('events' => $events,
                     'warnings' => $warnings);
    }



    public static function calendar_create_events_parameters() {

        return new external_function_parameters(
                array(
                    'username' => new external_value(PARAM_TEXT, 'username (not user id) being operated on'),
                    'events' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'event id', VALUE_DEFAULT, null, NULL_ALLOWED),
                                'name' => new external_value(PARAM_TEXT, 'event name', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                                'description' => new external_value(PARAM_RAW, 'Description', VALUE_DEFAULT, null, NULL_ALLOWED),
                                'format' => new external_format_value('description', VALUE_DEFAULT),
                                'courseid' => new external_value(PARAM_INT, 'course id', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                                'groupid' => new external_value(PARAM_INT, 'group id', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                                'repeats' => new external_value(PARAM_INT, 'number of repeats', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                                'eventtype' => new external_value(PARAM_TEXT, 'Event type', VALUE_DEFAULT, 'user', NULL_NOT_ALLOWED),
                                'timestart' => new external_value(PARAM_INT, 'timestart', VALUE_DEFAULT, time(), NULL_NOT_ALLOWED),
                                'timeduration' => new external_value(PARAM_INT, 'time duration', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                                'visible' => new external_value(PARAM_INT, 'visible', VALUE_DEFAULT, 1, NULL_NOT_ALLOWED),
                                'sequence' => new external_value(PARAM_INT, 'sequence', VALUE_DEFAULT, 1, NULL_NOT_ALLOWED),
                            ), 'event')
                )
            )
        );
    }


    public static function  calendar_create_events_returns() {
            return new external_single_structure(
                    array(
                        'events' => new external_multiple_structure( new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'event id'),
                                    'name' => new external_value(PARAM_TEXT, 'event name'),
                                    'description' => new external_value(PARAM_RAW, 'Description', VALUE_OPTIONAL),
                                    'format' => new external_format_value('description'),
                                    'courseid' => new external_value(PARAM_INT, 'course id'),
                                    'groupid' => new external_value(PARAM_INT, 'group id'),
                                    'userid' => new external_value(PARAM_INT, 'user id'),
                                    'repeatid' => new external_value(PARAM_INT, 'repeat id', VALUE_OPTIONAL),
                                    'modulename' => new external_value(PARAM_TEXT, 'module name', VALUE_OPTIONAL),
                                    'instance' => new external_value(PARAM_INT, 'instance id'),
                                    'eventtype' => new external_value(PARAM_TEXT, 'Event type'),
                                    'timestart' => new external_value(PARAM_INT, 'timestart'),
                                    'timeduration' => new external_value(PARAM_INT, 'time duration'),
                                    'visible' => new external_value(PARAM_INT, 'visible'),
                                    'uuid' => new external_value(PARAM_TEXT, 'unique id of ical events', VALUE_OPTIONAL, '', NULL_NOT_ALLOWED),
                                    'sequence' => new external_value(PARAM_INT, 'sequence'),
                                    'timemodified' => new external_value(PARAM_INT, 'time modified'),
                                    'subscriptionid' => new external_value(PARAM_INT, 'Subscription id', VALUE_OPTIONAL),
                                ), 'event')
                        ),
                      'warnings' => new external_warnings()
                    )
            );
    }

    public static function calendar_create_events($username, $events)
    {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot."/calendar/lib.php");

        // Parameter validation.
        //$params = self::validate_parameters(self::create_calendar_events_parameters(), array('events' => $events));

        $users = $DB->get_record('user', array('username' => clean_param($username, PARAM_RAW)), 'id');
        if (count($users) != 1) {
          throw new invalid_parameter_exception('Unique username expected.');
        }

        $userid = reset($users);

        $hassystemcap = has_capability('moodle/calendar:manageentries', context_system::instance());

        $transaction = $DB->start_delegated_transaction();
        $return = array();
        $warnings = array();

        //print_r($events);

        foreach ($events as $event) {

            // Let us set some defaults.
            $event['userid'] = $userid;
            $event['modulename'] = '';
            $event['instance'] = 0;
            $event['subscriptionid'] = null;
            $event['uuid']= '';
            $event['format'] = external_validate_format($event['format']);
            if ($event['repeats'] > 0) {
                $event['repeat'] = 1;
            } else {
                $event['repeat'] = 0;
            }

            $eventobj = new calendar_event($event);

            // Let's check if the user is allowed to create an event.
            if (!calendar_add_event_allowed($eventobj)) {
                $warnings [] = array('item' => $event['name'], 'warningcode' => 'nopermissions', 'message' => 'you do not have permissions to create this event');
                continue;
            }
            // Let's create the event.
            $var = $eventobj->create($event);
            $var = (array)$var->properties();
            if ($event['repeat']) {
                $children = $DB->get_records('event', array('repeatid' => $var['id']));
                foreach ($children as $child) {
                    $return[] = (array) $child;
                }
            } else {
                $return[] = $var;
            }
        }

        // Everything done smoothly, let's commit.
        $transaction->allow_commit();
        return array('events' => $return, 'warnings' => $warnings);
    }



    public static function calendar_get_by_id_parameters() {
        return new external_function_parameters(
            array(
                'username' => new external_value(PARAM_TEXT, 'username (not user id) being operated on'),
                'options' => new external_single_structure(
                            array(
                                    'eventids' => new external_multiple_structure(
                                            new external_value(PARAM_INT, 'event ids')
                                            , 'List of event ids',
                                            VALUE_DEFAULT, array(), NULL_ALLOWED
                                                ),
                            ), 'Options', VALUE_DEFAULT, array())
            )
        );
    }


    public static function calendar_get_by_id_returns()
    {
      return self::calendar_get_returns();
    }

    public static function calendar_get_by_id($username, $options)
    {
        global $SITE, $DB, $USER, $CFG;
        require_once($CFG->dirroot."/calendar/lib.php");

        // Parameter validation.
        //$params = self::validate_parameters(self::get_calendar_events_parameters(), array('events' => $events, 'options' => $options));
        //$funcparam = array('courses' => array(), 'groups' => array());

        // find the user id of the username
        $users = $DB->get_record('user', array('username' => clean_param($username, PARAM_RAW)), 'id');
        if (count($users) != 1) {
          throw new invalid_parameter_exception('Unique username expected.');
        }

        $userid = reset($users);

        $hassystemcap = has_capability('moodle/calendar:manageentries', context_system::instance());
        $warnings = array();

        // Let us findout courses that we can return events from.
        if (!$hassystemcap) {
          if (is_array($groupids) && count($groupids)) {
            $warnings[] = array('warningcode' => 'nopermissions',
                                'message' => 'you do not have permissions to access user\'s events');
          }
        }

        // events asked for eventids.
        $eventids  = $options['eventids'];
        $events = array();

        if (is_array($eventids) && count($eventids)) {
          $eventsbyid = calendar_get_events_by_id($eventids);
          foreach ($eventsbyid as $eventid => $eventobj) {
            $event = (array) $eventobj;

            if (isset($events[$eventid])) {
              continue;
            }

            if ($hassystemcap) {
                $events[$eventid] = $event;
            } else if (!empty($eventobj->modulename)) {
                $cm = get_coursemodule_from_instance($eventobj->modulename, $eventobj->instance);
                if (groups_course_module_visible($cm)) {
                    $events[$eventid] = $event;
                }
            } else {
                $eventobj = calendar_event::load($eventobj);
                if (($eventobj->courseid == $SITE->id) ||
                            (!empty($eventobj->groupid) && in_array($eventobj->groupid, $groups)) ||
                            (!empty($eventobj->courseid) && in_array($eventobj->courseid, $courses)) ||
                            ($USER->id == $eventobj->userid) ||
                            (calendar_edit_event_allowed($eventid))) {
                    $events[$eventid] = $event;
                } else {
                    $warnings[] = array('item' => $eventid, 'warningcode' => 'nopermissions', 'message' => 'you do not have permissions to view this event');
                }
            }
          }
        }

        foreach ($events as &$e) {
          if ($e['userid'] == $userid) {
            $e['userid'] = $username;
          }
        }

        return array('events' => $events,
                     'warnings' => $warnings);
    }
}
