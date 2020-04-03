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
 * facetoface module PHPUnit data generator class
 *
 * @package    mod_facetoface
 * @subpackage phpunit
 * @author     Maria Torres <maria.torres@totaralms.com>
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

defined('MOODLE_INTERNAL') || die();

class mod_facetoface_generator extends testing_module_generator {

    /**
     * Create new facetoface module instance
     * @param array|stdClass $record
     * @param array $options
     * @throws coding_exception
     * @return stdClass activity record with extra cmid field
     */
    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once("$CFG->dirroot/mod/facetoface/lib.php");

        $this->instancecount++;
        $i = $this->instancecount;

        $record = (object)(array)$record;
        $options = (array)$options;

        if (empty($record->course)) {
            throw new coding_exception('module generator requires $record->course');
        }

        $defaults = array();
        $defaults['intro'] = 'Test facetoface ' . $i;
        $defaults['introformat'] = FORMAT_MOODLE;
        $defaults['name'] = get_string('pluginname', 'facetoface').' '.$i;
        $defaults['shortname'] = 'facetoface' . $i;
        $defaults['description'] = 'description';
        $defaults['thirdparty'] = null; // Default to username
        $defaults['thirdpartywaitlist'] = 0;
        $defaults['display'] = 6;
        $defaults['showoncalendar'] = '1';
        $defaults['approvalreqd'] = 0;
        $defaults['usercalentry'] = 1;
        $defaults['multiplesessions'] = 0;
        $defaults['completionstatusrequired'] = '{"100":1}';
        $defaults['managerreserve'] = 0;
        $defaults['maxmanagerreserves'] = 1;
        $defaults['reservecanceldays'] = 1;
        $defaults['reservedays'] = 2;
        foreach ($defaults as $field => $value) {
            if (!isset($record->$field)) {
                $record->$field = $value;
            }
        }

        if (isset($options['idnumber'])) {
            $record->cmidnumber = $options['idnumber'];
        } else {
            $record->cmidnumber = '';
        }

        $record->coursemodule = $this->precreate_course_module($record->course, $options);
        $id = facetoface_add_instance($record, null);
        return $this->post_add_instance($id, $record->coursemodule);
    }

    /**
     * Add facetoface session
     * @param array|stdClass $record
     * @param array $options
     * @throws coding_exception
     * @return bool|int session created
     */
    public function add_session($record, $options = array()) {
        global $USER, $CFG;
        require_once("$CFG->dirroot/mod/facetoface/lib.php");

        $record = (object) (array) $record;

        if (empty($record->facetoface)) {
            throw new coding_exception('Session generator requires $record->facetoface');
        }

        if (empty($record->sessiondates)) {
            $time = time();
            $sessiondate = new stdClass();
            $sessiondate->timestart = $time;
            $sessiondate->timefinish = $time + (DAYSECS * 2);
            $sessiondate->sessiontimezone = 'Pacific/Auckland';
            $sessiondates = array($sessiondate);
        } else {
            $sessiondates = $record->sessiondates;
        }

        if (!isset($record->datetimeknown)) {
            $record->datetimeknown = 1;
        }
        if (!isset($record->capacity)) {
            $record->capacity = 10;
        }
        if (!isset($record->allowoverbook)) {
            $record->allowoverbook = 0;
        }
        if (!isset($record->duration)) {
            $record->duration = '';
        }
        if (!isset($record->normalcost)) {
            $record->normalcost = '$100';
        }
        if (!isset($record->discountcost)) {
            $record->discountcost = '$NZ20';
        }
        if (!isset($record->discountcost)) {
            $record->discountcost = FORMAT_MOODLE;
        }
        if (!isset($record->roomid)) {
            $record->roomid = 0;
        }
        if (!isset($record->timemodified)) {
            $record->timemodified = time();
        }
        if (!isset($record->waitlisteveryone)) {
            $record->waitlisteveryone = 0;
        }

        $record->usermodified = $USER->id;

        return facetoface_add_session($record, $sessiondates);
    }

    /**
     * Create facetoface content (Session)
     * @param stdClass $instance
     * @param array|stdClass $record
     * @return bool|int content created
     */
    public function create_content($instance, $record = array()) {
        $record = (array)$record + array(
            'facetoface' => $instance->id
        );

        return $this->add_session($record);
    }
}
