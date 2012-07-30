<?php

function shsiii_process_css($css, $theme) {

    // Set the background image for the logo
    if (!empty($theme->settings->logo)) {
        $logo = $theme->settings->logo;
    } else {
        $logo = null;
    }
    $css = shsiii_set_logo($css, $logo);

    // Set custom CSS
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = shsiii_set_customcss($css, $customcss);

    $css = shsiii_insert_fonts($css, $theme);

    return $css;
}

function shsiii_set_logo($css, $logo) {
    global $OUTPUT;
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = $OUTPUT->pix_url('images/logo','theme');
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

function shsiii_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}


function shsiii_insert_fonts($css, $theme) {
    global $CFG;
    $tag = '\[\[font:([^\]]*)\]\]';
    $replacement = $CFG->wwwroot.'/theme/shsiii/fonts/\1';
    $css = preg_replace('%'.$tag.'%', $replacement, $css);
    return $css;
}
