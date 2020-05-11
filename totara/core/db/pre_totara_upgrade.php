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
 * @author Ciaran Irvine <ciaran.irvine@totaralms.com>
 * @package totara_core
 */

/*
 * This file is executed before any upgrade of Totara site.
 * This file is not executed during initial installation or upgrade from vanilla Moodle.
 *
 * Note that Totara 1.x and 2.2.x testes are in lib/setup.php, we can get here only from higher versions.
 */

defined('MOODLE_INTERNAL') || die();
global $OUTPUT, $DB, $CFG, $TOTARA;

require_once ("$CFG->dirroot/totara/core/db/utils.php");

$dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.
$success = get_string('success');

// Check unique idnumbers in totara tables before we start upgrade.
// Do not upgrade lang packs yet so that they can go back to previous version!
if ($CFG->version < 2013051402.00) { // Upgrade from 2.4.x or earlier.
    $duplicates = totara_get_nonunique_idnumbers();
    if (!empty($duplicates)) {
        $duplicatestr = '';
        foreach ($duplicates as $duplicate) {
            $duplicatestr .= get_string('idnumberduplicates', 'totara_core', $duplicate) . '<br/>';
        }
        throw new moodle_exception('totarauniqueidnumbercheckfail', 'totara_core', '', $duplicatestr);
    }
    echo $OUTPUT->notification(get_string('totaraupgradecheckduplicateidnumbers', 'totara_core'), 'notifysuccess');
}

// Always update all language packs if we can, because they are used in Totara upgrade/install scripts.
totara_upgrade_installed_languages();

// Migrate badge capabilities to Moodle core.
if ($CFG->version < 2013051402.00) { // Upgrade from 2.4.x or earlier.
    $DB->set_field_select('capabilities', 'component', 'moodle', "component = 'totara_core' AND name LIKE 'moodle/badges:%'");
}

// Add custom Totara completion field to prevent fatal problems during upgrade.
if ($CFG->version < 2013111802.00) { // Upgrade from Totara 2.5.x or earlier.
    $table = new xmldb_table('course_completions');
    $field = new xmldb_field('invalidatecache', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'reaggregate');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
}

// One-off fix for incorrect default setting from Moodle.
if (!empty($CFG->totara_build) and $CFG->totara_build < '20181026.00') {
    if (!get_config('scorm', 'protectpackagedownloads')) {
        unset_config('protectpackagedownloads', 'scorm');
    }
}
