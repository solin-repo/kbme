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
 * @author David Curry <david.curry@totaralms.com>
 * @package totara_dashboard
 */

/**
 * Local database upgrade script
 *
 * @param   integer $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean $result
 */
function xmldb_totara_dashboard_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2015030201) {
        $table = new xmldb_table('totara_dashboard_user');
        $key = new xmldb_key('dashuser_das_fk', XMLDB_KEY_FOREIGN, array('dashboardid'), 'totara_dashboard', array('id'));
        $field = new xmldb_field('dashboardid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null,'userid');

        // This should never happen but just in case, delete any invalid data.
        $dashes = $DB->get_recordset('totara_dashboard_user');
        foreach ($dashes as $dash) {
            if (!preg_match('/^[0-9]{1,10}$/', $dash->dashboardid)) {
                // Delete the invalid record.
                $DB->delete_records('totara_dashboard_user', array('id' => $dash->id));

                // Log what has happended.
                $type = 'Invalid Dashboard Warning';
                $info = "Userid:{$dash->userid} - Dashboardid:{$dash->dashboardid}";
                upgrade_log(UPGRADE_LOG_NOTICE, 'totara_dashboard', $type, $info);
            }
        }
        $dashes->close();

        // Launch drop key dashuser_das_fk.
        $dbman->drop_key($table, $key);

        // Update the field type.
        $dbman->change_field_type($table, $field);

        // Launch add key dashuser_das_fk.
        $dbman->add_key($table, $key);

        totara_upgrade_mod_savepoint(true, 2015030201, 'totara_dashboard');
    }

    if ($oldversion < 2015100201) {
        global $DB;

        $dashboards = $DB->get_records('totara_dashboard_cohort');
        if ($dashboards) {
            foreach ($dashboards as $dashboard) {
                if (!$DB->record_exists('cohort', array('id' => $dashboard->cohortid))) {
                    $DB->delete_records('totara_dashboard_cohort', array('cohortid' => $dashboard->cohortid));
                }
            }
        }

        totara_upgrade_mod_savepoint(true, 2015100201, 'totara_dashboard');
    }

    if ($oldversion < 2015100202.00) {
        // All dashboard blocks have been added to the wrong pagetype.
        // Previously they were my-totara-dashboard-x, they are now totara-dashboard-x
        // First up, take care of all basic dashboard blocks per dashboard. This will perform the best.

        $rs = $DB->get_recordset('totara_dashboard', [], '', 'id');
        foreach ($rs as $dashboard) {
            // Update the block instances and positions for each dashboard.
            $oldkey = 'my-totara-dashboard-' . $dashboard->id;
            $newkey = 'totara-dashboard-' . $dashboard->id;
            $DB->set_field('block_instances', 'pagetypepattern', $newkey, ['pagetypepattern' => $oldkey]);
            $DB->set_field('block_positions', 'pagetype', $newkey, ['pagetype' => $oldkey]);
        }
        $rs->close();

        upgrade_plugin_savepoint(true, 2015100202.00, 'totara', 'dashboard');
    }

    if ($oldversion < 2015100202.01) {
        // All dashboard blocks have been added to the wrong pagetype.
        // Previously they were my-totara-dashboard-x, they are now totara-dashboard-x
        // Now deal with situations where the user has managed to move the block within the space.

        // There should be none of these, but still, be very aware of it!
        $rs = $DB->get_recordset_select(
            'block_instances',
            $DB->sql_like('pagetypepattern', ':key'),
            ['key' => 'my-totara-dashboard-%'],
            'id',
            'id,pagetypepattern'
        );
        foreach ($rs as $row) {
            $oldkey = $row->pagetypepattern;
            $newkey = substr($row->pagetypepattern, 3);
            $DB->set_field('block_instances', 'pagetypepattern', $newkey, ['pagetypepattern' => $oldkey]);
        }
        $rs->close();

        $rs = $DB->get_recordset_select(
            'block_positions',
            $DB->sql_like('pagetype', ':key'),
            ['key' => 'my-totara-dashboard-%'],
            'id',
            'id,pagetype'
        );
        foreach ($rs as $row) {
            $oldkey = $row->pagetypepattern;
            $newkey = substr($row->pagetypepattern, 3);
            $DB->set_field('block_positions', 'pagetype', $newkey, ['pagetype' => $oldkey]);
        }
        $rs->close();

        upgrade_plugin_savepoint(true, 2015100202.01, 'totara', 'dashboard');
    }

    return true;
}
