<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2016 onwards Totara Learning Solutions LTD
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
 * @author  Valerii Kuznetsov <valerii.kuznetsov@totaralearning.com>
 * @package mod_facetoface
 */

use mod_facetoface\{attendees_helper, reservations, seminar_event, signup, signup_helper};
use mod_facetoface\signup\state\{attendance_state, booked, requested, requestedadmin, waitlisted};
use totara_job\job_assignment;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests reservation functions
 */
class mod_facetoface_reservation_testcase extends advanced_testcase {
    /**
     * Check that users deallocated correctly
     */
    public function test_facetoface_remove_allocations() {
        $this->resetAfterTest(true);

        $manager = $this->getDataGenerator()->create_user();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);

        /** @var mod_facetoface_generator $facetofacegenerator */
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $facetoface = $facetofacegenerator->create_instance(array(
            'course' => $course->id,
            'multiplesessions' => 1,
            'managerreserve' => 1,
            'maxmanagerreserves' => 2
        ));
        // Create session.
        $sessiondate = new stdClass();
        $sessiondate->timestart = time() + DAYSECS;
        $sessiondate->timefinish = time() + DAYSECS + 60;
        $sessiondate->sessiontimezone = 'Pacific/Auckland';

        $sessiondata = array(
            'facetoface' => $facetoface->id,
            'capacity' => 5,
            'allowoverbook' => 1,
            'sessiondates' => array($sessiondate),
            'mincapacity' => '1',
        );
        $sessionid = $facetofacegenerator->add_session($sessiondata);
        $sessiondata['datetimeknown'] = '1';
        $session = facetoface_get_session($sessionid);
        $seminarevent = new \mod_facetoface\seminar_event($sessionid);
        $seminar = $seminarevent->get_seminar();

        // Allocate to session by manager.
        $this->setUser($manager);
        \mod_facetoface\signup_helper::signup(\mod_facetoface\signup::create($user1->id, new \mod_facetoface\seminar_event($session->id)));
        \mod_facetoface\signup_helper::signup(\mod_facetoface\signup::create($user2->id, new \mod_facetoface\seminar_event($session->id)));

        $this->execute_adhoc_tasks();
        $sink = $this->redirectMessages();
        \mod_facetoface\reservations::remove_allocations($seminarevent, $seminar, array($user1->id), true, $manager->id);
        $this->execute_adhoc_tasks();
        $this->assertSame(1, $sink->count());
        $messages = $sink->get_messages();
        $sink->clear();

        $this->assertContains('BOOKING CANCELLED', $messages[0]->fullmessage);
        $this->assertEquals($user1->id, $messages[0]->useridto);

