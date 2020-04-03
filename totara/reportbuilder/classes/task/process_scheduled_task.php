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
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara_reportbuilder
 */

namespace totara_reportbuilder\task;

/**
 * Process Scheduled reports
 */
class process_scheduled_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('processscheduledtask', 'totara_reportbuilder');
    }


    /**
     * Process Scheduled reports
     */
    public function execute() {
        global $CFG, $DB, $SESSION;
        require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');
        require_once($CFG->dirroot . '/totara/reportbuilder/groupslib.php');
        require_once($CFG->dirroot . '/totara/core/lib/scheduler.php');

        require_once($CFG->dirroot . '/calendar/lib.php');

        $sql = "SELECT rbs.*, rb.fullname, u.timezone
                FROM {report_builder_schedule} rbs
                JOIN {report_builder} rb
                ON rbs.reportid = rb.id
                JOIN {user} u
                ON rbs.userid = u.id";

        $scheduledreports = $DB->get_records_sql($sql);

        mtrace('Processing ' . count($scheduledreports) . ' scheduled reports');

        // If exporting to file is turned off at system level, do not save reports.
        $exportsetting = get_config('reportbuilder', 'exporttofilesystem');

        foreach ($scheduledreports as $report) {
            // Set the next report time if its not yet set.
            $schedule = new \scheduler($report, array('nextevent' => 'nextreport'));

            if ($schedule->is_time()) {
                $user = $DB->get_record('user', array('id' => $report->userid, 'deleted' => 0));
                if (!$user) {
                    mtrace('ReportID:(' . $report->id . ') invalid user id.');
                    continue;
                }

                $tz = \core_date::get_user_timezone($user);

                $oldexecutiontime = $schedule->get_scheduled_time();
                $schedule->next(time(), true, $tz);
                $newexecutiontime = $schedule->get_scheduled_time();

                switch ($report->exporttofilesystem) {
                    case REPORT_BUILDER_EXPORT_EMAIL_AND_SAVE:
                        if ($exportsetting == 0) {
                            // Export turned off, email only.
                            $report->exporttofilesystem = REPORT_BUILDER_EXPORT_EMAIL;
                            mtrace('ReportID:(' . $report->id . ') Option: Email and save but save disabled so email only');
                        } else {
                            mtrace('ReportID:(' . $report->id . ') Option: Email and save scheduled report to file.');
                        }
                        break;
                    case REPORT_BUILDER_EXPORT_SAVE:
                        if ($exportsetting == 0) {
                            // Export turned off, ignore.
                            mtrace('ReportID:(' . $report->id . ') Option: Save scheduled report but export disabled, skipping');
                            continue 2;
                        } else {
                            mtrace('ReportID:(' . $report->id . ') Option: Save scheduled report to file system only.');
                        }
                        break;
                    default:
                        mtrace('ReportID:(' . $report->id . ') Option: Email scheduled report.');
                }

                // Hack $USER - includes current language change, $PAGE init, etc.
                cron_setup_user($user);

                // Reset any user specific caches. This isn't going to be fast but it is required.
                // All caches must be generated for the same user.
                \reportbuilder::reset_caches();
                \reportbuilder::reset_source_object_cache();

                // Send email.
                if (reportbuilder_send_scheduled_report($report)) {
                    mtrace('Sent email for report ' . $report->id);
                } else if ($report->exporttofilesystem == REPORT_BUILDER_EXPORT_SAVE) {
                    mtrace('No scheduled report email has been send');
                } else {
                    mtrace('Failed to send email for report ' . $report->id);
                }

                // Reset $USER and $SESSION. (This replaces previous reset of $SESSION->reportbuilder.)
                cron_setup_user('reset');

                // Store the next time to run this scheduled report. The new time
                // is only updated if the record's execution time matches the old
                // execution time. This is because the scheduler settings may have
                // changed in the interim and a new execution time computed. That
                // should not be overwritten.
                $conditions = [
                    'id' => $report->id,
                    'reportid' => $report->reportid,
                    'nextreport' => $oldexecutiontime
                ];
                $DB->set_field('report_builder_schedule', 'nextreport', $newexecutiontime, $conditions);
            } else if ($schedule->is_changed()) {
                $DB->update_record('report_builder_schedule', $schedule->to_object());
            }
        }
    }
}
