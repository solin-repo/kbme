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
 * @author  Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package mod_facetoface
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Tests functions in mod/facetoface/db/upgradelib.php
 */
class mod_facetoface_upgradelib_testcase extends advanced_testcase {

    /**
     * Test the F2F customfield migration function that helps us move signup and cancellation data.
     * Here we test with the default setting.
     */
    public function test_mod_facetoface_migrate_session_signup_customdata_default() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/lib/upgradelib.php');
        require_once($CFG->dirroot.'/mod/facetoface/db/upgradelib.php');

        // We have to reset as we're going to be adding a config var when calling mod_facetoface_migrate_session_signup_customdata.
        $this->resetAfterTest();
        $this->preventResetByRollback();

        $dbman = $DB->get_manager();

        /** @var mod_facetoface_generator $facetofacegenerator */
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        /** @var totara_hierarchy_generator $hierarchygenerator */
        $hierarchygenerator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');

        // To test this method we are going to use 3 learners, 1 manager, and 1 course with a facetoface activity with
        // approval required turned on.
        // Each of the three users has the
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $manager1 = $this->getDataGenerator()->create_user();
        $hierarchygenerator->assign_primary_position($user1->id, $manager1->id, null, null);
        $hierarchygenerator->assign_primary_position($user2->id, $manager1->id, null, null);
        $hierarchygenerator->assign_primary_position($user3->id, $manager1->id, null, null);
        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', array('course' => $course->id, 'approvalreqd' => '1'));
        $sid = $facetofacegenerator->add_session(array('facetoface' => $facetoface->id, 'sessiondates' => array()));
        $session = facetoface_get_session($sid);
        $fieldid = $DB->get_field('facetoface_signup_info_field', 'id', array('shortname' => 'signupnote'));

