<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2014 onwards Totara Learning Solutions LTD
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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package user
 */

namespace totara_core\event;

defined ('MOODLE_INTERNAL') || die();

/**
 * Event triggered when user position is viewed.
 *
 * @property-read array $other {
 *      'type' => int Type of assignment
 * }
 *
 */
class position_viewed extends \core\event\base {
    /**
     * Flag for prevention of direct create() call.
     * @var bool
     */
    protected static $preventcreatecall = true;

    /**
     * Create instance of event.
     *
     * @param \position_assignment $instance Position record.
     * @param \context $context
     * @return position_viewed
     */
    public static function create_from_instance(\position_assignment $instance, \context $context) {
        $data = array(
            'context' => $context,
            'relateduserid' => $instance->userid,
            'other' => array('type' => $instance->type),
        );

        self::$preventcreatecall = false;
        $event = self::create($data);
        self::$preventcreatecall = true;

        return $event;
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns localised event name.
     */
    public static function get_name() {
        return get_string('eventpositionviewed', 'totara_hierarchy');
    }

    /**
     * Returns description of what happened.
     */
    public function get_description() {
        return "User position viewed";
    }

    /**
     * Returns url to position.
     * @return \moodle_url
     */
    public function get_url() {
        $courseid = ($this->data['courseid']) ? $this->data['courseid'] : SITEID;

        return new \moodle_url('/user/positions.php', array(
            'user' => $this->data['relateduserid'],
            'courseid' => $courseid,
            'type' => $this->data['other']['type']
        ));
    }

    protected function validate_data() {
        global $POSITION_CODES;
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call create() directly, use create_from_instance() instead.');
        }

        if (!(isset($this->data['other']['type']) && in_array($this->data['other']['type'], $POSITION_CODES))) {
            throw new \coding_exception('Type must be in position codes');
        }

        parent::validate_data();
    }
}