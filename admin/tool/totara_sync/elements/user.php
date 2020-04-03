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
 * @author Eugene Venter <eugene@catalyst.net.nz>
 * @package totara
 * @subpackage totara_sync
 */

require_once($CFG->dirroot.'/admin/tool/totara_sync/elements/classes/element.class.php');
require_once($CFG->dirroot.'/totara/customfield/fieldlib.php');
require_once($CFG->dirroot.'/totara/hierarchy/prefix/position/lib.php');

class totara_sync_element_user extends totara_sync_element {
    const KEEP_USERS = 0;
    const DELETE_USERS = 1;
    const SUSPEND_USERS = 2;

    protected $customfieldsdb = array();

    function get_name() {
        return 'user';
    }

    function has_config() {
        return true;
    }

    /**
     * Set customfieldsdb property with menu of choices options
     */
    function set_customfieldsdb() {
        global $DB;

        $rs = $DB->get_recordset('user_info_field', array(), '', 'id,shortname,datatype,required,defaultdata,locked,forceunique,param1');
        if ($rs->valid()) {
            foreach ($rs as $r) {
                $this->customfieldsdb['customfield_'.$r->shortname]['id'] = $r->id;
                $this->customfieldsdb['customfield_'.$r->shortname]['required'] = $r->required;
                $this->customfieldsdb['customfield_'.$r->shortname]['forceunique'] = $r->forceunique;
                $this->customfieldsdb['customfield_'.$r->shortname]['default'] = $r->defaultdata;

                if ($r->datatype == 'menu') {
                    // Set all options to lower case to match values to options without case sensitivity.
                    $options = explode("\n", core_text::strtolower($r->param1));
                    $this->customfieldsdb['customfield_'.$r->shortname]['menu_options'] = $options;
                }
            }
        }
        $rs->close();
    }

    function config_form(&$mform) {
        $mform->addElement('selectyesno', 'sourceallrecords', get_string('sourceallrecords', 'tool_totara_sync'));
        $mform->addElement('static', 'sourceallrecordsdesc', '', get_string('sourceallrecordsdesc', 'tool_totara_sync'));

        // User email settings.
        $mform->addElement('selectyesno', 'allowduplicatedemails', get_string('allowduplicatedemails', 'tool_totara_sync'));
        $mform->addElement('text', 'defaultsyncemail', get_string('defaultemailaddress', 'tool_totara_sync'), array('size' => 50));
        $mform->addElement('static', 'emailsettingsdesc', '', get_string('emailsettingsdesc', 'tool_totara_sync'));
        $mform->setType('defaultsyncemail', PARAM_TEXT);
        $mform->disabledIf('defaultsyncemail', 'allowduplicatedemails', 'eq', 0);
        $mform->setDefault('defaultsyncemail', '');


        // User password settings.
        $mform->addElement('selectyesno', 'ignoreexistingpass', get_string('ignoreexistingpass', 'tool_totara_sync'));
        $mform->addElement('static', 'ignoreexistingpassdesc', '', get_string('ignoreexistingpassdesc', 'tool_totara_sync'));
        $mform->addElement('selectyesno', 'forcepwchange', get_string('forcepwchange', 'tool_totara_sync'));
        $mform->addElement('static', 'forcepwchangedesc', '', get_string('forcepwchangedesc', 'tool_totara_sync'));
        $mform->addElement('selectyesno', 'undeletepwreset', get_string('undeletepwreset', 'tool_totara_sync'));
        $mform->addElement('static', 'undeletepwresetdesc', '', get_string('undeletepwresetdesc', 'tool_totara_sync'));

        $mform->addElement('header', 'crudheading', get_string('allowedactions', 'tool_totara_sync'));
        $mform->addElement('checkbox', 'allow_create', get_string('create', 'tool_totara_sync'));
        $mform->setDefault('allow_create', 1);
        $mform->addElement('checkbox', 'allow_update', get_string('update', 'tool_totara_sync'));
        $mform->setDefault('allow_update', 1);
        $deleteopt = array();
        $deleteopt[self::KEEP_USERS] = get_string('auth_remove_keep','auth');
        $deleteopt[self::SUSPEND_USERS] = get_string('auth_remove_suspend','auth');
        $deleteopt[self::DELETE_USERS] = get_string('auth_remove_delete','auth');
        $mform->addElement('select', 'allow_delete', get_string('delete', 'tool_totara_sync'), $deleteopt);
        $mform->setDefault('allow_delete', self::KEEP_USERS);
        $mform->setExpanded('crudheading');
    }

    function validation($data, $files) {
        $errors = array();
        if ($data['allowduplicatedemails'] && !empty($data['defaultsyncemail']) && !validate_email($data['defaultsyncemail'])) {
            $errors['defaultsyncemail'] = get_string('invalidemail');
        }
        return $errors;
    }

    function config_save($data) {
        $this->set_config('sourceallrecords', $data->sourceallrecords);
        $this->set_config('allowduplicatedemails', $data->allowduplicatedemails);
        if (!empty($data->allow_create)) {
            // When user creation is allowed, force change the first name and last name settings on.
            set_config('import_firstname', "1", 'totara_sync_source_user_csv');
            set_config('import_firstname', "1", 'totara_sync_source_user_database');
            set_config('import_lastname', "1", 'totara_sync_source_user_csv');
            set_config('import_lastname', "1", 'totara_sync_source_user_database');
            if (empty($data->allowduplicatedemails)) {
                // When user creation is allowed and duplicate emails are not allowed, force change the email settings on.
                set_config('import_email', "1", 'totara_sync_source_user_csv');
                set_config('import_email', "1", 'totara_sync_source_user_database');
            }
        }
        $this->set_config('defaultsyncemail', $data->defaultsyncemail);
        $this->set_config('ignoreexistingpass', $data->ignoreexistingpass);
        $this->set_config('forcepwchange', $data->forcepwchange);
        $this->set_config('undeletepwreset', $data->undeletepwreset);
        $this->set_config('allow_create', !empty($data->allow_create));
        $this->set_config('allow_update', !empty($data->allow_update));
        $this->set_config('allow_delete', $data->allow_delete);
        if (!empty($data->source_user)) {
            $source = $this->get_source($data->source_user);
            // Build link to source config.
            $url = new moodle_url('/admin/tool/totara_sync/admin/sourcesettings.php', array('element' => $this->get_name(), 'source' => $source->get_name()));
            if ($source->has_config()) {
                // Set import_deleted and warn if necessary.
                $import_deleted_new = ($data->sourceallrecords == 0) ? '1' : '0';
                $import_deleted_old = $source->get_config('import_deleted');
                if ($import_deleted_new != $import_deleted_old) {
                    $source->set_config('import_deleted', $import_deleted_new);
                    totara_set_notification(get_string('checkuserconfig', 'tool_totara_sync', $url->out()), null, array('class'=>'notifynotice'));
                }
            }
        }
    }

    function sync() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        $this->addlog(get_string('syncstarted', 'tool_totara_sync'), 'info', 'usersync');
        // Array to store the users we create or update that
        // will need to have their assignments synced.
        $assign_sync_users = array();

        try {
            // This can go wrong in many different ways - catch as a generic exception.
            $synctable = $this->get_source_sync_table();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (debugging()) {
                $msg .= !empty($e->debuginfo) ? " - {$e->debuginfo}" : '';
            }
            totara_sync_log($this->get_name(), $msg, 'error', 'unknown');
            return false;
        }

