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

defined('MOODLE_INTERNAL') || die();

class mod_facetoface_renderer extends plugin_renderer_base {
    protected $context = null;

    /**
     * Builds session list table given an array of sessions
     */
    public function print_session_list_table($sessions, $viewattendees, $editsessions, $displaytimezones, $reserveinfo = array()) {
        $output = '';

        $tableheader = array();
        $customfields = facetoface_get_session_customfields();
        foreach ($customfields as $customfield) {
            if (!empty($customfield->showinsummary)) {
                $tableheader[] = format_string($customfield->fullname);
            }
        }
        $tableheader[] = get_string('date', 'facetoface');
        if (!empty($displaytimezones)) {
            $tableheader[] = get_string('timeandtimezone', 'facetoface');
        } else {
            $tableheader[] = get_string('time', 'facetoface');
        }
        $tableheader[] = get_string('room', 'facetoface');
        if ($viewattendees) {
            $tableheader[] = get_string('capacity', 'facetoface');
        } else {
            $tableheader[] = get_string('seatsavailable', 'facetoface');
        }
        $tableheader[] = get_string('status', 'facetoface');
        $tableheader[] = get_string('options', 'facetoface');

        $timenow = time();

        $table = new html_table();
        $table->summary = get_string('previoussessionslist', 'facetoface');
        $table->attributes['class'] = 'generaltable fullwidth';
        $table->head = $tableheader;
        $table->data = array();

        foreach ($sessions as $session) {

            $isbookedsession = false;
            $bookedsession = $session->bookedsession;
            $sessionstarted = false;
            $sessionfull = false;

            $sessionrow = array();

            // Custom fields.
            $customfieldsdata = customfield_get_data($session, 'facetoface_session', 'facetofacesession');
            foreach ($customfields as $customfield) {
                if (empty($customfield->showinsummary)) {
                    continue;
                }
                if (array_key_exists($customfield->fullname, $customfieldsdata)) {
                    $sessionrow[] =  $customfieldsdata[$customfield->fullname];
                } else {
                    $sessionrow[] = '&nbsp;';
                }
            }

            // Dates/times
            $allsessiondates = '';
            $allsessiontimes = '';
            if ($session->datetimeknown) {
                foreach ($session->sessiondates as $date) {
                    if (!empty($allsessiondates)) {
                        $allsessiondates .= html_writer::empty_tag('br');
                        $allsessiontimes .= html_writer::empty_tag('br');
                    }
                    $sessionobj = facetoface_format_session_times($date->timestart, $date->timefinish, $date->sessiontimezone);
                    if ($sessionobj->startdate == $sessionobj->enddate) {
                        $allsessiondates .= $sessionobj->startdate;
                    } else {
                        $allsessiondates .= $sessionobj->startdate . ' - ' . $sessionobj->enddate;
                    }
                    $sessiontimezonetext = !empty($displaytimezones) ? $sessionobj->timezone : '';
                    $allsessiontimes .= $sessionobj->starttime . ' - ' . $sessionobj->endtime . ' ' . $sessiontimezonetext;
                }
            } else {
                $allsessiondates = get_string('wait-listed', 'facetoface');
                $allsessiontimes = get_string('wait-listed', 'facetoface');
                $sessionwaitlisted = true;
            }
            $sessionrow[] = $allsessiondates;
            $sessionrow[] = $allsessiontimes;

            // Room.
            if (isset($session->room)) {
                $sessionrow[] = facetoface_room_html($session->room);
            } else {
                $sessionrow[] = '';
            }

            // Capacity.
            if ($session->datetimeknown) {
                $signupcount = facetoface_get_num_attendees($session->id, MDL_F2F_STATUS_BOOKED);
            } else {
                $signupcount = facetoface_get_num_attendees($session->id, MDL_F2F_STATUS_WAITLISTED);
            }
            if ($viewattendees) {
                if ($session->datetimeknown) {
                    $a = array('current' => $signupcount, 'maximum' => $session->capacity);
                    $stats = get_string('capacitycurrentofmaximum', 'facetoface', $a);
                    if ($signupcount > $session->capacity) {
                        $stats .= get_string('capacityoverbooked', 'facetoface');
                    }
                    $waitlisted = facetoface_get_num_attendees($session->id, MDL_F2F_STATUS_WAITLISTED) - $signupcount;
                    if ($waitlisted > 0) {
                        $stats .= " (" . $waitlisted . " " . get_string('status_waitlisted', 'facetoface') . ")";
                    }
                } else {
                    $stats = $session->capacity . " (" . $signupcount . " " . get_string('status_waitlisted', 'facetoface') . ")";
                }
            } else {
                $stats = max(0, $session->capacity - $signupcount);
            }
            $sessionrow[] = $stats;

            // Status.
            $status  = get_string('bookingopen', 'facetoface');
            if ($session->datetimeknown && facetoface_has_session_started($session, $timenow) && facetoface_is_session_in_progress($session, $timenow)) {
                $status = get_string('sessioninprogress', 'facetoface');
                $sessionstarted = true;
            } else if ($session->datetimeknown && facetoface_has_session_started($session, $timenow)) {
                $status = get_string('sessionover', 'facetoface');
                $sessionstarted = true;
            } else if ($bookedsession && $session->id == $bookedsession->sessionid) {
                $signupstatus = facetoface_get_status($bookedsession->statuscode);
                $status = get_string('status_'.$signupstatus, 'facetoface');
                $isbookedsession = true;
            } else if ($signupcount >= $session->capacity) {
                $status = get_string('bookingfull', 'facetoface');
                $sessionfull = true;
            }
            // Check if the user is allowed to cancel his booking.
            $allowcancellation = facetoface_allow_user_cancellation($session);

            $sessionrow[] = $status;

            // Options.
            $options = '';
            if ($editsessions) {
                $options .= $this->output->action_icon(new moodle_url('sessions.php', array('s' => $session->id)), new pix_icon('t/edit', get_string('editsession', 'facetoface'))) . ' ';
                $options .= $this->output->action_icon(new moodle_url('sessions.php', array('s' => $session->id, 'c' => 1)), new pix_icon('t/copy', get_string('copysession', 'facetoface'))) . ' ';
                $options .= $this->output->action_icon(new moodle_url('sessions.php', array('s' => $session->id, 'd' => 1)), new pix_icon('t/delete', get_string('deletesession', 'facetoface'))) . ' ';
                $options .= html_writer::empty_tag('br');
            }
            if ($viewattendees) {
                $options .= html_writer::link('attendees.php?s='.$session->id.'&backtoallsessions='.$session->facetoface, get_string('attendees', 'facetoface'), array('title' => get_string('seeattendees', 'facetoface')));
                $options .= html_writer::empty_tag('br');
            }

            // Output links to reserve/allocate spaces.
            if (!empty($reserveinfo)) {
                $sessreserveinfo = $reserveinfo;
                if (!$session->allowoverbook) {
                    $sessreserveinfo = facetoface_limit_reserveinfo_to_capacity_left($session->id, $sessreserveinfo,
                                                                                    max(0, $session->capacity - $signupcount));
                }
                $sessreserveinfo = facetoface_limit_reserveinfo_by_session_date($sessreserveinfo, $session);
                if (!empty($sessreserveinfo['allocate']) && $sessreserveinfo['maxallocate'][$session->id] > 0) {
                    // Able to allocate and not used all allocations for other sessions.
                    $allocateurl = new moodle_url('/mod/facetoface/reserve.php', array('action' => 'allocate', 's' => $session->id,
                                                                                      'backtoallsessions' => $session->facetoface));
                    $options .= html_writer::link($allocateurl, get_string('allocate', 'mod_facetoface'));
                    $options .= ' ('.$sessreserveinfo['allocated'][$session->id].'/'.$sessreserveinfo['maxallocate'][$session->id].')';
                    $options .= html_writer::empty_tag('br');
                }
                if (!empty($sessreserveinfo['reserve']) && $sessreserveinfo['maxreserve'][$session->id] > 0) {
                    if (empty($sessreserveinfo['reservepastdeadline'])) {
                        $reserveurl = new moodle_url('/mod/facetoface/reserve.php', array('action' => 'reserve', 's' => $session->id,
                                                                                         'backtoallsessions' => $session->facetoface));
                        $options .= html_writer::link($reserveurl, get_string('reserve', 'mod_facetoface'));
                        $options .= ' ('.$sessreserveinfo['reserved'][$session->id].'/'.$sessreserveinfo['maxreserve'][$session->id].')';
                        $options .= html_writer::empty_tag('br');
                    }
                } else if (!empty($sessreserveinfo['reserveother']) && empty($sessreserveinfo['reservepastdeadline'])) {
                    $reserveurl = new moodle_url('/mod/facetoface/reserve.php', array('action' => 'reserve', 's' => $session->id,
                                                                                     'backtoallsessions' => $session->facetoface));
                    $options .= html_writer::link($reserveurl, get_string('reserveother', 'mod_facetoface'));
                    $options .= html_writer::empty_tag('br');
                }

                if (has_capability('mod/facetoface:managereservations', $this->context)) {
                    $managereserveurl = new moodle_url('/mod/facetoface/managereservations.php',
                        array('action'=> 'manage', 's' => $session->id));

                    $options .= html_writer::link($managereserveurl, get_string('managereservations', 'mod_facetoface'));
                    $options .= html_writer::empty_tag('br');
                }
            }

            if ($isbookedsession) {
                $signupurl = new moodle_url('/mod/facetoface/signup.php', array('s' => $session->id, 'backtoallsessions' => $session->facetoface));
                $options .= html_writer::link($signupurl, get_string('moreinfo', 'facetoface'), array('title' => get_string('moreinfo', 'facetoface')));
                if ($allowcancellation) {
                    $options .= html_writer::empty_tag('br');
                    $cancelurl = new moodle_url('/mod/facetoface/cancelsignup.php', array('s' => $session->id, 'backtoallsessions' => $session->facetoface));
                    $canceltext = facetoface_is_user_on_waitlist($session) ? 'cancelwaitlist' : 'cancelbooking';
                    $options .= html_writer::link($cancelurl, get_string($canceltext, 'facetoface'), array('title' => get_string($canceltext, 'facetoface')));
                }
            } else if (!$sessionstarted and !$bookedsession) {
                $showsignuplink = true;
                // If Face-to-face enrolment plugin is not enabled check visibility of the activity.
                if (!enrol_is_enabled('totara_facetoface')) {
                    // Check visibility of activity (includes visible flag, conditional availability, etc) before adding Sign up link.
                    $cm = get_coursemodule_from_instance('facetoface', $session->facetoface);
                    $modinfo = get_fast_modinfo($cm->course);
                    $cm = $modinfo->get_cm($cm->id);
                    $showsignuplink = $cm->uservisible;
                }
                if (!facetoface_session_has_capacity($session, $this->context, MDL_F2F_STATUS_WAITLISTED) && !$session->allowoverbook) {
                    $options .= get_string('none', 'facetoface');
                } else if ($showsignuplink) {
                    // Signup link is displayed only if the activity is available for the user or the Face-to-face plugin is enable.
                    $signupurl = new moodle_url('/mod/facetoface/signup.php', array('s' => $session->id, 'backtoallsessions' => $session->facetoface));
                    $signuptext = facetoface_is_signup_by_waitlist($session) ? 'joinwaitlist' : 'signup';
                    $options .= html_writer::link($signupurl, get_string($signuptext, 'facetoface'));
                }
            }
            if (empty($options)) {
                if ($sessionstarted && $allowcancellation) {
                    $cancelurl = new moodle_url('/mod/facetoface/cancelsignup.php', array('s' => $session->id, 'backtoallsessions' => $session->facetoface));
                    $canceltext = facetoface_is_user_on_waitlist($session) ? 'cancelwaitlist' : 'cancelbooking';
                    $options = html_writer::link($cancelurl, get_string($canceltext, 'facetoface'), array('title' => get_string($canceltext, 'facetoface')));
                } else {
                    $options = get_string('none', 'facetoface');
                }
            }
            $sessionrow[] = $options;

            $row = new html_table_row($sessionrow);

            // Set the CSS class for the row.
            if ($sessionstarted) {
                $row->attributes = array('class' => 'dimmed_text');
            } else if ($isbookedsession) {
                $row->attributes = array('class' => 'highlight');
            } else if ($sessionfull) {
                $row->attributes = array('class' => 'dimmed_text');
            }

            // Add row to table.
            $table->data[] = $row;
        }

        $output .= html_writer::table($table);

        return $output;
    }

