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

defined('MOODLE_INTERNAL') || die();

/**
 * Tests functions in mod/facetoface/db/upgradelib.php
 */
class mod_facetoface_install_testcase extends advanced_testcase {

    /**
     * Test installation of notifications
     * Here we test with the default setting.
     */
    public function test_mod_facetoface_migrate_session_signup_customdata_default() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/facetoface/db/install.php');

        $this->resetAfterTest();
        //$this->preventResetByRollback();

        // At this stage we have all notifications added, clean them and add couple that will cause conflict
        $DB->delete_records('facetoface_notification_tpl');

        // One title conflict with reference='confirmation'
        $confirmtitle = get_string('setting:defaultconfirmationsubjectdefault_v9', 'facetoface');
        $confirmbody = text_to_html(get_string('setting:defaultconfirmationmessagedefault_v9', 'facetoface'));

        $conflict1 = new stdClass();
        $conflict1->status = 1;
        $conflict1->reference = 'test1';
        $conflict1->title = $confirmtitle;
        $conflict1->body = $confirmbody;
        $conflict1->managerprefix = text_to_html(get_string('setting:defaultconfirmationinstrmngrdefault_v92', 'facetoface'));
        $DB->insert_record('facetoface_notification_tpl', $conflict1);

        // Second title conflict (double) with reference='waitlist'
        $waitlisttitle = get_string('setting:defaultwaitlistedsubjectdefault_v9', 'facetoface');
        $waitlistbody = text_to_html(get_string('setting:defaultwaitlistedmessagedefault_v9', 'facetoface'));
        $conflict2a = new stdClass();
        $conflict2a->status = 1;
        $conflict2a->reference = 'test2a';
        $conflict2a->title = $waitlisttitle;
        $conflict2a->body = $waitlistbody;
        $DB->insert_record('facetoface_notification_tpl', $conflict2a);

        $conflict2b = new stdClass();
        $conflict2b->status = 1;
        $conflict2b->reference = 'test2b';
        $conflict2b->title = $waitlisttitle . ' 1';
        $conflict2b->body = $waitlistbody;
        $DB->insert_record('facetoface_notification_tpl', $conflict2b);

        xmldb_facetoface_install_notification_templates();

        // Confirm that indexes were added correctly
        $confirm = $DB->get_record('facetoface_notification_tpl', array('reference' => 'test1'));
        $this->assertEquals($confirmtitle, $confirm->title);
        $this->assertEquals($confirmbody, $confirm->body);
        $this->assertEquals(1, $confirm->status);

        $confirm1 = $DB->get_record('facetoface_notification_tpl', array('reference' => 'confirmation'));
        $this->assertEquals($confirmtitle . ' 1', $confirm1->title);
        $this->assertEquals($confirmbody, $confirm1->body);
        $this->assertEquals(1, $confirm1->status);

        $waitlist = $DB->get_record('facetoface_notification_tpl', array('reference' => 'test2a'));
        $this->assertEquals($waitlisttitle, $waitlist->title);
        $this->assertEquals($waitlistbody, $waitlist->body);
        $this->assertEquals(1, $waitlist->status);

        $waitlist1 = $DB->get_record('facetoface_notification_tpl', array('reference' => 'test2b'));
        $this->assertEquals($waitlisttitle . ' 1', $waitlist1->title);
        $this->assertEquals($waitlistbody, $waitlist1->body);
        $this->assertEquals(1, $waitlist1->status);

        $waitlist2 = $DB->get_record('facetoface_notification_tpl', array('reference' => 'waitlist'));
        $this->assertEquals($waitlisttitle . ' 2', $waitlist2->title);
        $this->assertEquals($waitlistbody, $waitlist2->body);
        $this->assertEquals(1, $waitlist2->status);

        // Confirm that non-conflicting entries are not affected.
        $cancellation = $DB->get_record('facetoface_notification_tpl', array('reference' => 'cancellation'));
        $this->assertEquals(1, $cancellation->status);
        $this->assertEquals(get_string('setting:defaultcancellationsubjectdefault_v9', 'facetoface'), $cancellation->title);
        $this->assertEquals(text_to_html(get_string('setting:defaultcancellationmessagedefault_v9', 'facetoface')), $cancellation->body);
        $this->assertEquals(text_to_html(get_string('setting:defaultcancellationinstrmngrdefault_v92', 'facetoface')), $cancellation->managerprefix);
    }
}
