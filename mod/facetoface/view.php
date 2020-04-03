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
 * @package modules
 * @subpackage facetoface
 */

require_once '../../config.php';
require_once 'lib.php';
require_once 'renderer.php';

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$f = optional_param('f', 0, PARAM_INT); // facetoface ID
$location = optional_param('location', '', PARAM_RAW); // location
$roomid = optional_param('roomid', 0, PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA); // download attendance

if ($id) {
    if (!$cm = get_coursemodule_from_id('facetoface', $id)) {
        print_error('error:incorrectcoursemoduleid', 'facetoface');
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('error:coursemisconfigured', 'facetoface');
    }
    if (!$facetoface = $DB->get_record('facetoface', array('id' => $cm->instance))) {
        print_error('error:incorrectcoursemodule', 'facetoface');
    }
}
elseif ($f) {
    if (!$facetoface = $DB->get_record('facetoface', array('id' => $f))) {
        print_error('error:incorrectfacetofaceid', 'facetoface');
    }
    if (!$course = $DB->get_record('course', array('id' => $facetoface->course))) {
        print_error('error:coursemisconfigured', 'facetoface');
    }
    if (!$cm = get_coursemodule_from_instance('facetoface', $facetoface->id, $course->id)) {
        print_error('error:incorrectcoursemoduleid', 'facetoface');
    }
}
else {
    print_error('error:mustspecifycoursemodulefacetoface', 'facetoface');
}

$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/facetoface/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_pagelayout('standard');

// Check for auto nofication duplicates.
if (has_capability('moodle/course:manageactivities', $context)) {
    require_once($CFG->dirroot.'/mod/facetoface/notification/lib.php');
    if (facetoface_notification::has_auto_duplicates($facetoface->id)) {
        $url = new moodle_url('/mod/facetoface/notification/index.php', array('update' => $cm->id));
        totara_set_notification(get_string('notificationduplicatesfound', 'facetoface', $url->out()));
    }
}

if (!empty($download)) {
    require_capability('mod/facetoface:viewattendees', $context);
    facetoface_download_attendance($facetoface->name, $facetoface->id, $location, $download);
    exit();
}

require_login($course, true, $cm);
require_capability('mod/facetoface:view', $context);

$event = \mod_facetoface\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot($cm->modname, $facetoface);
$event->trigger();

$title = $course->shortname . ': ' . format_string($facetoface->name);

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'facetoface'));

$pagetitle = format_string($facetoface->name);

$f2f_renderer = $PAGE->get_renderer('mod_facetoface');

$completion=new completion_info($course);
$completion->set_module_viewed($cm);

echo $OUTPUT->header();

if (empty($cm->visible) and !has_capability('mod/facetoface:viewemptyactivities', $context)) {
    notice(get_string('activityiscurrentlyhidden'));
}
echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('allsessionsin', 'facetoface', $facetoface->name), 2);

if (!empty($facetoface->intro)) {
    echo $OUTPUT->box(format_module_intro('facetoface', $facetoface, $cm->id), 'generalbox', 'intro');
}

$locations = get_locations($facetoface->id);
if (count($locations) > 2) {

    echo html_writer::start_tag('form', array('action' => 'view.php', 'method' => 'post'));
    echo html_writer::start_tag('div') . html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'f', 'value' => $facetoface->id));
    echo html_writer::select($locations, 'location', $location, '');
    echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('showbylocation', 'facetoface')));
    echo html_writer::end_tag('div'). html_writer::end_tag('form');
}

$rooms = facetoface_get_rooms($facetoface->id);
if (count($rooms) > 1) {
    $roomselect = array(0 => get_string('allrooms', 'facetoface'));
    $onlyrooms = $roomselect;
    $notonlytrooms = false;
    foreach ($rooms as $rid => $room) {
        $roomname = format_string($room->name);
        if ($roomname === '') {
            $roomname = get_string('notspecified', 'facetoface');
        }
        $roomdetails = array();
        $building = format_string($room->building);
        if ($building === '') {
            $building = get_string('notspecified', 'facetoface');
        } else {
            $notonlytrooms = true;
        }
        $roomdetails[] = get_string('building', 'facetoface') . ': ' . $building;
        $address = format_string($room->address);
        if ($address === '') {
            $address = get_string('notspecified', 'facetoface');
        } else {
            $notonlytrooms = true;
        }
        $roomdetails[] = get_string('address', 'facetoface') . ': ' . $address;
        $roomdetails = implode(' - ', $roomdetails);
        $roomselect[sha1($roomdetails)][$roomdetails][$rid] = get_string('room', 'facetoface') . ': ' . $roomname;
        $onlyrooms[$rid] = $roomname;
    }
    if (!$notonlytrooms) {
        // There are only N/As in buildings and rooms, let's just show room names.
        $roomselect = $onlyrooms;
    }

    echo $OUTPUT->single_select($PAGE->url, 'roomid', $roomselect, $roomid, null, null, array('label' => get_string('filterbyroom', 'facetoface')));
}

$sessions = facetoface_get_sessions($facetoface->id, $location, $roomid);
print_session_list($course->id, $facetoface, $sessions);

if (has_capability('mod/facetoface:viewattendees', $context)) {
    echo html_writer::start_tag('form', array('action' => 'view.php', 'method' => 'get'));
    echo html_writer::start_tag('div') . html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'f', 'value' => $facetoface->id));
    echo get_string('exportattendance', 'facetoface') . '&nbsp;';
    $formats = array(0 => get_string('format', 'mod_facetoface'),
                    'excel' => get_string('excelformat', 'facetoface'),
                    'ods' => get_string('odsformat', 'facetoface'));
    echo html_writer::select($formats, 'download', 0, '');
    echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('exporttofile', 'facetoface')));
    echo html_writer::end_tag('div'). html_writer::end_tag('form');
}

