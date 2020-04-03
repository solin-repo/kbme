<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Block for displaying user-defined links
 *
 * @package   facetoface
 * @author    Brian Barnes <brian.barnes@totaralms.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class export_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'from', $this->_customdata['from']);
        $mform->setType('from', PARAM_INT);
        $mform->addElement('hidden', 'to', $this->_customdata['to']);
        $mform->setType('to', PARAM_INT);
        if (isset($this->_customdata['course'])) {
            $mform->addElement('hidden', 'course', $this->_customdata['course']);
        } else {
            $mform->addElement('hidden', 'course', '');
        }
        $mform->setType('course', PARAM_TEXT);

        $formats = array(
            'excel' => get_string('excelformat', 'facetoface'),
            'ods' => get_string('odsformat', 'facetoface')
        );
        $mform->addElement('select', 'format', get_string('format', 'facetoface'), $formats);
        $mform->setType('format', PARAM_TEXT);
        $this->add_action_buttons(false, get_string('exporttofile', 'facetoface'));
    }
}