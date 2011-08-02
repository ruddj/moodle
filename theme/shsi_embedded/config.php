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
 * Config for the splash theme
 *
 * @package   theme_shsi
 * @copyright 2011 James Rudd based on Moodle 1.9 theme by David Isaacs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

////////////////////////////////////////////////////
// Name of the theme.
////////////////////////////////////////////////////
$THEME->name = 'shsi_embedded';


/////////////////////////////////////////////////////
// List existing theme(s) to use as parents.
////////////////////////////////////////////////////
$THEME->parents = array(
    'shsi',
    'afterburner',
    'base',
);

$THEME->parents_exclude_sheets = array(
    'base'=>array(
        'pagelayout',
        'dock'),
    'afterburner' => array (
        'afterburner_styles',
        'afterburner_menu'
        )
);

////////////////////////////////////////////////////
// Name of the stylesheet(s) you are including in
// this new theme's /styles/ directory.
////////////////////////////////////////////////////
$THEME->sheets = array(
    'shs_embedded_styles',
);

$THEME->layouts = array(
    'base' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'standard' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'course' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'coursecategory' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'incourse' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'frontpage' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'admin' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'mydashboard' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'mypublic' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'login' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'popup' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'frametop' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'maintenance' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'embedded' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    'report' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
);


$THEME->editor_sheets = array('editor');

$THEME->javascripts = array('script_embedded');

////////////////////////////////////////////////////
// Do you want to use the new navigation dock?
////////////////////////////////////////////////////
$THEME->enable_dock = true;

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
