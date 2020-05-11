<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2014 onwards Totara Learning Solutions LTD
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
 * @author David Curry <david.curry@totaralearning.com>
 * @package totara_appraisal
 */

require_once($CFG->dirroot.'/totara/appraisal/lib.php');
require_once($CFG->dirroot.'/totara/job/classes/job_assignment.php');

use totara_job\job_assignment;

/**
 * Make sure $param1 is json encoded for all aggregate questions.
 */
function appraisals_upgrade_clean_aggregate_params() {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    $aggregates = $DB->get_records('appraisal_quest_field', array('datatype' => 'aggregate'));

    foreach ($aggregates as $aggregate) {
        // We only need to fix comma deliminated strings, skip encoded params.
        if (strpos($aggregate->param1, ']') || strpos($aggregate->param1, '}')) {
            continue;
        }

        $param1 = str_replace('"', '', $aggregate->param1);
        $param1 = explode(',', $param1);
        $aggregate->param1 = json_encode($param1);

        $DB->update_record('appraisal_quest_field', $aggregate);
    }
}

// TL-15900 Update team leaders in dynamic appraisals.
// Due to a bug in job assignments, the timemodified was not being updated when the
// manager path was updated. After fixing it, we need to make sure that all appraisal
// team leaders will be updated in dynamic appraisals. Just reduce the user assignment
// jobassignmentlastmodified field where the current team lead doesn't match.
function totara_appraisal_upgrade_update_team_leaders() {
    global $DB;

    // A team leader job assignment exists, but no team leader has been assigned in the appraisal.
    $sql = "UPDATE {appraisal_user_assignment}
               SET jobassignmentlastmodified = 0
             WHERE jobassignmentlastmodified > 0
               AND EXISTS (SELECT 1
                     FROM {job_assignment} learnerja
                     JOIN {job_assignment} managerja
                       ON learnerja.managerjaid = managerja.id
                     JOIN {job_assignment} teamleadja
                       ON managerja.managerjaid = teamleadja.id
                    WHERE learnerja.id = {appraisal_user_assignment}.jobassignmentid)
           AND NOT EXISTS (SELECT 1
                     FROM {appraisal_role_assignment} teamleadra
                    WHERE teamleadra.appraisaluserassignmentid = {appraisal_user_assignment}.id
                      AND teamleadra.appraisalrole = :teamleaderrole)";
    $DB->execute($sql, array('teamleaderrole' => appraisal::ROLE_TEAM_LEAD));

    // A team leader has been assigned in the appraisal, but no team leader job assignment exists.
    $sql = "UPDATE {appraisal_user_assignment}
               SET jobassignmentlastmodified = 0
             WHERE jobassignmentlastmodified > 0
               AND EXISTS (SELECT 1
                     FROM {appraisal_role_assignment} teamleadra
                    WHERE teamleadra.appraisaluserassignmentid = {appraisal_user_assignment}.id
                      AND teamleadra.appraisalrole = :teamleaderrole
                      AND teamleadra.userid <> 0)
           AND NOT EXISTS (SELECT 1
                     FROM {job_assignment} learnerja
                     JOIN {job_assignment} managerja
                       ON learnerja.managerjaid = managerja.id
                     JOIN {job_assignment} teamleadja
                       ON managerja.managerjaid = teamleadja.id
                    WHERE learnerja.id = {appraisal_user_assignment}.jobassignmentid)";
    $DB->execute($sql, array('teamleaderrole' => appraisal::ROLE_TEAM_LEAD));

    // Both exist, but they don't have matching users.
    $sql = "UPDATE {appraisal_user_assignment}
               SET jobassignmentlastmodified = 0
             WHERE jobassignmentlastmodified > 0
               AND EXISTS (SELECT 1
                     FROM {job_assignment} learnerja
                     JOIN {job_assignment} managerja
                       ON learnerja.managerjaid = managerja.id
                     JOIN {job_assignment} teamleadja
                       ON managerja.managerjaid = teamleadja.id
                     JOIN {appraisal_role_assignment} teamleadra
                       ON teamleadra.appraisalrole = :teamleaderrole
                    WHERE learnerja.id = {appraisal_user_assignment}.jobassignmentid
                      AND teamleadra.appraisaluserassignmentid = {appraisal_user_assignment}.id
                      AND (teamleadja.userid <> teamleadra.userid AND teamleadja.userid IS NOT NULL OR
                           teamleadra.userid = 0))";
    $DB->execute($sql, array('teamleaderrole' => appraisal::ROLE_TEAM_LEAD));
}

/**
 * TL-16443 Make all multichoice questions use int for param1.
 *
 * Whenever someone created a new scale for their question, it would store it as an integer in the param1 text field.
 * However, when using an existing scale, it would record the scale id with quotes around it. This caused a failure
 * in some sql. To make everything consistent and easier to process, we're changing them all to integers in text
 * fields, without quotes.
 */
function totara_appraisal_upgrade_fix_inconsistent_multichoice_param1() {
    global $DB;

    list($sql, $params) = $DB->sql_text_replace('param1', '"', '', SQL_PARAMS_NAMED);

    $sql = "UPDATE {appraisal_quest_field}
               SET {$sql}
             WHERE datatype IN ('multichoicemulti', 'multichoicesingle')
               AND " . $DB->sql_like('param1', ':colon', true, true, true) . "
               AND " . $DB->sql_like('param1', ':bracket', true, true, true) . "
               AND " . $DB->sql_like('param1', ':braces', true, true, true);
    $params['colon'] = '%:%';
    $params['bracket'] = '%[%';
    $params['braces'] = '%{%';

    $DB->execute($sql, $params);
}

/**
 * TL-22800 fix duplicate user assignments
 *
 * There was production issue in which there was a race condition between the
 * appraisal assignment cron and the front end and in the end, there were
 * duplicate records in the user assignment table.
 *
 * This fix adds a unique (appraisal, appraisee) index to the table so that
 * duplicates will not occur any more. However, it is possible that duplicates
 * exist prior to this upgrade. Hence, this function checks for duplicates first
 * before creating the index; if duplicates exist, the *entire* upgrade process
 * is stopped.
 */
function totara_appraisal_upgrade_add_user_assignment_index() {
    global $DB;

    $duplicates = $DB->record_exists_sql("
        SELECT userid, appraisalid, count(appraisalid) as duplicates
        FROM {appraisal_user_assignment}
        GROUP BY appraisalid, userid
        HAVING count(appraisalid) > 1
    ");

    if ($duplicates) {
        throw new moodle_exception(
            'notlocalisederrormessage',
            'totara_appraisal',
            '',
            "UPGRADE CANNOT CONTINUE: appraisal_user_assignment table has duplicates. Please get in touch with the Totara support team and cite that your site has been affected by TL-22800"
        );
    }

    $table = new xmldb_table('appraisal_user_assignment');
    $index = new xmldb_index('appruserassi_usrappr_ix', XMLDB_INDEX_UNIQUE, array('appraisalid', 'userid'));

    $dbman = $DB->get_manager();
    if (!$dbman->index_exists($table, $index)) {
        $dbman->add_index($table, $index);
    }
}
