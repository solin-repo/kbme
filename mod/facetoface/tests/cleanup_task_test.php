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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package mod_facetoface
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

global $CFG;
require_once($CFG->dirroot . '/mod/facetoface/lib.php');

class mod_facetoface_cleanup_task_testcase extends advanced_testcase {

    /**
     * Tests the Cleanup Task for Face-to-face.
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function test_cleanup_task() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/lib.php');

        $this->resetAfterTest(true);

        $time = time();
        $day = 86400;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();

        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($user3->id, $course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($user4->id, $course1->id, $studentrole->id);

        /** @var totara_hierarchy_generator $hierarchygenerator */
        $hierarchygenerator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $hierarchygenerator->assign_primary_position($user2->id, $user1->id, null, null);
        $hierarchygenerator->assign_primary_position($user3->id, $user1->id, null, null);
        $hierarchygenerator->assign_primary_position($user4->id, $user1->id, null, null);

        /** @var mod_facetoface_generator $facetofacegenerator */
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $facetoface = $facetofacegenerator->create_instance(['course' => $course1->id, 'usercalentry' => false]);
        $session1id = $facetofacegenerator->add_session([
            'facetoface' => $facetoface->id,
            'sessiondates' => [
                (object)[
                    'timestart' => $time + ($day / 24) * 36,
                    'timefinish' => $time + ($day / 24) * 38,
                    'sessiontimezone' => 'Pacific/Auckland',
                ]
            ]
        ]);
        $session1 = facetoface_get_session($session1id);

        // Sign the users up to the first session.
        $sink = $this->redirectMessages();
        facetoface_user_signup($session1, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user2->id, false, $user1);
        facetoface_user_signup($session1, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user3->id, false, $user1);
        facetoface_user_signup($session1, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user4->id, false, $user1);
        $this->assertSame(3, $sink->count());
        $sink->clear();

