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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara
 * @subpackage feedback360
 */

require_once($CFG->dirroot.'/totara/feedback360/db/upgradelib.php');

/**
 * Local database upgrade script
 *
 * @param   integer $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean $result
 */
function xmldb_totara_feedback360_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2015090400) {

        // Define field anonymous to be added to feedback360.
        $table = new xmldb_table('feedback360');
        $field = new xmldb_field('anonymous', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'recipients');

        // Conditionally launch add field anonymous.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Feedback360 savepoint reached.
        totara_upgrade_mod_savepoint(true, 2015090400, 'totara_feedback360');
    }

    if ($oldversion < 2015100201) {

        // Changing precision of field sortorder on table feedback360_quest_field to (10).
        $table = new xmldb_table('feedback360_quest_field');
        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'descriptionformat');

        // This field was indexed, we'll need to drop it and then re-index after updating the field.
        $index = new xmldb_index('feequesfiel_sor_ix', XMLDB_INDEX_NOTUNIQUE, array('sortorder'));

        // Conditionally launch drop index feequesfiel_sor_ix.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $dbman->change_field_precision($table, $field);

        // We know the index doesn't exist by this point, so no need to check for it first.
        $dbman->add_index($table, $index);

        totara_upgrade_mod_savepoint(true, 2015100201, 'totara_feedback360');
    }

    // TL-16443 Make all multichoice questions use int for param1.
    if ($oldversion < 2015100202) {

        totara_feedback360_upgrade_fix_inconsistent_multichoice_param1();

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2015100202, 'totara', 'feedback360');
    }

    return true;
}
