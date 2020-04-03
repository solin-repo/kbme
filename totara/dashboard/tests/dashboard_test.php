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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara_dashboard
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/totara/dashboard/lib.php');
require_once($CFG->libdir . '/testing/generator/lib.php');

/**
 * Test totara dashboard
 *
 * To test, run this from the command line from the $CFG->dirroot
 * vendor/bin/phpunit totara_dashboard_testcase
 *
 */
class totara_dashboard_testcase extends advanced_testcase {
    /**
     * Test creation of dashboard
     */
    public function test_dashboard_create() {
        global $DB;
        $this->resetAfterTest(true);

        $cohorts_gen = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $cohorts = array($cohorts_gen->create_cohort()->id, $cohorts_gen->create_cohort()->id, $cohorts_gen->create_cohort()->id);
        $dashboard = new totara_dashboard();
        $data = array(
            'name' => 'Test',
            'locked' => 1,
            'published' => 1,
            'cohorts' => $cohorts
        );

        $dashboard->set_from_form((object)$data)->save();
        $id = $dashboard->get_id();
        unset($dashboard);
        $check = new totara_dashboard($id);
        $this->assertEquals('Test', $check->name);
        $this->assertEquals($id, $check->get_id());
        $this->assertTrue($check->is_locked());
        $this->assertTrue($check->is_published());
        foreach ($cohorts as $cohort) {
            $this->assertContains($cohort, $check->get_cohorts());
        }

        // Check that navigation block created.
        $count = $DB->count_records('block_instances',
                array('pagetypepattern' => 'totara-dashboard-' . $id, 'blockname' => 'totara_dashboard'));
        $this->assertEquals(1, $count);
    }

    /**
     * Test dashboard update
     */
    public function test_dashboard_edit() {
        $this->resetAfterTest(true);

        $cohorts_gen = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $cohorts = array($cohorts_gen->create_cohort()->id, $cohorts_gen->create_cohort()->id, $cohorts_gen->create_cohort()->id);

        $data = array(
            'name' => 'Test',
            'locked' => 1,
            'published' => 1,
            'cohorts' => $cohorts
        );
        $dashboard = $this->getDataGenerator()->get_plugin_generator('totara_dashboard')->create_dashboard($data);
        $dashboard->name = 'Edited';
        $dashboard->unlock();
        $dashboard->unpublish();
        $dashboard->save();
        $id = $dashboard->get_id();
        unset($dashboard);

        $check = new totara_dashboard($id);
        $this->assertEquals('Edited', $check->name);
        $this->assertEquals($id, $check->get_id());
        $this->assertFalse($check->is_locked());
        $this->assertFalse($check->is_published());
        foreach ($cohorts as $cohort) {
            $this->assertContains($cohort, $check->get_cohorts());
        }
    }

    /**
     * Get manage list test
     * Make list of published, unpublised, locked, unlocked dashboards with and without cohorts.
     * Check that they all received
     */
    public function test_manage_list() {
        $this->resetAfterTest(true);

        $cohorts_gen = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $cohorts = array($cohorts_gen->create_cohort()->id, $cohorts_gen->create_cohort()->id, $cohorts_gen->create_cohort()->id);
        $dashboard_gen = $this->getDataGenerator()->get_plugin_generator('totara_dashboard');
        $dashboard_gen->create_dashboard(array('name' => 't1', 'locked' => 0, 'published' => 0, 'cohorts' => $cohorts));
        $dashboard_gen->create_dashboard(array('name' => 't2', 'locked' => 0, 'published' => 1, 'cohorts' => array()));
        $dashboard_gen->create_dashboard(array('name' => 't3', 'locked' => 1, 'published' => 0, 'cohorts' => $cohorts));
        $dashboard_gen->create_dashboard(array('name' => 't4', 'locked' => 1, 'published' => 1, 'cohorts' => array()));

        $list = totara_dashboard::get_manage_list();
        $this->assertCount(4, $list);
        $all = array_flip(array('t1', 't2', 't3', 't4'));
        // Check that every item appears only once.
        foreach ($list as $item) {
            $this->assertArrayHasKey($item->name, $all);
            unset($all[$item->name]);
        }
    }

    /**
     * User copy of dashboard test
     */
    public function test_user_copy() {
        global $DB;
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $cohorts_gen = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $cohort = $cohorts_gen->create_cohort()->id;
        $cohorts_gen->cohort_assign_users($cohort, array($user->id));

        $dashboard_gen = $this->getDataGenerator()->get_plugin_generator('totara_dashboard');
        $dashboard = $dashboard_gen->create_dashboard(array('cohorts' => array($cohort)));
        $dashboard->user_copy($user->id);

        // Check that user dashboard record added.
        $pageid = $dashboard->get_user_pageid($user->id);
        $this->assertGreaterThan(0, $pageid);

        // Check that instance of totara_dashboard block is created for user.
        $count = $DB->count_records('block_instances',
                array('pagetypepattern' => 'totara-dashboard-' . $dashboard->get_id(),
                      'blockname' => 'totara_dashboard',
                      'subpagepattern' => $pageid));
        $this->assertEquals(1, $count);
    }

