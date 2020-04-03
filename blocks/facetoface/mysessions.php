<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2009 Catalyst IT LTD
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
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @author Francois Marier <francois@catalyst.net.nz>
 * @package blocks
 * @subpackage facetoface
 */

// Displays sessions for which the current user is a "teacher" (can see attendees' list)
// as well as the ones where the user is signed up (i.e. a "student")

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('lib.php');
require_once('sessionfilter_form.php');
require_once('export_form.php');

$PAGE->set_context(context_system::instance());
require_login();

$userid     = optional_param('userid',     $USER->id, PARAM_INT);
$allfuture  = optional_param('allfuture',  false, PARAM_BOOL);

// filter options
$courseid     = optional_param('courseid',   '', PARAM_TEXT);
$locationid   = optional_param('locationid',  0, PARAM_INT);

$filter_form = new sessionfilter_form(null, array('allfuture' => $allfuture, 'userid' => $userid));

if (!($data = $filter_form->get_data())) {
    $data = new stdClass();
    $data->from = time();
    $data->to = strtotime('+3 month');
}
if ($courseid) {
    $data->courseid = $courseid;
}

$export_form = new export_form(null, convert_to_array($data));
if ($export_data = $export_form->get_data()) {
    $data = $export_data;
}

$records = get_sessions($data);
$show_location = add_location_info($records);

// Only keep the sessions for which this user can see attendees
$dates = array();
if ($records) {
    // Check the system context if an user allows to view the sessions.
    $contextsystem = context_system::instance();
    $viewsessions = 'mod/facetoface:view';
    $viewsessionscapability  = has_capability($viewsessions, $contextsystem);

    // If the user allows to view a number of attendees in these sessions at the system context.
    $viewattendees = 'mod/facetoface:viewattendees';
    $viewattendeescapability = has_capability($viewattendees, $contextsystem);

    foreach ($records as $record) {
        $contextcourse = context_course::instance($record->courseid);
        $contextmodule = context_module::instance($record->cmid);
        // Check the capability at system context/course/module levels.
        if ($viewsessionscapability ||
            has_capability($viewsessions, $contextcourse) ||
            has_capability($viewsessions, $contextmodule)) {
                // If the user allows to view a number of attendees in these sessions at the course/module levels.
                $record->viewattendees = $viewattendeescapability ||
                                         has_capability($viewattendees, $contextcourse) ||
                                         has_capability($viewattendees, $contextmodule);
                if ($locationid > 0) {
                    if ((int)$record->locationid === $locationid) {
                        $dates[] = $record;
                    }
                } else {
                    $dates[] = $record;
                }
        }
    }

}
$nbdates = count($dates);

// Process actions if any
if ($export_data) {
    export_spreadsheet($dates, $export_data->format, true);
    exit;
}

// format the session and dates to only show one booking where they span multiple dates
// i.e. multiple days startdate = firstday, finishdate = last day
$groupeddates = group_session_dates($dates);

$pagetitle = format_string(get_string('facetoface', 'facetoface') . ' ' . get_string('sessions', 'block_facetoface'));
$PAGE->navbar->add($pagetitle);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_url('/blocks/facetoface/mysessions.php');
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->box_start();

// show tabs
$currenttab = 'attendees';
include_once('tabs.php');

$renderer = $PAGE->get_renderer('block_facetoface');

if (empty($users)) {
    // Date range form
    echo $OUTPUT->heading(get_string('filters', 'block_facetoface'), 2);
    $filter_form->display();
}

// Show all session dates
if ($nbdates > 0) {
    echo $OUTPUT->heading(get_string('sessiondatesview', 'block_facetoface'), 2);
    echo $renderer->print_dates($groupeddates, true, false, false, false, false, $show_location);

    // Export form
    $export_form->display();

} else {
    echo $OUTPUT->heading(get_string('sessiondatesview', 'block_facetoface'), 2);
    echo html_writer::tag('p', get_string('sessiondatesviewattendeeszero', 'block_facetoface'));
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

