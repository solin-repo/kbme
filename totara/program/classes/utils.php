<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Yuliya Bozhko <yuliya.bozhko@totaralearning.com>
 * @package totara_program
 */

namespace totara_program;

/**
 * Class providing various utility functions for use by programs but which can
 * be used independently of and without instantiating a program object
 */
class utils {

    /**
     * Find if a user is assigned to a program/certification
     *
     * @param int $programid
     * @param int|null $userid
     *
     * @return bool
     */
    public static function user_is_assigned(int $programid, ?int $userid) :bool {
        global $DB;

        // Check needed for backwards compatibility with program::user_is_assigned method.
        if (!$userid) {
            return false;
        }

        static $prog_assigned = [];
        if (PHPUNIT_TEST) {
            $prog_assigned = [];
        }

        $key = $programid . '-' . $userid;

        if (!isset($prog_assigned[$key])) {
            // Update this when we move constants into an autoloaded class, these
            // are defined in program.class.php which has a lot of extra require calls
            // PROGRAM_EXCEPTION_RAISED = 1
            // PROGRAM_EXCEPTION_DISMISSED = 2
            $statuses = [1,2];
            list($statussql, $statusparams) = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED, null, false);

            $params = [
                'programid' => $programid,
                'userid' => $userid
            ];

            $params = array_merge($params, $statusparams);
            $result = $DB->record_exists_select('prog_user_assignment', "programid = :programid AND userid = :userid AND exceptionstatus $statussql", $params);

            if ($result === false) {
                // Check for plan assignment
                $sql = "SELECT COUNT(*) FROM
                    {dp_plan} p
                    JOIN
                    {dp_plan_program_assign} pa
                    ON
                    p.id = pa.planid
                    WHERE
                    p.userid = :userid
                    AND pa.programid = :programid
                    AND pa.approved = :approved
                    AND p.status >= :approvedstatus";
                $params = [
                    'userid' => $userid,
                    'programid' => $programid,
                    'approved' => 50, //DP_APPROVAL_APPROVED,
                    'approvedstatus' => 50 //DP_PLAN_STATUS_APPROVED
                ];

                if ($DB->count_records_sql($sql, $params) > 0) {
                    $result = true;
                } else {
                    $result = false;
                }
            }

            $prog_assigned[$key] = $result;
        }

        return $prog_assigned[$key];
    }
}