        try {
            // This can go wrong in many different ways - catch as a generic exception.
            $synctable_clone = $this->get_source_sync_table_clone($synctable);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (debugging()) {
                $msg .= !empty($e->debuginfo) ? " - {$e->debuginfo}" : '';
            }
            totara_sync_log($this->get_name(), $msg, 'error', 'unknown');
            return false;
        }

        $this->set_customfieldsdb();

        $invalididnumbers = $this->check_sanity($synctable, $synctable_clone);
        $issane = (empty($invalididnumbers) ? true : false);
        $problemswhileapplying = false;

        // Initialise to safe defaults if settings not present.
        if (!isset($this->config->sourceallrecords)) {
            $this->config->sourceallrecords = 0;
        }
        if (!isset($this->config->allow_create)) {
            $this->config->allow_create = 0;
        }
        if (!isset($this->config->allow_update)) {
            $this->config->allow_update = 0;
        }
        if (!isset($this->config->allow_delete)) {
            $this->config->allow_delete = self::KEEP_USERS;
        }

        // May sure the required deleted column is present if necessary.
        $synctablecolumns = $DB->get_columns($synctable);
        $deletedcolumnpresent = isset($synctablecolumns['deleted']);

        if ($this->config->allow_delete == self::DELETE_USERS) {
            $sql = null;
            if ($this->config->sourceallrecords == 0) {
                if ($deletedcolumnpresent) {
                    // Get records with "deleted" flag set.
                    // Do not use DISTINCT here, idnumber may not be unique - we want errors for all duplicates.
                    // If there are repeated rows in external table we will just delete twice.
                    $sql = "SELECT u.id, u.idnumber, u.auth
                              FROM {{$synctable}} s
                              JOIN {user} u ON (s.idnumber = u.idnumber AND u.idnumber != '')
                             WHERE u.totarasync = 1 AND u.deleted = 0 AND s.deleted = 1";
                }
            } else if ($this->config->sourceallrecords == 1) {
                // All records provided by source - get missing user records.
                // Also consider the deleted flag if present.
                if ($deletedcolumnpresent) {
                    $sql = "SELECT u.id, u.idnumber, u.auth
                              FROM {user} u
                         LEFT JOIN {{$synctable}} s ON (u.idnumber = s.idnumber AND u.idnumber != '')
                             WHERE u.totarasync = 1 AND u.deleted = 0 AND (s.idnumber IS NULL OR s.deleted = 1)";
                } else {
                    $sql = "SELECT u.id, u.idnumber, u.auth
                              FROM {user} u
                         LEFT JOIN {{$synctable}} s ON (u.idnumber = s.idnumber AND u.idnumber != '')
                             WHERE u.totarasync = 1 AND u.deleted = 0 AND s.idnumber IS NULL";
                }
            }
            if ($sql) {
                $rs = $DB->get_recordset_sql($sql);
                foreach ($rs as $user) {
                    // Remove user.
                    try {
                        // Do not delete the records which have invalid values(e.g. spelling mistake).
                        if (array_search($user->idnumber, $invalididnumbers) === false) {
                            $usr = $DB->get_record('user', array('id' => $user->id));
                            // Check for guest account record.
                            if ($usr->username === 'guest' || isguestuser($usr)) {
                                $this->addlog(get_string('cannotdeleteuserguest', 'tool_totara_sync', $user->idnumber), 'warn', 'deleteuser');
                                $problemswhileapplying = true;
                                continue;
                            }
                            // Check for admin account record.
                            if ($usr->auth === 'manual' && is_siteadmin($usr)) {
                                $this->addlog(get_string('cannotdeleteuseradmin', 'tool_totara_sync', $user->idnumber), 'warn', 'deleteuser');
                                $problemswhileapplying = true;
                                continue;
                            }
                            if (delete_user($usr)) {
                                $this->addlog(get_string('deleteduserx', 'tool_totara_sync', $user->idnumber), 'info', 'deleteuser');
                            } else {
                                $this->addlog(get_string('cannotdeleteuserx', 'tool_totara_sync', $user->idnumber), 'warn', 'deleteuser');
                                $problemswhileapplying = true;
                            }
                        }
                    } catch (Exception $e) {
                        // We don't want this exception to stop processing so we will continue.
                        // The code may have started a transaction. If it did then roll back the transaction.
                        if ($DB->is_transaction_started()) {
                            $DB->force_transaction_rollback();
                        }
                        $this->addlog(get_string('cannotdeleteuserx', 'tool_totara_sync', $user->idnumber) . ': ' .
                            $e->getMessage(), 'warn', 'deleteuser');
                        $problemswhileapplying = true;
                        continue; // Continue processing users.
                    }
                }
                $rs->close();
            }

        } else if ($this->config->allow_delete == self::SUSPEND_USERS) {
            $sql = null;
            if ($this->config->sourceallrecords == 0) {
                if ($deletedcolumnpresent) {
                    // Get records with "deleted" flag set.
                    // Do not use DISTINCT here, idnumber may not be unique - we want errors for all duplicates.
                    // If there are repeated rows in external table we will just delete twice.
                    $sql = "SELECT u.id, u.idnumber, u.auth
                              FROM {{$synctable}} s
                              JOIN {user} u ON (s.idnumber = u.idnumber AND u.idnumber != '')
                             WHERE u.totarasync = 1 AND u.deleted = 0 AND u.suspended = 0 AND s.deleted = 1";
                }
            } else if ($this->config->sourceallrecords == 1) {
                // All records provided by source - get missing user records.
                // Also consider the deleted flag if present.
                if ($deletedcolumnpresent) {
                    $sql = "SELECT u.id, u.idnumber, u.auth
                              FROM {user} u
                         LEFT JOIN {{$synctable}} s ON (u.idnumber = s.idnumber AND u.idnumber != '')
                             WHERE u.totarasync = 1 AND u.deleted = 0 AND u.suspended = 0 AND (s.idnumber IS NULL OR s.deleted = 1)";
                } else {
                    $sql = "SELECT u.id, u.idnumber, u.auth
                              FROM {user} u
                         LEFT JOIN {{$synctable}} s ON (u.idnumber = s.idnumber AND u.idnumber != '')
                             WHERE u.totarasync = 1  AND u.deleted = 0 AND u.suspended = 0 AND s.idnumber IS NULL";
                }
            }
            if ($sql) {
                $rs = $DB->get_recordset_sql($sql);
                foreach ($rs as $user) {
                    // Do not suspend the records which have invalid values(e.g. spelling mistake).
                    if (array_search($user->idnumber, $invalididnumbers) === false) {
                        $user = $DB->get_record('user', array('id' => $user->id));
                        $user->suspended = 1;
                        \core\session\manager::kill_user_sessions($user->id);
                        user_update_user($user, false);
                        \totara_core\event\user_suspended::create_from_user($user)->trigger();
                        $this->addlog(get_string('suspendeduserx', 'tool_totara_sync', $user->idnumber), 'info', 'suspenduser');
                    }
                }
                $rs->close();
            }
        }

        if ($deletedcolumnpresent) {
            // Remove the deleted records from the sync table.
            // This ensures that our create/update queries runs smoothly.
            $DB->execute("DELETE FROM {{$synctable}} WHERE deleted <> 0");
            $DB->execute("DELETE FROM {{$synctable_clone}} WHERE deleted <> 0");
        }

