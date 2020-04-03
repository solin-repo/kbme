<?php

// Display user position information
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->dirroot.'/totara/core/js/lib/setup.php');
require_once($CFG->dirroot.'/totara/hierarchy/prefix/position/lib.php');
require_once('positions_form.php');


// Get input parameters
$user       = required_param('user', PARAM_INT);               // user id
$type       = optional_param('type', '', PARAM_ALPHA);      // position type
$courseid   = optional_param('course', SITEID, PARAM_INT);   // course id

// Position types check
if (!$positionsenabled = get_config('totara_hierarchy', 'positionsenabled')) {
    print_error('error:noposenabled', 'totara_hierarchy');
}

// Create array of enabled positions
$enabled_positions = explode(',', $positionsenabled);

if (empty($POSITION_CODES[$type])) {
    // Set default enabled position type
    foreach ($POSITION_CODES as $ptype => $poscode) {
        if (in_array($poscode, $enabled_positions)) {
            $type = $ptype;
            break;
        }
    }
}

if ($POSITION_CODES[$type] == POSITION_TYPE_ASPIRATIONAL && totara_feature_disabled('positions')) {
    print_error('error:positionsdisabled', 'totara_hierarchy');
}

$poscode = $POSITION_CODES[$type];
if (!in_array($poscode, $enabled_positions)) {
    print_error('error:postypenotenabled', 'totara_hierarchy');
}

if (empty($courseid)) {
    $courseid = SITEID;
}

// Load some basic data
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('error:courseidincorrect', 'totara_core');
}

if (!$user = $DB->get_record('user', array('id' => $user))) {
    print_error('error:useridincorrect', 'totara_core');
}

