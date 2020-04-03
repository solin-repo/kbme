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
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @author Aaron Barnes <aaron.barnes@totaralms.com>
 * @author Francois Marier <francois@catalyst.net.nz>
 * @package modules
 * @subpackage facetoface
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('lib.php');
require_once('session_form.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$f = optional_param('f', 0, PARAM_INT); // facetoface Module ID
$s = optional_param('s', 0, PARAM_INT); // facetoface session ID
$c = optional_param('c', 0, PARAM_INT); // copy session
$d = optional_param('d', 0, PARAM_INT); // delete session
$confirm = optional_param('confirm', false, PARAM_BOOL); // delete confirmation

$session = null;
if ($id && !$s) {
    if (!$cm = get_coursemodule_from_id('facetoface', $id)) {
        print_error('error:incorrectcoursemoduleid', 'facetoface');
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('error:coursemisconfigured', 'facetoface');
    }
    if (!$facetoface =$DB->get_record('facetoface',array('id' => $cm->instance))) {
        print_error('error:incorrectcoursemodule', 'facetoface');
    }
} else if ($s) {
     if (!$session = facetoface_get_session($s)) {
         print_error('error:incorrectcoursemodulesession', 'facetoface');
     }
     if (!$facetoface = $DB->get_record('facetoface',array('id' => $session->facetoface))) {
         print_error('error:incorrectfacetofaceid', 'facetoface');
     }
     if (!$course = $DB->get_record('course', array('id'=> $facetoface->course))) {
         print_error('error:coursemisconfigured', 'facetoface');
     }
     if (!$cm = get_coursemodule_from_instance('facetoface', $facetoface->id, $course->id)) {
         print_error('error:incorrectcoursemoduleid', 'facetoface');
     }
     if (!$session->roomid == 0 && !$sroom = $DB->get_record('facetoface_room', array('id' => $session->roomid))) {
        print_error('error:incorrectroomid', 'facetoface');
     }

} else {
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
$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/facetoface:editsessions', $context);

$errorstr = '';


local_js(array(
    TOTARA_JS_DIALOG,
    TOTARA_JS_TREEVIEW
));
$PAGE->set_url('/mod/facetoface/sessions.php', array('f' => $f));
$PAGE->requires->string_for_js('save', 'totara_core');
$PAGE->requires->string_for_js('error:addpdroom-dialognotselected', 'totara_core');
$PAGE->requires->strings_for_js(array('cancel', 'ok'), 'moodle');
$PAGE->requires->strings_for_js(array('chooseroom', 'pdroomcapacityexceeded'), 'facetoface');

$display_selected = dialog_display_currently_selected(get_string('selected', 'facetoface'), 'addpdroom-dialog');
$jsconfig = array('sessionid' => $s, 'display_selected_item' => $display_selected, 'facetofaceid' => $facetoface->id);
$jsmodule = array(
    'name' => 'totara_f2f_room',
    'fullpath' => '/mod/facetoface/sessions.js',
    'requires' => array('json', 'totara_core'));
$PAGE->requires->js_init_call('M.totara_f2f_room.init', array($jsconfig), false, $jsmodule);
$PAGE->requires->js_init_call('M.facetoface_datelinkage.init', null, false, $jsmodule);

$returnurl = "view.php?f=$facetoface->id";

$editoroptions = array(
    'noclean'  => false,
    'maxfiles' => EDITOR_UNLIMITED_FILES,
    'maxbytes' => $course->maxbytes,
    'context'  => $context,
);

// Handle deletions
if ($d and $confirm) {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', 'error');
    }

    if (facetoface_delete_session($session)) {
        \mod_facetoface\event\session_deleted::create_from_session($session, $context)->trigger();
    } else {
        print_error('error:couldnotdeletesession', 'facetoface', $returnurl);
    }
    redirect($returnurl);
}

$sessionid = isset($session->id) ? $session->id : 0;

$canconfigurecancellation = has_capability('mod/facetoface:configurecancellation', $context);

$defaulttimezone = '99';
if (!isset($session)) {
    $sessiondata = new stdClass();
    $sessiondata->id = 0;
    $sessiondata->allowcancellations = $facetoface->allowcancellationsdefault;
    $sessiondata->cancellationcutoff = $facetoface->cancellationscutoffdefault;
    $nbdays = 1;

} else {
    if (!empty($session->sessiondates[0]->sessiontimezone) and $session->sessiondates[0]->sessiontimezone != '99') {
        $defaulttimezone = core_date::normalise_timezone($session->sessiondates[0]->sessiontimezone);
    }
    // Load custom fields data for the session.
    customfield_load_data($session, 'facetofacesession', 'facetoface_session');

    // Set values for the form and unset some values that will be evaluated later.
    $sessiondata = clone($session);
    if (isset($sessiondata->sessiondates)) {
        unset($sessiondata->sessiondates);
    }

    if (isset($sessiondata->roomid)) {
        unset($sessiondata->roomid);
    }

    $sessiondata->detailsformat = FORMAT_HTML;
    $editoroptions = $TEXTAREA_OPTIONS;
    $editoroptions['context'] = $context;
    $sessiondata = file_prepare_standard_editor($sessiondata, 'details', $editoroptions, $editoroptions['context'],
        'mod_facetoface', 'session', $session->id);

    $sessiondata->datetimeknown = (int)(1 == $session->datetimeknown);

    $nbdays = count($session->sessiondates);
    if ($session->sessiondates) {
        $i = 0;
        foreach ($session->sessiondates as $date) {
            $idfield = "sessiondateid[$i]";
            $timestartfield = "timestart[$i]";
            $timefinishfield = "timefinish[$i]";
            $timezonefield = "sessiontimezone[$i]";

            if ($date->sessiontimezone === '') {
                $date->sessiontimezone = '99';
            } else if ($date->sessiontimezone != 99) {
                $date->sessiontimezone = core_date::normalise_timezone($date->sessiontimezone);
            }

            $sessiondata->$idfield = $date->id;
            $sessiondata->$timestartfield = $date->timestart;
            $sessiondata->$timefinishfield = $date->timefinish;
            $sessiondata->$timezonefield = $date->sessiontimezone;
            $i++;
        }
    }

    if (!empty($sroom->id)) {
        if (!$sroom->custom) {
            // Pre-defined room
            $sessiondata->pdroomid = $session->roomid;
            $sessiondata->pdroomcapacity = $sroom->capacity;
        } else {
            // Custom room
            $sessiondata->customroom = 1;
            $sessiondata->croomname = $sroom->name;
            $sessiondata->croombuilding = $sroom->building;
            $sessiondata->croomaddress = $sroom->address;
            $sessiondata->croomcapacity = $sroom->capacity;
        }
    }

    if ($session->mincapacity) {
        $sessiondata->enablemincapacity = 1;
    }
}

$mform = new mod_facetoface_session_form(null, compact('id', 'f', 's', 'c', 'session', 'nbdays', 'course', 'editoroptions', 'defaulttimezone', 'facetoface', 'cm', 'sessiondata'));
if ($mform->is_cancelled()) {
    redirect($returnurl);
}

if ($fromform = $mform->get_data()) { // Form submitted

    if (empty($fromform->submitbutton)) {
        print_error('error:unknownbuttonclicked', 'facetoface', $returnurl);
    }

    // Pre-process fields
    if (empty($fromform->allowoverbook)) {
        $fromform->allowoverbook = 0;
    }
    if (empty($fromform->waitlisteveryone)) {
        $fromform->waitlisteveryone = 0;
    }
    if (empty($fromform->normalcost)) {
        $fromform->normalcost = 0;
    }
    if (empty($fromform->discountcost)) {
        $fromform->discountcost = 0;
    }
    if (empty($fromform->selfapproval)) {
        $fromform->selfapproval = 0;
    }
    if (empty($fromform->availablesignupnote)) {
        $fromform->availablesignupnote = 0;
    }

    //check dates and calculate total duration
    $sessiondates = array();
    if ($fromform->datetimeknown === '1') {
        $fromform->duration = 0;
    }
    for ($i = 0; $i < $fromform->date_repeats; $i++) {
        if (!empty($fromform->datedelete[$i])) {
            continue; // skip this date
        }
        $timestartfield = "timestart[$i]";
        $timefinishfield = "timefinish[$i]";
        if (!empty($fromform->$timestartfield) && !empty($fromform->$timefinishfield)) {
            $date = new stdClass();
            $date->sessiontimezone = $fromform->sessiontimezone[$i];
            $date->timestart = $fromform->$timestartfield;
            $date->timefinish = $fromform->$timefinishfield;
            if ($fromform->datetimeknown === '1') {
                $fromform->duration += ($date->timefinish - $date->timestart);
            }
            $sessiondates[] = $date;
        }
    }

    $todb = new stdClass();
    $todb->facetoface = $facetoface->id;
    $todb->datetimeknown = $fromform->datetimeknown;
    $todb->capacity = $fromform->capacity;
    $todb->allowoverbook = $fromform->allowoverbook;
    $todb->waitlisteveryone = $fromform->waitlisteveryone;
    $todb->duration = $fromform->duration;
    $todb->normalcost = $fromform->normalcost;
    $todb->discountcost = $fromform->discountcost;
    $todb->usermodified = $USER->id;
    $todb->roomid = (isset($session->roomid)) ? $session->roomid : 0;
    $todb->selfapproval = $facetoface->approvalreqd ? $fromform->selfapproval : 0;
    $todb->availablesignupnote = $fromform->availablesignupnote;

    // If min capacity is not provided or unset default to 0.
    if (empty($fromform->enablemincapacity) || $fromform->mincapacity < 0) {
        $fromform->mincapacity = 0;
    }

    $todb->mincapacity = $fromform->mincapacity;
    $todb->cutoff = $fromform->cutoff;

    if ($canconfigurecancellation) {
        $todb->allowcancellations = $fromform->allowcancellations;
        $todb->cancellationcutoff = $fromform->cancellationcutoff;
    } else {
        if ($session) {
            $todb->allowcancellations = $session->allowcancellations;
            $todb->cancellationcutoff = $session->cancellationcutoff;
        } else {
            $todb->allowcancellations = $facetoface->allowcancellationsdefault;
            $todb->cancellationcutoff = $facetoface->cancellationscutoffdefault;
        }
    }

    $transaction = $DB->start_delegated_transaction();

    $update = false;
    if (!$c and $session != null) {
        $update = true;
        $todb->id = $session->id;
        $sessionid = $session->id;
        $olddates = $DB->get_records('facetoface_sessions_dates', array('sessionid' => $session->id), 'timestart');
        if (!facetoface_update_session($todb, $sessiondates)) {
            print_error('error:couldnotupdatesession', 'facetoface', $returnurl);
        }
    } else {
        // If we are duplicating a session with a custom room, clear the roomid.
        if ($c && !empty($fromform->customroom)) {
            $todb->roomid = 0;
        }

        // Create or Duplicate the session.
        if (!$sessionid = facetoface_add_session($todb, $sessiondates)) {
            print_error('error:couldnotaddsession', 'facetoface', $returnurl);
        }
    }

    // Save session room info.
    if (!facetoface_save_session_room($sessionid, $fromform)) {
        print_error('error:couldnotsaveroom', 'facetoface');
    }
    $fromform->id = $sessionid;
    customfield_save_data($fromform, 'facetofacesession', 'facetoface_session');

    $transaction->allow_commit();

    // Retrieve record that was just inserted/updated.
    if (!$session = facetoface_get_session($sessionid)) {
        print_error('error:couldnotfindsession', 'facetoface', $returnurl);
    }

    if ($update) {
        // Now that we have updated the session record fetch the rest of the data we need.
        facetoface_update_attendees($session);

        // Get datetimeknown value from form.
        $datetimeknown = $fromform->datetimeknown == 1;
    }

    // Get details.
    // This should be done before sending any notification as it could be a required field in their template.
    $data = file_postupdate_standard_editor($fromform, 'details', $editoroptions, $context, 'mod_facetoface', 'session', $session->id);
    $session->details = $data->details;
    $DB->set_field('facetoface_sessions', 'details', $data->details, array('id' => $session->id));

    // Save trainer roles.
    if (isset($fromform->trainerrole)) {
        facetoface_update_trainers($facetoface, $session, $fromform->trainerrole);
    }

    // Save any calendar entries.
    $session->sessiondates = $sessiondates;
    facetoface_update_calendar_entries($session, $facetoface);

    if ($update) {
        // Check whether we are dealing with a custom room.
        $oldcustomroom = isset($sroom) && $sroom->custom && isset($fromform->customroom) ? $sroom : false;

        // If we are passing new custom room details to the function to save us a trip to the database.
        $customroomdetails = $oldcustomroom ? (object) array(
            'name' => $fromform->croomname,
            'building' => $fromform->croombuilding,
            'address' => $fromform->croomaddress,
            'capacity' => $fromform->croomcapacity,
        ) : false;

        // Send any necessary datetime change notifications but only if date/time is known.
        if ($datetimeknown && (facetoface_session_dates_check($olddates, $sessiondates)  ||
            facetoface_session_room_check($todb->roomid, $session->roomid, $oldcustomroom, $customroomdetails))) {
            $attendees = facetoface_get_attendees($session->id);
            foreach ($attendees as $user) {
                facetoface_send_datetime_change_notice($facetoface, $session, $user->id, $olddates);
            }
        }

        \mod_facetoface\event\session_updated::create_from_session($session, $context)->trigger();
    } else {
        \mod_facetoface\event\session_created::create_from_session($session, $context)->trigger();
    }

    redirect($returnurl);
}

if ($c) {
    $heading = get_string('copyingsession', 'facetoface', $facetoface->name);
}
else if ($d) {
    $heading = get_string('deletingsession', 'facetoface', $facetoface->name);
}
else if ($id or $f) {
    $heading = get_string('addingsession', 'facetoface', $facetoface->name);
}
else {
    $heading = get_string('editingsession', 'facetoface', $facetoface->name);
}

$pagetitle = format_string($facetoface->name);

$PAGE->set_cm($cm);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

echo $OUTPUT->box_start();
echo $OUTPUT->heading($heading);

if (!empty($errorstr)) {
    echo $OUTPUT->container(html_writer::tag('span', $errorstr, array('class' => 'errorstring')), array('class' => 'notifyproblem'));
}

if ($d) {
    $viewattendees = has_capability('mod/facetoface:viewattendees', $context);
    facetoface_print_session($session, $viewattendees);
    $optionsyes = array('sesskey' => sesskey(), 's' => $session->id, 'd' => 1, 'confirm' => 1);
    echo $OUTPUT->confirm(get_string('deletesessionconfirm', 'facetoface', format_string($facetoface->name)),
        new moodle_url('sessions.php', $optionsyes),
        new moodle_url($returnurl));
}
else {
    $mform->display();
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer($course);