        if (!empty($this->config->allow_update)) {
            // This must be done before creating new accounts because once the accounts are created this query would return them as well,
            // even when they do not need to be updated.
            $sql = "SELECT s.*, u.id AS uid
                      FROM {user} u
                INNER JOIN {{$synctable}} s ON (u.idnumber = s.idnumber AND u.idnumber != '')
                     WHERE u.totarasync=1
                       AND (s.timemodified = 0 OR u.timemodified != s.timemodified)";  // If no timemodified, always update.
            $rsupdateaccounts = $DB->get_recordset_sql($sql);
        }

        if (!empty($this->config->allow_create)) {
            // Get accounts that must be created.
            $sql = "SELECT s.*
                      FROM {{$synctable}} s
           LEFT OUTER JOIN {user} u ON (s.idnumber=u.idnumber)
                     WHERE u.idnumber IS NULL AND s.idnumber IS NOT NULL AND s.idnumber != ''";
            $rscreateaccounts = $DB->get_recordset_sql($sql);

            // The idea of doing this is to get the accounts that need to be created. Since users are created first and then user assignments,
            // it is not possible (after creating users) to know which accounts need to be created.
            $DB->execute("DELETE FROM {{$synctable_clone}}
                           WHERE idnumber IN (
                          SELECT s.idnumber
                            FROM {user} u
                      INNER JOIN {{$synctable}} s ON (u.idnumber = s.idnumber AND u.idnumber != ''))");

            // Create missing accounts.
            foreach ($rscreateaccounts as $suser) {
                try {
                    $this->create_user($suser);
                    $this->addlog(get_string('createduserx', 'tool_totara_sync', $suser->idnumber), 'info', 'createuser');
                } catch (Exception $e) {
                    // We don't need to (and don't want to) do any rollback here because we trust that create_user has done it.
                    $this->addlog(get_string('cannotcreateuserx', 'tool_totara_sync', $suser->idnumber) . ': ' .
                            $e->getMessage(), 'error', 'createuser');
                    $problemswhileapplying = true;
                    continue; // Continue processing users.
                }
            }
            $rscreateaccounts->close(); // Free memory.

            // Get data for user assignments for assignment sync later.
            $sql = "SELECT sc.*, u.id as uid
                      FROM {{$synctable_clone}} sc
                INNER JOIN {user} u ON (sc.idnumber = u.idnumber AND u.idnumber != '')";
            $rscreateassignments = $DB->get_recordset_sql($sql);
            foreach ($rscreateassignments as $suser) {
                $assign_sync_users[] = $suser;
            }
            $rscreateassignments->close(); // Free memory.
        }

        if (!empty($this->config->allow_update)) {
            foreach ($rsupdateaccounts as $suser) {
                $user = $DB->get_record('user', array('id' => $suser->uid));

                // Decide now if we'll try to update the password later.
                $updatepassword = empty($this->config->ignoreexistingpass) &&
                                  isset($suser->password) &&
                                  trim($suser->password) !== '';

                if (!empty($this->config->allow_create) && !empty($user->deleted)) {
                    // Revive previously-deleted user.
                    if (undelete_user($user)) {
                        $user->deleted = 0;

                        if (!$updatepassword && !empty($this->config->undeletepwreset)) {
                            // If the password wasn't supplied in the sync and reset is enabled then tag the revived
                            // user for new password generation (if applicable).
                            $userauth = get_auth_plugin(strtolower($user->auth));
                            if ($userauth->can_change_password()) {
                                set_user_preference('auth_forcepasswordchange', 1, $user->id);
                                set_user_preference('create_password',          1, $user->id);
                            }
                            unset($userauth);
                        }

                        $this->addlog(get_string('reviveduserx', 'tool_totara_sync', $suser->idnumber), 'info', 'updateusers');
                    } else {
                        $this->addlog(get_string('cannotreviveuserx', 'tool_totara_sync', $suser->idnumber), 'warn', 'updateusers');
                        $problemswhileapplying = true;
                        // Try to continue with other operations to this user.
                    }
                }

                $suspenduser = false;
                if (isset($suser->suspended)) {
                    // Check if the user is going to be suspended before updating the $user object.
                    if ($user->suspended == 0 and $suser->suspended == 1) {
                        $suspenduser = true;
                    }
                } else {
                    if ($user->suspended == 1 and $this->config->allow_delete == self::SUSPEND_USERS) {
                        // User was previously deleted which resulted in suspension of account, enable the account now.
                        $suser->suspended = '0';
                    }
                }

                // Update user.
                $this->set_sync_user_fields($user, $suser);

                try {
                    $DB->update_record('user', $user);
                } catch (Exception $e) {
                    $this->addlog(get_string('cannotupdateuserx', 'tool_totara_sync', $suser->idnumber) . ': ' .
                            $e->getMessage(), 'warn', 'updateusers');
                    $problemswhileapplying = true;
                    // Try to continue with other operations to this user.
                }

                // Update user password.
                if ($updatepassword) {
                    $userauth = get_auth_plugin(strtolower($user->auth));
                    if ($userauth->can_change_password()) {
                        if (!$userauth->user_update_password($user, $suser->password)) {
                            $this->addlog(get_string('cannotsetuserpassword', 'tool_totara_sync', $user->idnumber),
                                    'warn', 'updateusers');
                            $problemswhileapplying = true;
                            // Try to continue with other operations to this user.
                        }
                    } else {
                        $this->addlog(get_string('cannotsetuserpasswordnoauthsupport', 'tool_totara_sync', $user->idnumber),
                                'warn', 'updateusers');
                        $problemswhileapplying = true;
                        // Try to continue with other operations to this user.
                    }
                    unset($userauth);
                }

                // Using auth plugin that does not allow password changes, lets clear auth_forcepasswordchange setting.
                $userauth = get_auth_plugin(strtolower($user->auth));
                if (!$userauth->can_change_password()) {
                    set_user_preference('auth_forcepasswordchange', 0, $user->id);
                    set_user_preference('create_password', 0, $user->id);
                }
                unset($userauth);

                // Store user data for assignment sync later.
                $assign_sync_users[] = $suser;
                // Update custom field data.
                $user = $this->put_custom_field_data($user, $suser);

                $this->addlog(get_string('updateduserx', 'tool_totara_sync', $suser->idnumber), 'info', 'updateusers');

                \core\event\user_updated::create_from_userid($user->id)->trigger();

                if ($suspenduser) {
                    \core\session\manager::kill_user_sessions($user->id);
                    \totara_core\event\user_suspended::create_from_user($user)->trigger();
                }
            }
            $rsupdateaccounts->close();
            unset($user, $pos_assignment, $posdata); // Free memory.
        }

        // Process the assignments after all the user records have been
        // created and updated so we know they're in the right state.
        foreach ($assign_sync_users as $suser) {
            try {
                $this->sync_user_assignments($suser->uid, $suser);
            } catch (Exception $e) {
                // We don't want this exception to stop processing so we will continue.
                // The code may have started a transaction. If it did then roll back the transaction.
                if ($DB->is_transaction_started()) {
                    $DB->force_transaction_rollback();
                }
                $this->addlog(get_string('cannotcreateuserassignments', 'tool_totara_sync', $suser->idnumber) . ': ' .
                        $e->getMessage(), 'warn', 'updateusers');
                $problemswhileapplying = true;
                continue; // Continue processing users.
            }
        }
        // Free memory used by user assignment array.
        unset($assign_sync_users);

        $this->get_source()->drop_table();
        $this->addlog(get_string('syncfinished', 'tool_totara_sync'), 'info', 'usersync');

        return $issane && !$problemswhileapplying;
    }

