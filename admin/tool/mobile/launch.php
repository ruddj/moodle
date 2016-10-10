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
 * Launch page, launch the app using custom URL schemes.
 *
 * If the user is not logged when visiting this page, he will be redirected to the login page.
 * Once he is logged, he will be redirected again to this page and the app launched via custom URL schemes.
 *
 * @package    tool_mobile
 * @copyright  2016 Juan Leyva
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/externallib.php');

$serviceshortname  = required_param('service',  PARAM_ALPHANUMEXT);
$passport          = required_param('passport',  PARAM_RAW);    // Passport send from the app to validate the response URL.
$urlscheme         = optional_param('urlscheme', 'moodlemobile', PARAM_NOTAGS); // The URL scheme the app supports.

// Check web services enabled.
if (!$CFG->enablewebservices) {
    throw new moodle_exception('enablewsdescription', 'webservice');
}

// Check if the plugin is properly configured.
$typeoflogin = get_config('tool_mobile', 'typeoflogin');
if ($typeoflogin != tool_mobile\api::LOGIN_VIA_BROWSER and
        $typeoflogin != tool_mobile\api::LOGIN_VIA_EMBEDDED_BROWSER) {
    throw new moodle_exception('pluginnotenabledorconfigured', 'tool_mobile');
}

// Check if the service exists and is enabled.
$service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
if (empty($service)) {
    throw new moodle_exception('servicenotavailable', 'webservice');
}

require_login(0, false);

// Require an active user: not guest, not suspended.
core_user::require_active_user($USER);

// Get an existing token or create a new one.
$token = external_generate_token_for_current_user($service);

// Log token access.
$DB->set_field('external_tokens', 'lastaccess', time(), array('id' => $token->id));

$params = array(
    'objectid' => $token->id,
);
$event = \core\event\webservice_token_sent::create($params);
$event->add_record_snapshot('external_tokens', $token);
$event->trigger();

// Passport is generated in the mobile app, so the app opening can be validated using that variable.
// Passports are valid only one time, it's deleted in the app once used.
$siteid = md5($CFG->wwwroot . $passport);
$apptoken = base64_encode($siteid . ':::' . $token->token);

// Redirect using the custom URL scheme checking first if a URL scheme is forced in the site settings.
$forcedurlscheme = get_config('tool_mobile', 'forcedurlscheme');
if (!empty($forcedurlscheme)) {
    $urlscheme = $forcedurlscheme;
}

$location = "$urlscheme://token=$apptoken";

// For iOS 10 onwards, we have to simulate a user click.
if (core_useragent::is_ios()) {
    $PAGE->set_context(null);
    $PAGE->set_url('/local/mobile/launch.php', array('service' => $serviceshortname, 'passport' => $passport, 'urlscheme' => $urlscheme));

    echo $OUTPUT->header();
    $notice = get_string('clickheretolaunchtheapp', 'tool_mobile');
    echo html_writer::link($location, $notice, array('id' => 'launchapp'));
    echo html_writer::script(
        "window.onload = function() {
            document.getElementById('launchapp').click();
        };"
    );
    echo $OUTPUT->footer();
} else {
    // For Android a http redirect will do fine.
    header('Location: ' . $location);
    die;
}