        $sink = $this->redirectMessages();
        \mod_facetoface\reservations::remove_allocations($seminarevent, $seminar, array($user2->id), false, $manager->id);
        $this->execute_adhoc_tasks();
        $this->assertSame(1, $sink->count());
        $messages = $sink->get_messages();
        $sink->clear();
        $this->assertContains('BOOKING CANCELLED', $messages[0]->fullmessage);
        $this->assertEquals($user2->id, $messages[0]->useridto);
    }

    /**
     * @return array
     */
    public function data_closed_registrationtimeset() {
        $now = time();
        $monthsecs = 30 * DAYSECS;
        return [
            [$now + $monthsecs, 0],
            [0, $now - $monthsecs * 3],
            [$now - $monthsecs * 5, $now - $monthsecs * 4],
            [$now + $monthsecs * 6, $now + $monthsecs * 7]
        ];
    }

    /**
     * @param integer $registrationtimestart
     * @param integer $registrationtimefinish
     * @dataProvider data_closed_registrationtimeset
     */
    public function test_make_reservation_when_registration_is_closed($registrationtimestart, $registrationtimefinish) {
        $gen = $this->getDataGenerator();
        $manager = $gen->create_user();
        $staff = $gen->create_user();
        $student1 = $gen->create_user();
        $student2 = $gen->create_user();
        $student3 = $gen->create_user();
        $course = $gen->create_course();

        $managerja = job_assignment::create_default($manager->id);
        job_assignment::create_default($student1->id, ['managerjaid' => $managerja->id]);
        job_assignment::create_default($student2->id, ['managerjaid' => $managerja->id]);
        $staffja = job_assignment::create_default($staff->id);
        job_assignment::create_default($student1->id, ['managerjaid' => $staffja->id]);
        job_assignment::create_default($student2->id, ['managerjaid' => $staffja->id]);

        $gen->enrol_user($student1->id, $course->id);
        $gen->enrol_user($student3->id, $course->id);
        $gen->enrol_user($manager->id, $course->id, 'manager');
        $gen->enrol_user($staff->id, $course->id, 'staffmanager');

        /** @var mod_facetoface_generator $f2fgen */
        $f2fgen = $gen->get_plugin_generator('mod_facetoface');
        $f2f = $f2fgen->create_instance([
            'course' => $course->id,
            'managerreserve' => 1,
            'maxmanagerreserves' => 2
        ]);
        $sessionid = $f2fgen->add_session([
            'facetoface' => $f2f->id,
            'sessiondates' => [time() + YEARSECS],
            'registrationtimestart' => $registrationtimestart,
            'registrationtimefinish' => $registrationtimefinish
        ]);

        $seminarevent = new seminar_event($sessionid);
        $signup1by1 = signup::create($student1->id, $seminarevent)->set_actorid($student1->id);
        $signup2by2 = signup::create($student2->id, $seminarevent)->set_actorid($student2->id);
        $signup3by3 = signup::create($student3->id, $seminarevent)->set_actorid($student3->id);
        foreach ([booked::class, waitlisted::class, requested::class, requestedadmin::class] as $stateclass) {
            $this->setUser($student1);
            $this->assertFalse($signup1by1->can_switch($stateclass), '#1 should not be able to switch to '.$stateclass);
            $this->setUser($student2);
            $this->assertFalse($signup2by2->can_switch($stateclass), '#2 should not be able to switch to '.$stateclass);
            $this->setUser($student3);
            $this->assertFalse($signup3by3->can_switch($stateclass), '#3 should not be able to switch to '.$stateclass);
        }

        $this->setUser($manager);
        $reservebymanager = signup::create(0, $seminarevent)->set_bookedby($manager->id);
        $this->assertTrue($reservebymanager->can_switch(booked::class), 'manager should be able to switch to '.booked::class);
        $this->assertFalse($reservebymanager->can_switch(waitlisted::class), 'manager should not be able to switch to '.waitlisted::class);

        $this->setUser($staff);
        $reservebystaff = signup::create(0, $seminarevent)->set_bookedby($staff->id);
        foreach ([booked::class, waitlisted::class] as $stateclass) {
            $this->assertFalse($reservebystaff->can_switch($stateclass), 'staff should not be able to switch to '.$stateclass);
        }
    }

    /**
     * Test current signup records including reservations against expected outcomes.
     * @param integer $sessionid
     * @param stdClass[] $expected
     */
    private static function assert_signups_of_event(int $sessionid, array $expected): void {
        global $DB;
        $statcode = waitlisted::get_code();
        $signups = iterator_to_array($DB->get_recordset_sql(
            "SELECT su.id, su.userid, sus.statuscode
               FROM {facetoface_signups} su
         INNER JOIN {facetoface_signups_status} sus ON su.id = sus.signupid
              WHERE su.sessionid = {$sessionid}
                AND sus.superceded = 0
                AND sus.statuscode >= {$statcode}
           ORDER BY su.id"
        ), false);
        // remove id
        $signups = array_map(function ($signup) {
            unset($signup->id);
            return $signup;
        }, $signups);
        self::assertEquals($expected, $signups);
    }

    public function test_make_reservation_in_overbooked_event() {
        global $DB;
        /** @var moodle_database $DB */

        $gen = $this->getDataGenerator();
        $manager = $gen->create_user();
        $student1 = $gen->create_user();
        $student2 = $gen->create_user();
        $student3 = $gen->create_user();
        $course = $gen->create_course();

        $managerja = job_assignment::create_default($manager->id);
        job_assignment::create_default($student1->id, ['managerjaid' => $managerja->id]);
        job_assignment::create_default($student2->id, ['managerjaid' => $managerja->id]);

        $gen->enrol_user($student1->id, $course->id);
        $gen->enrol_user($student3->id, $course->id);
        $gen->enrol_user($manager->id, $course->id, 'manager');

        /** @var mod_facetoface_generator $f2fgen */
        $f2fgen = $gen->get_plugin_generator('mod_facetoface');
        $f2f = $f2fgen->create_instance([
            'course' => $course->id,
            'managerreserve' => 1,
            'maxmanagerreserves' => 2
        ]);
        $sessionid = $f2fgen->add_session([
            'facetoface' => $f2f->id,
            'capacity' => 2,
            'allowoverbook' => 1,
            'sessiondates' => [time() + YEARSECS]
        ]);

        $seminarevent = new seminar_event($sessionid);
        $this->setUser($manager);

        // Add student3
        $signup3 = signup_helper::signup(signup::create($student3->id, $seminarevent));
        $this->assertInstanceOf(booked::class, $signup3->get_state());
        $this->assertEquals(1, facetoface_get_num_attendees($sessionid));
        $this->assertEquals(0, facetoface_get_num_attendees($sessionid, waitlisted::get_code(), '='));

        // Make two reservations
        reservations::add($seminarevent, $manager->id, 0, 2);
        $this->assertEquals(2, facetoface_get_num_attendees($sessionid));
        $this->assertEquals(1, facetoface_get_num_attendees($sessionid, waitlisted::get_code(), '='));

        sleep(1);

        // Add student1
        $signup1 = signup_helper::signup(signup::create($student1->id, $seminarevent));
        $this->assertInstanceOf(waitlisted::class, $signup1->get_state());
        $this->assertEquals(2, facetoface_get_num_attendees($sessionid));
        $this->assertEquals(2, facetoface_get_num_attendees($sessionid, waitlisted::get_code(), '='));

        self::assert_signups_of_event($sessionid, [
            (object)['userid' => $student3->id, 'statuscode' => booked::get_code()],
            (object)['userid' => 0, 'statuscode' => booked::get_code()],
            (object)['userid' => 0, 'statuscode' => waitlisted::get_code()],
            (object)['userid' => $student1->id, 'statuscode' => waitlisted::get_code()],
        ]);

        $reservedsignupstats = iterator_to_array($DB->get_recordset_sql(
            "SELECT sus.id, sus.statuscode
               FROM {facetoface_signups_status} sus
         INNER JOIN {facetoface_signups} su ON sus.signupid = su.id
              WHERE su.sessionid = {$sessionid}
                AND su.userid = 0
           ORDER BY su.id"
        ), false);

        $this->assertCount(2, $reservedsignupstats);
        $this->assertEquals(booked::get_code(), $reservedsignupstats[0]->statuscode);
        $this->assertEquals(waitlisted::get_code(), $reservedsignupstats[1]->statuscode);

        // Remove student3
        signup_helper::user_cancel(signup::create($student3->id, $seminarevent));
        self::assert_signups_of_event($sessionid, [
            (object)['userid' => 0, 'statuscode' => booked::get_code()],
            (object)['userid' => 0, 'statuscode' => booked::get_code()],
            (object)['userid' => $student1->id, 'statuscode' => waitlisted::get_code()],
        ]);

        // Remove one reservation
        reservations::remove($seminarevent, $manager->id, 1);
        self::assert_signups_of_event($sessionid, [
            (object)['userid' => 0, 'statuscode' => booked::get_code()],
            (object)['userid' => $student1->id, 'statuscode' => booked::get_code()],
        ]);
    }
}