    /**
     * Create a user
     *
     * @param stdClass $suser escaped sync user object
     *
     * @return boolean true if successful
     * @throws totara_sync_exception
     */
    function create_user($suser) {
        global $CFG, $DB;

        $transaction = $DB->start_delegated_transaction();

        try {
            // Prep a few params.
            $user = new stdClass;
            $user->username = core_text::strtolower($suser->username);  // Usernames always lowercase in moodle.
            $user->idnumber = $suser->idnumber;
            $user->confirmed = 1;
            $user->totarasync = 1;
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->lang = $CFG->lang;
            $user->timecreated = time();
            $user->auth = isset($suser->auth) ? strtolower($suser->auth) : 'manual';
            $this->set_sync_user_fields($user, $suser);

            try {
                $user->id = $DB->insert_record('user', $user);  // Insert user.
            } catch (Exception $e) {
                // Throws exception which will be captured by caller.
                $transaction->rollback(new totara_sync_exception('user', 'createusers', 'cannotcreateuserx', $user->idnumber));
            }

            try {
                $userauth = get_auth_plugin(strtolower($user->auth));
            } catch (Exception $e) {
                // Throws exception which will be captured by caller.
                $transaction->rollback(new totara_sync_exception('user', 'createusers', 'invalidauthforuserx', $user->auth));
            }

            if ($userauth->can_change_password()) {
                if (!isset($suser->password) || trim($suser->password) === '') {
                    // Tag for password generation.
                    set_user_preference('auth_forcepasswordchange', 1, $user->id);
                    set_user_preference('create_password',          1, $user->id);
                } else {
                    // Set user password.
                    if (!$userauth->user_update_password($user, $suser->password)) {
                        $this->addlog(get_string('cannotsetuserpassword', 'tool_totara_sync', $user->idnumber), 'warn', 'createusers');
                    } else if (!empty($this->config->forcepwchange)) {
                        set_user_preference('auth_forcepasswordchange', 1, $user->id);
                    }
                }
            }
            unset($userauth);
            // Update custom field data.
            $user = $this->put_custom_field_data($user, $suser);

        } catch (totara_sync_exception $e) {
            // One of the totara sync exceptions above was triggered. Rollback has already occurred. Just pass on the exception.
            throw $e;
        } catch (Exception $e) {
            // Some other exception has occurred. Rollback, which in turn passes on the exception.
            $transaction->rollback(new totara_sync_exception('user', 'createusers', 'cannotcreateuserx', $suser->idnumber));
        }

        $transaction->allow_commit();

        $event = \core\event\user_created::create(
            array(
                'objectid' => $user->id,
                'context' => context_user::instance($user->id),
            )
        );
        $event->trigger();

        return true;
    }

    /**
     * Store the custom field data for the given user.
     *
     * @param stdClass $suser escaped sync user object
     */
    public function put_custom_field_data($user, $suser) {
        global $CFG;

        $customfields = json_decode($suser->customfields);

        if ($customfields) {
            require_once($CFG->dirroot.'/user/profile/lib.php');
            foreach ($customfields as $name => $value) {
                if ($value === null) {
                    continue; // Don't make empty records, it messes with defaults.
                }

                $profile = str_replace('customfield_', 'profile_field_', $name);
                // If the custom field is a menu, the option index will be set by function totara_sync_data_preprocess.
                $user->{$profile} = $value;
            }
            profile_save_data($user, true);
        }

        return $user;
    }

    /**
     * Sync a user's position assignments
     *
     * @return boolean true on success
     */
    function sync_user_assignments($userid, $suser) {
        global $DB;

        $pos_assignment = new position_assignment(array(
            'userid' => $userid,
            'type' => POSITION_TYPE_PRIMARY
        ));

        // If we have no position info at all we do not need to set a position.
        if (!isset($suser->postitle) && empty($suser->posidnumber) && !isset($suser->posstartdate)
            && !isset($suser->posenddate) && empty($suser->orgidnumber) && !isset($suser->manageridnumber)
            && empty($suser->appraiseridnumber)) {
            // TODO: These should all be testing !isset(), none should be testing empty().
            return false;
        }
        $posdata = new stdClass;
        $posdata->fullname = $pos_assignment->fullname;
        $posdata->shortname = $pos_assignment->shortname;
        $posdata->positionid = $pos_assignment->positionid;
        $posdata->organisationid = $pos_assignment->organisationid;
        $posdata->managerid = $pos_assignment->managerid;
        $posdata->appraiserid = $pos_assignment->appraiserid;
        if (isset($suser->postitle)) {
            $posdata->fullname = $suser->postitle;
            $posdata->shortname = empty($suser->postitleshortname) ? $suser->postitle : $suser->postitleshortname;
        }
        if (isset($suser->posidnumber)) {
            if (empty($suser->posidnumber)) {
                // Reset values.
                $posdata->positionid = 0;
            } else {
                $pos = $DB->get_record('pos', array('idnumber' => $suser->posidnumber));
                $posdata->positionid = $pos->id;
            }
        }
        if (isset($suser->posstartdate)) {
            if (empty($suser->posstartdate)) {
                $posdata->timevalidfrom = null;
            } else {
                $posdata->timevalidfrom = $suser->posstartdate;
            }
        }
        if (isset($suser->posenddate)) {
            if (empty($suser->posenddate)) {
                $posdata->timevalidto = null;
            } else {
                $posdata->timevalidto = $suser->posenddate;
            }
        }
        if (isset($suser->orgidnumber)) {
            if (empty($suser->orgidnumber)) {
                $posdata->organisationid = 0;
            } else {
                $posdata->organisationid = $DB->get_field('org', 'id', array('idnumber' => $suser->orgidnumber));
            }
        }
        if (isset($suser->manageridnumber)) {
            if (empty($suser->manageridnumber)) {
                $posdata->managerid = null;
            } else {
                try {
                    $posdata->managerid = $DB->get_field('user', 'id', array('idnumber' => $suser->manageridnumber, 'deleted' => 0), MUST_EXIST);
                } catch (dml_missing_record_exception $e) {
                    $posdata->managerid = null;
                }
            }
        }
        if (isset($suser->appraiseridnumber)) {
            if (empty($suser->appraiseridnumber)) {
                $posdata->appraiserid = null;
            } else {
                try {
                    $posdata->appraiserid = $DB->get_field('user', 'id',
                            array('idnumber' => $suser->appraiseridnumber, 'deleted' => 0), MUST_EXIST);
                } catch (dml_missing_record_exception $e) {
                    $posdata->appraiserid = null;
                }
            }
        }

        position_assignment::set_properties($pos_assignment, $posdata);

        $pos_assignment->managerid = $posdata->managerid;
        assign_user_position($pos_assignment);

        return true;
    }

