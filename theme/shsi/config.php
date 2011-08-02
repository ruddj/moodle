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
$THEME->name = 'shsi';


/////////////////////////////////////////////////////
// List existing theme(s) to use as parents.
////////////////////////////////////////////////////
$THEME->parents = array(
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
    'shs_styles',
    'shs_layout',
    'shs_menu',
);


$THEME->editor_sheets = array('editor');

////////////////////////////////////////////////////
// Do you want to use the new navigation dock?
////////////////////////////////////////////////////
$THEME->enable_dock = true;

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