    /**
     * Main calendar hook function for rendering the f2f filter controls
     *
     * @return string html
     */
    public function calendar_filter_controls() {
        global $SESSION;

        $fields = facetoface_get_customfield_filters();

        $output = '';
        foreach ($fields as $f) {
            $currentval = !empty($SESSION->calendarfacetofacefilter[$f->shortname]) ? $SESSION->calendarfacetofacefilter[$f->shortname] : '';
            $output .= $this->custom_field_chooser($f, $currentval);
        }

        return $output;
    }

    /**
     * Generates a custom field select for a f2f custom field
     *
     * @param int $field
     * @param string $currentval
     *
     * @return string html
     */
    public function custom_field_chooser($field, $currentvalue) {
        global $DB;

        $fieldname = "field_$field->shortname";
        $value = empty($currentvalue) ? '' : $currentvalue;;
        $values = array();
        switch ($field->datatype) {
            case 'multiselect':
                $param1 = json_decode($field->param1, true);
                foreach ($param1 as $option) {
                    $values[] = $option['option'];
                }
                break;
            case 'menu':
                $values = explode("\n", $field->param1);
                break;
            case 'text':
                $label = html_writer::empty_tag('input', array('type' => 'text', 'size' => 15, 'name' => $fieldname, 'value' => $value, 'id' => 'id_' . $fieldname));
                return html_writer::tag('label', format_string($field->fullname) . ':', array('for' => 'id_' . $fieldname)) . $label;
                break;
            case 'checkbox':
                $values = array(0 => get_string('no'), 1 => get_string('yes'));
                break;
            case 'datetime':
                $label = html_writer::empty_tag('input', array('type' => 'text', 'size' => 10, 'name' => $fieldname, 'value' => $value, 'id' => 'id_' . $fieldname));
                build_datepicker_js('#id_' . $fieldname);
                return html_writer::tag('label', format_string($field->fullname) . ':', array('for' => 'id_' . $fieldname)) . $label;
                break;
            case 'textarea':
                $label = html_writer::empty_tag('input', array('type' => 'text', 'size' => 15, 'name' => $fieldname, 'value' => $value, 'id' => 'id_' . $fieldname));
                return html_writer::tag('label', format_string($field->fullname) . ':', array('for' => 'id_' . $fieldname)) . $label;
                break;
            default:
                return false;
        }

        // Build up dropdown list of values.
        $options = array();
        if (!empty($values)) {
            foreach ($values as $value) {
                $v = clean_param(trim($value), PARAM_TEXT);
                if (!empty($v)) {
                    $options[s($v)] = format_string($v);
                }
            }
        }

        $nothing = get_string('all');
        $nothingvalue = 'all';

        $currentvalue = empty($currentvalue) ? $nothingvalue : $currentvalue;

        $dropdown = html_writer::select($options, $fieldname, $currentvalue, array($nothingvalue => $nothing));

        return html_writer::tag('label', format_string($field->fullname) . ':', array('for' => 'id_customfields')) . $dropdown;

    }

