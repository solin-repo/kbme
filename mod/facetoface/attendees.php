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
 * @author Francois Marier <francois@catalyst.net.nz>
 * @author Aaron Barnes <aaronb@catalyst.net.nz>
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package totara
 * @subpackage facetoface
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/facetoface/lib.php');
require_once($CFG->dirroot.'/mod/facetoface/attendees_message_form.php');
require_once($CFG->libdir.'/totaratablelib.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

/**
 * Load and validate base data
 */
// Face-to-face session ID
$s = required_param('s', PARAM_INT);
// Take attendance
$takeattendance    = optional_param('takeattendance', false, PARAM_BOOL);
// Cancel request
$cancelform        = optional_param('cancelform', false, PARAM_BOOL);
// Action being performed
$action            = optional_param('action', 'attendees', PARAM_ALPHA);
// Only return content
$onlycontent        = optional_param('onlycontent', false, PARAM_BOOL);
// export download
$download = optional_param('download', '', PARAM_ALPHA);
// If approval requests have been updated, show a success message.
$approved = optional_param('approved', 0, PARAM_INT);

// Load data
if (!$session = facetoface_get_session($s)) {
    print_error('error:incorrectcoursemodulesession', 'facetoface');
}
if (!$facetoface = $DB->get_record('facetoface', array('id' => $session->facetoface))) {
    print_error('error:incorrectfacetofaceid', 'facetoface');
}
if (!$course = $DB->get_record('course', array('id' => $facetoface->course))) {
    print_error('error:coursemisconfigured', 'facetoface');
}
if (!$cm = get_coursemodule_from_instance('facetoface', $facetoface->id, $course->id)) {
    print_error('error:incorrectcoursemodule', 'facetoface');
}
$context = context_module::instance($cm->id);

if ($action == 'approvalrequired') {
    // Allow managers to be able to approve staff without being enrolled in the course.
    require_login();
} else {
    require_login($course, false, $cm);
}

// Setup urls
$baseurl = new moodle_url('/mod/facetoface/attendees.php', array('s' => $session->id));

/**
 * Capability checks to see if the current user can view this page
 *
 * This page is a bit of a special case in this respect as there are four uses for this page.
 *
 * 1) Viewing attendee list
 *   - Requires mod/facetoface:viewattendees capability in the course
 *
 * 2) Viewing cancellation list
 *   - Requires mod/facetoface:viewcancellations capability in the course
 *
 * 3) Taking attendance
 *   - Requires mod/facetoface:takeattendance capabilities in the course
 *
 * 4) A manager approving his staff's booking requests
 *   - Manager does not neccesarily have any capabilities in this course
 *   - Show only attendees who are also the manager's staff
 *   - Show only staff awaiting approval
 *   - Show any staff who have cancelled
 *   - Shouldn't throw an error if there are previously declined attendees
 */
// Allowed actions are actions the user has permissions to do
$allowed_actions = array();
// Available actions are actions that have a point. e.g. view the cancellations page whhen there are no cancellations is not an "available" action, but it maybe be an "allowed" action
$available_actions = array();

$PAGE->set_context($context);
$PAGE->set_url('/mod/facetoface/atendees.php', array('s' => $s));

// Actions the user can perform
$has_attendees = facetoface_get_num_attendees($s);

if (has_capability('mod/facetoface:viewattendees', $context)) {
    $allowed_actions[] = 'attendees';
    $allowed_actions[] = 'waitlist';
    $allowed_actions[] = 'addattendees';
    $available_actions[] = 'attendees';

    if (facetoface_get_users_by_status($s, MDL_F2F_STATUS_WAITLISTED)) {
        $available_actions[] = 'waitlist';
    }
}

if (has_capability('mod/facetoface:viewcancellations', $context)) {
    $allowed_actions[] = 'cancellations';

    if (facetoface_get_users_by_status($s, MDL_F2F_STATUS_USER_CANCELLED)) {
        $available_actions[] = 'cancellations';
    }
}

if (has_capability('mod/facetoface:takeattendance', $context)) {
    $allowed_actions[] = 'takeattendance';
    $allowed_actions[] = 'messageusers';

    if ($has_attendees && $session->datetimeknown && facetoface_has_session_started($session, time())) {
        $available_actions[] = 'takeattendance';
    }

    if (in_array('attendees', $available_actions) || in_array('cancellations', $available_actions) || in_array('waitlist', $available_actions)) {
        $available_actions[] = 'messageusers';
    }
}

$includeattendeesnote = (has_any_capability(array('mod/facetoface:viewattendeesnote', 'mod/facetoface:manageattendeesnote'), $context));

$attendees = array();
$cancellations = array();
$requests = array();

$staff = null;
if ($facetoface->approvalreqd) {
    $selectpositiononsignupglobal = get_config(null, 'facetoface_selectpositiononsignupglobal');
    if (!empty($selectpositiononsignupglobal) && !empty($facetoface->selectpositiononsignup)) {
        // Check if the user is manager of a position selected by staff signed up to this session.
        $requestssql = "SELECT fs.userid FROM {facetoface_signups} fs
                      JOIN {pos_assignment} pa ON fs.positionassignmentid = pa.id
                     WHERE sessionid = :sessionid AND managerid = :managerid";
        $params = array('sessionid' => $session->id, 'managerid' => $USER->id);
        $staff = $DB->get_fieldset_sql($requestssql, $params);

        // Get temporary staff.
        if (!empty($CFG->enabletempmanagers)) {
            $tempstaff = $DB->get_fieldset_select('temporary_manager', 'userid', 'tempmanagerid = ? AND expirytime > ?',
                array($USER->id, time()));

            $staff = array_unique(array_merge($staff, $tempstaff));
        }
    } else {
        // Get the staff the user is primary manager of.
        $staff = totara_get_staff();
        if (!$staff) {
            $staff = array();
        }
    }
}

$canapproveanyrequest = has_capability('mod/facetoface:approveanyrequest', $context);
if ($canapproveanyrequest || !empty($staff)) {
    // Check if any staff have requests awaiting approval.
    $get_requests = facetoface_get_requests($session->id);
    if ($get_requests) {
        // Calculate which requesting users are relevant to the viewer.
        $requests = ($canapproveanyrequest ? $get_requests : array_intersect_key($get_requests, array_flip($staff)));

        if ($requests) {
            $allowed_actions[] = 'approvalrequired';
            $available_actions[] = 'approvalrequired';
        }
    }
}

// If no allowed actions so far, check if this was manager who has just approved staff requests (approved == 1).
// If so, we will be loading the 'approval required' page with a success message.
if (empty($allowed_actions) && ($approved == 1) && !empty($staff)) {
    $allowed_actions[] = 'approvalrequired';
    $available_actions[] = 'approvalrequired';
}

// Check if we are NOT already showing attendees and the user has staff.
// If this is true then we need to show attendees but limit it to just those attendees that are also staff.
if (!in_array('attendees', $allowed_actions) && !empty($staff)) {
    // Check if any staff are attending.
    if ($session->datetimeknown) {
        $get_attendees = facetoface_get_attendees($session->id, array(MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_NO_SHOW,
            MDL_F2F_STATUS_PARTIALLY_ATTENDED, MDL_F2F_STATUS_FULLY_ATTENDED));
    } else {
        $get_attendees = facetoface_get_attendees($session->id, array(MDL_F2F_STATUS_WAITLISTED, MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_NO_SHOW,
            MDL_F2F_STATUS_PARTIALLY_ATTENDED, MDL_F2F_STATUS_FULLY_ATTENDED));
    }
    if ($get_attendees) {
        // Calculate which attendees are relevant to the viewer.
        $attendees = array_intersect_key($get_attendees, array_flip($staff));

        if ($attendees) {
            $allowed_actions[] = 'attendees';
            $available_actions[] = 'attendees';
        }
    }
}

// Check if we are NOT already showing cancellations and the user has has staff.
// If this is true then we still need to show cancellations but limit it to just those cancellations that are also staff.
if (!in_array('cancellations', $allowed_actions) && !empty($staff)) {
    // Check if any staff have cancelled.
    $get_cancellations = facetoface_get_cancellations($session->id);
    if ($get_cancellations) {
        // Calculate which cancelled users are relevant to the viewer.
        $cancellations = array_intersect_key($get_cancellations, array_flip($staff));

        if ($cancellations) {
            $allowed_actions[] = 'cancellations';
            $available_actions[] = 'cancellations';
        }
    }
}

$can_view_session = !empty($allowed_actions);
if (!$can_view_session) {
    $return = new moodle_url('/mod/facetoface/view.php', array('f' => $facetoface->id));
    redirect($return);
    die();
}
// $allowed_actions is already set, so we can now know if the current action is allowed.
$actionallowed = in_array($action, $allowed_actions);

/***************************************************************************
 * Handle actions
 */
$show_table = false;
$heading_message = '';
$params = array('sessionid' => $s);
$cols = array();
$actions = array();
if ($action == 'attendees' && $actionallowed) {
    $heading = get_string('attendees', 'facetoface');

    // Check if any dates are set
    if (!$session->datetimeknown) {
        $heading_message = get_string('sessionnoattendeesaswaitlist', 'facetoface');
    }

    // Get list of actions
    if (in_array('addattendees', $allowed_actions)) {
        $actions['addremove']    = get_string('addremoveattendees', 'facetoface');
        $actions['bulkaddfile']  = get_string('bulkaddattendeesfromfile', 'facetoface');
        $actions['bulkaddinput'] = get_string('bulkaddattendeesfrominput', 'facetoface');
    }

    if ($has_attendees) {
        $actions['exportxls'] = get_string('exportattendancexls', 'facetoface');
        $actions['exportods'] = get_string('exportattendanceods', 'facetoface');
        $actions['exportcsv'] = get_string('exportattendancetxt', 'facetoface');
    };

    $params['statusgte'] = MDL_F2F_STATUS_BOOKED;
    $cols = array(
        array('user', 'idnumber'),
        array('user', 'namelink'),
        array('user', 'email'),
        array('user', 'position'),
        //array('session', 'discountcode'),
        array('status', 'statuscode'),
    );

    $show_table = true;
}

if ($action == 'waitlist' && $actionallowed) {
    $heading = get_string('wait-list', 'facetoface');

    $params['status'] = MDL_F2F_STATUS_WAITLISTED;
    $cols = array(
        array('user', 'namelink'),
        array('user', 'email'),
    );

    $lotteryenabled = get_config(null, 'facetoface_lotteryenabled');

    $actions['confirmattendees'] = get_string('confirm');
    $actions['cancelattendees'] = get_string('cancel');
    if ($lotteryenabled) {
        $actions['playlottery'] = get_string('playlottery', 'facetoface');
    }

    $show_table = true;
}

if ($action == 'cancellations' && $actionallowed) {
    $heading = get_string('cancellations', 'facetoface');

    // Get list of actions
    $actions = array(
        'exportxls'     => get_string('exportxls', 'totara_reportbuilder'),
        'exportods'     => get_string('exportods', 'totara_reportbuilder'),
        'exportcsv'     => get_string('exportcsv', 'totara_reportbuilder')
    );

    $params['status'] = MDL_F2F_STATUS_USER_CANCELLED;
    $cols = array(
        array('user', 'idnumber'),
        array('user', 'namelink'),
        array('session', 'cancellationdate'),
        array('session', 'cancellationreason'),
    );

    $show_table = true;
}

if ($action == 'takeattendance' && $actionallowed) {
    $heading = get_string('takeattendance', 'facetoface');

    // Get list of actions
    $actions = array(
        'exportxls'                 => get_string('exportxls', 'totara_reportbuilder'),
        'exportods'                 => get_string('exportods', 'totara_reportbuilder'),
        'exportcsv'                 => get_string('exportcsv', 'totara_reportbuilder')
    );

    $params['statusgte'] = MDL_F2F_STATUS_BOOKED;
    $cols = array(
        array('status', 'select'),
        array('user', 'namelink'),
        array('status', 'set'),
    );

    $show_table = true;
}

/**
 * Handle submitted data
 */
if ($form = data_submitted()) {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', 'error');
    }

    $return = new moodle_url('/mod/facetoface/attendees.php', array('s' => $s));

    if ($cancelform) {
        redirect($return);
        die();
    }

    // Approve requests
    if ($action == 'approvalrequired' && !empty($form->requests) && $actionallowed) {
        facetoface_approve_requests($form);

        $return->params(array('action' => 'approvalrequired', 'approved' => 1));
        redirect($return);
        die();
    }

    // Take attendance.
    if ($action == 'takeattendance' && $actionallowed && $takeattendance) {
        if (facetoface_take_attendance($form)) {
            // Trigger take attendance update event.
            \mod_facetoface\event\attendance_updated::create_from_session($session, $context)->trigger();
            totara_set_notification(get_string('updateattendeessuccessful', 'facetoface'), $return,
                    array('class' => 'notifysuccess'));
        }
        totara_set_notification(get_string('error:takeattendance', 'facetoface'), $return, array('class' => 'notifyproblem'));
    }

    // Send messages
    if ($action == 'messageusers' && $actionallowed) {
        $formurl = clone($baseurl);
        $formurl->param('action', 'messageusers');

        $mform = new mod_facetoface_attendees_message_form($formurl, array('s' => $s));

        // Check form validates
        if ($mform->is_cancelled()) {
            redirect($baseurl);
        } else if ($data = $mform->get_data()) {
            // Get recipients list
            $recipients = array();
            if (!empty($data->recipient_group)) {
                foreach ($data->recipient_group as $key => $value) {
                    if (!$value) {
                        continue;
                    }
                    $recipients = $recipients + facetoface_get_users_by_status($s, $key, 'u.id, u.*, su.positiontype');
                }
            }

            // Get indivdual recipients
            if (empty($recipients) && !empty($data->recipients_selected)) {
                // Strip , prefix
                $data->recipients_selected = substr($data->recipients_selected, 1);
                $recipients = explode(',', $data->recipients_selected);
                list($insql, $params) = $DB->get_in_or_equal($recipients);
                $recipients = $DB->get_records_sql('SELECT * FROM {user} WHERE id ' . $insql, $params);
                if (!$recipients) {
                    $recipients = array();
                }
            }

            // Send messages.
            $facetofaceuser = \mod_facetoface\facetoface_user::get_facetoface_user();

            $emailcount = 0;
            $emailerrors = 0;
            foreach ($recipients as $recipient) {
                $body = $data->body['text'];
                $bodyplain = html_to_text($body);

                if (email_to_user($recipient, $facetofaceuser, $data->subject, $bodyplain, $body) === true) {
                    $emailcount += 1;

                    // Are sending to managers and does user have a manager assigned for the position type they signedup with.
                    if (empty($data->cc_managers) || !$manager = totara_get_manager($recipient->id, $recipient->positiontype)) {
                        continue;
                    }

                    // Append to message.
                    $body = get_string('messagesenttostaffmember', 'facetoface', fullname($recipient))."\n\n".$data->body['text'];
                    $bodyplain = html_to_text($body);

                    if (email_to_user($manager, $facetofaceuser, $data->subject, $bodyplain, $body) === true) {
                        $emailcount += 1;
                    }
                } else {
                    $emailerrors += 1;
                }
            }

            if ($emailcount) {
                if (!empty($data->cc_managers)) {
                    $message = get_string('xmessagessenttoattendeesandmanagers', 'facetoface', $emailcount);
                } else {
                    $message = get_string('xmessagessenttoattendees', 'facetoface', $emailcount);
                }

                totara_set_notification($message, $return, array('class' => 'notifysuccess'));
            }

            if ($emailerrors) {
                $message = get_string('xmessagesfailed', 'facetoface', $emailerrors);
                totara_set_notification($message);
            }

            redirect($return);
            die();
        }
    }
}


