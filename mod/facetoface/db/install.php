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
 * @package mod
 * @subpackage facetoface
 */

function xmldb_facetoface_install() {
    global $DB;

    // We need to validate the content of these language strings to make sure that they are not too long for the database field
    // they are about to be written to.
    $titles = array(
        'setting:defaultconfirmationsubjectdefault' => get_string('setting:defaultconfirmationsubjectdefault', 'facetoface'),
        'setting:defaultwaitlistedsubjectdefault' => get_string('setting:defaultwaitlistedsubjectdefault', 'facetoface'),
        'setting:defaultcancellationsubjectdefault' => get_string('setting:defaultcancellationsubjectdefault', 'facetoface'),
        'setting:defaultdeclinesubjectdefault' => get_string('setting:defaultdeclinesubjectdefault', 'facetoface'),
        'setting:defaultremindersubjectdefault' => get_string('setting:defaultremindersubjectdefault', 'facetoface'),
        'setting:defaultrequestsubjectdefault' => get_string('setting:defaultrequestsubjectdefault', 'facetoface'),
        'setting:defaultdatetimechangesubjectdefault' => get_string('setting:defaultdatetimechangesubjectdefault', 'facetoface'),
        'setting:defaulttrainerconfirmationsubjectdefault' => get_string('setting:defaulttrainerconfirmationsubjectdefault', 'facetoface'),
        'setting:defaulttrainersessioncancellationsubjectdefault' => get_string('setting:defaulttrainersessioncancellationsubjectdefault', 'facetoface'),
        'setting:defaulttrainersessionunassignedsubjectdefault' => get_string('setting:defaulttrainersessionunassignedsubjectdefault', 'facetoface'),
        'setting:defaultcancelreservationsubjectdefault' => get_string('setting:defaultcancelreservationsubjectdefault', 'facetoface'),
        'setting:defaultcancelallreservationssubjectdefault' => get_string('setting:defaultcancelallreservationssubjectdefault', 'facetoface')
    );

    foreach ($titles as $key => $title) {
        if (core_text::strlen($title) > 255) {
            // We choose to truncate here. If we throw an exception like we should then the user won't be able to add face to face
            // sessions and the user may not be able to edit the language pack to fix it. Thus we truncate and debug.
            $titles[$key] = core_text::substr($title, 0, 255);
            debugging('A face to face notification title was truncated due to its length: ' . $key, DEBUG_NORMAL);
        }
    }

    //Create default notification templates
    $tpl_confirmation = new stdClass();
    $tpl_confirmation->status = 1;
    $tpl_confirmation->reference = 'confirmation';
    $tpl_confirmation->title = $titles['setting:defaultconfirmationsubjectdefault'];
    $tpl_confirmation->body = text_to_html(get_string('setting:defaultconfirmationmessagedefault', 'facetoface'));
    $tpl_confirmation->managerprefix = text_to_html(get_string('setting:defaultconfirmationinstrmngrdefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_confirmation);

    $tpl_cancellation = new stdClass();
    $tpl_cancellation->status = 1;
    $tpl_cancellation->reference = 'cancellation';
    $tpl_cancellation->title = $titles['setting:defaultcancellationsubjectdefault'];
    $tpl_cancellation->body = text_to_html(get_string('setting:defaultcancellationmessagedefault', 'facetoface'));
    $tpl_cancellation->managerprefix = text_to_html(get_string('setting:defaultcancellationinstrmngrdefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_cancellation);

    $tpl_waitlist = new stdClass();
    $tpl_waitlist->status = 1;
    $tpl_waitlist->reference = 'waitlist';
    $tpl_waitlist->title = $titles['setting:defaultwaitlistedsubjectdefault'];
    $tpl_waitlist->body = text_to_html(get_string('setting:defaultwaitlistedmessagedefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_waitlist);

    $tpl_reminder = new stdClass();
    $tpl_reminder->status = 1;
    $tpl_reminder->reference = 'reminder';
    $tpl_reminder->title = $titles['setting:defaultremindersubjectdefault'];
    $tpl_reminder->body = text_to_html(get_string('setting:defaultremindermessagedefault', 'facetoface'));
    $tpl_reminder->managerprefix = text_to_html(get_string('setting:defaultreminderinstrmngrdefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_reminder);

    $tpl_request = new stdClass();
    $tpl_request->status = 1;
    $tpl_request->reference = 'request';
    $tpl_request->title = $titles['setting:defaultrequestsubjectdefault'];
    $tpl_request->body = text_to_html(get_string('setting:defaultrequestmessagedefault', 'facetoface'));
    $tpl_request->managerprefix = text_to_html(get_string('setting:defaultrequestinstrmngrdefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_request);

    $tpl_decline = new stdClass();
    $tpl_decline->status = 1;
    $tpl_decline->reference = 'decline';
    $tpl_decline->title = $titles['setting:defaultdeclinesubjectdefault'];
    $tpl_decline->body = text_to_html(get_string('setting:defaultdeclinemessagedefault', 'facetoface'));
    $tpl_decline->managerprefix = text_to_html(get_string('setting:defaultdeclineinstrmngrdefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_decline);

    $tpl_timechange = new stdClass();
    $tpl_timechange->status = 1;
    $tpl_timechange->reference = 'timechange';
    $tpl_timechange->title = $titles['setting:defaultdatetimechangesubjectdefault'];
    $tpl_timechange->body = text_to_html(get_string('setting:defaultdatetimechangemessagedefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_timechange);

    $tpl_trainercancel = new stdClass();
    $tpl_trainercancel->status = 1;
    $tpl_trainercancel->reference = 'trainercancel';
    $tpl_trainercancel->title = $titles['setting:defaulttrainersessioncancellationsubjectdefault'];
    $tpl_trainercancel->body = text_to_html(get_string('setting:defaulttrainersessioncancellationmessagedefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_trainercancel);

    $tpl_trainerunassign = new stdClass();
    $tpl_trainerunassign->status = 1;
    $tpl_trainerunassign->reference = 'trainerunassign';
    $tpl_trainerunassign->title = $titles['setting:defaulttrainersessionunassignedsubjectdefault'];
    $tpl_trainerunassign->body = text_to_html(get_string('setting:defaulttrainersessionunassignedmessagedefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_trainerunassign);

    $tpl_trainerconfirm = new stdClass();
    $tpl_trainerconfirm->status = 1;
    $tpl_trainerconfirm->reference = 'trainerconfirm';
    $tpl_trainerconfirm->title = $titles['setting:defaulttrainerconfirmationsubjectdefault'];
    $tpl_trainerconfirm->body = text_to_html(get_string('setting:defaulttrainerconfirmationmessagedefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_trainerconfirm);

    $tpl_allreservationcancel = new stdClass();
    $tpl_allreservationcancel->status = 1;
    $tpl_allreservationcancel->reference = 'allreservationcancel';
    $tpl_allreservationcancel->title = $titles['setting:defaultcancelallreservationssubjectdefault'];
    $tpl_allreservationcancel->body = text_to_html(get_string('setting:defaultcancelallreservationsmessagedefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_allreservationcancel);

    $tpl_reservationcancel = new stdClass();
    $tpl_reservationcancel->status = 1;
    $tpl_reservationcancel->reference = 'reservationcancel';
    $tpl_reservationcancel->title = $titles['setting:defaultcancelreservationsubjectdefault'];
    $tpl_reservationcancel->body = text_to_html(get_string('setting:defaultcancelreservationmessagedefault', 'facetoface'));
    $DB->insert_record('facetoface_notification_tpl', $tpl_reservationcancel);

    // Setting room, building, and address as default filters.
    set_config('facetoface_calendarfilters', 'room,building,address');

    facetoface_create_signup_cancellation_customfield_notes();

    // Make sure the upgrade from TL-6962 doesn't run on already fixed data.
    $configparams = array(
        'plugin' => 'facetoface',
        'name' => 'upgrade_customfieldmigration_signup',
        'value' => 'done',
    );
    $DB->insert_record('config_plugins', $configparams);

    $configparams = array(
        'plugin' => 'facetoface',
        'name' => 'upgrade_customfieldmigration_cancellation',
        'value' => 'done',
    );
    $DB->insert_record('config_plugins', $configparams);

}

/**
 * Create signup and cancellation default text notes.
 */
function facetoface_create_signup_cancellation_customfield_notes() {
    global $DB;

    // Clear data. This tables are new and should not contain any data.
    $DB->delete_records('facetoface_signup_info_field');
    $DB->delete_records('facetoface_cancellation_info_field');

    $data = new stdClass();
    $data->id = 0;
    $data->datatype = 'text';
    $data->shortname = 'signupnote';
    $data->fullname = 'Signup note';
    $data->description = '';
    $data->defaultdata = '';
    $data->forceunique = 0;
    $data->hidden = 0;
    $data->locked = 0;
    $data->required = 0;
    $data->sortorder = 1;
    $data->description_editor = array('text' => '', 'format' => 0);
    $signupinfofieldid = $DB->insert_record('facetoface_signup_info_field', $data);

    // Cancellation note default field.
    $data = new stdClass();
    $data->id = 0;
    $data->datatype = 'text';
    $data->shortname = 'cancellationnote';
    $data->fullname = 'Cancellation note';
    $data->description = '';
    $data->defaultdata = '';
    $data->forceunique = 0;
    $data->hidden = 0;
    $data->locked = 0;
    $data->required = 0;
    $data->sortorder = 1;
    $data->description_editor = array('text' => '', 'format' => 0);
    $cancellationinfofieldid = $DB->insert_record('facetoface_cancellation_info_field', $data);

    return array($signupinfofieldid,$cancellationinfofieldid);
}