    public function setcontext($context) {
        $this->context = $context;
    }

    /**
     * Generate the multiselect inputs + add/remove buttons to control allocating / deallocating users
     * for this session
     *
     * @param object $team containing the lists of users who can be allocated / deallocated
     * @param object $session
     * @param array $reserveinfo details of the number of allocations allowed / left
     * @return string HTML fragment to be output
     */
    function session_user_selector($team, $session, $reserveinfo) {
        $table = new html_table();
        $table->attributes['class'] = 'generaltable generalbox groupmanagementtable boxaligncenter';

        $cells = array();
        // Current allocations.
        $cell = new html_table_cell();
        $cell->id = 'existingcell';
        $info = (object)array(
            'allocated' => $reserveinfo['allocated'][$session->id],
            'max' => $reserveinfo['maxallocate'][$session->id],
        );
        $heading = get_string('currentallocations', 'mod_facetoface', $info);
        $cell->text = html_writer::tag('label', $heading, array('for' => 'deallocation'));
        $selected = optional_param_array('deallocation', array(), PARAM_INT);

        $opts = '';
        $opts .= html_writer::start_tag('optgroup', array('label' => get_string('thissession', 'mod_facetoface')));
        if (empty($team->current)) {
            $opts .= html_writer::tag('option', get_string('none'), array('value' => null, 'disabled' => 'disabled'));
        } else {
            foreach ($team->current as $user) {
                $name = fullname($user);
                $attr = array('value' => $user->id);
                if (in_array($user->id, $selected)) {
                    $attr['selected'] = 'selected';
                }
                if (!empty($user->cannotremove)) {
                    $attr['disabled'] = 'disabled';
                    $name .= ' ('.get_string($user->cannotremove, 'mod_facetoface').')';
                }
                $opts .= html_writer::tag('option', $name, $attr)."\n";
            }
        }
        $opts .= html_writer::end_tag('optgroup');
        if (!empty($team->othersession)) {
            $opts .= html_writer::start_tag('optgroup', array('label' => get_string('othersession', 'mod_facetoface')));
            foreach ($team->othersession as $user) {
                $name = fullname($user);
                $attr = array('value' => $user->id, 'disabled' => 'disabled');
                if (!empty($user->cannotremove)) {
                    $name .= ' ('.get_string($user->cannotremove, 'mod_facetoface').')';
                }
                $opts .= html_writer::tag('option', $name, $attr)."\n";
            }
        }
        $select = html_writer::tag('select', $opts, array('name' => 'deallocation[]', 'multiple' => 'multiple',
                                                          'id' => 'deallocation', 'size' => 20));
        $cell->text .= html_writer::div($select, 'userselector');
        $cells[] = $cell;

        // Buttons.
        $cell = new html_table_cell();
        $cell->id = 'buttonscell';
        $addlabel = $this->output->larrow().' '.get_string('add');
        $buttons = html_writer::empty_tag('input', array('name' => 'add', 'id' => 'add', 'type' => 'submit',
                                                         'value' => $addlabel, 'title' => get_string('add')));
        $buttons .= html_writer::empty_tag('br');
        $removelabel = get_string('remove').' '.$this->output->rarrow();
        $buttons .= html_writer::empty_tag('input', array('name' => 'remove', 'id' => 'remove', 'type' => 'submit',
                                                          'value' => $removelabel, 'title' => get_string('remove')));
        $cell->text = html_writer::tag('p', $buttons, array('class' => 'arrow_button'));
        $cells[] = $cell;

        // Potential allocations.
        $cell = new html_table_cell();
        $cell->id = 'potentialcell';
        $cell->text = html_writer::tag('label', get_string('potentialallocations', 'mod_facetoface',
                                                           $reserveinfo['allocate'][$session->id]),
                                       array('for' => 'allocation'));

        $selected = optional_param_array('allocation', array(), PARAM_INT);
        $optspotential = array();
        foreach ($team->potential as $potential) {
            $optspotential[$potential->id] = fullname($potential);
        }
        $attr = array('multiple' => 'multiple', 'id' => 'allocation', 'size' => 20);
        if ($reserveinfo['allocate'][$session->id] == 0) {
            $attr['disabled'] = 'disabled';
        }
        $select = html_writer::select($optspotential, 'allocation[]', $selected, null, $attr);

        $cell->text .= html_writer::div($select, 'userselector');
        $cells[] = $cell;

        $row = new html_table_row($cells);

        $table->data[] = $row;

        return html_writer::table($table);
    }