    /**
     * Get user dashboards test
     *
     */
    public function test_get_user_dashboards() {
        $this->resetAfterTest(true);

        // Create 3 users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $cohorts_gen = $this->getDataGenerator()->get_plugin_generator('totara_cohort');

        // Create three audiences.
        // First with only user1.
        $cohort1 = $cohorts_gen->create_cohort()->id;
        $cohorts_gen->cohort_assign_users($cohort1, array($user1->id));

        // Second with user2 and user3.
        $cohort2 = $cohorts_gen->create_cohort()->id;
        $cohorts_gen->cohort_assign_users($cohort2, array($user2->id, $user3->id));

        // Third with only user3.
        $cohort3 = $cohorts_gen->create_cohort()->id;
        $cohorts_gen->cohort_assign_users($cohort3, array($user3->id));

        $dashboard_gen = $this->getDataGenerator()->get_plugin_generator('totara_dashboard');

        // First dashboard has only audience 2.
        $dashboard1 = $dashboard_gen->create_dashboard(array('cohorts' => array($cohort3)));
        // Second dashboard has audience 2 and 3.
        $dashboard2 = $dashboard_gen->create_dashboard(array('cohorts' => array($cohort2, $cohort3)));

        // Check that user is user1 not assigned to dashboard.
        $this->assertEmpty(totara_dashboard::get_user_dashboards($user1->id));

        // Check that user2 assigned to 1 dashboards.
        $user2dashes = totara_dashboard::get_user_dashboards($user2->id);
        $this->assertCount(1, $user2dashes);
        $this->assertEquals($dashboard2->get_id(), array_shift($user2dashes)->id);

        // Check that user2 assigned to 2 dashboards.
        $user3dashes = totara_dashboard::get_user_dashboards($user3->id);
        $this->assertCount(2, $user3dashes);
        $user3dashesids = array(array_shift($user3dashes)->id, array_shift($user3dashes)->id);
        $this->assertContains($dashboard1->get_id(), $user3dashesids);
        $this->assertContains($dashboard2->get_id(), $user3dashesids);
        $this->assertNotEquals($user3dashesids[0], $user3dashesids[1]);
    }

    /**
     * Reset user settings test
     */
    public function test_user_reset() {
        global $DB;
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $cohorts_gen = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $cohort = $cohorts_gen->create_cohort()->id;
        $cohorts_gen->cohort_assign_users($cohort, array($user->id));

        $dashboard_gen = $this->getDataGenerator()->get_plugin_generator('totara_dashboard');
        $dashboard = $dashboard_gen->create_dashboard(array('cohorts' => array($cohort)));
        $dashboard->user_copy($user->id);
        $pageid = $dashboard->get_user_pageid($user->id);

        // Reset.
        $dashboard->user_reset($user->id);

         // Check that user dashboard record removed.
        $this->assertEquals(0, $dashboard->get_user_pageid($user->id));

        // Check that instance of totara_dashboard block is deleted for user.
        $count = $DB->count_records('block_instances',
                array('pagetypepattern' => 'totara-dashboard-' . $dashboard->get_id(),
                      'blockname' => 'totara_dashboard',
                      'subpagepattern' => $pageid));
        $this->assertEquals(0, $count);
    }

