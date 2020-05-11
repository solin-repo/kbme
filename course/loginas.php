<?php
// Allows a teacher/admin to login as another user (in stealth mode).

require_once('../config.php');
require_once('lib.php');
require_once($CFG->dirroot . '/user/lib.php');

$id       = optional_param('id', SITEID, PARAM_INT);   // course id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

$url = new moodle_url('/course/loginas.php', array('id'=>$id));
$PAGE->set_url($url);

// Reset user back to their real self if needed, for security reasons you need to log out and log in again.
if (\core\session\manager::is_loggedinas()) {
    require_sesskey();
    require_logout();

    // We can not set wanted URL here because the session is closed.
    redirect(new moodle_url($url, array('redirect'=>1)));
}

if ($redirect) {
    if ($id and $id != SITEID) {
        $SESSION->wantsurl = "$CFG->wwwroot/course/view.php?id=".$id;
    } else {
        $SESSION->wantsurl = "$CFG->wwwroot/";
    }

    redirect(get_login_url());
}

// User must be logged in.
require_login();
require_sesskey();

// Try login-as this user.
$userid = required_param('user', PARAM_INT);
$target_user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0]);

// Totara: Use consolidated function to check loginas capability.
if ($id and $id != SITEID) {
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
    require_login($course);
    $context = context_course::instance($course->id);
} else {
    $course = get_site();
    $context = context_system::instance();
}
$PAGE->set_context($context);

if (!user_can_loginas($target_user, $course)) {
    print_error('nologinas');
}

// Login as this user and return to course home page.
\core\session\manager::loginas($userid, $context);
$newfullname = fullname($USER, true);

$strloginas    = get_string('loginas');
$strloggedinas = get_string('loggedinas', '', $newfullname);

$PAGE->set_title($strloggedinas);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strloggedinas);
notice($strloggedinas, "$CFG->wwwroot/course/view.php?id=$course->id");