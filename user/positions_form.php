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
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package totara
 * @subpackage totara_hierarchy
 */

require_once($CFG->dirroot.'/lib/formslib.php');

class user_position_assignment_form extends moodleform {

    // Define the form
    function definition () {
        global $CFG, $DB, $POSITION_TYPES;

        $mform = $this->_form;
        $type = $this->_customdata['type'];
        $pa = $this->_customdata['position_assignment'];
        $submitted = $this->_customdata['submitted'];
        $submittedpositionid = $this->_customdata['submittedpositionid'];
        $submittedorganisationid = $this->_customdata['submittedorganisationid'];
        $submittedmanagerid = $this->_customdata['submittedmanagerid'];
        $submittedappraiserid = $this->_customdata['submittedappraiserid'];
        $submittedtempmanagerid = $this->_customdata['submittedtempmanagerid'];
        $editoroptions = $this->_customdata['editoroptions'];
        $can_edit = $this->_customdata['can_edit'];
        $can_edit_tempmanager = empty($this->_customdata['can_edit_tempmanager']) ? 0 : 1;

        // Check if a primary position.
        $primary = isset($POSITION_TYPES[POSITION_TYPE_PRIMARY])
            && $type == $POSITION_TYPES[POSITION_TYPE_PRIMARY] ? true : false;

        // Check if an aspirational position
        $aspirational = false;
        if (isset($POSITION_TYPES[POSITION_TYPE_ASPIRATIONAL]) && $type == $POSITION_TYPES[POSITION_TYPE_ASPIRATIONAL]) {
            $aspirational = true;
        }

        if ($submitted) {
            $positionid = $submittedpositionid;
            $organisationid = $submittedorganisationid;
            $appraiserid = $submittedappraiserid;
        } else {
            $positionid = $pa->positionid;
            $organisationid = $pa->organisationid;
            $appraiserid = $pa->appraiserid;
        }

        // Get position title
        $position_title = '';
        if ($positionid) {
            $position_title = $DB->get_field('pos', 'fullname', array('id' => $positionid));
        }

        // Get organisation title
        $organisation_title = '';
        if ($organisationid) {
            $organisation_title = $DB->get_field('org', 'fullname', array('id' => $organisationid));
        }

        // The fields required to display the name of a user.
        $usernamefields = get_all_user_name_fields(true, 'u');

        // Get manager title.
        $manager_title = '';
        $manager_id = 0;
        if ($submitted) {
            if ($submittedmanagerid) {
                $manager = $DB->get_record_sql(
                    "SELECT
                        u.id,
                        {$usernamefields}
                     FROM
                        {user} u
                     WHERE
                        u.id = ?",
                     array($submittedmanagerid));

                if ($manager) {
                    $manager_title = fullname($manager);
                    $manager_id = $manager->id;
                }
            }
        } else if ($pa->reportstoid) {
            $manager = $DB->get_record_sql(
                "SELECT
                    u.id,
                    {$usernamefields},
                    ra.id AS ra
                 FROM
                    {user} u
                 INNER JOIN
                    {role_assignments} ra
                     ON u.id = ra.userid
                 WHERE
                    ra.id = ?",
                 array($pa->reportstoid));

            if ($manager) {
                $manager_title = fullname($manager);
                $manager_id = $manager->id;
            }
        }

        // Get appraiser title.
        $appraiser_title = '';
        $appraiser_id = 0;
        if ($appraiserid) {
            $appraiser = $DB->get_record_sql(
                "SELECT
                    u.id,
                    {$usernamefields}
                 FROM
                    {user} u
                 WHERE
                    u.id = ?",
                 array($appraiserid));
            if ($appraiser) {
                $appraiser_title = fullname($appraiser);
                $appraiser_id = $appraiser->id;
            }
        }

        // Add some extra hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'general', get_string('type'.$type, 'totara_hierarchy'));

        if (!$aspirational) {
            $mform->addElement('text', 'fullname', get_string('titlefullname', 'totara_hierarchy'));
            $mform->setType('fullname', PARAM_TEXT);
            $mform->addHelpButton('fullname', 'titlefullname', 'totara_hierarchy');

            $mform->addElement('text', 'shortname', get_string('titleshortname', 'totara_hierarchy'));
            $mform->setType('shortname', PARAM_TEXT);
            $mform->addHelpButton('shortname', 'titleshortname', 'totara_hierarchy');

            $mform->addElement('editor', 'description_editor', get_string('pos_description', 'totara_core'), null, $editoroptions);
            $mform->setType('description_editor', PARAM_CLEANHTML);
            $mform->addHelpButton('description_editor', 'pos_description', 'totara_core');
        }

        if (!totara_feature_disabled('positions')) {
            $pos_class = strlen($position_title) ? 'nonempty' : '';
            $mform->addElement('static', 'positionselector', get_string('position', 'totara_hierarchy'),
                html_writer::tag('span', format_string($position_title), array('class' => $pos_class, 'id' => 'positiontitle')).
                ($can_edit ? html_writer::empty_tag('input', array('type' => 'button', 'value' => get_string('chooseposition', 'totara_hierarchy'), 'id' => 'show-position-dialog')) : '')
            );
            $mform->addElement('hidden', 'positionid');
            $mform->setType('positionid', PARAM_INT);
            $mform->setDefault('positionid', 0);
            if (!$aspirational) {
                $mform->addHelpButton('positionselector', 'chooseposition', 'totara_hierarchy');
            } else {
                $mform->addHelpButton('positionselector', 'useraspirationalposition', 'totara_hierarchy');
            }
        }

        if (!$aspirational) {
            $org_class = strlen($organisation_title) ? 'nonempty' : '';
            $mform->addElement('static', 'organisationselector', get_string('organisation', 'totara_hierarchy'),
                html_writer::tag('span', format_string($organisation_title), array('class' => $org_class, 'id' => 'organisationtitle')) .
                ($can_edit ? html_writer::empty_tag('input', array('type' => 'button', 'value' => get_string('chooseorganisation', 'totara_hierarchy'), 'id' => 'show-organisation-dialog')) : '')
            );

            $mform->addElement('hidden', 'organisationid');
            $mform->setType('organisationid', PARAM_INT);
            $mform->setDefault('organisationid', 0);
            $mform->addHelpButton('organisationselector', 'chooseorganisation', 'totara_hierarchy');

            $mform->addElement('date_selector', 'timevalidfrom', get_string('startdate', 'totara_hierarchy'), array('optional' => true));
            $mform->addHelpButton('timevalidfrom', 'startdate', 'totara_hierarchy');
            $mform->setDefault('timevalidfrom', 0);

            $mform->addElement('date_selector', 'timevalidto', get_string('finishdate', 'totara_hierarchy'), array('optional' => true));
            $mform->addHelpButton('timevalidto', 'finishdate', 'totara_hierarchy');
            $mform->setDefault('timevalidto', 0);

            // Manager details.
            $mform->addElement('header', 'managerheader', get_string('manager', 'totara_hierarchy'));

            // Show manager
            // If we can edit, show button. Else show link to manager's profile
            if ($can_edit) {
                $manager_class = strlen($manager_title) ? 'nonempty' : '';
                $mform->addElement(
                    'static',
                    'managerselector',
                    get_string('manager', 'totara_hierarchy'),
                    html_writer::tag('span', format_string($manager_title), array('class' => $manager_class, 'id' => 'managertitle'))
                    . html_writer::empty_tag('input', array('type' => 'button', 'value' => get_string('choosemanager', 'totara_hierarchy'), 'id' => 'show-manager-dialog'))
                );
            } else {
                $mform->addElement(
                    'static',
                    'managerselector',
                    get_string('manager', 'totara_hierarchy'),
                    html_writer::tag('span', html_writer::link(new moodle_url('/user/view.php', array('id' => $manager_id)), format_string($manager_title)), array('id' => 'managertitle'))
                );
            }

            $mform->addElement('hidden', 'managerid');
            $mform->setType('managerid', PARAM_INT);
            $mform->setDefault('managerid', $manager_id);
            $mform->addHelpButton('managerselector', 'choosemanager', 'totara_hierarchy');

            if (!totara_feature_disabled('appraisals')) {
                // Show appraiser.
                // If we can edit, show button. Else show link to appraiser's profile.
                if ($can_edit) {
                    $appraiser_class = strlen($appraiser_title) ? 'nonempty' : '';
                    $mform->addElement(
                        'static',
                        'appraiserselector',
                        get_string('appraiser', 'totara_hierarchy'),
                        html_writer::tag('span', format_string($appraiser_title),
                            array('class' => $appraiser_class, 'id' => 'appraisertitle')) .
                        html_writer::empty_tag('input', array('type' => 'button',
                            'value' => get_string('chooseappraiser', 'totara_hierarchy'), 'id' => 'show-appraiser-dialog'))
                    );
                } else {
                    $mform->addElement(
                        'static',
                        'appraiserselector',
                        get_string('appraiser', 'totara_hierarchy'),
                        html_writer::tag('span', html_writer::link(new moodle_url('/user/view.php',
                            array('id' => $appraiser_id)), format_string($appraiser_title)), array('id' => 'appraisertitle'))
                    );
                }

                $mform->addElement('hidden', 'appraiserid');
                $mform->setType('appraiserid', PARAM_INT);
                $mform->setDefault('appraiserid', $appraiser_id);
                $mform->addHelpButton('appraiserselector', 'chooseappraiser', 'totara_hierarchy');
            }


            if ($primary && !empty($CFG->enabletempmanagers)) {
                // Temporary manager.
                if ($submitted) {
                    $tempmanager = $DB->get_record('user', array('id' => $submittedtempmanagerid));
                    $tempmanager_expiry = null;
                } else {
                    $tempmanager = totara_get_manager($pa->userid, null, false, true);
                    $tempmanager_expiry = !empty($tempmanager) ? $tempmanager->expirytime : null;
                }

                $tempmanager_id = !empty($tempmanager->id) ? $tempmanager->id : 0;
                $tempmanager_title = !empty($tempmanager) ? fullname($tempmanager) : '';
                // If we can edit, show button, else show link to manager's profile.
                if ($can_edit_tempmanager) {
                    $tempmanager_class = strlen($tempmanager_title) ? 'nonempty' : '';
                    $mform->addElement(
                        'static',
                        'tempmanagerselector',
                        get_string('tempmanager', 'totara_core'),
                        html_writer::tag('span', format_string($tempmanager_title),
                                array('class' => $tempmanager_class, 'id' => 'tempmanagertitle')) .
                        html_writer::empty_tag('input', array('type' => 'button',
                                'value' => get_string('choosetempmanager', 'totara_core'), 'id' => 'show-tempmanager-dialog'))
                    );
                } else {
                    $mform->addElement(
                        'static',
                        'tempmanagerselector',
                        get_string('tempmanager', 'totara_core'),
                        html_writer::tag('span', html_writer::link(new moodle_url('/user/view.php',
                                array('id' => $tempmanager_id)), format_string($tempmanager_title)),
                                array('id' => 'tempmanagertitle'))
                    );
                }

                $mform->addElement('hidden', 'tempmanagerid');
                $mform->setType('tempmanagerid', PARAM_INT);
                $mform->setDefault('tempmanagerid', $tempmanager_id);
                $mform->addHelpButton('tempmanagerselector', 'choosetempmanager', 'totara_core');

                $mform->addElement('date_selector', 'tempmanagerexpiry', get_string('tempmanagerexpiry', 'totara_core'), array('optional' => true));
                if ($tempmanager_id > 0) {
                    $tempmanager_expiry ?  $tempmanager_expiry : strtotime($CFG->tempmanagerexpirydays . ' days');
                } else {
                    $tempmanager_expiry = 0;
                }
                $mform->setDefault('tempmanagerexpiry', $tempmanager_expiry);
                $mform->addHelpButton('tempmanagerexpiry', 'tempmanagerexpiry', 'totara_core');
            }
        }

        $this->add_action_buttons(true, get_string('updateposition', 'totara_hierarchy'));

    }

