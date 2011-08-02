<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $name = 'theme_shsiii/password_reset';
    $title = 'Password Reset URL';
    $description = 'URL to password reset placed in the salutation menu';
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Logo file setting
    /*$name = 'theme_shsiii/logo';
    $title = get_string('logo','theme_shsiii');
    $description = get_string('logodesc', 'theme_shsiii');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $settings->add($setting);*/

    // Foot note setting
    /*$name = 'theme_shsiii/footnote';
    $title = get_string('footnote','theme_shsiii');
    $description = get_string('footnotedesc', 'theme_shsiii');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);*/

    // Custom CSS file
    $name = 'theme_shsiii/customcss';
    $title = get_string('customcss','theme_shsiii');
    $description = get_string('customcssdesc', 'theme_shsiii');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

}

//$THEME->csspostprocess = 'shsiii_process_css';