    /**
     * Output the given list of reservations/allocations that this manager has made
     * in other sessions in this facetoface.
     *
     * @param object[] $bookings
     * @param object $manager
     * @return string HTML fragment to output the list
     */
    function other_reservations($bookings, $manager) {
        global $USER;

        if (!$bookings) {
            return '';
        }

        // Gather the session data together.
        $sessions = array();
        foreach ($bookings as $booking) {
            if (!isset($sessions[$booking->sessionid])) {
                $session = facetoface_get_session($booking->sessionid);
                $sessions[$booking->sessionid] = (object)array(
                    'reservations' => 0,
                    'bookings' => array(),
                    'dates' => facetoface_format_session_dates($session),
                );
            }
            if ($booking->userid) {
                $sessions[$booking->sessionid]->bookings[$booking->userid] = fullname($booking);
            } else {
                $sessions[$booking->sessionid]->reservations++;
            }
        }

        // Output the details as a table.
        if ($manager->id == $USER->id) {
            $bookingstr = get_string('yourbookings', 'facetoface');
        } else {
            $bookingstr = get_string('managerbookings', 'facetoface', fullname($manager));
        }
        $table = new html_table();
        $table->head = array(
            get_string('sessiondatetime', 'facetoface'),
            $bookingstr,
        );
        $table->attributes = array('class' => 'generaltable managerbookings');

        foreach ($sessions as $session) {
            $details = array();
            if ($session->reservations) {
                $details[] = get_string('reservations', 'mod_facetoface', $session->reservations);
            }
            $details += $session->bookings;
            $details = '<li>'.implode('</li><li>', $details).'</li>';
            $details = html_writer::tag('ul', $details);
            $row = new html_table_row(array($session->dates, $details));
            $table->data[] = $row;
        }

        $heading = $this->output->heading(get_string('existingbookings', 'mod_facetoface'), 3);

        return $heading . html_writer::table($table);
    }

