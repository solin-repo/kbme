<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2017 onwards Totara Learning Solutions LTD
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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package mod_facetoface
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("{$CFG->dirroot}/mod/facetoface/lib.php");

use totara_job\job_assignment;
use mod_facetoface\seminar_event;
use mod_facetoface\signup;
use mod_facetoface\signup_helper;
use mod_facetoface\signup\state\{booked, waitlisted};

/**
 * Class mod_facetoface_waitlist_event_testcase
 */
class mod_facetoface_waitlist_event_testcase extends advanced_testcase {

    /**
     * @return stdClass
     */
    private function create_facetoface(): stdClass {
        $generator = $this->getDataGenerator();

        /** @var mod_facetoface_generator $f2fgenerator */
        $f2fgenerator = $generator->get_plugin_generator("mod_facetoface");

        $course = $generator->create_course(null, ['createsections' => true]);
        $parameters = ['course' => $course->id];
        $f2f = $f2fgenerator->create_instance((object) $parameters);
        return $f2f;
    }

    /**
     * @param int $numberofusers    How many users to be created
     * @param stdClass $course Course to enrol users to
     * @return stdClass[]
     */
    private function create_users(int $numberofusers=2, stdClass $course): array {
        $generator = $this->getDataGenerator();
        $users = array();

        for ($i = 0; $i < $numberofusers; $i++) {
            $user = $generator->create_user();
            $generator->enrol_user($user->id, $course->id);
            $users[] = $user;
        }

        return $users;
    }

    /**
     * Create signup and force state
     * @param stdClass $user
     * @param stdClass $session
     * @param string $stateclass This is for the sign up status whether user is booked or waitlisted
     */
    private function create_signup(stdClass $user, stdClass $session, string $stateclass): signup {
        global $DB;
        if (!$DB->record_exists("job_assignment", ['userid' => $user->id])) {
            $manager = $this->getDataGenerator()->create_user();
            $managerja = job_assignment::create_default($manager->id);
            $uniqid = uniqid();
            $data = [
                'userid' => $user->id,
                'fullname' => 'userja' . $uniqid,
                'shortname' => 'userja' . $uniqid,
                'idnumber' => $uniqid,
                'managerjaid' => $managerja->id
            ];

            job_assignment::create($data);
        }

        $signup = signup::create($user->id, new seminar_event($session->id));
        signup_helper::signup($signup);

        $state = new $stateclass($signup);
        $refClass = new ReflectionClass($signup);
        $method = $refClass->getMethod("update_status");
        $method->setAccessible(true);
        $method->invoke($signup, $state);

        return $signup;
    }

    /**
     * Test suite of checking the whether the render is rendering correctly a wait-listed seminar event that has
     * a user as booked along side with the users that have wait-listed status. As a result, the test should only expects
     * one user as waitlisted,not two, even though the event is a wait-listed event
     *
     * @return void
     */
    public function test_rendering_f2f_waitlist_event_with_booked_users(): void {
        global $USER, $DB, $PAGE, $CFG;
        $PAGE->set_url("/");

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $f2f = $this->create_facetoface();
        $course = $DB->get_record("course", ['id' => $f2f->course]);
        $users = $this->create_users(2, $course);

        /** @var mod_facetoface_generator $f2fgenerator */
        $f2fgenerator = $this->getDataGenerator()->get_plugin_generator("mod_facetoface");
        $sessionid = $f2fgenerator->add_session((object)[
            'facetoface' => $f2f->id,
            'capacity' => 10,
            'timecreated' => time(),
            'timemodified' => time(),
            'usermodified' => $USER->id,
            'sessiondates' => []
        ]);

        $session = $DB->get_record("facetoface_sessions", ['id' => $sessionid]);
        $session->sessiondates = [];

        // Sign up the first user as a wait-listed user
        $this->create_signup(current($users), $session, waitlisted::class);

        // Sign up the second user as booked user
        next($users);
        $this->create_signup(current($users), $session, booked::class);

        /** @var mod_facetoface_renderer $f2frenderer */
        $f2frenderer = $PAGE->get_renderer("mod_facetoface");
        $rendered = $f2frenderer->print_session_list_table([$session], true, true, true, [], $CFG->wwwroot);

        // As the test suite setup was 1 user with sign up status as booked and the other as waitlisted,
        // therefore, within this test, it is expected only `1 waitlisted` rendered
        $expected = "1 / 10 (1 Wait-listed)";
        $this->assertContains($expected, $rendered);
    }

