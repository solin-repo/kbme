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
 * @package totara
 * @subpackage reportbuilder
 */

/**
 * Add reportbuilder administration menu settings
 */

$ADMIN->add('reports', new admin_category('totara_reportbuilder', get_string('reportbuilder','totara_reportbuilder')), 'comments');

// Main report builder settings.
$rb = new admin_settingpage('rbsettings',
                            new lang_string('globalsettings','totara_reportbuilder'),
                            array('totara/reportbuilder:managereports'));

if ($ADMIN->fulltree) {
    $rb->add(new totara_reportbuilder_admin_setting_configexportoptions());

    $rb->add(new admin_setting_configcheckbox('reportbuilder/exporttofilesystem', new lang_string('exporttofilesystem', 'totara_reportbuilder'),
        new lang_string('reportbuilderexporttofilesystem_help', 'totara_reportbuilder'), false));

    $rb->add(new admin_setting_configdirectory('reportbuilder/exporttofilesystempath', new lang_string('exportfilesystempath', 'totara_reportbuilder'),
        new lang_string('exportfilesystempath_help', 'totara_reportbuilder'), ''));

    $rb->add(new totara_reportbuilder_admin_setting_configdaymonthpicker('reportbuilder/financialyear', new lang_string('financialyear', 'totara_reportbuilder'),
        new lang_string('reportbuilderfinancialyear_help', 'totara_reportbuilder'), array('d' => 1, 'm' => 7)));

    // NOTE: for performance reasons do not use constants here.
    $options = array(
        0 => get_string('noactiverestrictionsbehaviournone', 'totara_reportbuilder'), // == rb_global_restriction_set::NO_ACTIVE_NONE
        1 => get_string('noactiverestrictionsbehaviourall', 'totara_reportbuilder'),  // == rb_global_restriction_set::NO_ACTIVE_ALL
    );
    $rb->add(new admin_setting_configselect('reportbuilder/noactiverestrictionsbehaviour',
        new lang_string('noactiverestrictionsbehaviour', 'totara_reportbuilder'),
        new lang_string('noactiverestrictionsbehaviour_desc', 'totara_reportbuilder'),
        1, $options));

    // NOTE: do not use constants here for performance reasons.
    //  0 == reportbuilder::GLOBAL_REPORT_RESTRICTIONS_DISABLED
    //  1 == reportbuilder::GLOBAL_REPORT_RESTRICTIONS_ENABLED
    $rb->add(new admin_setting_configcheckbox('reportbuilder/globalrestrictiondefault',
        new lang_string('globalrestrictiondefault', 'totara_reportbuilder'),
        new lang_string('globalrestrictiondefault_desc', 'totara_reportbuilder'), 1));

    $rb->add(new admin_setting_configtext('reportbuilder/globalrestrictionrecordsperpage',
        new lang_string('globalrestrictionrecordsperpage', 'totara_reportbuilder'),
        new lang_string('globalrestrictionrecordsperpage_desc', 'totara_reportbuilder'), 40, PARAM_INT));

    $rb->add(
        new admin_setting_configcheckbox(
            'totara_reportbuilder/allowtotalcount',
            new lang_string('allowtotalcount', 'totara_reportbuilder'),
            new lang_string('allowtotalcount_desc', 'totara_reportbuilder'),
            0,
            PARAM_INT
        )
    );
}

// Add all above settings to the report builder settings node.
$ADMIN->add('totara_reportbuilder', $rb);

// Add links to Global Reports Restrictions.
$ADMIN->add('totara_reportbuilder', new admin_externalpage('rbmanageglobalrestrictions', new lang_string('manageglobalrestrictions','totara_reportbuilder'),
    new moodle_url('/totara/reportbuilder/restrictions/index.php'), array('totara/reportbuilder:managereports'), empty($CFG->enableglobalrestrictions)));

// Add links to report builder reports.
$ADMIN->add('totara_reportbuilder', new admin_externalpage('rbmanagereports', new lang_string('managereports','totara_reportbuilder'),
            new moodle_url('/totara/reportbuilder/index.php'), array('totara/reportbuilder:managereports')));