    function set_sync_user_fields(&$user, $suser) {
        global $CFG;

        $fields = array('address', 'city', 'country', 'department', 'description',
            'email', 'firstname', 'institution', 'lang', 'lastname', 'firstnamephonetic',
            'lastnamephonetic', 'middlename', 'alternatename', 'phone1', 'phone2',
            'timemodified', 'timezone', 'url', 'username', 'suspended', 'emailstop', 'auth');

        $requiredfields = array('username', 'firstname', 'lastname', 'email');

        foreach ($fields as $field) {
            if (isset($suser->$field)) {
                if (!in_array($field, $requiredfields) || trim($suser->$field) !== '') {
                    // Not an empty required field - other fields are allowed to be empty.
                    // Handle exceptions first.
                    switch ($field) {
                        case 'username':
                            // Must be lower case.
                            $user->$field = core_text::strtolower($suser->$field);
                            break;
                        case 'country':
                            if (!empty($suser->$field)) {
                                // Must be upper case.
                                $user->$field = core_text::strtoupper($suser->$field);
                            } else if (empty($user->$field) && isset($CFG->country) && !empty($CFG->country)) {
                                // Sync and target are both empty - so use the default country.
                                $user->$field = $CFG->country;
                            }
                            break;
                        case 'city':
                            if (!empty($suser->$field)) {
                                $user->$field = $suser->$field;
                            } else if (empty($user->$field) && isset($CFG->defaultcity) && !empty($CFG->defaultcity)) {
                                // Sync and target are both empty - So use the default city.
                                $user->$field = $CFG->defaultcity;
                            }
                            break;
                        case 'timemodified':
                            // Default to now.
                            $user->$field = empty($suser->$field) ? time() : $suser->$field;
                            break;
                        case 'lang':
                            // Sanity check will check for validity and add log but we will still
                            // store invalid lang and it will default to $CFG->lang internally.
                            if (!empty($suser->$field)) {
                                $user->$field = $suser->$field;
                            }
                            break;
                        default:
                            $user->$field = $suser->$field;
                    }
                }
            }
        }

        // If there is no email, check the default email.
        $usedefaultemail = !empty($this->config->allowduplicatedemails) && !empty($this->config->defaultsyncemail);
        if (empty($suser->email) && empty($user->email) && $usedefaultemail) {
            $user->email = $this->config->defaultsyncemail;
        }

        $user->suspended = empty($suser->suspended) ? 0 : $suser->suspended;
    }

    /**
     * Check if the data contains invalid values
     *
     * @param string $synctable sync table name
     * @param string $synctable_clone sync clone table name
     *
     * @return array containing idnumbers of all records that are invalid
     */
    function check_sanity($synctable, $synctable_clone) {
        global $DB;

        // Get a row from the sync table, so we can check field existence.
        if (!$syncfields = $DB->get_record_sql("SELECT * FROM {{$synctable}}", null, IGNORE_MULTIPLE)) {
            return; // Nothing to check.
        }

        $allinvalididnumbers = array();
        $invalidids = array();
        // Get duplicated idnumbers.
        $badids = $this->get_duplicated_values($synctable, $synctable_clone, 'idnumber', 'duplicateuserswithidnumberx');
        $invalidids = array_merge($invalidids, $badids);
        // Get empty idnumbers.
        $badids = $this->check_empty_values($synctable, 'idnumber', 'emptyvalueidnumberx');
        $invalidids = array_merge($invalidids, $badids);

        // Check for usernames with invalid characters.
        $badids = $this->check_invalid_username($synctable, $synctable_clone);
        $invalidids = array_merge($invalidids, $badids);
        // Get duplicated usernames.
        $badids = $this->get_duplicated_values($synctable, $synctable_clone, 'username', 'duplicateuserswithusernamex');
        $invalidids = array_merge($invalidids, $badids);
        // Get empty usernames.
        $badids = $this->check_empty_values($synctable, 'username', 'emptyvalueusernamex');
        $invalidids = array_merge($invalidids, $badids);
        // Check usernames against the DB to avoid saving repeated values.
        $badids = $this->check_values_in_db($synctable, 'username', 'duplicateusernamexdb');
        $invalidids = array_merge($invalidids, $badids);

        // Get empty firstnames. If it is provided then it must have a non-empty value.
        if (property_exists($syncfields, 'firstname')) {
            $badids = $this->check_empty_values($synctable, 'firstname', 'emptyvaluefirstnamex');
            $invalidids = array_merge($invalidids, $badids);
        }

        // Get empty lastnames. If it is provided then it must have a non-empty value.
        if (property_exists($syncfields, 'lastname')) {
            $badids = $this->check_empty_values($synctable, 'lastname', 'emptyvaluelastnamex');
            $invalidids = array_merge($invalidids, $badids);
        }

        // Check position start date is not larger than position end date.
        if (property_exists($syncfields, 'posstartdate') && property_exists($syncfields, 'posenddate')) {
            $badids = $this->get_invalid_start_end_dates($synctable, 'posstartdate', 'posenddate', 'posstartdateafterenddate');
            $invalidids = array_merge($invalidids, $badids);
        }

        // Check invalid language set.
        if (property_exists($syncfields, 'lang')) {
            $badids = $this->get_invalid_lang($synctable);
            $invalidids = array_merge($invalidids, $badids);
        }

        // Check invalid country codes.
        if (property_exists($syncfields, 'country')) {
            $badids = $this->check_invalid_countrycode($synctable);
            $invalidids = array_merge($invalidids, $badids);
        }

        // Check invalid auth types.
        if (property_exists($syncfields, 'auth')) {
            $badids = $this->get_invalid_auth($synctable);
            $invalidids = array_merge($invalidids, $badids);
        }

        if (empty($this->config->allow_create)) {
            $badids = $this->check_users_unable_to_revive($synctable);
            $invalidids = array_merge($invalidids, $badids);
        }

        if (!isset($this->config->allowduplicatedemails)) {
            $this->config->allowduplicatedemails = 0;
        }
        if (!isset($this->config->ignoreexistingpass)) {
            $this->config->ignoreexistingpass = 0;
        }
        if (property_exists($syncfields, 'email') && !$this->config->allowduplicatedemails) {
            // Get duplicated emails.
            $badids = $this->get_duplicated_values($synctable, $synctable_clone, 'email', 'duplicateuserswithemailx');
            $invalidids = array_merge($invalidids, $badids);
            // Get empty emails.
            $badids = $this->check_empty_values($synctable, 'email', 'emptyvalueemailx');
            $invalidids = array_merge($invalidids, $badids);
            // Check emails against the DB to avoid saving repeated values.
            $badids = $this->check_values_in_db($synctable, 'email', 'duplicateusersemailxdb');
            $invalidids = array_merge($invalidids, $badids);
            // Get invalid emails.
            $badids = $this->get_invalid_emails($synctable);
            $invalidids = array_merge($invalidids, $badids);
        }

        // Get invalid options (in case of menu of choices).
        if ($syncfields->customfields != '[]') {
            $badids = $this->validate_custom_fields($synctable);
            $invalidids = array_merge($invalidids, $badids);
        }

        // The idea of this loop is to make sure that all users in the synctable are valid regardless of the order they are created.
        // Example: user1 is valid but his manager is not and his manager is checked later, so user1 will be marked as valid when he is not.
        // This loop avoids that behaviour by checking in each iteration if there are still invalid users.
        while (1) {
            // Get invalid positions.
            if (property_exists($syncfields, 'posidnumber')) {
                $badids = $this->get_invalid_org_pos($synctable, 'pos', 'posidnumber', 'posxnotexist');
                $invalidids = array_merge($invalidids, $badids);
            }

            // Get invalid orgs.
            if (property_exists($syncfields, 'orgidnumber')) {
                $badids = $this->get_invalid_org_pos($synctable, 'org', 'orgidnumber', 'orgxnotexist');
                $invalidids = array_merge($invalidids, $badids);
            }

            // Get invalid managers and self-assigned users.
            if (property_exists($syncfields, 'manageridnumber')) {
                $badids = $this->get_invalid_roles($synctable, $synctable_clone, 'manager');
                $invalidids = array_merge($invalidids, $badids);
                $badids = $this->check_self_assignment($synctable, 'manageridnumber', 'selfassignedmanagerx');
                $invalidids = array_merge($invalidids, $badids);
                $badids = $this->check_circular_management_assignment($synctable);
                $invalidids = array_merge($invalidids, $badids);
            }

            // Get invalid appraisers and self-assigned users.
            if (property_exists($syncfields, 'appraiseridnumber')) {
                $badids = $this->get_invalid_roles($synctable, $synctable_clone, 'appraiser');
                $invalidids = array_merge($invalidids, $badids);
                $badids = $this->check_self_assignment($synctable, 'appraiseridnumber', 'selfassignedappraiserx');
                $invalidids = array_merge($invalidids, $badids);
            }

            if ($invalidids) {
                // Split $invalidids array into chunks as there are varying limits on the amount of parameters.
                $invalidids_multi = array_chunk($invalidids, $DB->get_max_in_params());
                foreach ($invalidids_multi as $invalidids) {
                    list($badids, $params) = $DB->get_in_or_equal($invalidids);
                    // Collect idnumber for records which are invalid.
                    $rs = $DB->get_records_sql("SELECT id, idnumber FROM {{$synctable}} WHERE id $badids", $params);
                    foreach ($rs as $id => $record) {
                        $allinvalididnumbers[$id] = $record->idnumber;
                    }
                    $DB->delete_records_select($synctable, "id $badids", $params);
                    $DB->delete_records_select($synctable_clone, "id $badids", $params);
                    $invalidids = array();
                }
                unset($invalidids_multi);
            } else {
                break;
            }
        }

        return $allinvalididnumbers;
    }

