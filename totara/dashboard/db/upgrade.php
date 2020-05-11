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

require_once($CFG->dirroot.'/totara/dashboard/db/upgradelib.php');

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

    if ($oldversion < 2015120900) {
        global $DB;

        $dashboards = $DB->get_records('totara_dashboard_cohort');
        if ($dashboards) {
            foreach ($dashboards as $dashboard) {
                if (!$DB->record_exists('cohort', array('id' => $dashboard->cohortid))) {
                    $DB->delete_records('totara_dashboard_cohort', array('cohortid' => $dashboard->cohortid));
                }
            }
        }

        totara_upgrade_mod_savepoint(true, 2015120900, 'totara_dashboard');
    }

    if ($oldversion < 2016072600) {

        totara_dashboard_migrate_my_learning_on_upgrade();

        totara_upgrade_mod_savepoint(true, 2016072600, 'totara_dashboard');
    }

    if ($oldversion < 2016072601) {

        totara_dashboard_add_my_learning_dashboard_on_upgrade();

        totara_upgrade_mod_savepoint(true, 2016072601, 'totara_dashboard');
    }

    if ($oldversion < 2016072602) {

        // Migrate block instances belonging to totara dashboards from 'content'
        // to 'main' region.
        // Note: this must happen *after* my learning is migrated to a dashboard
        // to ensure the my learning block regions are updated to 'main' too.
        $sql = "UPDATE {block_instances} SET defaultregion = 'main'
            WHERE defaultregion = 'content' AND
            pagetypepattern LIKE ?";
        $params = ['my-totara-dashboard-%'];
        $DB->execute($sql, $params);

        totara_upgrade_mod_savepoint(true, 2016072602, 'totara_dashboard');
    }

    if ($oldversion < 2016080501) {
        // Cleanup and migrate My learning related settings.
        if (isset($CFG->defaulthomepage)) {
            if ($CFG->defaulthomepage == HOMEPAGE_MY) {
                set_config('defaulthomepage', HOMEPAGE_TOTARA_DASHBOARD);
            } else if ($CFG->defaulthomepage == HOMEPAGE_USER) {
                set_config('defaulthomepage', HOMEPAGE_TOTARA_DASHBOARD);
                set_config('allowdefaultpageselection', 1);
            }
        }
        unset_config('allowguestmymoodle');

        // Disable dashboards for the main admin to make it backwards compatible with Totara 2.9.
        $admin = get_admin();
        if ($admin) {
            set_user_preference('user_home_page_preference', HOMEPAGE_SITE, $admin->id);
        }

        totara_upgrade_mod_savepoint(true, 2016080501, 'totara_dashboard');
    }

    if ($oldversion < 2016092001.00) {
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

        upgrade_plugin_savepoint(true, 2016092001.00, 'totara', 'dashboard');
    }

    if ($oldversion < 2016092001.01) {
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

        upgrade_plugin_savepoint(true, 2016092001.01, 'totara', 'dashboard');
    }

    return true;
}
