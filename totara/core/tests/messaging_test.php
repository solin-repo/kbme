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
 * @author Maria Torres <maria.torres@totaralms.com>
 * @package totara_core
 */

global $CFG;
require_once($CFG->dirroot . '/totara/reportbuilder/tests/reportcache_advanced_testcase.php');

class totara_core_messaging_testcase extends advanced_testcase {

    /** @var totara_plan_generator $plangenerator */
    private $plangenerator = null;

    /** @var totara_program_generator $programgenerator */
    private $programgenerator = null;

    /** @var totara_hierarchy_generator $hierarchygenerator */
    private $hierarchygenerator = null;

    private $user1, $user2, $user3, $manager1, $manager2;

    protected function tearDown() {
        $this->plangenerator = null;
        $this->programgenerator = null;
        $this->hierarchygenerator = null;
        $this->user1 = null;
        parent::tearDown();
    }

    public function setUp() {
        parent::setup();

        $this->programgenerator = $this->getDataGenerator()->get_plugin_generator('totara_program');
        $this->plangenerator = $this->getDataGenerator()->get_plugin_generator('totara_plan');
        $this->audiencegenerator = $this->getDataGenerator()->get_plugin_generator('totara_cohort');
        $this->hierarchygenerator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');

        // Create some users to work with.
        $this->user1 = $this->getDataGenerator()->create_user(array('email' => 'user1@example.com'));
        $this->user2 = $this->getDataGenerator()->create_user(array('email' => 'user2@example.com'));
        $this->user3 = $this->getDataGenerator()->create_user(array('email' => 'user3@example.com'));

        $this->manager1 = $this->getDataGenerator()->create_user(array('email' => 'manager1@example.com'));
        $this->manager2 = $this->getDataGenerator()->create_user(array('email' => 'manager2@example.com'));

        // Assign managers to students.
        $this->hierarchygenerator->assign_primary_position($this->user1->id, $this->manager1->id, null, null);
        $this->hierarchygenerator->assign_primary_position($this->user2->id, $this->manager2->id, null, null);
        $this->hierarchygenerator->assign_primary_position($this->user3->id, $this->manager1->id, null, null);
    }

    /**
     * Data provider for the facetoface_messages function.
     *
     * @return array $data Data to be used by test_facetoface_messages.
     */
    public function messages_setting() {
        $data = array(
            array(1, 'no-reply@example.com'),
            array(1, ''),
            array(0, 'no-reply@example.com'),
            array(0, ''),
        );
        return $data;
    }

    /**
     * Test from user is correctly set according to settings.
     * @param int $emailonlyfromnoreplyaddress Setting to use only from no reply address
     * @param string $noreplyaddress No-reply address
     * @dataProvider messages_setting
     */
    public function test_messages_from_no_reply($emailonlyfromnoreplyaddress, $noreplyaddress) {
        global $USER, $CFG;
        $this->preventResetByRollback();
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Set email only from no reply address.
        set_config('emailonlyfromnoreplyaddress', $emailonlyfromnoreplyaddress);

        // Set the no reply address.
        set_config('noreplyaddress', $noreplyaddress);

        $sink = $this->redirectEmails();

        ob_start(); // Start a buffer to catch all the mtraces in the task.

        // Messages in Programs.
        $program1 = $this->programgenerator->create_program();
        $this->programgenerator->assign_program($program1->id, array($this->user1->id, $this->user2->id));

        $this->waitForSecond(); // Messages are only sent if they were created before "now", so we need to wait one second.

        // Attempt to send any program messages.
        $task = new \totara_program\task\send_messages_task();
        $task->execute();

        // Check user from.
        $fromuser = $USER;
        if ($noreplyaddress === '') {
            // Messaging now forces a default email address of noreply@{wwwroot} if the no reply email
            // has not been set.
            // Within PHPUNIT the administrators email address is set the same as the default.
            $noreplyaddress = 'noreply@' . get_host_from_url($CFG->wwwroot);
        }
        $expectedname = sprintf("%s %s", $fromuser->firstname, $fromuser->lastname);
        $expectedemail = $emailonlyfromnoreplyaddress ? $noreplyaddress : $fromuser->email;
        $checkformat = '%s (%s)';
        $expected = sprintf($checkformat, $expectedname, $expectedemail);

        // Check that that one email was sent and the from adress corresponds to the noreply address.
        $emails = $sink->get_messages();
        $this->assertCount(2, $emails);
        foreach ($emails as $email) {
            $actual = sprintf($checkformat, $email->fromname, $email->from);
            $this->assertEquals($expected, $actual);
        }
        $sink->clear();

        // Messages in Learning plan.
        $sink = $this->redirectEmails();
        $plan = $this->plangenerator->create_learning_plan(array('userid' => $this->user1->id));
        $this->plangenerator->create_learning_plan_objective($plan->id, $this->user1->id, null);

        // Check emails.
        $emails = $sink->get_messages();
        $this->assertCount(1, $emails);
        foreach ($emails as $email) {
            $actual = sprintf($checkformat, $email->fromname, $email->from);
            $this->assertEquals($expected, $actual);
        }
        $sink->clear();
        ob_end_clean();
    }
}
