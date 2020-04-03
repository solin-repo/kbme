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
 * @package totara_core
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/totara/hierarchy/prefix/position/lib.php');

class event_user_position_test extends advanced_testcase {

    public function test_position_updated_event() {
        global $POSITION_CODES;
        $this->resetAfterTest();
        $sink = $this->redirectEvents();

        $user = $this->getDataGenerator()->create_user();
        $type = $POSITION_CODES['primary'];

        $assignment = new position_assignment();
        $assignment->userid = $user->id;
        $assignment->managerid = 2;
        $assignment->type = $type;

        assign_user_position($assignment);

        $events = $sink->get_events();
        $sink->clear();

        $this->assertEquals(count($events), 2);
        $eventdata[0] = $events[0]->get_data();
        $eventdata[1] = $events[1]->get_data();
        if ($eventdata[0]['eventname'] == '\totara_core\event\position_updated') {
            $posupdateeventdata = $eventdata[0];
            $roleassignedeventdata = $eventdata[1];
        } else {
            $posupdateeventdata = $eventdata[1];
            $roleassignedeventdata = $eventdata[0];
        }

        $this->assertEquals('totara_core', $posupdateeventdata['component']);
        $this->assertEquals('\totara_core\event\position_updated', $posupdateeventdata['eventname']);
        $this->assertEquals('updated', $posupdateeventdata['action']);
        $this->assertEquals($type, $posupdateeventdata['other']['type']);

        $this->assertEquals('core', $roleassignedeventdata['component']);
        $this->assertEquals('\core\event\role_assigned', $roleassignedeventdata['eventname']);
        $this->assertEquals('assigned', $roleassignedeventdata['action']);
    }

    public function test_position_viewed_event() {
        global $POSITION_CODES;
        $this->resetAfterTest();
        // Create user and course.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $type = $POSITION_CODES['primary'];

        $assignment = new position_assignment();
        $assignment->userid = $user->id;
        $assignment->type = $type;


        // Trigger event of viewing his position.
        $coursecontext = context_course::instance($course->id);

        $event = \totara_core\event\position_viewed::create_from_instance($assignment, $coursecontext);
        $event->trigger();
        $data = $event->get_data();

        $this->assertEquals($coursecontext, $event->get_context());
        $this->assertSame('r', $data['crud']);
        $this->assertSame(\core\event\base::LEVEL_OTHER, $data['edulevel']);
        $this->assertSame($user->id, $data['relateduserid']);
        $this->assertSame($assignment->type, $data['other']['type']);
        $this->assertEventContextNotUsed($event);
    }

    public function test_position_viewed_event_wrong_data() {
        $this->resetAfterTest();
        // Create user and course.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        $assignment = new position_assignment();
        $assignment->userid = $user->id;
        $assignment->type = 999;


        // Trigger event of viewing his position.
        $coursecontext = context_course::instance($course->id);

        $this->setExpectedException('coding_exception');
        $event = \totara_core\event\position_viewed::create_from_instance($assignment, $coursecontext);
        $event->trigger();

    }

    public function test_position_updated_event_wrong_data() {
        $this->resetAfterTest();
        $sink = $this->redirectEvents();

        $user = $this->getDataGenerator()->create_user();

        $assignment = new position_assignment();
        $assignment->userid = $user->id;
        $assignment->managerid = 2;
        $assignment->type = 999;

        $this->setExpectedException('coding_exception');
        assign_user_position($assignment);

    }
}