    /**
     * Sorting test
     */
    public function test_sorting() {
        global $DB;
        $this->resetAfterTest(true);
        $DB->delete_records('totara_dashboard');

        $dashboard_gen = $this->getDataGenerator()->get_plugin_generator('totara_dashboard');
        $dashboard1 = $dashboard_gen->create_dashboard();
        $dashboard2 = $dashboard_gen->create_dashboard();
        $dashboard3 = $dashboard_gen->create_dashboard();

        // Check initial sortorder.
        $order1 = $DB->get_records('totara_dashboard', array(), 'sortorder');
        $this->assertEquals($dashboard1->get_id(), array_shift($order1)->id);
        $this->assertEquals($dashboard2->get_id(), array_shift($order1)->id);
        $this->assertEquals($dashboard3->get_id(), array_shift($order1)->id);

        // Move down 1 => 213.
        $dashboard1->move_down();
        $order2 = $DB->get_records('totara_dashboard', array(), 'sortorder');
        $this->assertEquals($dashboard2->get_id(), array_shift($order2)->id);
        $this->assertEquals($dashboard1->get_id(), array_shift($order2)->id);
        $this->assertEquals($dashboard3->get_id(), array_shift($order2)->id);

        // Move up 3 => 231.
        $dashboard3 = new totara_dashboard($dashboard3->get_id());
        $dashboard3->move_up();
        $order3 = $DB->get_records('totara_dashboard', array(), 'sortorder');
        $this->assertEquals($dashboard2->get_id(), array_shift($order3)->id);
        $this->assertEquals($dashboard3->get_id(), array_shift($order3)->id);
        $this->assertEquals($dashboard1->get_id(), array_shift($order3)->id);

        // Move up 3 => 321.
        $dashboard3 = new totara_dashboard($dashboard3->get_id());
        $dashboard3->move_up();
        $order4 = $DB->get_records('totara_dashboard', array(), 'sortorder');
        $this->assertEquals($dashboard3->get_id(), array_shift($order4)->id);
        $this->assertEquals($dashboard2->get_id(), array_shift($order4)->id);
        $this->assertEquals($dashboard1->get_id(), array_shift($order4)->id);

        // Move down 2 => 312.
        $dashboard2 = new totara_dashboard($dashboard2->get_id());
        $dashboard2->move_down();
        $order5 = $DB->get_records('totara_dashboard', array(), 'sortorder');
        $this->assertEquals($dashboard3->get_id(), array_shift($order5)->id);
        $this->assertEquals($dashboard1->get_id(), array_shift($order5)->id);
        $this->assertEquals($dashboard2->get_id(), array_shift($order5)->id);

    }

    /**
     * Test deleting of dashboard
     */
    public function test_delete() {
        global $DB;
        $this->resetAfterTest(true);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $cohorts_gen = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $cohort1 = $cohorts_gen->create_cohort()->id;
        $cohorts_gen->cohort_assign_users($cohort1, array($user1->id));

        $cohort2 = $cohorts_gen->create_cohort()->id;
        $cohorts_gen->cohort_assign_users($cohort2, array($user2->id));

        $dashboard_gen = $this->getDataGenerator()->get_plugin_generator('totara_dashboard');
        $dashboard1 = $dashboard_gen->create_dashboard(array('cohorts' => array($cohort1)));
        $dashboard2 = $dashboard_gen->create_dashboard(array('cohorts' => array($cohort1, $cohort2)));
        $dashboard3 = $dashboard_gen->create_dashboard(array('cohorts' => array($cohort2)));

        $dashboard2->user_copy($user2->id);
        $dashboard3->user_copy($user2->id);

        $dashboard1id = $dashboard1->get_id();
        $dashboard2id = $dashboard2->get_id();
        $dashboard3id = $dashboard3->get_id();

        $blockhtml1 = $dashboard_gen->add_block($dashboard2id, 'html', 1);
        $blockhtml2 = $dashboard_gen->add_block($dashboard2id, 'html', 2);
        $blockhtml3 = $dashboard_gen->add_block($dashboard2id, 'html', 3);
        $count2 = $DB->count_records('block_instances', array('pagetypepattern' => 'totara-dashboard-' . $dashboard2id));
        $this->assertEquals(5, $count2);

        $dashboard2->delete();
        unset($dashboard2);

        // Check order.
        $order = $DB->get_records('totara_dashboard', array(), 'sortorder');
        $this->assertEquals($dashboard1id, array_shift($order)->id);
        $this->assertEquals($dashboard3id, array_shift($order)->id);

        // Check that assignements of dashboard3 and 1 untouched.
        $cohorts = $DB->get_records_sql("SELECT * FROM {totara_dashboard_cohort} WHERE dashboardid IN (?, ?)",
                array($dashboard1id, $dashboard3id));
        $this->assertCount(2, $cohorts);

        // Check that assignment of dashbord2 gone.
        $cohorts2 = $DB->get_records('totara_dashboard_cohort', array('dashboardid' => $dashboard2id));
        $this->assertEmpty($cohorts2);

        // Check that user copy of dashboard2 removed.
        $userpages2 = $DB->get_records('totara_dashboard_user', array('dashboardid' => $dashboard2id));
        $this->assertEmpty($userpages2);

        $count2 = $DB->count_records('block_instances', array('pagetypepattern' => 'totara-dashboard-' . $dashboard2id));
        $this->assertEquals(0, $count2);

        // Check that user copy of dashboard3 left.
        $userpages3 = $DB->get_records('totara_dashboard_user', array('dashboardid' => $dashboard3id));
        $this->assertCount(1, $userpages3);

        $count3 = $DB->count_records('block_instances', array('pagetypepattern' => 'totara-dashboard-' . $dashboard3id));
        $this->assertEquals(2, $count3);
    }
}