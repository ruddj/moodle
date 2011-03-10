<?php

/**
 * @author James Rudd
 *
 * Authentication Plugin: SBHS SSO
 *
 * Authenticates against an SBHS Web Cred
 *
 * 2008-03-06  File created.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

/**
 * SBHS authentication plugin.
 */
class auth_plugin_sbhs extends auth_plugin_base {

    /**
     * Constructor.
     */
    function __construct() {
        $this->authtype = 'sbhs';
        $this->config = get_config('auth/sbhs');
    }

    /**
    *  Must overide abstract, otherwise can not create new accounts. Does not pass on to LDAP.
    */

    function user_login($username, $password) {
        return false;
    }

    /**
    * Hook for overriding behavior of login page.
    * This method is called from login/index.php page for all enabled auth plugins.
    */
    function loginpage_hook() {
        global $frm;  // can be used to override submitted login form
        //global $user; // can be used to replace authenticate_user_login()

        // Return if SBHS enabled and settings not specified yet
        if (empty($this->config->ssofunc)) {
            return;
        }

        require_once($this->config->ssofunc);

        if (sso_isLoggedIn()) {
            if ($frm == NULL) {
                $frm = new stdClass;
            }

            $frm->username = sso_getUser();
            $frm->password = sso_getPass();

            // LDAP module SSO magic:
            // The logic to detect an SSO session is run whenever LDAP SSO is enabled, but the logic to attempt
            // NTLM/Kerberos is only run on a request from an address matching a mask. So we can emulate this for
            // when the password is not available -- this requires putting the session key in the password field and
            // setting a magic flag.
            // Other SHS SSO magic is to turn on LDAP SSO in Moodle, but set it to only apply for an impossibly small
            // subnet (recommended: 169.254.255.255/32). Also don't configure NTLM/Kerberos in Apache.
            if ($frm->password == '') {
              $username = core_text::strtolower(sso_getUser()); // Compatibility hack
              $timeout  = 10; //AUTH_NTLMTIMEOUT
              $sesskey  = sesskey();

              $frm->password = $sesskey;
              $frm->username = $username;
              set_cache_flag('auth/ldap/ntlmsess', $sesskey, $username, $timeout);
            }

            return;
        }
    }

    /**
     * Hook for overriding behavior of logout page.
    * This method is called from login/logout.php page for all enabled auth plugins.
    * This will redirect you to the login page if you are not logged in to the Portal,
    * but, if you are, you will be returned to the Portal instead
    */
    function logoutpage_hook() {
        global $USER;     // use $USER->auth to find the plugin used for login
        global $redirect; // can be used to override redirect after logout

        // Return if SBHS enabled and settings not specified yet
        if (empty($this->config->ssofunc)) {
           return;
        }

        require_once($this->config->ssofunc);
        if (sso_isLoggedIn()) {
            $redirect = $this->config->redirect;
        }
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }


    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {
        // set to defaults if undefined
        if (!isset ($config->ssofunc)) {
            $config->ssofunc = '/srv/www/sites/apps/sso/functions.php';
        }
        if (!isset ($config->redirect)) {
            $config->redirect = 'https://student.sbhs.net.au';
        }

        // save settings
        set_config('ssofunc', $config->ssofunc, 'auth/sbhs');
        set_config('redirect', $config->redirect, 'auth/sbhs');

        return true;
    }

}

?>