        // Sign user 1 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        // Set signup note to "Elephants" for user 1.
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_signupnote = 'Elephants';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 2 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user2signup = facetoface_get_attendee($session->id, $user2->id);
        // Set signup note to "Monkeys" for user 2.
        $customdata = new stdClass;
        $customdata->id = $user2signup->submissionid;
        $customdata->customfield_signupnote = 'Monkeys';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 3 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user3->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user3signup = facetoface_get_attendee($session->id, $user3->id);
        // Set signup note to "Sharks" for user 3.
        $customdata = new stdClass;
        $customdata->id = $user3signup->submissionid;
        $customdata->customfield_signupnote = 'Sharks';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Check that all three users have been signed up and that they are in the requested state which is where we expect them.
        $this->assertSame(3, $DB->count_records('facetoface_signup_info_data'));
        $this->assertSame('Elephants', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user1signup->submissionid)));
        $this->assertSame('Monkeys', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user2signup->submissionid)));
        $this->assertSame('Sharks', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user3signup->submissionid)));
        $this->assertCount(3, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_REQUESTED));
        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_BOOKED));

        // Approve user 1 and user 3's signup requests.
        facetoface_approve_requests((object)array(
            's' => $sid,
            'action' => 'approvalrequired',
            'requests' => array(
                $user1->id => 2,
                $user3->id => 2
            )
        ));

        // Check that we now have two users in the waitlisted state and only 1 remaining in the requested state.
        $this->assertCount(1, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_REQUESTED));
        $this->assertCount(2, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_BOOKED));

        // Check that the user 1 has Elephants set as their signup note.
        $signupstatus = new stdClass();
        $signupstatus->id = $user1signup->submissionid;
        $user1data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user1data);
        $this->assertSame('Elephants', $user1data['signupnote']);

        // Check that the user 2 has Monkeys set as their signup note.
        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user2data);
        $this->assertSame('Monkeys', $user2data['signupnote']);

        // Check that the user 3 has Sharks set as their signup note.
        $signupstatus = new stdClass();
        $signupstatus->id = $user3signup->submissionid;
        $user3data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user3data);
        $this->assertSame('Sharks', $user3data['signupnote']);

        // OK all data is correct and as we expect, now revert it so that it appears as though we've got a history like
        // we did prior to 2.7.14 and 2.9.6.
        // Because the API has been changed to fix this issue we must replicate what we previously saw by directly inserting
        // data in the custom field data tables.
        $DB->delete_records('facetoface_signup_info_data');

        // Create signup note data for user 1 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they got signed up and then approved by their manager.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user1signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user1signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(3, $user1signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user1signupids)->id, 'fieldid' => $fieldid, 'data' => 'Elephants'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $fieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $fieldid, 'data' => ''));

        // Create signup note data for user 2 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they signed up but have not been approved.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user2signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user2signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(1, $user2signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user2signupids)->id, 'fieldid' => $fieldid, 'data' => 'Monkeys'));

        // Create signup note data for user 3 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they got signed up and then approved by their manager.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user3signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user3signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(3, $user3signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user3signupids)->id, 'fieldid' => $fieldid, 'data' => 'Sharks'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user3signupids)->id, 'fieldid' => $fieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user3signupids)->id, 'fieldid' => $fieldid, 'data' => ''));

        // Run the upgrade helper function and ensure
        mod_facetoface_migrate_session_signup_customdata($DB, $dbman, 'facetoface_signup_info_data', 'facetofacesignupid');

        // Confirm that now when I fetch using the signup->id I get the expected value "Elephants" for user 1.
        $signupstatus = new stdClass();
        $signupstatus->id = $user1signup->submissionid;
        $user1data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user1data);
        $this->assertSame('Elephants', $user1data['signupnote']);

        // Confirm that now when I fetch using the signup->id I get the expected value "Monkeys" for user 1.
        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user2data);
        $this->assertSame('Monkeys', $user2data['signupnote']);

        // Confirm that now when I fetch using the signup->id I get the expected value "Sharks" for user 1.
        $signupstatus = new stdClass();
        $signupstatus->id = $user3signup->submissionid;
        $user3data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user3data);
        $this->assertSame('Sharks', $user3data['signupnote']);

        // Check that there are no users.
        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_USER_CANCELLED));
        // Cancel user 2's signup request.
        facetoface_cancel_attendees($sid, array($user2->id));
        // Check we now have 1 user who is cancelled.
        $this->assertCount(1, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_USER_CANCELLED));

        // Signup user 2 again.
        $result = facetoface_user_import($course, $facetoface, $session, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user2signup = facetoface_get_attendee($session->id, $user2->id);
        // Save an empty signup note this time.
        $customdata = new stdClass;
        $customdata->id = $user2signup->submissionid;
        $customdata->customfield_signupnote = '';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');
        // Now that they have been signed up again we expect there to be no cancelled users.
        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_USER_CANCELLED));

        // Check we get an empty signup note.
        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertSame('', $user2data['signupnote']);

        // Now we want to fake this exact data situation prior to the status => signup migration.
        // We can't do this using the API's they've been fixed so we just cheat and insert directly into the database.
        $DB->delete_records('facetoface_signup_info_data');
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user2signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user2signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(3, $user2signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user2signupids)->id, 'fieldid' => $fieldid, 'data' => 'Fish'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user2signupids)->id, 'fieldid' => $fieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user2signupids)->id, 'fieldid' => $fieldid, 'data' => ''));

        mod_facetoface_migrate_session_signup_customdata($DB, $dbman, 'facetoface_signup_info_data', 'facetofacesignupid');

        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);

        $this->assertArrayHasKey('signupnote', $user2data);
        $this->assertSame('Fish', $user2data['signupnote']);
    }

    /**
     * Test the F2F customfield migration function that helps us move signup and cancellation data.
     * Here we test with the latest records setting.
     */
    public function test_mod_facetoface_migrate_session_signup_customdata_latest() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/lib/upgradelib.php');
        require_once($CFG->dirroot.'/mod/facetoface/db/upgradelib.php');

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $CFG->facetoface_customfield_migration_behaviour = 'latest';

        $dbman = $DB->get_manager();

        /** @var mod_facetoface_generator $facetofacegenerator */
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        /** @var totara_hierarchy_generator $hierarchygenerator */
        $hierarchygenerator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $manager1 = $this->getDataGenerator()->create_user();
        $hierarchygenerator->assign_primary_position($user1->id, $manager1->id, null, null);
        $hierarchygenerator->assign_primary_position($user2->id, $manager1->id, null, null);
        $hierarchygenerator->assign_primary_position($user3->id, $manager1->id, null, null);
        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', array('course' => $course->id, 'approvalreqd' => '1'));
        $sid = $facetofacegenerator->add_session(array('facetoface' => $facetoface->id, 'sessiondates' => array()));
        $session = facetoface_get_session($sid);
        $fieldid = $DB->get_field('facetoface_signup_info_field', 'id', array('shortname' => 'signupnote'));

        $result = facetoface_user_import($course, $facetoface, $session, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_signupnote = 'Elephants';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        $result = facetoface_user_import($course, $facetoface, $session, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user2signup = facetoface_get_attendee($session->id, $user2->id);
        $customdata = new stdClass;
        $customdata->id = $user2signup->submissionid;
        $customdata->customfield_signupnote = 'Monkeys';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        $result = facetoface_user_import($course, $facetoface, $session, $user3->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user3signup = facetoface_get_attendee($session->id, $user3->id);
        $customdata = new stdClass;
        $customdata->id = $user3signup->submissionid;
        $customdata->customfield_signupnote = 'Sharks';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        $this->assertSame(3, $DB->count_records('facetoface_signup_info_data'));

        $this->assertSame('Elephants', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user1signup->submissionid)));
        $this->assertSame('Monkeys', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user2signup->submissionid)));
        $this->assertSame('Sharks', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user3signup->submissionid)));

        $this->assertCount(3, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_REQUESTED));
        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_BOOKED));

        facetoface_approve_requests((object)array(
            's' => $sid,
            'action' => 'approvalrequired',
            'requests' => array(
                $user1->id => 2,
                $user3->id => 2
            )
        ));

        $this->assertCount(1, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_REQUESTED));
        $this->assertCount(2, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_BOOKED));

        $signupstatus = new stdClass();
        $signupstatus->id = $user1signup->submissionid;
        $user1data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user1data);
        $this->assertSame('Elephants', $user1data['signupnote']);

        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user2data);
        $this->assertSame('Monkeys', $user2data['signupnote']);

        $signupstatus = new stdClass();
        $signupstatus->id = $user3signup->submissionid;
        $user3data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user3data);
        $this->assertSame('Sharks', $user3data['signupnote']);

        // OK all data is correct and as we expect, now revert it so that it appears as though we've got a history like
        // we did prior to 2.7.14 and 2.9.6.

        $DB->delete_records('facetoface_signup_info_data');
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user1signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user1signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(3, $user1signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user1signupids)->id, 'fieldid' => $fieldid, 'data' => 'Elephants'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $fieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $fieldid, 'data' => ''));

        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user2signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user2signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(1, $user2signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user2signupids)->id, 'fieldid' => $fieldid, 'data' => 'Monkeys'));

        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user3signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user3signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(3, $user3signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user3signupids)->id, 'fieldid' => $fieldid, 'data' => 'Sharks'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user3signupids)->id, 'fieldid' => $fieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user3signupids)->id, 'fieldid' => $fieldid, 'data' => ''));

        // Statuscode 10 = MDL_F2F_STATUS_USER_CANCELLED.
        mod_facetoface_migrate_session_signup_customdata($DB, $dbman, 'facetoface_signup_info_data', 'facetofacesignupid');

        $signupstatus = new stdClass();
        $signupstatus->id = $user1signup->submissionid;
        $user1data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user1data);
        $this->assertSame('', $user1data['signupnote']); // Latest says Elephants is lost.

        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user2data);
        $this->assertSame('Monkeys', $user2data['signupnote']);

        $signupstatus = new stdClass();
        $signupstatus->id = $user3signup->submissionid;
        $user3data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user3data);
        $this->assertSame('', $user3data['signupnote']); // Latest says Sharks is lost.

        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_USER_CANCELLED));

        facetoface_cancel_attendees($sid, array($user2->id));

        $this->assertCount(1, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_USER_CANCELLED));

        $result = facetoface_user_import($course, $facetoface, $session, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user2signup = facetoface_get_attendee($session->id, $user2->id);
        $customdata = new stdClass;
        $customdata->id = $user2signup->submissionid;
        $customdata->customfield_signupnote = '';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_USER_CANCELLED));

        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user2data);
        $this->assertSame('', $user2data['signupnote']);

        // OK Regular cancellation works OK.
        $DB->delete_records('facetoface_signup_info_data');
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user2signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user2signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(3, $user2signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user2signupids)->id, 'fieldid' => $fieldid, 'data' => 'Fish'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user2signupids)->id, 'fieldid' => $fieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user2signupids)->id, 'fieldid' => $fieldid, 'data' => ''));

        mod_facetoface_migrate_session_signup_customdata($DB, $dbman, 'facetoface_signup_info_data', 'facetofacesignupid');

        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);

        $this->assertArrayHasKey('signupnote', $user2data);
        $this->assertSame('', $user2data['signupnote']); // Latest - Fish is lost.
    }

    /**
     * Test the F2F customfield migration function that helps us move signup and cancellation data.
     * Here we test cancellation notes.
     */
    public function test_mod_facetoface_migrate_session_cancellation_customdata_default() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/lib/upgradelib.php');
        require_once($CFG->dirroot.'/mod/facetoface/db/upgradelib.php');

        // We have to reset as we're going to be adding a config var when calling mod_facetoface_migrate_session_signup_customdata.
        $this->resetAfterTest();
        $this->preventResetByRollback();

        $dbman = $DB->get_manager();

        /** @var mod_facetoface_generator $facetofacegenerator */
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        /** @var totara_hierarchy_generator $hierarchygenerator */
        $hierarchygenerator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');

        // To test this method we are going to use 3 learners, 1 manager, and 1 course with a facetoface activity with
        // approval required turned on.
        // Each of the three users has the same manager, we don't use the manager, they are just there as its more accurate and
        // prevents debugging calls.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $manager1 = $this->getDataGenerator()->create_user();
        $hierarchygenerator->assign_primary_position($user1->id, $manager1->id, null, null);
        $hierarchygenerator->assign_primary_position($user2->id, $manager1->id, null, null);
        $hierarchygenerator->assign_primary_position($user3->id, $manager1->id, null, null);
        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', array('course' => $course->id, 'approvalreqd' => '1'));
        $sid = $facetofacegenerator->add_session(array('facetoface' => $facetoface->id, 'sessiondates' => array()));
        $session = facetoface_get_session($sid);
        $signupfieldid = $DB->get_field('facetoface_signup_info_field', 'id', array('shortname' => 'signupnote'));
        $cancellationfieldid = $DB->get_field('facetoface_cancellation_info_field', 'id', array('shortname' => 'cancellationnote'));

        // Sign user 1 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        // Set signup note to "Elephants" for user 1.
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_signupnote = 'Elephants';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 2 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user2signup = facetoface_get_attendee($session->id, $user2->id);
        // Set signup note to "Monkeys" for user 2.
        $customdata = new stdClass;
        $customdata->id = $user2signup->submissionid;
        $customdata->customfield_signupnote = 'Monkeys';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 3 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user3->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user3signup = facetoface_get_attendee($session->id, $user3->id);
        // Set signup note to "Sharks" for user 3.
        $customdata = new stdClass;
        $customdata->id = $user3signup->submissionid;
        $customdata->customfield_signupnote = 'Sharks';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Check that all three users have been signed up and that they are in the requested state which is where we expect them.
        $this->assertSame(3, $DB->count_records('facetoface_signup_info_data'));
        $this->assertSame('Elephants', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user1signup->submissionid)));
        $this->assertSame('Monkeys', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user2signup->submissionid)));
        $this->assertSame('Sharks', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user3signup->submissionid)));

        $this->assertCount(3, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_REQUESTED));
        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_BOOKED));

        // Approve all three users.
        facetoface_approve_requests((object)array(
            's' => $sid,
            'action' => 'approvalrequired',
            'requests' => array(
                $user1->id => 2,
                $user2->id => 2,
                $user3->id => 2
            )
        ));

        // Check that we now have two users in the waitlisted state and only 1 remaining in the requested state.
        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_REQUESTED));
        $this->assertCount(3, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_BOOKED));

        // Now cancel users 1 and 2, we'll give them customfield data after this.
        facetoface_cancel_attendees($sid, array($user1->id, $user2->id));

        // Set cancellation note to "Blue" for user 1.
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_cancellationnote = 'Blue';
        customfield_save_data($customdata, 'facetofacecancellation', 'facetoface_cancellation');

        // Set cancellation note to "Red" for user 2.
        $user2signup = facetoface_get_attendee($session->id, $user2->id);
        $customdata = new stdClass;
        $customdata->id = $user2signup->submissionid;
        $customdata->customfield_cancellationnote = 'Red';
        customfield_save_data($customdata, 'facetofacecancellation', 'facetoface_cancellation');

        // Get the cancellation note data for user 1 and check it is as we expect.
        $signupstatus = new stdClass();
        $signupstatus->id = $user1signup->submissionid;
        $user1data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertArrayHasKey('cancellationnote', $user1data);
        $this->assertSame('Blue', $user1data['cancellationnote']);

        // Get the cancellation note data for user 2 and check it is as we expect.
        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertArrayHasKey('cancellationnote', $user2data);
        $this->assertSame('Red', $user2data['cancellationnote']);

        // Now we are going to sign user 1 up again, and cancel them again.
        // Sign user 1 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_signupnote = 'Rockets';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');
        facetoface_approve_requests((object)array(
            's' => $sid,
            'action' => 'approvalrequired',
            'requests' => array(
                $user1->id => 2,
            )
        ));
        facetoface_cancel_attendees($sid, array($user1->id));
        // Set cancellation note to "Blue" for user 1.
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_cancellationnote = '';
        customfield_save_data($customdata, 'facetofacecancellation', 'facetoface_cancellation');

        // OK all data is correct and as we expect, now revert it so that it appears as though we've got a history like
        // we did prior to 2.7.14 and 2.9.6.
        // Because the API has been changed to fix this issue we must replicate what we previously saw by directly inserting
        // data in the custom field data tables.
        $DB->delete_records('facetoface_signup_info_data');

        // Create signup note data for user 1 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they got signed up and then approved by their manager.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user1signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user1signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(8, $user1signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => 'Elephants'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_cancellation_info_data', array('facetofacecancellationid' => next($user1signupids)->id, 'fieldid' => $cancellationfieldid, 'data' => 'Blue'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => 'Rockets'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_cancellation_info_data', array('facetofacecancellationid' => next($user1signupids)->id, 'fieldid' => $cancellationfieldid, 'data' => ''));

        // Create signup note data for user 2 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they signed up but have not been approved.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user2signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user2signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(4, $user2signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user2signupids)->id, 'fieldid' => $signupfieldid, 'data' => 'Monkeys'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user2signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user2signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_cancellation_info_data', array('facetofacecancellationid' => next($user2signupids)->id, 'fieldid' => $cancellationfieldid, 'data' => 'Red'));

        // Create signup note data for user 3 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they got signed up and then approved by their manager.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user3signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user3signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(3, $user3signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user3signupids)->id, 'fieldid' => $signupfieldid, 'data' => 'Sharks'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user3signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user3signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));

        // Run the upgrade helper function and ensure that we get the data we expect.
        mod_facetoface_migrate_session_signup_customdata($DB, $dbman, 'facetoface_signup_info_data', 'facetofacesignupid');
        mod_facetoface_migrate_session_signup_customdata($DB, $dbman, 'facetoface_cancellation_info_data', 'facetofacecancellationid');

        // Now check that we have the signup and cancellation notes still in tack.
        // User 1 should have Elephants as signup and Blue as cancellation.
        $signupstatus = new stdClass();
        $signupstatus->id = $user1signup->submissionid;
        $user1data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertArrayHasKey('signupnote', $user1data);
        $this->assertArrayNotHasKey('cancellationnote', $user1data);
        $this->assertSame('Rockets', $user1data['signupnote']);
        $user1data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertArrayHasKey('cancellationnote', $user1data);
        $this->assertArrayNotHasKey('signupnote', $user1data);
        $this->assertSame('Blue', $user1data['cancellationnote']);

        // User 2 should have Monkeys as signup and Red as cancellation.
        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertSame('Monkeys', $user2data['signupnote']);
        $user1data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertSame('Red', $user1data['cancellationnote']);
        $this->assertArrayNotHasKey('signupnote', $user1data);

        // User 1 should have Sharks as signup and should not have a cancellation note.
        $signupstatus = new stdClass();
        $signupstatus->id = $user3signup->submissionid;
        $user3data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertSame('Sharks', $user3data['signupnote']);
        $user1data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertArrayNotHasKey('cancellationnote', $user1data);
        $this->assertArrayNotHasKey('signupnote', $user1data);
    }

    /**
     * Test the F2F customfield migration function that helps us move signup and cancellation data.
     * Here we test cancellation notes.
     */
    public function test_mod_facetoface_migrate_session_cancellation_customdata_latest() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/lib/upgradelib.php');
        require_once($CFG->dirroot.'/mod/facetoface/db/upgradelib.php');

        // We have to reset as we're going to be adding a config var when calling mod_facetoface_migrate_session_signup_customdata.
        $this->resetAfterTest();
        $this->preventResetByRollback();

        $dbman = $DB->get_manager();

        $CFG->facetoface_customfield_migration_behaviour = 'latest';

        /** @var mod_facetoface_generator $facetofacegenerator */
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        /** @var totara_hierarchy_generator $hierarchygenerator */
        $hierarchygenerator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');

        // To test this method we are going to use 3 learners, 1 manager, and 1 course with a facetoface activity with
        // approval required turned on.
        // Each of the three users has the same manager, we don't use the manager, they are just there as its more accurate and
        // prevents debugging calls.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $manager1 = $this->getDataGenerator()->create_user();
        $hierarchygenerator->assign_primary_position($user1->id, $manager1->id, null, null);
        $hierarchygenerator->assign_primary_position($user2->id, $manager1->id, null, null);
        $hierarchygenerator->assign_primary_position($user3->id, $manager1->id, null, null);
        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', array('course' => $course->id, 'approvalreqd' => '1'));
        $sid = $facetofacegenerator->add_session(array('facetoface' => $facetoface->id, 'sessiondates' => array()));
        $session = facetoface_get_session($sid);
        $signupfieldid = $DB->get_field('facetoface_signup_info_field', 'id', array('shortname' => 'signupnote'));
        $cancellationfieldid = $DB->get_field('facetoface_cancellation_info_field', 'id', array('shortname' => 'cancellationnote'));

        // Sign user 1 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        // Set signup note to "Elephants" for user 1.
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_signupnote = 'Elephants';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 2 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user2signup = facetoface_get_attendee($session->id, $user2->id);
        // Set signup note to "Monkeys" for user 2.
        $customdata = new stdClass;
        $customdata->id = $user2signup->submissionid;
        $customdata->customfield_signupnote = 'Monkeys';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 3 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user3->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user3signup = facetoface_get_attendee($session->id, $user3->id);
        // Set signup note to "Sharks" for user 3.
        $customdata = new stdClass;
        $customdata->id = $user3signup->submissionid;
        $customdata->customfield_signupnote = 'Sharks';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Check that all three users have been signed up and that they are in the requested state which is where we expect them.
        $this->assertSame(3, $DB->count_records('facetoface_signup_info_data'));
        $this->assertSame('Elephants', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user1signup->submissionid)));
        $this->assertSame('Monkeys', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user2signup->submissionid)));
        $this->assertSame('Sharks', $DB->get_field('facetoface_signup_info_data', 'data', array('facetofacesignupid' => $user3signup->submissionid)));

        $this->assertCount(3, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_REQUESTED));
        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_BOOKED));

        // Approve all three users.
        facetoface_approve_requests((object)array(
            's' => $sid,
            'action' => 'approvalrequired',
            'requests' => array(
                $user1->id => 2,
                $user2->id => 2,
                $user3->id => 2
            )
        ));

        // Check that we now have two users in the waitlisted state and only 1 remaining in the requested state.
        $this->assertCount(0, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_REQUESTED));
        $this->assertCount(3, facetoface_get_users_by_status($sid, MDL_F2F_STATUS_BOOKED));

        // Now cancel users 1 and 2, we'll give them customfield data after this.
        facetoface_cancel_attendees($sid, array($user1->id, $user2->id));

        // Set cancellation note to "Blue" for user 1.
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_cancellationnote = 'Blue';
        customfield_save_data($customdata, 'facetofacecancellation', 'facetoface_cancellation');

        // Set cancellation note to "Red" for user 2.
        $user2signup = facetoface_get_attendee($session->id, $user2->id);
        $customdata = new stdClass;
        $customdata->id = $user2signup->submissionid;
        $customdata->customfield_cancellationnote = 'Red';
        customfield_save_data($customdata, 'facetofacecancellation', 'facetoface_cancellation');

        // Get the cancellation note data for user 1 and check it is as we expect.
        $signupstatus = new stdClass();
        $signupstatus->id = $user1signup->submissionid;
        $user1data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertArrayHasKey('cancellationnote', $user1data);
        $this->assertSame('Blue', $user1data['cancellationnote']);

        // Get the cancellation note data for user 2 and check it is as we expect.
        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertArrayHasKey('cancellationnote', $user2data);
        $this->assertSame('Red', $user2data['cancellationnote']);

        // Now we are going to sign user 1 up again, and cancel them again.
        // Sign user 1 up to the facetoface activity.
        $result = facetoface_user_import($course, $facetoface, $session, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_signupnote = 'Rockets';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');
        facetoface_approve_requests((object)array(
            's' => $sid,
            'action' => 'approvalrequired',
            'requests' => array(
                $user1->id => 2,
            )
        ));
        facetoface_cancel_attendees($sid, array($user1->id));
        // Set cancellation note to "Blue" for user 1.
        $user1signup = facetoface_get_attendee($session->id, $user1->id);
        $customdata = new stdClass;
        $customdata->id = $user1signup->submissionid;
        $customdata->customfield_cancellationnote = '';
        customfield_save_data($customdata, 'facetofacecancellation', 'facetoface_cancellation');

        // OK all data is correct and as we expect, now revert it so that it appears as though we've got a history like
        // we did prior to 2.7.14 and 2.9.6.
        // Because the API has been changed to fix this issue we must replicate what we previously saw by directly inserting
        // data in the custom field data tables.
        $DB->delete_records('facetoface_signup_info_data');

        // Create signup note data for user 1 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they got signed up and then approved by their manager.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user1signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user1signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(8, $user1signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => 'Elephants'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_cancellation_info_data', array('facetofacecancellationid' => next($user1signupids)->id, 'fieldid' => $cancellationfieldid, 'data' => 'Blue'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => 'Rockets'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user1signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_cancellation_info_data', array('facetofacecancellationid' => next($user1signupids)->id, 'fieldid' => $cancellationfieldid, 'data' => ''));

        // Create signup note data for user 2 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they signed up but have not been approved.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user2signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user2signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(4, $user2signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user2signupids)->id, 'fieldid' => $signupfieldid, 'data' => 'Monkeys'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user2signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user2signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_cancellation_info_data', array('facetofacecancellationid' => next($user2signupids)->id, 'fieldid' => $cancellationfieldid, 'data' => 'Red'));

        // Create signup note data for user 3 against their signup status record. We need to check it gets correctly reattached to
        // their signup record.
        // Like before we expect that they got signed up and then approved by their manager.
        // Please note ordering by ID is not correct, but as unit tests act so fast we can't tell by any other means what order things happened.
        $user3signupids = $DB->get_records('facetoface_signups_status', array('signupid' => $user3signup->submissionid), 'id ASC, superceded DESC, timecreated DESC');
        $this->assertCount(3, $user3signupids);
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => reset($user3signupids)->id, 'fieldid' => $signupfieldid, 'data' => 'Sharks'));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user3signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));
        $DB->insert_record('facetoface_signup_info_data', array('facetofacesignupid' => next($user3signupids)->id, 'fieldid' => $signupfieldid, 'data' => ''));

        // Run the upgrade helper function and ensure that we get the data we expect.
        mod_facetoface_migrate_session_signup_customdata($DB, $dbman, 'facetoface_signup_info_data', 'facetofacesignupid');
        mod_facetoface_migrate_session_signup_customdata($DB, $dbman, 'facetoface_cancellation_info_data', 'facetofacecancellationid');

        // Now check that we have the signup and cancellation notes still in tack.
        // User 1 should have Elephants as signup and Blue as cancellation.
        $signupstatus = new stdClass();
        $signupstatus->id = $user1signup->submissionid;
        $user1data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertSame('', $user1data['signupnote']);
        $this->assertArrayNotHasKey('cancellationnote', $user1data);
        $user1data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertSame('', $user1data['cancellationnote']);
        $this->assertArrayNotHasKey('signupnote', $user1data);

        // User 2 should have Monkeys as signup and Red as cancellation.
        $signupstatus = new stdClass();
        $signupstatus->id = $user2signup->submissionid;
        $user2data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertSame('', $user2data['signupnote']);
        $user1data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertSame('Red', $user1data['cancellationnote']);
        $this->assertArrayNotHasKey('signupnote', $user1data);

        // User 1 should have Sharks as signup and should not have a cancellation note.
        $signupstatus = new stdClass();
        $signupstatus->id = $user3signup->submissionid;
        $user3data = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);
        $this->assertSame('', $user3data['signupnote']);
        $user1data = customfield_get_data($signupstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
        $this->assertArrayNotHasKey('cancellationnote', $user1data);
        $this->assertArrayNotHasKey('signupnote', $user1data);
    }

    public function test_mod_facetoface_delete_orphaned_customfield_data_signup() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/lib/upgradelib.php');
        require_once($CFG->dirroot.'/mod/facetoface/db/upgradelib.php');

        // We have to reset as we're going to be adding a config var when calling mod_facetoface_migrate_session_signup_customdata.
        $this->resetAfterTest();
        $this->preventResetByRollback();

        // Get some generators, and generate some data.
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $hierarchygenerator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');

        $time = time();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course();

        $f2f1 = $this->getDataGenerator()->create_module('facetoface', array('course' => $course->id, 'approvaloptions' => 'approval_none'));
        $sess1date = new stdClass();
        $sess1date->timestart = $time + (DAYSECS * 1);
        $sess1date->timefinish = $time + (DAYSECS * 2);
        $sess1date->sessiontimezone = 'Pacific/Auckland';
        $sess1dates = array($sess1date);
        $s1id = $facetofacegenerator->add_session(array('facetoface' => $f2f1->id, 'sessiondates' => $sess1dates));
        $session1 = facetoface_get_session($s1id);

        $f2f2 = $this->getDataGenerator()->create_module('facetoface', array('course' => $course->id, 'approvaloptions' => 'approval_none'));
        $sess2date = new stdClass();
        $sess2date->timestart = $time + (DAYSECS * 3);
        $sess2date->timefinish = $time + (DAYSECS * 4);
        $sess2date->sessiontimezone = 'Pacific/Auckland';
        $sess2dates = array($sess2date);
        $s2id = $facetofacegenerator->add_session(array('facetoface' => $f2f2->id, 'sessiondates' => $sess2dates));
        $session2 = facetoface_get_session($s2id);

        $signupfieldid = $DB->get_field('facetoface_signup_info_field', 'id', array('shortname' => 'signupnote'));
        $cancellationfieldid = $DB->get_field('facetoface_cancellation_info_field', 'id', array('shortname' => 'cancellationnote'));

        // Sign user 1 up to session 1.
        $result = facetoface_user_import($course, $f2f1, $session1, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $signup_u1s1 = facetoface_get_attendee($session1->id, $user1->id);
        // Now set up the custom data.
        $customdata = new stdClass;
        $customdata->id = $signup_u1s1->submissionid;
        $customdata->customfield_signupnote = 'Apples';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 2 up to session 1.
        $result = facetoface_user_import($course, $f2f1, $session1, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $signup_u2s1 = facetoface_get_attendee($session1->id, $user2->id);
        // Now set up the custom data.
        $customdata = new stdClass;
        $customdata->id = $signup_u2s1->submissionid;
        $customdata->customfield_signupnote = 'Oranges';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 1 up to session 2.
        $result = facetoface_user_import($course, $f2f2, $session2, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $signup_u1s2 = facetoface_get_attendee($session2->id, $user1->id);
        // Now set up the custom data.
        $customdata = new stdClass;
        $customdata->id = $signup_u1s2->submissionid;
        $customdata->customfield_signupnote = 'Mangoes';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 2 up to session 2.
        $result = facetoface_user_import($course, $f2f2, $session2, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $signup_u2s2 = facetoface_get_attendee($session2->id, $user2->id);
        // Now set up the custom data.
        $customdata = new stdClass;
        $customdata->id = $signup_u2s2->submissionid;
        $customdata->customfield_signupnote = 'Bananas';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Now manually delete the signups for s1u1 and s2u2 to orphan the custom fields.
        $DB->delete_records('facetoface_signups', array('userid' => $user1->id, 'sessionid' => $session1->id));
        $DB->delete_records('facetoface_signups', array('userid' => $user2->id, 'sessionid' => $session2->id));

        // Now do some pre-checks for sanity/comparison.
        $this->assertEquals(4, $DB->count_records('facetoface_signup_info_data'));
        $this->assertNotEmpty($DB->get_records('facetoface_signup_info_data', array('facetofacesignupid' => $signup_u1s1->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_signup_info_data', array('facetofacesignupid' => $signup_u2s1->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_signup_info_data', array('facetofacesignupid' => $signup_u1s2->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_signup_info_data', array('facetofacesignupid' => $signup_u2s2->submissionid)));

        mod_facetoface_delete_orphaned_customfield_data('signup');

        $this->assertEquals(2, $DB->count_records('facetoface_signup_info_data'));

        $this->assertEmpty($DB->get_records('facetoface_signup_info_data', array('facetofacesignupid' => $signup_u1s1->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_signup_info_data', array('facetofacesignupid' => $signup_u2s1->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_signup_info_data', array('facetofacesignupid' => $signup_u1s2->submissionid)));
        $this->assertEmpty($DB->get_records('facetoface_signup_info_data', array('facetofacesignupid' => $signup_u2s2->submissionid)));
    }

    public function test_mod_facetoface_delete_orphaned_customfield_data_cancellation() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/lib/upgradelib.php');
        require_once($CFG->dirroot.'/mod/facetoface/db/upgradelib.php');

        // We have to reset as we're going to be adding a config var when calling mod_facetoface_migrate_session_signup_customdata.
        $this->resetAfterTest();
        $this->preventResetByRollback();

        // Get some generators, and generate some data.
        $facetofacegenerator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $hierarchygenerator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');

        $time = time();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course();

        $f2f1 = $this->getDataGenerator()->create_module('facetoface', array('course' => $course->id, 'approvaloptions' => 'approval_none'));
        $sess1date = new stdClass();
        $sess1date->timestart = $time + (DAYSECS * 1);
        $sess1date->timefinish = $time + (DAYSECS * 2);
        $sess1date->sessiontimezone = 'Pacific/Auckland';
        $sess1dates = array($sess1date);
        $s1id = $facetofacegenerator->add_session(array('facetoface' => $f2f1->id, 'sessiondates' => $sess1dates));
        $session1 = facetoface_get_session($s1id);

        $f2f2 = $this->getDataGenerator()->create_module('facetoface', array('course' => $course->id, 'approvaloptions' => 'approval_none'));
        $sess2date = new stdClass();
        $sess2date->timestart = $time + (DAYSECS * 3);
        $sess2date->timefinish = $time + (DAYSECS * 4);
        $sess2date->sessiontimezone = 'Pacific/Auckland';
        $sess2dates = array($sess2date);
        $s2id = $facetofacegenerator->add_session(array('facetoface' => $f2f2->id, 'sessiondates' => $sess2dates));
        $session2 = facetoface_get_session($s2id);

        $signupfieldid = $DB->get_field('facetoface_signup_info_field', 'id', array('shortname' => 'signupnote'));
        $cancellationfieldid = $DB->get_field('facetoface_cancellation_info_field', 'id', array('shortname' => 'cancellationnote'));

        // Sign user 1 up to session 1.
        $result = facetoface_user_import($course, $f2f1, $session1, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $signup_u1s1 = facetoface_get_attendee($session1->id, $user1->id);
        // Now set up the custom data - to make sure it isn't affected.
        $customdata = new stdClass;
        $customdata->id = $signup_u1s1->submissionid;
        $customdata->customfield_signupnote = 'Apples';
        customfield_save_data($customdata, 'facetofacesignup', 'facetoface_signup');

        // Sign user 2 up to session 1.
        $result = facetoface_user_import($course, $f2f1, $session1, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $signup_u2s1 = facetoface_get_attendee($session1->id, $user2->id);

        // Sign user 1 up to session 2.
        $result = facetoface_user_import($course, $f2f2, $session2, $user1->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $signup_u1s2 = facetoface_get_attendee($session2->id, $user1->id);

        // Sign user 2 up to session 2.
        $result = facetoface_user_import($course, $f2f2, $session2, $user2->id, array(
            'discountcode' => '',
            'notificationtype' => MDL_F2F_NONE,
        ));
        $this->assertTrue($result['result']);
        $signup_u2s2 = facetoface_get_attendee($session2->id, $user2->id);

        // Cancel all the users and set up some cancellation custom fields.
        $error = null;
        facetoface_user_cancel($session1, $user1->id, true, $error, 'Rabbits');
        facetoface_user_cancel($session1, $user2->id, true, $error, 'Kittens');
        facetoface_user_cancel($session2, $user1->id, true, $error, 'Puppies');
        facetoface_user_cancel($session2, $user2->id, true, $error, 'Squirrels');

        // Now manually delete the signups for s1u1 and s2u2 to orphan the custom fields.
        $DB->delete_records('facetoface_signups', array('userid' => $user1->id, 'sessionid' => $session1->id));
        $DB->delete_records('facetoface_signups', array('userid' => $user2->id, 'sessionid' => $session2->id));

        // Now do some pre-checks for sanity/comparison.
        $this->assertEquals(4, $DB->count_records('facetoface_cancellation_info_data'));
        $this->assertEquals(1, $DB->count_records('facetoface_signup_info_data'));
        $this->assertNotEmpty($DB->get_records('facetoface_cancellation_info_data', array('facetofacecancellationid' => $signup_u1s1->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_cancellation_info_data', array('facetofacecancellationid' => $signup_u2s1->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_cancellation_info_data', array('facetofacecancellationid' => $signup_u1s2->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_cancellation_info_data', array('facetofacecancellationid' => $signup_u2s2->submissionid)));

        mod_facetoface_delete_orphaned_customfield_data('cancellation');

        $this->assertEquals(2, $DB->count_records('facetoface_cancellation_info_data'));
        $this->assertEquals(1, $DB->count_records('facetoface_signup_info_data'));

        $this->assertEmpty($DB->get_records('facetoface_cancellation_info_data', array('facetofacecancellationid' => $signup_u1s1->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_cancellation_info_data', array('facetofacecancellationid' => $signup_u2s1->submissionid)));
        $this->assertNotEmpty($DB->get_records('facetoface_cancellation_info_data', array('facetofacecancellationid' => $signup_u1s2->submissionid)));
        $this->assertEmpty($DB->get_records('facetoface_cancellation_info_data', array('facetofacecancellationid' => $signup_u2s2->submissionid)));
    }
}
