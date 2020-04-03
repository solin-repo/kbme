<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2015 onwards Totara Learning Solutions LTD
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
 * @author Petr Skoda <petr.skoda@totaralms.com>
 * @package totara_reportbuilder
 */

defined('MOODLE_INTERNAL') || die();

class totara_reportbuilder_scheduled_export_testcase extends advanced_testcase {
    use totara_reportbuilder\phpunit\report_testing;

    public function test_source() {
        global $DB, $CFG;
        require_once("$CFG->dirroot/totara/reportbuilder/lib.php");

        $this->resetAfterTest();
        $this->setAdminUser(); // We need permissions to access all reports.

        $testdir = make_writable_directory($CFG->dataroot . '/mytest');
        $testdir = realpath($testdir);
        $this->assertFileExists($testdir);

        set_config('exporttofilesystem', '1', 'reportbuilder');
        set_config('exporttofilesystempath', $testdir, 'reportbuilder');

        $admin = get_admin();
        $guest = guest_user();
        $user = $this->getDataGenerator()->create_user();

        $expected = array();
        $expected[] = array('User ID', 'User First Name', 'User Last Name');
        $expected[] = array($guest->id, $guest->firstname, $guest->lastname);
        $expected[] = array($admin->id, $admin->firstname, $admin->lastname);
        $expected[] = array($user->id, $user->firstname, $user->lastname);

        $rid = $this->create_report('user', 'Test user report 1');
        $DB->set_field('report_builder', 'defaultsortcolumn', 'user_id', array('id' => $rid));
        $DB->set_field('report_builder', 'defaultsortorder', SORT_ASC, array('id' => $rid));

        $report = new reportbuilder($rid, null, false, null, null, true);
        $this->add_column($report, 'user', 'id', null, null, null, 0);
        $this->add_column($report, 'user', 'firstname', null, null, null, 0);
        $this->add_column($report, 'user', 'lastname', null, null, null, 0);

        $report = new reportbuilder($rid);

        $schedules = array();
        $plugins = \totara_core\tabexport_writer::get_export_classes();

        foreach ($plugins as $plugin => $classname) {
            if (!$classname::is_ready()) {
                // We cannot test plugins that are not ready.
                continue;
            }
            $schedule = new stdClass();
            $schedule->id = 0;
            $schedule->reportid = $report->_id;
            $schedule->savedsearchid = 0;
            $schedule->format = $plugin;
            $schedule->frequency = 1; // Means daily.
            $schedule->schedule = 0; // Means midnight.
            $schedule->exporttofilesystem = REPORT_BUILDER_EXPORT_EMAIL_AND_SAVE;
            $schedule->nextreport = 0; // Means asap.
            $schedule->userid = $admin->id;
            $schedule->id = $DB->insert_record('report_builder_schedule', $schedule);
            $schedules[$schedule->id] = $DB->get_record('report_builder_schedule', array('id' => $schedule->id));
        }

        // Everything is ready, now create and test the files.
        foreach ($schedules as $schedule) {
            $writer = $plugins[$schedule->format];
            $this->assertTrue(class_exists($writer));
            ob_start(); // Ignore the useless warning about validity of export directory.
            reportbuilder_send_scheduled_report($schedule);
            $reportfilepathname = reportbuilder_get_export_filename($report, $admin->id, $schedule->id) . '.' . $writer::get_file_extension();
            ob_end_clean();
            $this->assertFileExists($reportfilepathname);
            unlink($reportfilepathname);
        }
    }
}
