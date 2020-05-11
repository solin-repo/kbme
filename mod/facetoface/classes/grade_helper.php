<?php
/*
 * This file is part of Totara LMS
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @package mod_facetoface
 */

namespace mod_facetoface;

use mod_facetoface\signup\state\{state, fully_attended, partially_attended, no_show};

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/gradelib.php');

/**
 * Additional grade functionality.
 */
final class grade_helper {
    /** Maximum compatibility with grade_update() */
    const FORMAT_GRADELIB = 0;

    /** facetoface-specific format */
    const FORMAT_FACETOFACE = 1;

    /**
     * Calculate a user's final grade.
     *
     * @param integer $userid user ID
     * @param \stdClass|seminar $facetoface seminar instance
     * @param integer $format see get_final_grades()
     * @return null|\stdClass see get_final_grades()
     */
    private static function get_final_grade_of(int $userid, $facetoface, int $format): ?\stdClass {
        global $DB;
        /** @var \moodle_database $DB */

        if (empty($userid)) {
            throw new \coding_exception('$userid must not be zero');
        }
        if ($facetoface instanceof seminar) {
            $f2fid = $facetoface->get_id();
        } else if ($facetoface instanceof \stdClass) {
            $f2fid = $facetoface->id;
        } else {
            throw new \coding_exception('$facetoface must be a seminar object or a database record');
        }

        [$insql, $inparams] = $DB->get_in_or_equal([fully_attended::get_code(), partially_attended::get_code(), no_show::get_code()], SQL_PARAMS_NAMED);

        // Use statuscode instead of grade because grade is not very reliable in T12.
        $sql =
            "SELECT su.id,
                    sus.grade,
                    sus.statuscode,
                    sd.timefinish AS timecompleted
               FROM {facetoface_signups} su
         INNER JOIN {facetoface_signups_status} sus ON sus.signupid = su.id
         INNER JOIN {facetoface_sessions} s ON s.id = su.sessionid
         INNER JOIN {facetoface_sessions_dates} sd ON (sd.sessionid = s.id)
              WHERE (s.facetoface = :f2f)
                AND (s.cancelledstatus = 0)
                AND (sus.superceded = 0)
                AND (sus.statuscode {$insql})
                AND (su.archived = 0 OR su.archived IS NULL)
                AND (su.userid = :uid)
           ORDER BY sus.timecreated DESC, sus.id DESC, sd.timefinish DESC";

        $records = $DB->get_records_sql($sql, ['f2f' => $f2fid, 'uid' => $userid] + $inparams, 0, 1);
        $record = reset($records);
        if ($record === false) {
            return null;
        }

        $object = new \stdClass();
        if ($format === self::FORMAT_FACETOFACE) {
            $object->timecompleted = $record->timecompleted;
            // Note: Add here if any other properties are necessary.
        } else {
            $object->id = $userid;
            // Note: Do not add anything here.
        }
        $object->userid = $userid;
        $rawgrade = state::from_code($record->statuscode)::get_grade();
        if ($rawgrade !== null) {
            // Convert ?int to ?float.
            $rawgrade = (float)$rawgrade;
        }
        $object->rawgrade = $rawgrade;
        return $object;
    }

    /**
     * Calculate users' final grades.
     *
     * @param \stdClass|seminar $facetoface seminar instance
     * @param integer $userid user ID or 0 to get all grades in the seminar
     * @param integer $format Set FORMAT_GRADELIB to return records that can be passed to `grade_update()`
     * @return array|false array of objects in the following format, or false if nothing applicable
     * - if FORMAT_GRADELIB is given, [ userid => [ id, userid, rawgrade ], ... ]
     * - if FORMAT_FACETOFACE is given, [ userid => [ userid, rawgrade, timecompleted ], ... ]
     */
    public static function get_final_grades($facetoface, int $userid = 0, int $format = self::FORMAT_GRADELIB) {
        global $DB;
        /** @var \moodle_database $DB */

        if (!in_array($format, [self::FORMAT_GRADELIB, self::FORMAT_FACETOFACE])) {
            throw new \coding_exception('Unknown $format: '.$format);
        }

        if ($facetoface instanceof seminar) {
            $f2fid = $facetoface->get_id();
        } else if ($facetoface instanceof \stdClass) {
            $f2fid = $facetoface->id;
        } else {
            throw new \coding_exception('$facetoface must be a seminar object or a database record');
        }

        if (!empty($userid)) {
            $userids = [$userid];
        } else {
            $userids = $DB->get_fieldset_sql(
                'SELECT DISTINCT su.userid
                   FROM {facetoface_signups} su
             INNER JOIN {facetoface_sessions} s ON s.id = su.sessionid
              LEFT JOIN {facetoface_signups_status} sus ON sus.signupid = su.id
                  WHERE (s.facetoface = ?)
                    AND (s.cancelledstatus = 0)
                    AND (sus.superceded = 0)
                    AND (su.archived = 0 OR su.archived IS NULL)',
                [$f2fid]
            );
        }

        $result = [];
        foreach ($userids as $userid) {
            $object = self::get_final_grade_of($userid, $facetoface, $format);
            if ($object !== null) {
                $result[$userid] = $object;
            }
        }

        if (empty($result)) {
            return false;
        }
        return $result;
    }
}