echo $OUTPUT->box_end();

$alreadydeclaredinterest = facetoface_user_declared_interest($facetoface);
if ($alreadydeclaredinterest || facetoface_activity_can_declare_interest($facetoface)) {
    if ($alreadydeclaredinterest) {
        $strbutton = get_string('declareinterestwithdraw', 'mod_facetoface');
    } else {
        $strbutton = get_string('declareinterest', 'mod_facetoface');
    }
    $url = new moodle_url('/mod/facetoface/interest.php', array('f' => $facetoface->id));
    echo $OUTPUT->single_button($url, $strbutton, 'get');
}

echo $OUTPUT->footer($course);

function print_session_list($courseid, $facetoface, $sessions) {
    global $CFG, $USER, $DB, $OUTPUT, $PAGE;

    $f2f_renderer = $PAGE->get_renderer('mod_facetoface');

    $timenow = time();

    $cm = get_coursemodule_from_instance('facetoface', $facetoface->id, $courseid, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $f2f_renderer->setcontext($context);
    $viewattendees = has_capability('mod/facetoface:viewattendees', $context);
    $editsessions = has_capability('mod/facetoface:editsessions', $context);

    $bookedsession = null;
    $submissions = facetoface_get_user_submissions($facetoface->id, $USER->id);
    if (!$facetoface->multiplesessions) {
         $submission = array_shift($submissions);
         $bookedsession = $submission;
    }

    $upcomingarray = array();
    $previousarray = array();
    $upcomingtbdarray = array();

    if ($sessions) {
        foreach ($sessions as $session) {

            $sessionstarted = false;
            $sessionfull = false;
            $sessionwaitlisted = false;
            $isbookedsession = false;

            $sessiondata = $session;
            if ($facetoface->multiplesessions) {
                $submission = facetoface_get_user_submissions($facetoface->id, $USER->id,
                        MDL_F2F_STATUS_REQUESTED, MDL_F2F_STATUS_FULLY_ATTENDED, $session->id);
                $bookedsession = array_shift($submission);
            }
            $sessiondata->bookedsession = $bookedsession;

            if ($session->roomid) {
                $room = $DB->get_record('facetoface_room', array('id' => $session->roomid));
                $sessiondata->room = $room;
            }

            // Is session waitlisted
            if (!$session->datetimeknown) {
                $sessionwaitlisted = true;
            }

            // Check if session is started
            if ($session->datetimeknown && facetoface_has_session_started($session, $timenow) && facetoface_is_session_in_progress($session, $timenow)) {
                $sessionstarted = true;
            }
            elseif ($session->datetimeknown && facetoface_has_session_started($session, $timenow)) {
                $sessionstarted = true;
            }

            // Put the row in the right table
            if ($sessionstarted) {
                $previousarray[] = $sessiondata;
            }
            elseif ($sessionwaitlisted) {
                $upcomingtbdarray[] = $sessiondata;
            }
            else { // Normal scheduled session
                $upcomingarray[] = $sessiondata;
            }
        }
    }

    $displaytimezones = get_config(null, 'facetoface_displaysessiontimezones');

    // Upcoming sessions
    echo $OUTPUT->heading(get_string('upcomingsessions', 'facetoface'));
    if (empty($upcomingarray) && empty($upcomingtbdarray)) {
        print_string('noupcoming', 'facetoface');
    }
    else {
        $upcomingarray = array_merge($upcomingarray, $upcomingtbdarray);
        $reserveinfo = array();
        if (!empty($facetoface->managerreserve)) {
            // Include information about reservations when drawing the list of sessions.
            $reserveinfo = facetoface_can_reserve_or_allocate($facetoface, $sessions, $context);
            echo html_writer::tag('p', get_string('lastreservation', 'mod_facetoface', $facetoface));
        }
        echo $f2f_renderer->print_session_list_table($upcomingarray, $viewattendees, $editsessions, $displaytimezones, $reserveinfo);
    }

    if ($editsessions) {
        echo html_writer::tag('p', html_writer::link(new moodle_url('sessions.php', array('f' => $facetoface->id)), get_string('addsession', 'facetoface')));
    }

    // Previous sessions
    if (!empty($previousarray)) {
        echo $OUTPUT->heading(get_string('previoussessions', 'facetoface'));
        echo $f2f_renderer->print_session_list_table($previousarray, $viewattendees, $editsessions, $displaytimezones);
    }
}

/**
 * Get facetoface locations
 *
 * @param   interger    $facetofaceid
 * @return  array
 */
function get_locations($facetofaceid) {
    global $DB;

    $locationmenu = array();
    $locationfieldid = $DB->get_field('facetoface_session_info_field', 'id', array('shortname' => 'location'));
    if (!$locationfieldid) {
        return $locationmenu;
    }

    $sql = "SELECT DISTINCT d.data AS location
              FROM {facetoface} f
              JOIN {facetoface_sessions} s ON s.facetoface = f.id
              JOIN {facetoface_session_info_data} d ON d.facetofacesessionid = s.id
             WHERE f.id = ? AND d.fieldid = ?";

    if ($records = $DB->get_records_sql($sql, array($facetofaceid, $locationfieldid))) {
        $locationmenu[''] = get_string('alllocations', 'facetoface');

        foreach ($records as $record) {
            $value = $record->location;
            $location = function() use ($value) {
                $value = strip_tags($value, '<br>');
                $value = nl2br($value);
                $value = preg_replace('#<br\s*/?>#i', ", ", $value);
                return $value;
            };
            $locationmenu[$value] = $location();
        }
    }
    return $locationmenu;
}

