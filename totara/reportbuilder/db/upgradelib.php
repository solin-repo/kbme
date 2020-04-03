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
 * @author Oleg Demeshev <oleg.demeshev@toraralearning.com>
 * @package totara_reportbuilder
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Scheduled reports belonging to a user are now deleted when the user gets deleted
 */
function totara_reportbuilder_delete_scheduled_reports() {
    global $DB;

    // Get the reports created by deleted user/s.
    $sql = "SELECT rbs.id
                  FROM {report_builder_schedule} rbs
                  JOIN {user} u ON u.id = rbs.userid
                 WHERE u.deleted = 1";
    $reports = $DB->get_records_sql($sql);
    // Delete all scheduled reports created by deleted user/s.
    foreach ($reports as $report) {
        $DB->delete_records('report_builder_schedule_email_audience',   array('scheduleid' => $report->id));
        $DB->delete_records('report_builder_schedule_email_systemuser', array('scheduleid' => $report->id));
        $DB->delete_records('report_builder_schedule_email_external',   array('scheduleid' => $report->id));
        $DB->delete_records('report_builder_schedule', array('id' => $report->id));
    }

    // Get deleted user/s.
    $sql = "SELECT DISTINCT rbses.userid
                  FROM {report_builder_schedule_email_systemuser} rbses
                  JOIN {user} u ON u.id = rbses.userid
                 WHERE u.deleted = 1";
    $reports = $DB->get_fieldset_sql($sql);
    if ($reports) {
        list($sqlin, $sqlparm) = $DB->get_in_or_equal($reports);
        // Remove deleted user/s from scheduled reports.
        $DB->execute("DELETE FROM {report_builder_schedule_email_systemuser} WHERE userid $sqlin", $sqlparm);
    }

    // Get deleted audience/s.
    $sql = "SELECT DISTINCT rbsea.cohortid
                  FROM {report_builder_schedule_email_audience} rbsea
                 WHERE NOT EXISTS (
                           SELECT 1 FROM {cohort} ch WHERE rbsea.cohortid = ch.id
               )";
    $cohorts = $DB->get_fieldset_sql($sql);
    if ($cohorts) {
        list($sqlin, $sqlparm) = $DB->get_in_or_equal($cohorts);
        // Remove deleted audience/s from scheduled reports.
        $DB->execute("DELETE FROM {report_builder_schedule_email_audience} WHERE cohortid $sqlin", $sqlparm);
    }

    return true;
}