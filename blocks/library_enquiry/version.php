<?php

// This file is part of Moodle - http://moodle.org/
//
/**

* SBHS Library Search
* Written by James Rudd, 08/05/2007
*
* Based on Wikipedia Search  v2006100700 Author: David Horat ( david.horat@gmail.com )
* based on work of Aggelos Panagiotakis( agelospanagiotakis@gmail.com )
* Also Based on simpledocssearch v0.1 Written by Darren Smith (with some help from Marky Clarky) on 21/3/2006
* Uses some code from Juilan Ridden, MonteNet
* License: GPL
*
* @package   block_library
* @copyright 2013 James Rudd
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/


defined('MOODLE_INTERNAL') || die();

$plugin->cron      = 0;
$plugin->version = 2013021400;
$plugin->requires  = 2010112400; // Moodle 2.0
$plugin->component = 'block_library_enquiry';      // Full name of the plugin (used for diagnostics)
$plugin->maturity  = MATURITY_STABLE; // = 200
$plugin->release   = 'v1.0.1';