/**
 * Print page header
 */
if (!$onlycontent) {
    local_js(
        array(
            TOTARA_JS_DIALOG,
            TOTARA_JS_TREEVIEW
        )
    );

    $PAGE->requires->string_for_js('save', 'admin');
    $PAGE->requires->string_for_js('cancel', 'moodle');
    $PAGE->requires->strings_for_js(
        array('uploadfile', 'addremoveattendees', 'approvalreqd', 'areyousureconfirmwaitlist',
            'bulkaddattendeesfrominput', 'submitcsvtext', 'bulkaddattendeesresults', 'bulkaddattendeesfromfile',
            'bulkaddattendeesresults', 'wait-list', 'cancellations', 'approvalreqd', 'takeattendance',
            'updateattendeessuccessful', 'updateattendeesunsuccessful', 'waitlistselectoneormoreusers',
            'confirmlotteryheader', 'confirmlotterybody', 'updatewaitlist', 'close'),
        'facetoface'
    );

    $json_action = json_encode($action);
    $args = array('args' => '{"sessionid":'.$session->id.','.
        '"action":'.$json_action.','.
        '"sesskey":"'.sesskey().'",'.
        '"approvalreqd":"'.$facetoface->approvalreqd.'"}');

    $jsmodule = array(
        'name' => 'totara_f2f_attendees',
        'fullpath' => '/mod/facetoface/attendees.js',
        'requires' => array('json', 'totara_core'));

    if ($action == 'messageusers') {
        $PAGE->requires->strings_for_js(array('editmessagerecipientsindividually', 'existingrecipients', 'potentialrecipients'), 'facetoface');
        $PAGE->requires->string_for_js('update', 'moodle');

        $jsmodule = array(
            'name' => 'totara_f2f_attendees_message',
            'fullpath' => '/mod/facetoface/attendees_messaging.js',
            'requires' => array('json', 'totara_core'));

        $PAGE->requires->js_init_call('M.totara_f2f_attendees_messaging.init', $args, false, $jsmodule);
    } else {
        $jsmodule = array(
            'name' => 'totara_f2f_attendees',
            'fullpath' => '/mod/facetoface/attendees.js',
            'requires' => array('json', 'totara_core'));

        $args = array('args' => '{"sessionid":'.$session->id.','.
            '"action":'.$json_action.','.
            '"sesskey":"'.sesskey().'",'.
            '"selectall":'.MDL_F2F_SELECT_ALL.','.
            '"selectnone":'.MDL_F2F_SELECT_NONE.','.
            '"selectset":"'.MDL_F2F_SELECT_SET.'",'.
            '"selectnotset":"'.MDL_F2F_SELECT_NOT_SET.'",'.
            '"courseid":"'.$course->id.'",'.
            '"facetofaceid":"'.$facetoface->id.'",'.
            '"notsetop":"'.MDL_F2F_STATUS_NOT_SET.'",'.
            '"approvalreqd":"'.$facetoface->approvalreqd.'"}');

        $PAGE->requires->js_init_call('M.totara_f2f_attendees.init', $args, false, $jsmodule);
    }

    \mod_facetoface\event\attendees_viewed::create_from_session($session, $context, $action)->trigger();

    $pagetitle = format_string($facetoface->name);

    $PAGE->set_url('/mod/facetoface/attendees.php', array('s' => $s));
    $PAGE->set_cm($cm);
    $PAGE->set_pagelayout('standard');

    $PAGE->set_title($pagetitle);
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();
}