    /**
     * Manage customfield tabs displayed in customfield/index.php
     *
     * @param string $currenttab
     * @return string tabs
     */
    public function customfield_management_tabs($currenttab = 'facetofacesession') {
        $tabs = array();
        $row = array();
        $activated = array();
        $inactive = array();

        $row[] = new tabobject('facetofacesession', new moodle_url('/mod/facetoface/customfields.php', array('prefix' => 'facetofacesession')),
            get_string('sessioncustomfieldtab', 'facetoface'));
        $row[] = new tabobject('facetofacesignup', new moodle_url('/mod/facetoface/customfields.php', array('prefix' => 'facetofacesignup')),
            get_string('signupcustomfieldtab', 'facetoface'));
        $row[] = new tabobject('facetofacecancellation', new moodle_url('/mod/facetoface/customfields.php', array('prefix' => 'facetofacecancellation')),
            get_string('cancellationcustomfieldtab', 'facetoface'));

        $tabs[] = $row;
        $activated[] = $currenttab;

        return print_tabs($tabs, $currenttab, $inactive, $activated, true);
    }

    /**
     * Show a list of all reservations for a session and allow them to be removed.
     *
     * @param object $reservations Data about all the reservations
     */
    public function print_reservation_management_table($reservations) {

        $out = '';

        if (count($reservations) > 0) {
            $table = new html_table();
            $table->head = array(
                get_string('managername', 'mod_facetoface'),
                get_string('spacesreserved', 'mod_facetoface'),
                get_string('actions'));

            $table->attributes = array('class' => 'generaltable managereservations fullwidth');

            $strdelete = get_string('delete');

            foreach ($reservations as $reservation) {
                $managername = fullname($reservation);

                $managerlink = html_writer::link(new moodle_url('/user/profile.php',
                    array('id' => $reservation->bookedby)), $managername);

                $deleteurl = new moodle_url('/mod/facetoface/managereservations.php', array('s' => $reservation->sessionid,
                    'action' => 'delete', 'managerid' => $reservation->bookedby, 'sesskey' => sesskey()));
                $buttons = $this->action_icon($deleteurl, new pix_icon('t/delete', $strdelete));

                $row = new html_table_row(array($managerlink, $reservation->reservedspaces, $buttons));
                $table->data[] = $row;
            }

            $out .= html_writer::table($table);

        } else {
            $out .= html_writer::tag('p', get_string('noreservationsforsession', 'mod_facetoface'));
        }

        return $out;
    }
}