    /**
     * Test suite of rendering the event with only wait-listed user
     * @return void
     */
    public function test_rendering_f2f_waitlist_event(): void {
        global $USER, $DB, $PAGE, $CFG;
        $PAGE->set_url("/");

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $f2f = $this->create_facetoface();
        $course = $DB->get_record("course", ['id' => $f2f->course]);
        $users = $this->create_users(2, $course);

        /** @var mod_facetoface_generator $f2fgenerator */
        $f2fgenerator = $this->getDataGenerator()->get_plugin_generator("mod_facetoface");
        $sessionid = $f2fgenerator->add_session((object)[
            'facetoface' => $f2f->id,
            'capacity' => 10,
            'timecreated' => time(),
            'timemodified' => time(),
            'usermodified' => $USER->id,
            'sessiondates' => []
        ]);

        $session = $DB->get_record("facetoface_sessions", ['id' => $sessionid]);
        $session->sessiondates = [];

        foreach ($users as $user) {
            $this->create_signup($user, $session, waitlisted::class);
        }

        /** @var mod_facetoface_renderer $f2frenderer */
        $f2frenderer = $PAGE->get_renderer("mod_facetoface");
        $rendered = $f2frenderer->print_session_list_table([$session], true, true, true, [], $CFG->wwwroot);

        $expected = "0 / 10 (2 Wait-listed)";
        $this->assertContains($expected, $rendered);
    }

    /**
     * Test suite of rendering the event with wait-listed user and the event is overbooked
     * @return void
     */
    public function test_rendering_f2f_overbooked_waitlist_event(): void {
        global $DB, $PAGE, $USER, $CFG;
        $PAGE->set_url("/");

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $f2f = $this->create_facetoface();
        $course = $DB->get_record("course", ['id' => $f2f->course]);
        $users = $this->create_users(4, $course);

        /** @var mod_facetoface_generator $f2fgenerator */
        $f2fgenerator = $this->getDataGenerator()->get_plugin_generator("mod_facetoface");
        $sessionid = $f2fgenerator->add_session((object)[
            'facetoface' => $f2f->id,
            'capacity' => 2,
            'timecreated' => time(),
            'timemodified' => time(),
            'usermodified' => $USER->id,
            'sessiondates' => []
        ]);

        $session = $DB->get_record("facetoface_sessions", ['id' => $sessionid]);
        $session->sessiondates = [];

        // Create 1 waitlisted user here
        $this->create_signup($users[0], $session, waitlisted::class);
        foreach ($users as $index => $user) {
            if ($index === 0) {
                // Skipping the first user, as the user was signed up as wait-listed user
                continue;
            }

            $this->create_signup($user, $session, booked::class);
        }

        /** @var mod_facetoface_renderer $f2frenderer */
        $f2frenderer = $PAGE->get_renderer("mod_facetoface");
        $rendered = $f2frenderer->print_session_list_table([$session], true, true, true, [], $CFG->wwwroot);

        $expected = "3 / 2 (Overbooked) (1 Wait-listed)";
        $this->assertContains($expected, $rendered);
    }

    /**
     * Properly test the waitlist everyone setting.
     *
     * @return void
     */
    public function test_waitlist_everyone(): void {
        global $DB, $USER;

        $f2f = $this->create_facetoface();
        $course = $DB->get_record("course", ['id' => $f2f->course]);
        $users = $this->create_users(4, $course);

        /** @var mod_facetoface_generator $f2fgenerator */
        $f2fgenerator = $this->getDataGenerator()->get_plugin_generator("mod_facetoface");
        $sessiondate = new stdClass();
        $sessiondate->timestart = time() + (DAYSECS * 1);
        $sessiondate->timefinish = $sessiondate->timestart + (DAYSECS * 1);
        $sessiondate->sessiontimezone = 'Pacific/Auckland';
        $sessionid = $f2fgenerator->add_session((object)[
            'facetoface' => $f2f->id,
            'capacity' => 2,
            'timecreated' => time(),
            'timemodified' => time(),
            'usermodified' => $USER->id,
            'sessiondates' => array($sessiondate),
        ]);

        // Waitlist everyone needs to be set globally for seminar_event::set_waitlist(1) to have any effect.
        set_config('facetoface_allowwaitlisteveryone', 1);

        $seminar_event = new seminar_event($sessionid);
        $seminar_event->set_waitlisteveryone(1);

        // Create 4 signups.
        $signups = [];
        foreach ($users as $index => $user) {
            $signup = signup::create($user->id, $seminar_event);
            signup_helper::signup($signup);
            $signups[] = $signup;
        }

        // All signups should be waitlisted.
        $records = $DB->get_records('facetoface_signups_status', ['statuscode' => waitlisted::get_code(), 'superceded' => 0]);
        $this->assertEquals('4', count($records));

        // Do a user cancellation.
        signup_helper::user_cancel($signups[0]);

        // All remaining signups should still be waitlisted
        $records = $DB->get_records('facetoface_signups_status', ['statuscode' => waitlisted::get_code(), 'superceded' => 0]);
        $this->assertEquals('3', count($records));
    }
}