/**
 * Print page content
 */

if (!$onlycontent && !$download) {
    echo $OUTPUT->box_start();
    echo $OUTPUT->heading(format_string($facetoface->name));

    if ($can_view_session) {
        echo facetoface_print_session($session, true);
    }

    include('attendee_tabs.php'); // If needed include tabs

    echo $OUTPUT->container_start('f2f-attendees-table');
}

if ($onlycontent && !$download) {
    // Legacy Totara HTML ajax, this should be converted to json + AJAX_SCRIPT.
    send_headers('text/html; charset=utf-8', false);
}

/**
 * Print attendees (if user able to view)
 */
$pix = new pix_icon('t/edit', get_string('edit', 'facetoface'));
if ($show_table) {
    // Get list of attendees

    switch ($action) {
        case 'cancellations':
            if ($cancellations) {
                $rows = $cancellations;
            } else {
                $rows = facetoface_get_cancellations($session->id);
            }
            break;

        case 'waitlist':
            $rows = facetoface_get_attendees($session->id, array(MDL_F2F_STATUS_WAITLISTED));
            break;

        case 'takeattendance':
            $rows = facetoface_get_attendees($session->id, array(MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_NO_SHOW,
                MDL_F2F_STATUS_PARTIALLY_ATTENDED, MDL_F2F_STATUS_FULLY_ATTENDED));
            break;

        case 'attendees':
            if ($attendees) {
                $rows = $attendees;
            } else {
                if ($session->datetimeknown) {
                    $rows = facetoface_get_attendees($session->id, array(MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_NO_SHOW,
                        MDL_F2F_STATUS_PARTIALLY_ATTENDED, MDL_F2F_STATUS_FULLY_ATTENDED));
                } else {
                    $rows = facetoface_get_attendees($session->id, array(MDL_F2F_STATUS_WAITLISTED, MDL_F2F_STATUS_BOOKED, MDL_F2F_STATUS_NO_SHOW,
                        MDL_F2F_STATUS_PARTIALLY_ATTENDED, MDL_F2F_STATUS_FULLY_ATTENDED));
                }
            }
            break;
    }

    if (!$download) {
        //output any notifications
        if (isset($result_message)) {
            echo $result_message;
        } else {
            $numattendees = facetoface_get_num_attendees($session->id);
            $overbooked = ($numattendees > $session->capacity);
            if (($action == 'attendees') && $overbooked) {
                $overbookedmessage = get_string('capacityoverbookedlong', 'facetoface', array('current' => $numattendees, 'maximum' => $session->capacity));
                echo $OUTPUT->notification($overbookedmessage, 'notifynotice');
            }
        }

        //output the section heading
        echo $OUTPUT->heading($heading);
    }

    if (empty($rows)) {
        if ($facetoface->approvalreqd) {
            if (count($requests) == 1) {
                echo $OUTPUT->notification(get_string('nosignedupusersonerequest', 'facetoface'));
            } else {
                echo $OUTPUT->notification(get_string('nosignedupusersnumrequests', 'facetoface', count($requests)));
            }
        } else {
            echo $OUTPUT->notification(get_string('nosignedupusers', 'facetoface'));
        }
    } else {
        if (($action == 'takeattendance') && $actionallowed && !$download) {

            $attendees_url = new moodle_url('attendees.php', array('s' => $s, 'takeattendance' => '1', 'action' => 'takeattendance'));
            echo html_writer::start_tag('form', array('action' => $attendees_url, 'method' => 'post', 'id' => 'attendanceform'));
            echo html_writer::tag('p', get_string('attendanceinstructions', 'facetoface'));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => $USER->sesskey));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 's', 'value' => $s));

            // Prepare status options array
            $statusoptions = get_attendance_status();
        }

        if (!$download) {
            echo html_writer::tag('div', '', array('class' => 'hide', 'id' => 'noticeupdate'));
        }

        $table = new totara_table('facetoface-attendees');
        $baseurl = new moodle_url('/mod/facetoface/attendees.php', array('s' => $session->id, 'sesskey' => sesskey(), 'onlycontent' => true));
        if ($action) {
            $baseurl->param('action', $action);
        }
        $table->define_baseurl($baseurl);
        $table->set_attribute('class', 'generalbox mod-facetoface-attendees '.$action);

        $exportfilename = isset($action) ? $action : 'attendees';

        $headers = array();
        $columns = array();
        $export_rows = array();

        $headers[] = get_string('name');
        $columns[] = 'name';
        $headers[] = get_string('timesignedup', 'facetoface');
        $columns[] = 'timesignedup';

        $hidecost = get_config(null, 'facetoface_hidecost');
        $hidediscount = get_config(NULL, 'facetoface_hidediscount');
        $selectpositiononsignupglobal = get_config(null, 'facetoface_selectpositiononsignupglobal');

        $showpositions = !empty($selectpositiononsignupglobal) && !empty($facetoface->selectpositiononsignup);
        if ($showpositions) {
            $headers[] = get_string('selectedposition', 'mod_facetoface');
            $columns[] = 'position';
        }

        if ($action == 'takeattendance' && $actionallowed && !$download) {
            $chooseoption = get_string('select','facetoface');
            $selectlist = html_writer::select($F2F_SELECT_OPTIONS, 'bulk_select', '', false);
            array_unshift($headers, $chooseoption . $selectlist);
            array_unshift($columns, 'selectedusers');
            $headers[] = get_string('currentstatus', 'facetoface');
            $columns[] = 'currentstatus';
        } else if ($action == 'cancellations') {
            $headers[] = get_string('timecancelled', 'facetoface');
            $columns[] = 'timecancelled';
            $headers[] = get_string('cancelreason', 'facetoface');
            $columns[] = 'cancellationreason';
        } else {
            if (!$hidecost) {
                $headers[] = get_string('cost', 'facetoface');
                $columns[] = 'cost';
                if (!$hidediscount) {
                    $headers[] = get_string('discountcode', 'facetoface');
                    $columns[] = 'discountcode';
                }
            }

            $headers[] = get_string('attendance', 'facetoface');
            $columns[] = 'attendance';

            if ($session->availablesignupnote && $action != 'takeattendance') {
                if ($includeattendeesnote) {

                    $headers[] = get_string('attendeenote', 'facetoface');
                    $columns[] = 'usernote';
                }
            }

            if ($action == 'waitlist' && !$download) {
                $headers[] = html_writer::tag('a', get_string('all'), array('href' => '#', 'class' => 'selectall'))
                            . '/'
                            . html_writer::tag('a', get_string('none'), array('href' => '#', 'class' => 'selectnone'));
                $columns[] = 'actions';
            }

        }
        if (!$download) {
            $table->define_columns($columns);
            $table->define_headers($headers);
            $table->setup();
            if ($action == 'takeattendance' && $actionallowed) {
                $table->add_toolbar_content(display_bulk_actions_picker(), 'left' , 'top', 1);
            }
        }
        $cancancelreservations = has_capability('mod/facetoface:reserveother', $context);
        $canchangesignedupjobposition = has_capability('mod/facetoface:changesignedupjobposition', $context);

        foreach ($rows as $attendee) {
            $data = array();
            // Add the name of the manager who made the booking after the user's name.
            $managername = null;
            if (!empty($attendee->bookedby)) {
                $managerurl = new moodle_url('/user/view.php', array('id' => $attendee->bookedby));
                $manager = (object)array('firstname' => $attendee->bookedbyfirstname, 'lastname' => $attendee->bookedbylastname);
                $managername = fullname($manager);
                if (!$download) {
                    $managername = html_writer::link($managerurl, $managername);
                }
            }
            if ($attendee->id) {
                $attendeename = fullname($attendee);
                if (!$download) {
                    $attendeeurl = new moodle_url('/user/view.php', array('id' => $attendee->id, 'course' => $course->id));
                    $attendeename = html_writer::link($attendeeurl, $attendeename);
                }
                if ($managername) {
                    $strinfo = (object)array('attendeename' => $attendeename, 'managername' => $managername);
                    $attendeename = get_string('namewithmanager', 'mod_facetoface', $strinfo);
                }
                $data[] = $attendeename;
            } else {
                // Reserved space - display 'Reserved' + the name of the person who booked it.
                $cancelicon = '';
                if (!$download && $attendee->bookedby) {
                    if ($cancancelreservations) {
                        $params = array(
                            's' => $session->id,
                            'managerid' => $attendee->bookedby,
                            'action' => 'reserve',
                            'backtosession' => $action,
                            'cancelreservation' => 1,
                            'sesskey' => sesskey(),
                        );
                        $cancelurl = new moodle_url('/mod/facetoface/reserve.php', $params);
                        $cancelicon = $OUTPUT->pix_icon('t/delete', get_string('cancelreservation', 'mod_facetoface'));
                        $cancelicon = ' '.html_writer::link($cancelurl, $cancelicon);
                    }
                }
                if ($managername) {
                    $reserved = get_string('reservedby', 'mod_facetoface', $managername);
                } else {
                    $reserved = get_string('reserved', 'mod_facetoface');
                }
                $data[] = $reserved.$cancelicon;
            }

            $data[] = userdate($attendee->timesignedup, get_string('strftimedatetime'));

            if ($showpositions) {
                $label = position::position_label($attendee);

                $url = new moodle_url('/mod/facetoface/attendee_position.php', array('s' => $session->id, 'id' => $attendee->id));
                $icon = $OUTPUT->action_icon($url, $pix, null, array('class' => 'action-icon attendee-edit-position pull-right'));
                $position = html_writer::span($label, 'position'.$attendee->id, array('id' => 'position'.$attendee->id));

                if ($canchangesignedupjobposition) {
                    $data[] = $icon . $position;
                } else {
                    $data[] = $position;
                }
            }

            if ($action == 'takeattendance' && $actionallowed) {
                $optionid = 'submissionid_' . $attendee->submissionid;
                $checkoptionid = 'check_submissionid_' . $attendee->submissionid;

                // Show current status
                if ($attendee->statuscode == MDL_F2F_STATUS_BOOKED) {
                    $attendee->statuscode = (string) MDL_F2F_STATUS_NOT_SET;
                }

                if (!$download) {
                    $status = $attendee->statuscode;
                    $checkbox = html_writer::checkbox($checkoptionid, $status, false, '', array(
                        'class' => 'selectedcheckboxes',
                        'data-selectid' => 'menusubmissionid_' . $attendee->submissionid
                    ));
                    array_unshift($data, $checkbox);
                    $select = html_writer::select($statusoptions, $optionid, $status, false);
                    $data[] = $select;
                } else {
                    if (!$hidecost) {
                        $data[] = facetoface_cost($attendee->id, $session->id, $session);
                        if (!$hidediscount) {
                            $data[] = $attendee->discountcode;
                        }
                    }

                    $data[] = get_string('status_' . facetoface_get_status($attendee->statuscode), 'facetoface');
                }
            } else if ($action == 'cancellations') {
                $data[] = userdate($attendee->timecancelled, get_string('strftimedatetime'));
                $showpix = new pix_icon('/t/preview', get_string('showcancelreason', 'facetoface'));
                $url = new moodle_url('/mod/facetoface/cancellation_note.php', array('s' => $session->id, 'userid' => $attendee->id));
                $icon = $OUTPUT->action_icon($url, $showpix, null, array('class' => 'action-icon attendee-cancellation-note pull-right'));

                $cancelstatus = new stdClass();
                $cancelstatus->id = $attendee->signupid;
                $cancellationnote = customfield_get_data($cancelstatus, 'facetoface_cancellation', 'facetofacecancellation', false);
                // Verify 'cancellation note' custom filed is not deleted.
                $cancellationnotetext = isset($cancellationnote['cancellationnote']) ? $cancellationnote['cancellationnote'] : '';
                if (!$download) {
                    $data[] = $icon . html_writer::span($cancellationnotetext, 'cancellationnote' . $attendee->id, array('id' => 'cancellationnote' . $attendee->id));
                } else {
                    $data[] = $cancellationnotetext;
                }
            } else {
                if (!$hidecost) {
                    $data[] = facetoface_cost($attendee->id, $session->id, $session);
                    if (!$hidediscount) {
                        $data[] = $attendee->discountcode;
                    }
                }

                $data[] = str_replace(' ', '&nbsp;', get_string('status_'.facetoface_get_status($attendee->statuscode), 'facetoface'));
                if ($session->availablesignupnote) {
                    $icon = '';
                    if (has_capability('mod/facetoface:manageattendeesnote', $context)) {
                        $url = new moodle_url('/mod/facetoface/attendee_note.php', array('s' => $session->id, 'userid' => $attendee->id));
                        $showpix = new pix_icon('/t/preview', get_string('showattendeesnote', 'facetoface'));
                        $icon = $OUTPUT->action_icon($url, $showpix, null, array('class' => 'action-icon attendee-add-note pull-right'));
                    }
                    if ($includeattendeesnote) {
                        // Get signup note.
                        $signupstatus = new stdClass();
                        $signupstatus->id = $attendee->submissionid;
                        $signupnote = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);

                        $signupnotetext = empty($signupnote) ? '' : $signupnote['signupnote'];
                        if (!$download) {
                            $data[] = $icon . html_writer::span($signupnotetext, 'note' . $attendee->id, array('id' => 'usernote' . $attendee->id));
                        } else {
                            $data[] = $signupnotetext;
                        }
                    }
                }
            }

            if ($action == 'waitlist' && !$download) {
                $d = html_writer::empty_tag('input', array('type' => 'checkbox', 'value' => $attendee->id, 'name' => 'userid'));
                $data[] = $d;
            }

            if (!$download) {
                $table->add_data($data);
            } else {
                $export_rows[] = $data;
            }
        }
        if (!$download) {
            $table->finish_html();
        } else {
            switch ($download) {
                case 'ods':
                    facetoface_download_ods($headers, $export_rows, $exportfilename);
                    break;
                case 'xls':
                    facetoface_download_xls($headers, $export_rows, $exportfilename);
                    break;
                case 'csv':
                    facetoface_download_csv($headers, $export_rows, $exportfilename);
                    break;
            }
        }
    }

    if (has_any_capability(array('mod/facetoface:addattendees', 'mod/facetoface:removeattendees', 'mod/facetoface:takeattendance'), $context)) {
        if ($action == 'takeattendance' && $actionallowed) {
            // Changes checker
            $PAGE->requires->yui_module('moodle-core-formchangechecker',
                'M.core_formchangechecker.init',
                array(array(
                    'formid' => 'attendanceform'
                ))
            );
            $PAGE->requires->string_for_js('changesmadereallygoaway', 'moodle');

            // Save and cancel buttons.
            echo html_writer::start_tag('p');
            echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('saveattendance', 'facetoface')));
            echo '&nbsp;' . html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'cancelform', 'value' => get_string('cancel')));
            echo html_writer::end_tag('p') . html_writer::end_tag('form');
        }
        echo $OUTPUT->container_start('actions last');
        if ($actions) {
            // Action selector
            echo html_writer::select($actions, 'f2f-actions', '', array('' => get_string('action')));
            if ($action == 'waitlist') {
                echo $OUTPUT->help_icon('f2f-waitlist-actions', 'mod_facetoface');
            }
        }
        echo $OUTPUT->container_end();
    }
}