        // Confirm the signups for session 1.
        $this->assertCount(3, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(0, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Suspend user 3.
        $user3 = $DB->get_record('user', array('id'=>$user3->id, 'deleted'=>0), '*', MUST_EXIST);
        $user3->suspended = 1;
        user_update_user($user3, false);

        // Delete user 4.
        delete_user($user4);

        // Why you may ask do we get this?
        // Because there are two events triggered here that lead to the user being cancelled.
        // 1. The user unenrolled event triggers cancellation.
        //    delete_user does this mid-way through.
        // 2. The user deleted event triggers cancellation.
        //    delete_user triggers this at the end of the function.
        $messages = $this->getDebuggingMessages();
        $this->resetDebugging();
        $this->assertCount(1, $messages);
        foreach ($messages as $message) {
            $this->assertSame('User status already changed to cancelled.', $message->message);
        }

        // The deleted user will be automatically updated but the suspended user won't.
        $this->assertCount(2, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(1, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_USER_CANCELLED));

        // This should have lead to all users in session 2 being marked as cancelled by session cancellation.
        $this->assertCount(2, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(1, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Run the cleanup task.
        $task = new \mod_facetoface\task\cleanup_task();
        $task->execute();

        $this->assertDebuggingNotCalled('Cleanup task is zealously cancelling users. Fix it!');

        // We should now have updated statuses for session 1.
        $this->assertCount(1, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(2, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_USER_CANCELLED));

        $sink->close();

        // Run the cleanup task.
        $task = new \mod_facetoface\task\cleanup_task();
        $task->execute();

        $this->assertDebuggingNotCalled('Cleanup task is zealously cancelling users. Fix it!');
    }

    public function test_suspended_users() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/lib.php');

        $this->resetAfterTest(true);

        $time = time();
        $day = 86400;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($user3->id, $course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($user4->id, $course1->id, $studentrole->id);

        /** @var mod_facetoface_generator $facetofacegenerator */
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $facetoface = $facetofacegenerator->create_instance(['course' => $course1->id, 'usercalentry' => false]);

        // Booking is open, session in the future
        $session1id = $facetofacegenerator->add_session([
            'facetoface' => $facetoface->id,
            'sessiondates' => [
                (object)[
                    'timestart' => $time + ($day / 24) * 36,
                    'timefinish' => $time + ($day / 24) * 38,
                    'sessiontimezone' => 'Pacific/Auckland',
                ]
            ]
        ]);
        $session1 = facetoface_get_session($session1id);

        // Session is over
        $session2id = $facetofacegenerator->add_session([
            'facetoface' => $facetoface->id,
            'sessiondates' => [
                (object)[
                    'timestart' => $time - ($day / 24) * 38,
                    'timefinish' => $time - ($day / 24) * 36,
                    'sessiontimezone' => 'Pacific/Auckland',
                ]
            ]
        ]);
        $session2 = facetoface_get_session($session2id);

        // Session is progress
        $session3id = $facetofacegenerator->add_session([
            'facetoface' => $facetoface->id,
            'sessiondates' => [
                (object)[
                    'timestart' => $time - ($day / 24) * 36,
                    'timefinish' => $time + ($day / 24) * 36,
                    'sessiontimezone' => 'Pacific/Auckland',
                ]
            ]
        ]);
        $session3 = facetoface_get_session($session3id);

        // Session is waitlisted
        $session4id = $facetofacegenerator->add_session([
            'facetoface' => $facetoface->id,
            'datetimeknown' => '0'
        ]);
        $session4 = facetoface_get_session($session4id);


        // Sign the users up to the session 1.
        $sink = $this->redirectMessages();
        facetoface_user_signup($session1, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user1->id, false);
        facetoface_user_signup($session1, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user2->id, false);
        facetoface_user_signup($session1, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user3->id, false);
        facetoface_user_signup($session1, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user4->id, false);
        $sink->clear();
        // Confirm the signups for session 1.
        $this->assertCount(4, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(0, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Sign the users up to the session 2.
        $sink = $this->redirectMessages();
        facetoface_user_signup($session2, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user1->id, false);
        facetoface_user_signup($session2, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user2->id, false);
        facetoface_user_signup($session2, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user3->id, false);
        facetoface_user_signup($session2, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user4->id, false);
        $sink->clear();
        // Confirm the signups for session 2.
        $this->assertCount(4, facetoface_get_users_by_status($session2->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(0, facetoface_get_users_by_status($session2->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Sign the users up to the session 3.
        $sink = $this->redirectMessages();
        facetoface_user_signup($session3, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user1->id, false);
        facetoface_user_signup($session3, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user2->id, false);
        facetoface_user_signup($session3, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user3->id, false);
        facetoface_user_signup($session3, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_BOOKED, $user4->id, false);
        $sink->clear();
        // Confirm the signups for session 3.
        $this->assertCount(4, facetoface_get_users_by_status($session3->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(0, facetoface_get_users_by_status($session3->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Sign the users up to the session 4.
        $sink = $this->redirectMessages();
        facetoface_user_signup($session4, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_WAITLISTED, $user1->id, false);
        facetoface_user_signup($session4, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_WAITLISTED, $user2->id, false);
        facetoface_user_signup($session4, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_WAITLISTED, $user3->id, false);
        facetoface_user_signup($session4, $facetoface, $course1, 'discountcode1', MDL_F2F_TEXT, MDL_F2F_STATUS_WAITLISTED, $user4->id, false);
        $sink->clear();
        // Confirm the signups for session 4.
        $this->assertCount(4, facetoface_get_users_by_status($session4->id, MDL_F2F_STATUS_WAITLISTED));
        $this->assertCount(0, facetoface_get_users_by_status($session4->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Suspend user 3.
        $user3 = $DB->get_record('user', array('id' => $user3->id), '*', MUST_EXIST);
        $user3->suspended = 1;
        user_update_user($user3, false);

        // Run the cleanup task.
        $task = new \mod_facetoface\task\cleanup_task();
        $task->execute();

        // Session 1 is open for booking, we should now have updated statuses for session 1.
        $this->assertCount(3, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(1, facetoface_get_users_by_status($session1->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Session 2 is over, we should not have updated statuses for session 2.
        $this->assertCount(4, facetoface_get_users_by_status($session2->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(0, facetoface_get_users_by_status($session2->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Session 3 in progress, we should not have updated statuses for session 3.
        $this->assertCount(4, facetoface_get_users_by_status($session3->id, MDL_F2F_STATUS_BOOKED));
        $this->assertCount(0, facetoface_get_users_by_status($session3->id, MDL_F2F_STATUS_USER_CANCELLED));

        // Session 4 is waitlisted, we should now have updated statuses for session 4.
        $this->assertCount(3, facetoface_get_users_by_status($session4->id, MDL_F2F_STATUS_WAITLISTED));
        $this->assertCount(1, facetoface_get_users_by_status($session4->id, MDL_F2F_STATUS_USER_CANCELLED));

        $sink->close();

    }
}