    /**
     * Get duplicated values for a specific field
     *
     * @param string $synctable sync table name
     * @param string $synctable_clone sync clone table name
     * @param string $field field name
     * @param string $identifier for logging messages
     *
     * @return array with invalid ids from synctable for duplicated values
     */
    function get_duplicated_values($synctable, $synctable_clone, $field, $identifier) {
        global $DB;

        $params = array();
        $invalidids = array();
        $extracondition = '';
        if (empty($this->config->sourceallrecords)) {
            $extracondition = "WHERE deleted = ?";
            $params[0] = 0;
        }
        $sql = "SELECT id, idnumber, $field
                  FROM {{$synctable}}
                 WHERE $field IN (SELECT $field FROM {{$synctable_clone}} $extracondition GROUP BY $field HAVING count($field) > 1)";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $r) {
            $this->addlog(get_string($identifier, 'tool_totara_sync', $r), 'error', 'checksanity');
            $invalidids[] = $r->id;
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Get invalid organisations or positions
     *
     * @param string $synctable sync table name
     * @param string $table table name (org or pos)
     * @param string $field field name
     * @param string $identifier for logging messages
     *
     * @return array with invalid ids from synctable for organisations or positions that do not exist in the database
     */
    function get_invalid_org_pos($synctable, $table, $field, $identifier) {
        global $DB;

        $params = array();
        $invalidids = array();
        $sql = "SELECT s.id, s.idnumber, s.$field
                  FROM {{$synctable}} s
       LEFT OUTER JOIN {{$table}} t ON s.$field = t.idnumber
                 WHERE s.$field IS NOT NULL
                   AND s.$field != ''
                   AND t.idnumber IS NULL";
        if (empty($this->config->sourceallrecords)) {
            $sql .= ' AND s.deleted = ?'; // Avoid users that will be deleted.
            $params[0] = 0;
        }
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $r) {
            $this->addlog(get_string($identifier, 'tool_totara_sync', $r), 'error', 'checksanity');
            $invalidids[] = $r->id;
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Get invalid ids from synctable where start date is greater than the end date
     *
     * @param string $synctable sync table name
     * @param string $datefield1 column name for start date
     * @param string $datefield2 column name for end date
     * @param string $identifier for logging messages
     *
     * @return array with invalid ids from synctable where start date is greater than the end date
     */
    function get_invalid_start_end_dates($synctable, $datefield1, $datefield2, $identifier) {
        global $DB;

        $invalidids = array();
        $sql = "SELECT s.id, s.idnumber
                FROM {{$synctable}} s
                WHERE s.$datefield1 > s.$datefield2
                AND s.$datefield2 != 0";
        if (empty($this->config->sourceallrecords)) {
            $sql .= ' AND s.deleted = 0'; // Avoid users that will be deleted.
        }
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $r) {
            $this->addlog(get_string($identifier, 'tool_totara_sync', $r), 'error', 'checksanity');
            $invalidids[] = $r->id;
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Get invalid roles (such as managers or appraisers)
     *
     * @param string $synctable sync table name
     * @param string $synctable_clone sync clone table name
     * @param string $role Name of role to check e.g. 'manager' or 'appraiser'
     *                     There must be a {$role}idnumber field in the sync db table and '{$role}notexist'
     *                     language string in lang/en/tool_totara_sync.php
     *
     * @return array with invalid ids from synctable for roles that do not exist in synctable nor in the database
     */
    function get_invalid_roles($synctable, $synctable_clone, $role) {
        global $DB;

        $idnumberfield = "{$role}idnumber";
        $params = array();
        $invalidids = array();
        $sql = "SELECT s.id, s.idnumber, s.{$idnumberfield}
                  FROM {{$synctable}} s
       LEFT OUTER JOIN {user} u
                    ON s.{$idnumberfield} = u.idnumber
                 WHERE s.{$idnumberfield} IS NOT NULL
                   AND s.{$idnumberfield} != ''
                   AND u.idnumber IS NULL
                   AND s.{$idnumberfield} NOT IN
                       (SELECT idnumber FROM {{$synctable_clone}})";
        if (empty($this->config->sourceallrecords)) {
            $sql .= ' AND s.deleted = ?'; // Avoid users that will be deleted.
            $params[0] = 0;
        }
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $r) {
            $this->addlog(get_string($role.'xnotexist', 'tool_totara_sync', $r), 'error', 'checksanity');
            $invalidids[] = $r->id;
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Ensure options from menu of choices are valid
     *
     * @param string $synctable sync table name
     *
     * @return array with invalid ids from synctable for options that do not exist in the database
     */
    public function validate_custom_fields($synctable) {
        global $DB;

        $params = empty($this->config->sourceallrecords) ? array('deleted' => 0) : array();
        $invalidids = array();
        $rs = $DB->get_recordset($synctable, $params, '', 'id, idnumber, customfields');

        // Used to force a warning on the sync completion message without skipping users.
        $forcewarning = false;

        // Keep track of the fields that need to be tested for having unique values.
        $unique_fields = array ();

        foreach ($rs as $r) {
            $customfields = json_decode($r->customfields, true);
            if (!empty($customfields)) {
                foreach ($customfields as $name => $value) {
                    // Check each of the fields that have attributes that may affect
                    // whether the sync data will be accepted or not.
                    if ($this->customfieldsdb[$name]['required'] && trim($value) == '' && empty($this->customfieldsdb[$name]['default'])) {
                        $this->addlog(get_string('fieldrequired', 'tool_totara_sync', (object)array('idnumber' => $r->idnumber, 'fieldname' => $name)), 'warn', 'checksanity');
                        $forcewarning = true;
                    }

                    if (isset($this->customfieldsdb[$name]['menu_options'])) {
                        if (trim($value) != '' && !in_array(core_text::strtolower($value), $this->customfieldsdb[$name]['menu_options'])) {
                            // Check menu value matches one of the available options, add an warning to the log if not.
                            $this->addlog(get_string('optionxnotexist', 'tool_totara_sync', (object)array('idnumber' => $r->idnumber, 'option' => $value, 'fieldname' => $name)), 'warn', 'checksanity');
                            $forcewarning = true;
                        }
                    } else if ($this->customfieldsdb[$name]['forceunique']) {
                        // Note: Skipping this for menu custom fields as the UI does not enforce uniqueness for them.

                        $sql = "SELECT uid.data
                                  FROM {user} usr
                                  JOIN {user_info_data} uid ON usr.id = uid.userid
                                 WHERE usr.idnumber != :idnumber
                                   AND uid.fieldid = :fieldid
                                   AND uid.data = :data";
                        // Check that the sync value does not exist in the user info data.
                        $params = array ('idnumber' => $r->idnumber, 'fieldid' => $this->customfieldsdb[$name]['id'], 'data' => $value);
                        $cfdata = $DB->get_records_sql($sql, $params);
                        // If the value already exists in the database then flag an error. If not, record
                        // it in unique_fields to later verify that it's not duplicated in the sync data.
                        if ($cfdata) {
                            $this->addlog(get_string('fieldduplicated', 'tool_totara_sync', (object)array('idnumber' => $r->idnumber, 'fieldname' => $name, 'value' => $value)), 'error', 'checksanity');
                            $invalidids[] = intval($r->id);
                            break;
                        } else {
                            $unique_fields[$name][intval($r->id)] = array ( 'idnumber' => $r->idnumber, 'value' => $value);
                        }
                    }
                }
            }
        }
        $rs->close();

        // Process any data that must have unique values.
        foreach ($unique_fields as $fieldname => $fielddata) {

            // We need to get all the field values into
            // an array so we can extract the duplicate values.
            $field_values = array ();
            foreach ($fielddata as $id => $values) {
                $field_values[$id] = $values['value'];
            }

            // Build up an array from the field values
            // where there are duplicates.
            $error_ids = array ();
            foreach ($field_values as $id => $value) {
                // Get a list of elements that match the current value.
                $matches = array_keys($field_values, $value);
                // If we've got more than one then we've got duplicates.
                if (count($matches) >  1) {
                    $error_ids = array_merge($error_ids, $matches);
                }
            }

            // The above process will create multiple occurences
            // for each problem value so remove the duplicates.
            $error_ids = array_unique ($error_ids);
            natsort($error_ids);

            // Loop through the error ids and produce a sync log entry.
            foreach ($error_ids as $id) {
                $log_data = (object) array('idnumber' => $fielddata[$id]['idnumber'], 'fieldname' => $fieldname, 'value' => $fielddata[$id]['value']);
                $this->addlog(get_string('fieldmustbeunique', 'tool_totara_sync', $log_data), 'error', 'checksanity');
            }
            $invalidids = array_merge ($invalidids, $error_ids);
        }

        if ($forcewarning) {
            // Put a dummy record in here to flag a problem without skipping the user.
            $invalidids[] = 0;
        }

        $invalidids = array_unique($invalidids);

        return $invalidids;
    }

    /**
     * Avoid saving values from synctable that already exist in the database
     *
     * @param string $synctable sync table name
     * @param string $field field name
     * @param string $identifier for logging messages
     *
     * @return array with invalid ids from synctable for usernames or emails that are already registered in the database
     */
    function check_values_in_db($synctable, $field, $identifier) {
        global $DB;

        $params = array();
        $invalidids = array();
        $sql = "SELECT s.id, s.idnumber, s.$field
                  FROM {{$synctable}} s
            INNER JOIN {user} u ON s.idnumber <> u.idnumber
                   AND s.$field = u.$field";
        if (empty($this->config->sourceallrecords)) {
            $sql .= ' AND s.deleted = ?'; // Avoid users that will be deleted.
            $params[0] = 0;
        }
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $r) {
            $this->addlog(get_string($identifier, 'tool_totara_sync', $r), 'error', 'checksanity');
            $invalidids[] = $r->id;
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Get users who are their own superior
     *
     * @param string $synctable sync table name
     * @param string $role that will be checked
     * @param string $identifier for logging messages
     *
     * @return array with invalid ids from synctable for users who are their own superior
     */
    function check_self_assignment($synctable, $role, $identifier) {
        global $DB;

        $params = array();
        $invalidids = array();
        $sql = "SELECT id, idnumber
                  FROM {{$synctable}}
                 WHERE idnumber = $role
                   AND idnumber != ''
                   AND idnumber IS NOT NULL";
        if (empty($this->config->sourceallrecords)) {
            $sql .= ' AND deleted = ?'; // Avoid users that will be deleted.
            $params[0] = 0;
        }
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $r) {
            $this->addlog(get_string($identifier, 'tool_totara_sync', $r), 'error', 'checksanity');
            $invalidids[] = $r->id;
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Check empty values for fields that are required
     *
     * @param string $synctable sync table name
     * @param string $field that will be checked
     * @param string $identifier for logging messages
     *
     * @return array with invalid ids from synctable for empty fields that are required
     */
    function check_empty_values($synctable, $field, $identifier) {
        global $DB;

        $params = array();
        $invalidids = array();
        $sql = "SELECT id, idnumber
                  FROM {{$synctable}}
                 WHERE $field = ''";
        if (empty($this->config->sourceallrecords) && $field != 'idnumber') {
            $sql .= ' AND deleted = ?'; // Avoid users that will be deleted.
            $params[0] = 0;
        }
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $r) {
            $this->addlog(get_string($identifier, 'tool_totara_sync', $r), 'error', 'checksanity');
            $invalidids[] = $r->id;
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Check for users that will be revived where allowcreate is off
     *
     * @param string $synctable sync table name
     *
     * @return array with invalid ids from synctable for users who are marked not deleted in the file but deleted in the db
     */
    function check_users_unable_to_revive($synctable) {
        global $DB;

        $invalidids = array();
        $sql = "SELECT s.id, s.idnumber
                  FROM {{$synctable}} s
                  INNER JOIN {user} u ON s.idnumber = u.idnumber
                 WHERE u.deleted = 1
                   AND s.idnumber != ''
                   AND s.idnumber IS NOT NULL";
        if (empty($this->config->sourceallrecords)) {
            // With sourceallrecords on we also need to check the deleted column in the sync table.
            $sql .= ' AND s.deleted = 0';
        }
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $r) {
            $this->addlog(get_string('cannotupdatedeleteduserx', 'tool_totara_sync', $r->idnumber), 'error', 'checksanity');
            $invalidids[] = $r->id;
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Get invalid email addresses in the email field
     *
     * @param string $synctable sync table name
     *
     * @return array with invalid ids from synctable for invalid emails
     */
    public function get_invalid_emails($synctable) {
        global $DB;

        $params = array();
        $invalidids = array();
        $extracondition = '';
        if (empty($this->config->sourceallrecords)) {
            $extracondition = "AND deleted = ?";
            $params[0] = 0;
        }
        $sql = "SELECT id, idnumber, email
                  FROM {{$synctable}}
                 WHERE email IS NOT NULL {$extracondition}";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $r) {
            if (!validate_email($r->email)) {
                $this->addlog(get_string('invalidemailx', 'tool_totara_sync', $r), 'error', 'checksanity');
                $invalidids[] = $r->id;
            }
            unset($r);
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Get invalid langauge in the lang field
     *
     * @param string $synctable sync table name
     *
     * @return array with a dummy invalid id record if there is a row with an invalid language
     */
    public function get_invalid_lang($synctable) {
        global $DB;

        $forcewarning = false;
        $params = array();
        $invalidids = array();
        $extracondition = '';
        if (empty($this->config->sourceallrecords)) {
            $extracondition = "AND deleted = ?";
            $params[0] = 0;
        }
        $sql = "SELECT id, idnumber, lang
                  FROM {{$synctable}}
                WHERE lang != '' AND lang IS NOT NULL {$extracondition}";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $r) {
            if (!get_string_manager()->translation_exists($r->lang)) {
                // Add log entry for invalid language but don't skip user.
                $this->addlog(get_string('invalidlangx', 'tool_totara_sync', $r), 'error', 'checksanity');
                $forcewarning = true;
            }
            unset($r);
        }
        $rs->close();

        if ($forcewarning) {
            // Put a dummy record in here to flag a problem without skipping the user.
            $invalidids[] = 0;
        }

        return $invalidids;
    }

    /**
     * Get invalid country codes
     *
     * @param string $synctable sync table name
     *
     * @return array with ids of any rows with invalid country codes
     */
    public function check_invalid_countrycode($synctable) {
        global $DB;

        $params = array();
        $invalidids = array();

        // Avoid users who will be deleted.
        $extracondition = '';
        if (empty($this->config->sourceallrecords)) {
            $extracondition = "AND deleted = :deleted";
            $params['deleted'] = 0;
        }

        $sql = "SELECT id, idnumber, country
                  FROM {{$synctable}}
                 WHERE country != ''
                   AND country IS NOT NULL
                   {$extracondition}";

        $rs = $DB->get_recordset_sql($sql, $params);

        if (!empty($rs)) {
            $countries = get_string_manager()->get_list_of_countries();
            foreach ($rs as $r) {
                if (!isset($countries[$r->country])) {
                    $this->addlog(get_string('invalidcountrycode', 'tool_totara_sync', $r), 'error', 'checksanity');
                    $invalidids[] = $r->id;
                }
            }
        }

        $rs->close();

        return $invalidids;
    }

    /**
     * Get invalid auth types
     *
     * @param string $synctable sync table name
     *
     * @return array with ids of any rows with invalid auth types
     */
    public function get_invalid_auth($synctable) {
        global $DB;

        $params = array();
        $invalidids = array();

        // Avoid users who will be deleted.
        $extracondition = '';
        if (empty($this->config->sourceallrecords)) {
            $extracondition = "AND deleted = :deleted";
            $params['deleted'] = 0;
        }

        $sql = "SELECT id, idnumber, auth
                  FROM {{$synctable}}
                WHERE auth != '' AND auth IS NOT NULL {$extracondition}";
        $rs = $DB->get_recordset_sql($sql, $params);

        foreach ($rs as $r) {

            if (!exists_auth_plugin($r->auth)) {
                $this->addlog(get_string('invalidauthxforuserx', 'tool_totara_sync', $r), 'error', 'checksanity');
                $invalidids[] = $r->id;
            }

            unset($r);
        }

        $rs->close();

        return $invalidids;
    }

    /**
     * Check for invalid usernames
     *
     * Note that this function actually updates the usernames in both sync tables.
     *
     * @param string $synctable sync table name
     * @param string $synctable_clone sync table clone name
     *
     * @return array with ids of any rows with invalid usernames
     */
    public function check_invalid_username($synctable, $synctable_clone) {
        global $DB;

        $invalidids = array();

        // Get a list of all the usernames.
        $sql = "SELECT id, idnumber, username FROM {{$synctable}}";
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $r) {
            // Get a clean version of the username with all invalid characters removed.
            $clean_username = clean_param($r->username, PARAM_USERNAME);

            // The cleaned username doesn't match the original. There's a issue.
            if ($r->username !== $clean_username) {
                // Check if the username is mixed case, if it is that is fine, it will be converted to lower case later.
                // The conversion is done in {@see \totara_sync_element_user::create_user()}
                if (\core_text::strtolower($r->username) !== $clean_username) {
                    // The cleaned username is not just a lowercase version of the original,
                    // characters have been removed, so log an error and record the id.
                    $this->addlog(get_string('invalidusernamex', 'tool_totara_sync', $r), 'error', 'checksanity');
                    $invalidids[] = $r->id;
                } else {
                    // The cleaned username has only had uppercase characters changed to lower case.
                    // It's acceptable so just flag a warning. the username will be imported in lowercase.
                    $this->addlog(get_string('invalidcaseusernamex', 'tool_totara_sync', $r), 'warn', 'checksanity');
                    $DB->set_field($synctable, 'username', $clean_username, array('id' => $r->id));
                    $DB->set_field($synctable_clone, 'username', $clean_username, array('id' => $r->id));
                }
            }
        }
        $rs->close();

        return $invalidids;
    }

    /**
     * Detect any circular management structures in the import.
     *
     * @param string $synctable sync table name
     * @return array with ids of any rows with invalid usernames
     */
    public function check_circular_management_assignment($synctable) {
        global $DB;

        $invalidids = array();

        // Get user/manager mappings for all existing users.
        $existing_managers_sql = "SELECT u.id, pa.managerid
                                    FROM {user} u
                               LEFT JOIN {pos_assignment} pa ON u.id = pa.userid AND pa.type = :primary";
        $managerids = $DB->get_records_sql_menu($existing_managers_sql, array('primary' => POSITION_TYPE_PRIMARY));

        // Get all useridnumber and manageridnumber records.
        $importsql = "SELECT st.id, st.idnumber, st.manageridnumber, u.id AS userid, m.id AS managerid
                        FROM {{$synctable}} st
                   LEFT JOIN {user} u ON u.idnumber = st.idnumber AND u.idnumber != ''
                   LEFT JOIN {user} m ON m.idnumber = st.manageridnumber AND m.idnumber != ''
                   WHERE st.idnumber != ''
                     AND st.manageridnumber IS NOT NULL
                     AND st.manageridnumber != st.idnumber";
        // Don't exclude st.manageridnumber which contain "" because they indicate that we want to erase the manager.

        $new_manager_assignments = $DB->get_records_sql($importsql);

        // Mappings are negative;
        $idmap = array();
        $idmapcount = 1;

        // First up make sure all users have id's, use a negative map where required.
        foreach ($new_manager_assignments as $assignment) {
            if (empty($assignment->userid)) {
                // The user doesn't exist, they will be created, get a mapping for them.
                $idmap[$assignment->idnumber] = -$idmapcount;
                $idmapcount++;
            }
        }
        // Now repeat but for managers.
        foreach ($new_manager_assignments as $assignment) {
            if (empty($assignment->managerid) && !isset($idmap[$assignment->manageridnumber])) {
                // The manager doesn't exist, they will be created, get a mapping for them.
                $idmap[$assignment->manageridnumber] = -$idmapcount;
                $idmapcount++;
            }
        }
        // Next we want to populate all managers into the managerids array, using the negative map where we lack an
        // actual id for the user.
        foreach ($new_manager_assignments as $assignment) {
            $userid = (!empty($assignment->userid)) ? $assignment->userid : $idmap[$assignment->idnumber];
            $managerid = (!empty($assignment->managerid)) ? $assignment->managerid : $idmap[$assignment->manageridnumber];
            $managerids[$userid] = $managerid;
        }

        // Now for each new assignment we need to check the lineage. Time to take some time!
        foreach ($new_manager_assignments as $assignment) {
            $userid = (!empty($assignment->userid)) ? $assignment->userid : $idmap[$assignment->idnumber];

            $managementpathids = totara_get_lineage($managerids, $userid, array(), false);

            // We have found a loop, skip this user and add a log entry.
            if ($managementpathids === false) {
                $this->addlog(get_string('circularmanagementassignmentx', 'tool_totara_sync', $assignment), 'error', 'checksanity');
                $invalidids[] = $assignment->id;
            }
        }

        return $invalidids;
    }
}
