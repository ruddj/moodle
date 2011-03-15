<?php

/**
 * Form for editing HTML block instances.
 *
 * @package   block_library
 * @copyright 2011 James Rudd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing Library block instances.
 *
 * @copyright 2011 James Rudd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_library_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('advcheckbox', 'config_samewindow', get_string('samewindow', 'block_library'));
		//$mform->addElement('advcheckbox', 'config_teacheronly', get_string('teacheronly', 'block_library'));
    }
}