    function definition_after_data() {
        $can_edit = $this->_customdata['can_edit'];
        // Freeze the form if appropriate.
        if (!$can_edit) {
            $this->freezeForm();
        }
    }

    function freezeForm() {
        $mform = $this->_form;
        $can_edit_tempmanager = empty($this->_customdata['can_edit_tempmanager']) ? 0 : 1;

        // Tempmanager - skip some elements.
        $skipelements = array();
        if ($can_edit_tempmanager) {
            // Freeze the form except for temp manager functionality.
            $skipelements = array('tempmanagerselector', 'tempmanagerid', 'tempmanagerexpiry', 'buttonar');
        }
        $mform->hardFreezeAllVisibleExcept($skipelements);

        // Get date format with abstract values to match to date_selector value format.
        $dateformat = array('day' => date('d'), 'month' => date('n'), 'year' => date('Y'), 'enabled' => true);
        // Hide elements with no values
        foreach (array_keys($mform->_elements) as $key) {
            $element =& $mform->_elements[$key];
            if (in_array($element->getName(), $skipelements)) {
                continue;
            }
            // Check static elements differently
            if ($element->getType() == 'static') {
                // Check if it is a js selector
                if (substr($element->getName(), -8) == 'selector') {
                    // Get id element
                    $elementid = $mform->getElement(substr($element->getName(), 0, -8).'id');
                    if (!$elementid || !$elementid->getValue()) {
                        $mform->removeElement($element->getName());
                    }
                    continue;
                }
            }
            // Get element value
            $value = $element->getValue();
            // Check groups
            // (matches date groups and action buttons)
            if (is_array($value)) {
                $diff = array_diff_key($dateformat, $value);
                if (empty($diff)) {
                    // This is a date_selector value which we do not want to remove.
                    continue;
                }
                // If values are strings (e.g. buttons, or date format string), remove
                foreach ($value as $k => $v) {
                    if (!is_numeric($v)) {
                        $mform->removeElement($element->getName());
                        break;
                    }
                }
            }
            // Otherwise check if empty
            elseif (!$value) {
                $mform->removeElement($element->getName());
            }
        }
    }

