<?php

// Moodle user Authentication
require_once("../config.php");
require_once($CFG->libdir.'/adminlib.php');

require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

// Disable APC Auth
defaults('USE_AUTHENTICATION',0);

// Proxy Server
defaults('PROXY', 'tcp://proxy.det.nsw.edu.au:8080');

?>
