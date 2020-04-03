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
 * @package mod_facetoface
 */

/**
 * Facetoface module upgrade related helper functions
 *
 * @package    mod_facetoface
 * @author     Sam Hemelryk <sam.hemelryk@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Helper function to aid in the migration of signup custom field data.
 *
 * $CFG->facetoface_customfield_migration_behaviour is used to determine behaviour.
 * If facetoface_customfield_migration_behaviour is not set then this will map the last non-empty data as the users current data.
 * Alternatively facetoface_customfield_migration_behaviour can be set to "latest" in which case the latest record regardless of
 * whether it is empty or not is restored.
 *
 * @param moodle_database $db
 * @param database_manager $dbman
 * @param string $tablename The name of the data table.
 * @param string $field The name of the field that is used as the id reference on the table.
 */
function mod_facetoface_migrate_session_signup_customdata(moodle_database $db, database_manager $dbman, $tablename, $field) {
    global $CFG;

    $temptable = new xmldb_table('facetoface_migration_temp');
    $temptable->add_field('dataid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $temptable->add_field('signupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $temptable->add_field('statusid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

    $dbman->create_table($temptable);

    if ($field === 'facetofacecancellationid') {
        $comparison = '=';
    } else {
        $comparison = '<>';
    }

    $transaction = $db->start_delegated_transaction();

    // Populate the mapping table.
    if (isset($CFG->facetoface_customfield_migration_behaviour) && $CFG->facetoface_customfield_migration_behaviour === 'latest') {
        $sql = "INSERT INTO {facetoface_migration_temp} (dataid, statusid, signupid)
                     SELECT d.id, ss.statusid, ss.signupid
                       FROM {{$tablename}} d
                       JOIN (SELECT MAX(s.id) AS statusid, s.signupid
                                FROM {facetoface_signups_status} s
                               WHERE s.statuscode {$comparison} 10
                            GROUP BY s.signupid
                            ) ss ON ss.statusid = d.{$field}";
        $db->execute($sql);
    } else {
        $sql = "INSERT INTO {facetoface_migration_temp} (dataid, statusid, signupid)
                     SELECT d.id, ss.statusid, ss.signupid
                       FROM {{$tablename}} d
                       JOIN (SELECT MAX(s.id) AS statusid, s.signupid
                                FROM {facetoface_signups_status} s
                                JOIN {{$tablename}} t ON t.{$field} = s.id
                               WHERE s.statuscode {$comparison} 10 AND t.data <> ''
                            GROUP BY s.signupid
                            ) ss ON ss.statusid = d.{$field}";
        $db->execute($sql);
    }

    // First drop all redundant rows, if we try the update before this then we bust the null constraint for rows that
    // will be redundant.
    $sql = "DELETE FROM {{$tablename}}
                  WHERE id NOT IN (
                      SELECT dataid
                        FROM {facetoface_migration_temp})";
    $db->execute($sql);

    // Now update the rows that remain to point to the signupid rather than the statusid.
    $sql = "UPDATE {{$tablename}}
               SET {$field} = (
                    SELECT signupid
                      FROM {facetoface_migration_temp}
                     WHERE {facetoface_migration_temp}.dataid = {{$tablename}}.id)";
    $db->execute($sql);

    $transaction->allow_commit();

    $dbman->drop_table($temptable);
}

/**
 * Delete orphaned custom field data.
 *
 * @param string          $type The type of customfield e.g. signup/cancellation
 */
function mod_facetoface_delete_orphaned_customfield_data($type) {
    global $DB;

    $foreignkey = "facetoface{$type}id";
    $customfield_table = "facetoface_{$type}_info_data";
    $customparam_table = "facetoface_{$type}_info_data_param";

    $customfieldids = $DB->get_fieldset_select(
        $customfield_table,
        'id',
        $foreignkey . ' NOT IN (SELECT s.id FROM {facetoface_signups} s)'
    );

    $managablechunks = array_chunk($customfieldids, $DB->get_max_in_params());

    $transaction = $DB->start_delegated_transaction();
    foreach ($managablechunks as $chunk) {
        if (!empty($chunk)) {
            list($sqlin, $inparams) = $DB->get_in_or_equal($chunk);
            $DB->delete_records_select($customparam_table, "dataid {$sqlin}", $inparams);
            $DB->delete_records_select($customfield_table, "id {$sqlin}", $inparams);
        }
    }
    $transaction->allow_commit();
}