    function validation($data, $files) {
        global $DB, $POSITION_TYPES;

        $mform = $this->_form;
        $can_edit_tempmanager = empty($this->_customdata['can_edit_tempmanager']) ? 0 : 1;
        $type = array_search($this->_customdata['type'], $POSITION_TYPES);

        // No validation required for the aspirations position.
        if (isset($POSITION_TYPES[POSITION_TYPE_ASPIRATIONAL]) && $type == POSITION_TYPE_ASPIRATIONAL) {
            return array();
        }

        $userid = (string) $this->_customdata['user']->id;
        $managerid = $mform->getElement('managerid')->getValue();
        $result = array();

        // Enforce start date before finish date, for primary and secondary positions.
        if ($data['timevalidfrom'] > $data['timevalidto'] && $data['timevalidfrom'] !== 0 && $data['timevalidto'] !== 0) {
            $errstr = get_string('error:startafterfinish','totara_hierarchy');
            $result['timevalidfrom'] = $errstr;
            $result['timevalidto'] = $errstr;
            unset($errstr);
        }

        if ($managerid) {
            // Get the line management structure with the new manager in place so
            // we can check that a circular management structure doesn't exist.
            $managerids = $DB->get_records_menu('pos_assignment', array('type' => $type), 'userid', 'userid,managerid');
            $managerids[$userid] = $managerid;
            $managementpathids = totara_get_lineage($managerids, $userid, array(), false);

            if ($managementpathids === false) {
                $result['managerselector'] = get_string('error:managercircular', 'totara_core');
            }
        }

        // If setting a temporary manager, check that an expiry date is set.
        if ($can_edit_tempmanager && $mform->getElement('tempmanagerid')->getValue()) {
            if (empty($data['tempmanagerexpiry'])) {
                $result['tempmanagerexpiry'] = get_string('error:tempmanagerexpirynotset', 'totara_core');
            } else {
                if (time() >  $data['tempmanagerexpiry'] && $data['tempmanagerexpiry'] !== 0) {
                    $result['tempmanagerexpiry'] = get_string('error:datenotinfuture', 'totara_core');
                }
            }
        }

        if (!empty($result)) {
            totara_set_notification(get_string('error:positionvalidationfailed', 'totara_core'));
        }

        return $result;
    }
}