if ($action == 'messageusers' && $actionallowed) {
    $OUTPUT->heading(get_string('messageusers', 'facetoface'));

    $formurl = clone($baseurl);
    $formurl->param('action', 'messageusers');

    $mform = new mod_facetoface_attendees_message_form($formurl, array('s' => $s));
    $mform->display();
}

// Go back
$url = new moodle_url('/mod/facetoface/view.php', array('f' => $facetoface->id));
echo html_writer::link($url, get_string('goback', 'facetoface')) . html_writer::end_tag('p');


/**
 * Print unapproved requests (if user able to view)
 */
if ($action == 'approvalrequired') {

    if ($approved == 1) {
        echo $OUTPUT->notification(get_string('attendancerequestsupdated', 'facetoface'), 'notifysuccess');
    }

    echo html_writer::empty_tag('br', array('id' => 'unapproved'));
    $numattendees = facetoface_get_num_attendees($session->id);
    $numwaiting = count($requests);
    $availablespaces = $session->capacity - $numattendees;
    $allowoverbook = $session->allowoverbook;
    $canoverbook = has_capability('mod/facetoface:overbook', $context);
    // Are there more users waiting than spaces available?
    // Note this does not apply to people with overbook capability (see facetoface_session_has_capacity).
    if (!$canoverbook && ($numwaiting > $availablespaces)) {
        $stringmodifier = ($availablespaces > 0) ? 'over' : 'no';
        $stringidentifier = ($allowoverbook) ? "approval{$stringmodifier}capacitywaitlist" : "approval{$stringmodifier}capacity";
        $overcapacitymessage = get_string($stringidentifier, 'facetoface', array('waiting' => $numwaiting, 'available' => $availablespaces));
        echo $OUTPUT->notification($overcapacitymessage, 'notifynotice');
    }
    // If they cannot overbook and no spaces are available, disable the ability to approve more requests.
    $approvaldisabled = array();
    if (!$canoverbook && ($availablespaces <= 0 && !$allowoverbook)) {
         $approvaldisabled['disabled'] = 'disabled';
    }
    $actionurl = clone($baseurl);

    echo html_writer::start_tag('form', array('action' => $actionurl, 'method' => 'post'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => $USER->sesskey));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 's', 'value' => $s));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'approvalrequired'));

    unset($actionurl);

    $headings = array();
    $headings[] = get_string('name');
    $headings[] = get_string('timerequested', 'facetoface');
    if ($session->availablesignupnote && $includeattendeesnote) {
        // The user has to hold specific permissions to view this.
        $headings[] = get_string('attendeenote', 'facetoface');
    }
    $headings[] = get_string('decidelater', 'facetoface');
    $headings[] = get_string('decline', 'facetoface');
    $headings[] = get_string('approve', 'facetoface');

    $table = new html_table();
    $table->summary = get_string('requeststablesummary', 'facetoface');
    $table->head = $headings;
    $table->align = array('left', 'center', 'center', 'center', 'center', 'center');

    foreach ($requests as $attendee) {
        $data = array();
        $attendee_link = new moodle_url('/user/view.php', array('id' => $attendee->id, 'course' => $course->id));
        $data[] = html_writer::link($attendee_link, format_string(fullname($attendee)));
        $data[] = userdate($attendee->timerequested, get_string('strftimedatetime'));

        if ($session->availablesignupnote) {
            $icon = '';
            if (has_capability('mod/facetoface:manageattendeesnote', $context)) {
                $url = new moodle_url('/mod/facetoface/attendee_note.php', array('s' => $session->id, 'userid' => $attendee->id));
                $icon = $OUTPUT->action_icon($url, $pix, null, array('class' => 'action-icon attendee-add-note pull-right'));
            }
            if ($includeattendeesnote) {
                // Get signup note.
                $signupstatus = new stdClass();
                $signupstatus->id = $attendee->signupid;
                $signupnote = customfield_get_data($signupstatus, 'facetoface_signup', 'facetofacesignup', false);

                $signupnotetext = empty($signupnote) ? '' : $signupnote['signupnote'];

                $note = html_writer::span($signupnotetext, 'note' . $attendee->id, array('id' => 'usernote' . $attendee->id));
                $data[] = $icon . $note;
            }
        }
        $data[] = html_writer::empty_tag('input', array_merge($approvaldisabled, array('type' => 'radio', 'name' => 'requests['.$attendee->id.']', 'value' => '0', 'checked' => 'checked')));
        $data[] = html_writer::empty_tag('input',array_merge($approvaldisabled, array('type' => 'radio', 'name' => 'requests['.$attendee->id.']', 'value' => '1')));
        $data[] = html_writer::empty_tag('input', array_merge($approvaldisabled, array('type' => 'radio', 'name' => 'requests['.$attendee->id.']', 'value' => '2')));
        $table->data[] = $data;
    }

    if (!empty($table->data)) {
        echo html_writer::table($table);
        echo html_writer::tag('p', html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('updaterequests', 'facetoface'))));
    } else {
        echo html_writer::start_span();
        echo html_writer::tag('p', get_string('nopendingapprovals', 'facetoface'));
        echo html_writer::end_span();
    }

    echo html_writer::end_tag('form');
}

/**
 * Print page footer
 */
if (!$onlycontent) {
    echo $OUTPUT->container_end();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer($course);
}
