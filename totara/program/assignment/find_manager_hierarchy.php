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
 * @author Jake Salmon <jake.salmon@kineo.com>
 * @package totara
 * @subpackage program
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/totara/core/dialogs/dialog_content_manager.class.php');
require_once($CFG->dirroot . '/totara/program/lib.php');

$PAGE->set_context(context_system::instance());
require_login();

// Get program id and check capabilities.
$programid = required_param('programid', PARAM_INT);
require_capability('totara/program:configureassignments', program_get_context($programid));

// Parent id
$parentid = optional_param('parentid', 0, PARAM_INT);

// Only return generated tree html
$treeonly = optional_param('treeonly', false, PARAM_BOOL);


// Get ids of already selected items.
$selected = optional_param('selected', array(), PARAM_SEQUENCE);
$removed = optional_param('removed', array(), PARAM_SEQUENCE);

$selectedids = totara_prog_removed_selected_ids($programid, $selected, $removed, ASSIGNTYPE_MANAGER);

$allselected = array();
$usernamefields = get_all_user_name_fields(true);
if (!empty($selectedids)) {
    list($selectedsql, $selectedparams) = $DB->get_in_or_equal($selectedids);
    // Query user table so we can get names of managers already selected.
    $allselected = $DB->get_records_select('user', "id {$selectedsql}", $selectedparams, '', ' id,' . $usernamefields);
}

foreach ($allselected as $manager) {
    $manager->fullname = fullname($manager);
}

// Don't let them remove the currently selected ones.
$unremovable = $allselected;

$dialog = new totara_dialog_content_manager();

// Toggle treeview only display
$dialog->show_treeview_only = $treeonly;

// Load items to display
$dialog->load_items($parentid);

// Set disabled/selected items.
$dialog->selected_items = $allselected;

// Set unremovable items.
$dialog->unremovable_items = $unremovable;

// Set title.
$dialog->selected_title = 'itemstoadd';

$dialog->select_title = '';

// Display page
echo $dialog->generate_markup();