// Check logged in user can view this profile
require_login($course);
// Check permissions
$personalcontext = context_user::instance($user->id);
$coursecontext = context_course::instance($course->id);
$PAGE->set_url(new moodle_url('/user/positions.php', array('user' => $user->id, 'type' => $type)));
$PAGE->set_context($coursecontext);
$editoroptions = array('subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'context' => $personalcontext);

$canview = false;
if (!empty($USER->id) && ($user->id == $USER->id)) {
    // Can view own profile
    $canview = true;
}
elseif (has_capability('moodle/user:viewdetails', $coursecontext)) {
    $canview = true;
}
elseif (has_capability('moodle/user:viewdetails', $personalcontext)) {
    $canview = true;
}

if (!$canview) {
    print_error('cannotviewprofile');
}

// Is user deleted?
if ($user->deleted) {
    print_error('userdeleted', 'moodle');
}

// Can user edit this user's positions?
$can_edit = pos_can_edit_position_assignment($user->id);

// Check a valid position type was supplied
if ($type === '') {
    $type = reset($POSITION_TYPES);
}
elseif (!in_array($type, $POSITION_TYPES)) {
    // Redirect to default position
    redirect("{$CFG->wwwroot}/user/positions.php?user={$user->id}&amp;course={$course->id}");
}

// Can user edit temp manager.
$can_edit_tempmanager = false;
if ($type == $POSITION_TYPES[POSITION_TYPE_PRIMARY] && !empty($CFG->enabletempmanagers)) {
    if (has_capability('totara/core:delegateusersmanager', $personalcontext)) {
        $can_edit_tempmanager = true;
    } else if ($USER->id == $user->id && has_capability('totara/core:delegateownmanager', $personalcontext)) {
        $can_edit_tempmanager = true;
    }
}

// Attempt to load the assignment
$position_assignment = new position_assignment(
    array(
        'userid'    => $user->id,
        'type'      => $POSITION_CODES[$type]
    )
);

$positiontype       = get_string('type'.$type, 'totara_hierarchy');
$fullname           = fullname($user, true);

if ($course->id != SITEID && has_capability('moodle/course:viewparticipants', $coursecontext)) {
    $PAGE->navbar->add(get_string('participants'), "{$CFG->wwwroot}/user/index.php?id={$course->id}");
    $PAGE->navbar->add($fullname, "{$CFG->wwwroot}/user/view.php?id={$user->id}&amp;course={$course->id}");
    $PAGE->navbar->add($positiontype, null);
} else {
    $PAGE->navbar->add(get_string('users'), "{$CFG->wwwroot}/admin/user.php");
    $PAGE->navbar->add($fullname, "{$CFG->wwwroot}/user/view.php?id={$user->id}&amp;course={$course->id}");
    $PAGE->navbar->add($positiontype, null);
}

// Setup custom javascript
local_js(array(
    TOTARA_JS_DIALOG,
    TOTARA_JS_TREEVIEW
));
$PAGE->requires->strings_for_js(array('chooseposition', 'chooseappraiser', 'choosemanager',
    'chooseorganisation', 'currentlyselected'), 'totara_hierarchy');
$PAGE->requires->strings_for_js(array('choosetempmanager'), 'totara_core');
$PAGE->requires->strings_for_js(array('error:positionnotselected', 'error:organisationnotselected', 'error:managernotselected',
                                       'error:tempmanagernotselected', 'error:appraisernotselected'), 'totara_core');
$jsmodule = array(
        'name' => 'totara_positionuser',
        'fullpath' => '/totara/core/js/position.user.js',
        'requires' => array('json'));
$selected_position = json_encode(dialog_display_currently_selected(get_string('selected', 'totara_hierarchy'), 'position'));
$selected_organisation = json_encode(dialog_display_currently_selected(get_string('selected', 'totara_hierarchy'), 'organisation'));
$selected_manager = json_encode(dialog_display_currently_selected(get_string('selected', 'totara_hierarchy'), 'manager'));
$selected_tempmanager = json_encode(dialog_display_currently_selected(get_string('selected', 'totara_hierarchy'), 'tempmanager'));
$js_can_edit = $can_edit ? 'true' : 'false';
$js_can_edit_tempmanager = $can_edit_tempmanager ? 'true' : 'false';
$selected_appraiser = json_encode(dialog_display_currently_selected(get_string('selected', 'totara_hierarchy'), 'appraiser'));
$args = array('args'=>'{"userid":'.$user->id.','.
        '"can_edit":'.$js_can_edit.','.
        '"can_edit_tempmanager":'.$js_can_edit_tempmanager.','.
        '"dialog_display_position":'.$selected_position.','.
        '"dialog_display_organisation":'.$selected_organisation.','.
        '"dialog_display_manager":'.$selected_manager.','.
        '"dialog_display_tempmanager":'.$selected_tempmanager.','.
        '"dialog_display_appraiser":'.$selected_appraiser.'}');

$PAGE->requires->js_init_call('M.totara_positionuser.init', $args, false, $jsmodule);

$PAGE->set_pagelayout('course');

$currenturl = "{$CFG->wwwroot}/user/positions.php?user={$user->id}&course={$course->id}&type={$type}";

// Form
$submitbutton = optional_param('submitbutton', null, PARAM_ALPHANUMEXT);
$submitted = !empty($submitbutton);
$submittedpositionid = optional_param('positionid', null, PARAM_INT);
$submittedorganisationid = optional_param('organisationid', null, PARAM_INT);
$submittedmanagerid = optional_param('managerid', null, PARAM_INT);
$submittedappraiserid = optional_param('appraiserid', null, PARAM_INT);
$submittedtempmanagerid = optional_param('tempmanagerid', null, PARAM_INT);
$position_assignment->descriptionformat = FORMAT_HTML;
$position_assignment = file_prepare_standard_editor($position_assignment, 'description', $editoroptions, $editoroptions['context'],
    'totara_core', 'pos_assignment', $position_assignment->id);
$form = new user_position_assignment_form($currenturl, compact('type', 'user', 'position_assignment', 'can_edit',
        'editoroptions', 'can_edit_tempmanager', 'submitted', 'submittedpositionid', 'submittedorganisationid',
        'submittedmanagerid', 'submittedappraiserid', 'submittedtempmanagerid'));
$form->set_data($position_assignment);
// Don't show the page if they do not have a position & can't edit positions.
if (!$can_edit && !$position_assignment->id && !$can_edit_tempmanager) {
    $PAGE->set_title("{$course->fullname}: {$fullname}: {$positiontype}");
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('nopositionsassigned', 'totara_hierarchy'));
}
else {
    if ($form->is_cancelled()) {
        // Redirect to default position
        redirect("{$CFG->wwwroot}/user/positions.php?user={$user->id}&amp;course={$course->id}&amp;&type=$type");
    }
    elseif ($data = $form->get_data()) {

        if (isset($data->positionid) && $data->positionid == 0) {
            $data->positionid = null;
        }

        $data->type = $POSITION_CODES[$type];
        $data->userid = $user->id;

        // Get new manager id.
        if (isset($data->managerid) && $data->managerid > 0) {
            if ($data->managerid == $data->userid) {
                print_error('error:userownmanager', 'totara_hierarchy');
            } else {
                // If there is a manager assigned, check manager is valid.
                $validmanager = $DB->get_record('user', array('id' => $data->managerid), 'username, deleted');
                if ($validmanager && $validmanager->deleted != 0) {
                    $data->managerid = null;
                    $data->reportstoid = null;
                    $data->managerpath = null;
                    $a = new stdClass();
                    $a->username = $validmanager->username;
                    totara_set_notification(get_string('error:managerdeleted','totara_hierarchy', $a), null, array('class' => 'notifynotice'));
                }
            }
        }

        // Get new appraiser id.
        if (isset($data->appraiserid) && $data->appraiserid > 0) {
            if ($data->appraiserid == $data->userid) {
                print_error('error:userownappraiser', 'totara_hierarchy');
            } else {
                // If there is a appraiser assigned, check appraiser is valid.
                $validappraiser = $DB->get_record('user', array('id' => $data->managerid), 'username, deleted');
                if ($validappraiser && $validappraiser->deleted != 0) {
                    $data->appraiserid = null;
                    $a = new stdClass();
                    $a->username = $validappraiser->username;
                    totara_set_notification(get_string('error:appraiserdeleted','totara_hierarchy', $a), null, array('class' => 'notifynotice'));
                }
            }
        }

        // If aspiration type, make sure no manager is set
        if ($data->type == POSITION_TYPE_ASPIRATIONAL) {
            $data->managerid = null;
        }

        // Setup data
        position_assignment::set_properties($position_assignment, $data);
        assign_user_position($position_assignment);
        $data->id = $position_assignment->id;

        // Description editor post-update
        if ($can_edit && $data->type != POSITION_TYPE_ASPIRATIONAL) {
            $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $editoroptions['context'], 'totara_core', 'pos_assignment', $data->id);
            $DB->set_field('pos_assignment', 'description', $data->description, array('id' => $data->id));
        }

        if (!empty($data->tempmanagerid)) {
            // Update temporary manager.
            // If there is a temporary manager assigned, check temporary manager is valid.
            $validtempmgr = $DB->get_record('user', array('id' => $data->tempmanagerid), 'username, deleted');
            if ($validtempmgr && $validtempmgr->deleted != 0) {
                $data->tempmanagerid = null;
                $a = new stdClass();
                $a->username = $validtempmgr->username;
                totara_set_notification(get_string('error:tempmanagerdeleted','totara_hierarchy', $a), null, array('class' => 'notifynotice'));
            } else {
                totara_update_temporary_manager($user->id, $data->tempmanagerid, $data->tempmanagerexpiry);
            }
        } else if (!empty($CFG->enabletempmanagers)) {
            // Unassign the current temporary manager.
            totara_unassign_temporary_manager($user->id);
        }

        // Display success message
        totara_set_notification(get_string('positionsaved','totara_hierarchy'), $currenturl, array('class' => 'notifysuccess'));
    }

    // Log
    \totara_core\event\position_viewed::create_from_instance($position_assignment, $coursecontext)->trigger();


    $PAGE->set_title("{$course->fullname}: {$fullname}: {$positiontype}");
    $PAGE->set_heading("{$positiontype}");
    echo $OUTPUT->header();

    $form->display();
}
echo $OUTPUT->footer();
