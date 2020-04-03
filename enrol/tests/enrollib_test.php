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
 * Test non-plugin enrollib parts.
 *
 * @package    core_enrol
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Test non-plugin enrollib parts.
 *
 * @package    core
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_enrollib_testcase extends advanced_testcase {

    public function test_enrol_get_all_users_courses() {
        global $DB, $CFG;

        $this->resetAfterTest();

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        $this->assertNotEmpty($teacherrole);

        $admin = get_admin();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();
        $user5 = $this->getDataGenerator()->create_user();

        $category1 = $this->getDataGenerator()->create_category(array('visible'=>0));
        $category2 = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(array('category'=>$category1->id));
        $course2 = $this->getDataGenerator()->create_course(array('category'=>$category2->id));
        $course3 = $this->getDataGenerator()->create_course(array('category'=>$category2->id, 'visible'=>0));
        $course4 = $this->getDataGenerator()->create_course(array('category'=>$category2->id));

        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $DB->set_field('enrol', 'status', ENROL_INSTANCE_DISABLED, array('id'=>$maninstance1->id));
        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $maninstance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $maninstance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $maninstance4 = $DB->get_record('enrol', array('courseid'=>$course4->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $manual = enrol_get_plugin('manual');
        $this->assertNotEmpty($manual);

        $manual->enrol_user($maninstance1, $user1->id, $teacherrole->id);
        $manual->enrol_user($maninstance1, $user2->id, $studentrole->id);
        $manual->enrol_user($maninstance1, $user4->id, $teacherrole->id, 0, 0, ENROL_USER_SUSPENDED);
        $manual->enrol_user($maninstance1, $admin->id, $studentrole->id);

        $manual->enrol_user($maninstance2, $user1->id);
        $manual->enrol_user($maninstance2, $user2->id);
        $manual->enrol_user($maninstance2, $user3->id, 0, 1, time()+(60*60));

        $manual->enrol_user($maninstance3, $user1->id);
        $manual->enrol_user($maninstance3, $user2->id);
        $manual->enrol_user($maninstance3, $user3->id, 0, 1, time()-(60*60));
        $manual->enrol_user($maninstance3, $user4->id, 0, 0, 0, ENROL_USER_SUSPENDED);


        $courses = enrol_get_all_users_courses($CFG->siteguest);
        $this->assertSame(array(), $courses);

        $courses = enrol_get_all_users_courses(0);
        $this->assertSame(array(), $courses);

        // Results are sorted by visibility, sortorder by default (in our case order of creation)

        $courses = enrol_get_all_users_courses($admin->id);
        $this->assertCount(1, $courses);
        $this->assertEquals(array($course1->id), array_keys($courses));

        $courses = enrol_get_all_users_courses($admin->id, true);
        $this->assertCount(0, $courses);
        $this->assertEquals(array(), array_keys($courses));

        $courses = enrol_get_all_users_courses($user1->id);
        $this->assertCount(3, $courses);
        $this->assertEquals(array($course2->id, $course1->id, $course3->id), array_keys($courses));

        $courses = enrol_get_all_users_courses($user1->id, true);
        $this->assertCount(1, $courses);
        $this->assertEquals(array($course2->id), array_keys($courses));

        $courses = enrol_get_all_users_courses($user2->id);
        $this->assertCount(3, $courses);
        $this->assertEquals(array($course2->id, $course1->id, $course3->id), array_keys($courses));

        $courses = enrol_get_all_users_courses($user2->id, true);
        $this->assertCount(1, $courses);
        $this->assertEquals(array($course2->id), array_keys($courses));

        $courses = enrol_get_all_users_courses($user3->id);
        $this->assertCount(2, $courses);
        $this->assertEquals(array($course2->id, $course3->id), array_keys($courses));

        $courses = enrol_get_all_users_courses($user3->id, true);
        $this->assertCount(1, $courses);
        $this->assertEquals(array($course2->id), array_keys($courses));

        $courses = enrol_get_all_users_courses($user4->id);
        $this->assertCount(2, $courses);
        $this->assertEquals(array($course1->id, $course3->id), array_keys($courses));

        $courses = enrol_get_all_users_courses($user4->id, true);
        $this->assertCount(0, $courses);
        $this->assertEquals(array(), array_keys($courses));

        // Make sure sorting and columns work.

        $basefields = array('id', 'category', 'sortorder', 'shortname', 'fullname', 'idnumber',
            'startdate', 'visible', 'groupmode', 'groupmodeforce', 'defaultgroupingid');

        $courses = enrol_get_all_users_courses($user2->id, true);
        $course = reset($courses);
        context_helper::preload_from_record($course);
        $course = (array)$course;
        $this->assertEquals($basefields, array_keys($course), '', 0, 10, true);

        $courses = enrol_get_all_users_courses($user2->id, false, 'timecreated');
        $course = reset($courses);
        $this->assertTrue(property_exists($course, 'timecreated'));

        $courses = enrol_get_all_users_courses($user2->id, false, null, 'id DESC');
        $this->assertEquals(array($course3->id, $course2->id, $course1->id), array_keys($courses));
    }

    public function test_enrol_user_sees_own_courses() {
        global $DB, $CFG;

        $this->resetAfterTest();

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        $this->assertNotEmpty($teacherrole);

        $admin = get_admin();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();
        $user5 = $this->getDataGenerator()->create_user();
        $user6 = $this->getDataGenerator()->create_user();

        $category1 = $this->getDataGenerator()->create_category(array('visible'=>0));
        $category2 = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(array('category'=>$category1->id));
        $course2 = $this->getDataGenerator()->create_course(array('category'=>$category2->id));
        $course3 = $this->getDataGenerator()->create_course(array('category'=>$category2->id, 'visible'=>0));
        $course4 = $this->getDataGenerator()->create_course(array('category'=>$category2->id));

        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $DB->set_field('enrol', 'status', ENROL_INSTANCE_DISABLED, array('id'=>$maninstance1->id));
        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $maninstance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $maninstance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $maninstance4 = $DB->get_record('enrol', array('courseid'=>$course4->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $manual = enrol_get_plugin('manual');
        $this->assertNotEmpty($manual);

        $manual->enrol_user($maninstance1, $admin->id, $studentrole->id);

        $manual->enrol_user($maninstance3, $user1->id, $teacherrole->id);

        $manual->enrol_user($maninstance2, $user2->id, $studentrole->id);

        $manual->enrol_user($maninstance1, $user3->id, $studentrole->id, 1, time()+(60*60));
        $manual->enrol_user($maninstance2, $user3->id, 0, 1, time()-(60*60));
        $manual->enrol_user($maninstance3, $user2->id, $studentrole->id);
        $manual->enrol_user($maninstance4, $user2->id, 0, 0, 0, ENROL_USER_SUSPENDED);

        $manual->enrol_user($maninstance1, $user4->id, $teacherrole->id, 0, 0, ENROL_USER_SUSPENDED);
        $manual->enrol_user($maninstance3, $user4->id, 0, 0, 0, ENROL_USER_SUSPENDED);


        $this->assertFalse(enrol_user_sees_own_courses($CFG->siteguest));
        $this->assertFalse(enrol_user_sees_own_courses(0));
        $this->assertFalse(enrol_user_sees_own_courses($admin));
        $this->assertFalse(enrol_user_sees_own_courses(-222)); // Nonexistent user.

        $this->assertTrue(enrol_user_sees_own_courses($user1));
        $this->assertTrue(enrol_user_sees_own_courses($user2->id));
        $this->assertFalse(enrol_user_sees_own_courses($user3->id));
        $this->assertFalse(enrol_user_sees_own_courses($user4));
        $this->assertFalse(enrol_user_sees_own_courses($user5));

        $this->setAdminUser();
        $this->assertFalse(enrol_user_sees_own_courses());

        $this->setGuestUser();
        $this->assertFalse(enrol_user_sees_own_courses());

        $this->setUser(0);
        $this->assertFalse(enrol_user_sees_own_courses());

        $this->setUser($user1);
        $this->assertTrue(enrol_user_sees_own_courses());

        $this->setUser($user2);
        $this->assertTrue(enrol_user_sees_own_courses());

        $this->setUser($user3);
        $this->assertFalse(enrol_user_sees_own_courses());

        $this->setUser($user4);
        $this->assertFalse(enrol_user_sees_own_courses());

        $this->setUser($user5);
        $this->assertFalse(enrol_user_sees_own_courses());

        $user1 = $DB->get_record('user', array('id'=>$user1->id));
        $this->setUser($user1);
        $reads = $DB->perf_get_reads();
        $this->assertTrue(enrol_user_sees_own_courses());
        $this->assertGreaterThan($reads, $DB->perf_get_reads());

        $user1 = $DB->get_record('user', array('id'=>$user1->id));
        $this->setUser($user1);
        require_login($course3);
        $reads = $DB->perf_get_reads();
        $this->assertTrue(enrol_user_sees_own_courses());
        $this->assertEquals($reads, $DB->perf_get_reads());
    }

    public function test_enrol_get_shared_courses() {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id);

        $course2 = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($user1->id, $course2->id);

        // Test that user1 and user2 have courses in common.
        $this->assertTrue(enrol_get_shared_courses($user1, $user2, false, true));
        // Test that user1 and user3 have no courses in common.
        $this->assertFalse(enrol_get_shared_courses($user1, $user3, false, true));

        // Test retrieving the courses in common.
        $sharedcourses = enrol_get_shared_courses($user1, $user2, true);

        // Only should be one shared course.
        $this->assertCount(1, $sharedcourses);
        $sharedcourse = array_shift($sharedcourses);
        // It should be course 1.
        $this->assertEquals($sharedcourse->id, $course1->id);
    }

    /**
     * Test user enrolment created event.
     */
    public function test_user_enrolment_created_event() {
        global $DB;

        $this->resetAfterTest();

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);

        $admin = get_admin();

        $course1 = $this->getDataGenerator()->create_course();

        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $manual = enrol_get_plugin('manual');
        $this->assertNotEmpty($manual);

        // Enrol user and capture event.
        $sink = $this->redirectEvents();
        $manual->enrol_user($maninstance1, $admin->id, $studentrole->id);
        $events = $sink->get_events();
        $sink->close();
        $event = array_shift($events);

        $dbuserenrolled = $DB->get_record('user_enrolments', array('userid' => $admin->id));
        $this->assertInstanceOf('\core\event\user_enrolment_created', $event);
        $this->assertEquals($dbuserenrolled->id, $event->objectid);
        $this->assertEquals(context_course::instance($course1->id), $event->get_context());
        $this->assertEquals('user_enrolled', $event->get_legacy_eventname());
        $expectedlegacyeventdata = $dbuserenrolled;
        $expectedlegacyeventdata->enrol = $manual->get_name();
        $expectedlegacyeventdata->courseid = $course1->id;
        $this->assertEventLegacyData($expectedlegacyeventdata, $event);
        $expected = array($course1->id, 'course', 'enrol', '../enrol/users.php?id=' . $course1->id, $course1->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Test user_enrolment_deleted event.
     */
    public function test_user_enrolment_deleted_event() {
        global $DB;

        $this->resetAfterTest(true);

        $manualplugin = enrol_get_plugin('manual');
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $student = $DB->get_record('role', array('shortname' => 'student'));

        $enrol = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);

        // Enrol user.
        $manualplugin->enrol_user($enrol, $user->id, $student->id);

        // Get the user enrolment information, used to validate legacy event data.
        $dbuserenrolled = $DB->get_record('user_enrolments', array('userid' => $user->id));

        // Unenrol user and capture event.
        $sink = $this->redirectEvents();
        $manualplugin->unenrol_user($enrol, $user->id);
        $events = $sink->get_events();
        $sink->close();
        $event = array_pop($events);

        // Validate the event.
        $this->assertInstanceOf('\core\event\user_enrolment_deleted', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals('user_unenrolled', $event->get_legacy_eventname());
        $expectedlegacyeventdata = $dbuserenrolled;
        $expectedlegacyeventdata->enrol = $manualplugin->get_name();
        $expectedlegacyeventdata->courseid = $course->id;
        $expectedlegacyeventdata->lastenrol = true;
        $this->assertEventLegacyData($expectedlegacyeventdata, $event);
        $expected = array($course->id, 'course', 'unenrol', '../enrol/users.php?id=' . $course->id, $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Gets the user enrolment records that match the given criteria.
     *
     * @param $userid
     * @param $courseid
     * @param $method
     * @return array
     */
    private function get_user_enrolments($userid, $courseid, $method) {
        global $DB;

        $sql = "SELECT ue.*
                  FROM {user_enrolments} ue
                  JOIN {enrol} enrol ON ue.enrolid = enrol.id
                 WHERE ue.userid = :userid AND enrol.courseid = :courseid AND enrol.enrol = :method";

        return $DB->get_records_sql($sql, array('userid' => $userid, 'courseid' => $courseid, 'method' => $method));
    }

    /**
     * Test unenrol_user.
     *
     * To make sure that other users, courses and enrolment plugins are not affected, we will set up:
     * - user1 is enrolled manually in course1 and course2
     * - user2 is enrolled manually in course2
     * - user1 and user2 are enrolled via self in course1
     * Then we will remove user1's manual course1 enrolment and check:
     * - user1 is still enrolled in course1 via self and course2 manually
     * - user2 is still enrolled in course1 via self and course2 manually
     * Then we will remove user2's manual course2 enrolment and check:
     * - user1 is still enrolled in course1 via self and course2 manually
     * - user2 is still enrolled in course1 via self but not course2
     *
     * To run:
     * vendor/bin/phpunit --filter test_unenrol_user --verbose core_enrollib_testcase enrol/tests/enrollib_test.php
     */
    public function test_unenrol_user() {
        global $DB;

        $this->resetAfterTest(true);

        // Set up the objects for the test.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        // Check the user enrolments table for user1 and user2 before we start.
        $userenrolmentsstart = $DB->get_records_select('user_enrolments', "userid IN ({$user1->id}, {$user2->id})");
        $this->assertEmpty($userenrolmentsstart);

        // Enrolment plugins.
        $manualplugin = enrol_get_plugin('manual');
        $selfplugin = enrol_get_plugin('self');
        $student = $DB->get_record('role', array('shortname' => 'student'));

        // Enrol users in courses with various methods.
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manualplugin->enrol_user($enrol, $user1->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manualplugin->enrol_user($enrol, $user1->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manualplugin->enrol_user($enrol, $user2->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->enrol_user($enrol, $user1->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->enrol_user($enrol, $user2->id, $student->id);

        // Verify the data.
        $this->assertEquals(5, $DB->count_records_select('user_enrolments', "userid IN ({$user1->id}, {$user2->id})"));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course1->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course2->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course2->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course1->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course1->id, 'self')));

        // Unenrol user1 from course1.
        $sink = $this->redirectEvents();
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manualplugin->unenrol_user($enrol, $user1->id);
        $events = $sink->get_events();
        $sink->close();

        // Check the events.
        $this->assertEquals(1, count($events)); // Just the user_enrolment_deleted event.
        $event = reset($events);
        $this->assertInstanceOf('core\event\user_enrolment_deleted', $event);
        // Lastenrol must be false because this user is still enrolled via self.
        $eventdata = $event->get_data();
        $this->assertEquals(false, $eventdata['other']['userenrolment']['lastenrol']);

        // Verify the data.
        $this->assertEquals(4, $DB->count_records_select('user_enrolments', "userid IN ({$user1->id}, {$user2->id})"));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course2->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course2->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course1->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course1->id, 'self')));

        // Unenrol user2 from course2.
        $sink = $this->redirectEvents();
        $enrol = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manualplugin->unenrol_user($enrol, $user2->id);
        $events = $sink->get_events();
        $sink->close();

        // Check the events.
        $this->assertEquals(2, count($events)); // User_enrolment_deleted and role_unassigned.
        if (get_class($events[0]) == 'core\event\role_unassigned') { // Figure out which one is which.
            $roleevent = $events[0];
            $unassignedevent = $events[1];
        } else {
            $roleevent = $events[1];
            $unassignedevent = $events[0];
        }
        $this->assertInstanceOf('core\event\role_unassigned', $roleevent);
        $this->assertInstanceOf('core\event\user_enrolment_deleted', $unassignedevent);
        // Lastenrol must be true because this user is no longer enroled at all.
        $unassignedeventdata = $unassignedevent->get_data();
        $this->assertEquals(true, $unassignedeventdata['other']['userenrolment']['lastenrol']);

        // Verify the data.
        $this->assertEquals(3, $DB->count_records_select('user_enrolments', "userid IN ({$user1->id}, {$user2->id})"));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course2->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course1->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course1->id, 'self')));
    }

    /**
     * Test unenrol_user_bulk.
     *
     * To make sure that other users, courses and enrolment plugins are not affected, we will set up:
     * - user1 is enrolled manually in course1
     * - user1 and user2 are enrolled via self in course1
     * - user1 and user2 are enrolled via self in course2
     * - user3 is enrolled via self in course1
     * Then we will remove user1 and user2 from course1 and check:
     * - user1 is still enrolled in course1 manually and course2 via self
     * - user2 is still enrolled in course2 via self
     * - user3 is still enrolled in course1 via self
     * Then we will remove user3 from course1 and check:
     * - user1 is still enrolled in course1 manually and course2 via self
     * - user2 is still enrolled in course2 via self
     * - user3 is not enrolled in any course
     *
     * To run:
     * vendor/bin/phpunit --filter test_unenrol_user_bulk --verbose core_enrollib_testcase enrol/tests/enrollib_test.php
     */
    public function test_unenrol_user_bulk() {
        global $DB;

        $this->resetAfterTest(true);

        // Set up the objects for the test.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        // Check the user enrolments table for user1, user2 and user3 before we start.
        $userenrolmentsstart = $DB->get_records_select('user_enrolments', "userid IN ({$user1->id}, {$user2->id}, {$user3->id})");
        $this->assertEmpty($userenrolmentsstart);

        // Enrolment plugins.
        $manualplugin = enrol_get_plugin('manual');
        $selfplugin = enrol_get_plugin('self');
        $student = $DB->get_record('role', array('shortname' => 'student'));

        // Enrol users in courses with various methods.
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manualplugin->enrol_user($enrol, $user1->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->enrol_user($enrol, $user1->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->enrol_user($enrol, $user2->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->enrol_user($enrol, $user1->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->enrol_user($enrol, $user2->id, $student->id);
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->enrol_user($enrol, $user3->id, $student->id);

        // Verify the data.
        $this->assertEquals(6,
            $DB->count_records_select('user_enrolments', "userid IN ({$user1->id}, {$user2->id}, {$user3->id})"));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course1->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course1->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course1->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course2->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course2->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user3->id, $course1->id, 'self')));

        // Unenrol user1 and user2 from course1 via self.
        $sink = $this->redirectEvents();
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->unenrol_user_bulk($enrol, array($user1->id, $user2->id));
        $events = $sink->get_events();
        $sink->close();

        // Check the events.
        $this->assertEquals(7, count($events)); // Four bulk, two assignment deleted, one role unassigned.
        $eventbulkenrolmentsstarted = null;
        $eventbulkenrolmentsended = null;
        $eventbulkroleassignmentsstarted = null;
        $eventbulkroleassignmentsended = null;
        $userenrolmentdeleteduser1 = null;
        $userenrolmentdeleteduser2 = null;
        $roleunassigneduser2 = null;
        foreach ($events as $event) {
            switch (get_class($event)) {
                case 'totara_core\event\bulk_enrolments_started':
                    $eventbulkenrolmentsstarted = $event;
                    break;
                case 'totara_core\event\bulk_enrolments_ended':
                    $eventbulkenrolmentsended = $event;
                    break;
                case 'totara_core\event\bulk_role_assignments_started':
                    $eventbulkroleassignmentsstarted = $event;
                    break;
                case 'totara_core\event\bulk_role_assignments_ended':
                    $eventbulkroleassignmentsended = $event;
                    break;
                case 'core\event\user_enrolment_deleted':
                    $data = $event->get_data();
                    if ($data['relateduserid'] == $user1->id && $data['courseid'] == $course1->id) {
                        $userenrolmentdeleteduser1 = $event;
                    } else if ($data['relateduserid'] == $user2->id && $data['courseid'] == $course1->id) {
                        $userenrolmentdeleteduser2 = $event;
                    }
                    break;
                case 'core\event\role_unassigned':
                    $data = $event->get_data();
                    if ($data['relateduserid'] == $user2->id && $data['courseid'] == $course1->id) { // CHECK THIS VARIABLE!
                        $roleunassigneduser2 = $event;
                    }
                    break;
            }
        }
        $this->assertInstanceOf('totara_core\event\bulk_enrolments_started', $eventbulkenrolmentsstarted);
        $this->assertInstanceOf('totara_core\event\bulk_enrolments_ended', $eventbulkenrolmentsended);
        $this->assertInstanceOf('totara_core\event\bulk_role_assignments_started', $eventbulkroleassignmentsstarted);
        $this->assertInstanceOf('totara_core\event\bulk_role_assignments_ended', $eventbulkroleassignmentsended);
        $this->assertInstanceOf('core\event\user_enrolment_deleted', $userenrolmentdeleteduser1);
        $this->assertInstanceOf('core\event\user_enrolment_deleted', $userenrolmentdeleteduser2);
        $this->assertInstanceOf('core\event\role_unassigned', $roleunassigneduser2);
        // Lastenrol must be true because this user is no longer enroled at all.
        $unassignedeventdata = $userenrolmentdeleteduser1->get_data();
        $this->assertEquals(false, $unassignedeventdata['other']['userenrolment']['lastenrol']);
        $unassignedeventdata = $userenrolmentdeleteduser2->get_data();
        $this->assertEquals(true, $unassignedeventdata['other']['userenrolment']['lastenrol']);

        // Verify the data.
        $this->assertEquals(4,
            $DB->count_records_select('user_enrolments', "userid IN ({$user1->id}, {$user2->id}, {$user3->id})"));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course1->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course2->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course2->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user3->id, $course1->id, 'self')));

        // Unenrol user3 from course1.
        $sink = $this->redirectEvents();
        $enrol = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'self'), '*', MUST_EXIST);
        $selfplugin->unenrol_user_bulk($enrol, array($user3->id));
        $events = $sink->get_events();
        $sink->close();

        // Check the events.
        $this->assertEquals(6, count($events)); // Four bulk, one assignment deleted, one role unassigned = 6.
        $eventbulkenrolmentsstarted = null;
        $eventbulkenrolmentsended = null;
        $eventbulkroleassignmentsstarted = null;
        $eventbulkroleassignmentsended = null;
        $userenrolmentdeleteduser = null;
        $roleunassigneduser = null;
        foreach ($events as $event) {
            switch (get_class($event)) {
                case 'totara_core\event\bulk_enrolments_started':
                    $eventbulkenrolmentsstarted = $event;
                    break;
                case 'totara_core\event\bulk_enrolments_ended':
                    $eventbulkenrolmentsended = $event;
                    break;
                case 'totara_core\event\bulk_role_assignments_started':
                    $eventbulkroleassignmentsstarted = $event;
                    break;
                case 'totara_core\event\bulk_role_assignments_ended':
                    $eventbulkroleassignmentsended = $event;
                    break;
                case 'core\event\user_enrolment_deleted':
                    $data = $event->get_data();
                    if ($data['relateduserid'] == $user3->id) {
                        $userenrolmentdeleteduser = $event;
                    }
                    break;
                case 'core\event\role_unassigned':
                    $data = $event->get_data();
                    if ($data['relateduserid'] == $user3->id) { // CHECK THIS VARIABLE!
                        $roleunassigneduser = $event;
                    }
                    break;
            }
        }
        $this->assertInstanceOf('totara_core\event\bulk_enrolments_started', $eventbulkenrolmentsstarted);
        $this->assertInstanceOf('totara_core\event\bulk_enrolments_ended', $eventbulkenrolmentsended);
        $this->assertInstanceOf('totara_core\event\bulk_role_assignments_started', $eventbulkroleassignmentsstarted);
        $this->assertInstanceOf('totara_core\event\bulk_role_assignments_ended', $eventbulkroleassignmentsended);
        $this->assertInstanceOf('core\event\user_enrolment_deleted', $userenrolmentdeleteduser);
        $this->assertInstanceOf('core\event\role_unassigned', $roleunassigneduser);
        // Lastenrol must be true because this user is no longer enroled at all.
        $unassignedeventdata = $userenrolmentdeleteduser->get_data();
        $this->assertEquals(true, $unassignedeventdata['other']['userenrolment']['lastenrol']);

        // Verify the data.
        $this->assertEquals(3,
            $DB->count_records_select('user_enrolments', "userid IN ({$user1->id}, {$user2->id}, {$user3->id})"));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course1->id, 'manual')));
        $this->assertEquals(1, count($this->get_user_enrolments($user1->id, $course2->id, 'self')));
        $this->assertEquals(1, count($this->get_user_enrolments($user2->id, $course2->id, 'self')));
    }
}
