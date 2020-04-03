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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara_dashboard
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/totara/core/lib.php');
require_once($CFG->dirroot . '/totara/cohort/lib.php');

/**
 * Dashboard instance management
 */
class totara_dashboard {

    /**
     * Dashboard id
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Dashboard name
     *
     * @var string
     */
    public $name = '';

    /**
     * Is dashboard visible for assigned users
     *
     * @var int 0|1
     */
    public $published = 0;

    /**
     * Can users change their dashboard
     *
     * @var int 0|1
     */
    public $locked = 0;

    /**
     * Order of dashboards display in navigation
     *
     * @var int
     */
    public $sortorder = 0;

    /**
     * Assigned cohorts
     *
     * @var array of int id's
     */
    private $cohorts = null;


    /**
     * Get List of all dashboards
     * It is not expected to have more than 100 dashboards, so no paging here.
     * Much bigger number of dashboards might reduce preformance.
     *
     * @return array of totara_dashboard
     */
    public static function get_manage_list() {
        global $DB;
        $records = $DB->get_records('totara_dashboard', null, 'sortorder', 'id');
        $dashboards = array();
        foreach ($records as $record) {
            $dashboards[] = new totara_dashboard($record->id);
        }
        return $dashboards;
    }

    /**
     * Get list of user dashboards
     *
     * @param int $userid
     * @return array of dashboard records
     */
    public static function get_user_dashboards($userid) {
        global $DB;

        // If dashboards are disabled then return an empty array
        // so redirects are done where necessary.
        if (totara_feature_disabled('totaradashboard')) {
            return array();
        }

        // Get user cohorts.
        $cohorts = totara_cohort_get_user_cohorts($userid);
        if (empty($cohorts)) {
            return array();
        }
        list($cohortssql, $cohortsparams) = $DB->get_in_or_equal($cohorts);

        // Check relevant dashboards.
        $sql = 'SELECT DISTINCT td.*
                FROM {totara_dashboard} td
                INNER JOIN {totara_dashboard_cohort} tdc ON (tdc.dashboardid = td.id)
                WHERE tdc.cohortid ' . $cohortssql . '
                  AND td.published = 1
                ORDER BY td.sortorder
               ';
        return $DB->get_records_sql($sql, $cohortsparams);
    }

    /**
     * Create instance of dashboard
     *
     * @param int $id
     */
    public function __construct($id = 0) {
        global $DB;

        if ($id == 0) {
            return;
        }

        $record = $DB->get_record('totara_dashboard', array('id' => $id));
        $this->id = $record->id;
        $this->name = $record->name;
        $this->published = $record->published;
        $this->locked = $record->locked;
        $this->sortorder = $record->sortorder;
    }

    /**
     * Is current dashboard first in order
     *
     * @return boolean
     */
    public function is_first() {
        global $DB;
        $record = $DB->get_record_sql('SELECT MIN(sortorder) minsort FROM {totara_dashboard}');
        if ($record->minsort == $this->sortorder) {
            return true;
        }
        return false;
    }

    /**
     * Is current dashboard last in order
     *
     * @return boolean
     */
    public function is_last() {
        global $DB;
        $record = $DB->get_record_sql('SELECT MAX(sortorder) maxsort FROM {totara_dashboard}');
        if ($record->maxsort == $this->sortorder) {
            return true;
        }
        return false;
    }

    /**
     * Change dashboard order to higher position
     */
    public function move_up() {
        db_reorder($this->id, $this->sortorder - 1, 'totara_dashboard');
    }

    /**
     * Change dashboard order to lower position
     */
    public function move_down() {
        db_reorder($this->id, $this->sortorder + 1, 'totara_dashboard');
    }

    /**
     * Is dashboard visible for assigned audience
     *
     * @return boolean
     */
    public function is_published() {
        return (bool)$this->published;
    }

    /**
     * Make dashboard visible for audince.
     *
     * @return totara_dashboard $this
     */
    public function publish() {
        $this->published = 1;
        return $this;
    }

    /**
     * Hide dashboard from audience
     *
     * @return totara_dashboard $this
     */
    public function unpublish() {
        $this->published = 0;
        return $this;
    }

    /**
     * Are users able to change their dashboard
     *
     * @return boolean
     */
    public function is_locked() {
        return (bool)$this->locked;
    }

    /**
     * Prevent changes to dashboard by users
     *
     * @return totara_dashboard $this
     */
    public function lock() {
        $this->locked = 1;
        return $this;
    }

    /**
     * Prevent changes to dashboard by users
     *
     * @return totara_dashboard $this
     */
    public function unlock() {
        $this->locked = 0;
        return $this;
    }

    /**
     * Save instance to database
     */
    public function save() {
        global $DB;
        $record = $this->get_for_form();

        if ($this->id > 0) {
            $DB->update_record('totara_dashboard', $record);
        } else {
            $id = $DB->insert_record('totara_dashboard', $record);
            $this->id = $id;
            db_reorder($this->id, -1, 'totara_dashboard');

            // Add dashboard block to every new dashboard.
            $this->add_naviation_block();
        }
        $this->save_cohorts();
    }

    /**
     * Return instance data
     *
     * @return stdClass
     */
    public function get_for_form() {
        $instance = new stdClass();
        $instance->id = $this->id;
        $instance->name = $this->name;
        $instance->published = (int)$this->published;
        $instance->locked = (int)$this->locked;
        $instance->sortorder = (int)$this->sortorder;
        $instance->cohorts = implode(',', $this->get_cohorts());

        return $instance;
    }

    /**
     * Set instance fields from stdClass
     *
     * @param stdClass $data
     * @return totara_dashboard $this
     */
    public function set_from_form(stdClass $data) {
        $this->name = '';
        $this->locked = 0;
        $this->published = 0;
        $this->set_cohorts(array());

        if (isset($data->name)) {
            $this->name = $data->name;
        }
        if (isset($data->locked)) {
            $this->locked = (bool)$data->locked;
        }
        if (isset($data->published)) {
            $this->published = (bool)$data->published;
        }
        if (isset($data->cohorts)) {
            if (is_array($data->cohorts)) {
                $this->cohorts = $data->cohorts;
            } else if (empty($data->cohorts)) {
                $this->cohorts = array();
            } else {
                $exploded = explode(',', $data->cohorts);
                foreach ($exploded as $check) {
                    if ((int)$check < 1) {
                        throw new coding_exception("Couldn't parse cohorts data:" . $data->cohorts);
                    }
                }
                $this->cohorts = $exploded;
            }
        }
        return $this;
    }

    /**
     * Get dashboard id
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get assigned audince id's
     *
     * @return array of cohort id's
     */
    public function get_cohorts() {
        global $DB;

        if (is_null($this->cohorts)) {
            $records = $DB->get_records('totara_dashboard_cohort', array('dashboardid' => $this->id));
            $cohortids = array();
            foreach ($records as $record) {
                $cohortids[] = $record->cohortid;
            }
            $this->cohorts = $cohortids;
        }
        return $this->cohorts;
    }

    /**
     * Set new assigned cohorts
     *
     * @param array $cohortids
     */
    public function set_cohorts(array $cohortids) {
        $this->cohorts = $cohortids;
    }

    /**
     * Save cohorts assignment to db
     */
    protected function save_cohorts() {
        global $DB;
        $cohortkeys = array_flip($this->cohorts);
        $records = $DB->get_records('totara_dashboard_cohort', array('dashboardid' => $this->id));
        foreach ($records as $record) {
            // If record is present in both, remove it from new assignemnts.
            if (isset($cohortkeys[$record->cohortid])) {
                unset($cohortkeys[$record->cohortid]);
            } else {
                // If record not in new assignments array, delete it.
                $DB->delete_records('totara_dashboard_cohort', array('id' => $record->id));
            }
        }

        // Add all new assignments to database.
        foreach ($cohortkeys as $cohortid => $unused) {
            $newcohort = new stdClass();
            $newcohort->dashboardid = $this->id;
            $newcohort->cohortid = $cohortid;
            $DB->insert_record('totara_dashboard_cohort', $newcohort);
        }
    }

    /**
     * Remove dashboard from DB
     */
    public function delete() {
        global $DB;
        if ($this->id) {
            // Reorder it to last.
            db_reorder($this->id, -1, 'totara_dashboard');

            // Delete user block instances.
            $this->reset_all();
            $DB->delete_records('totara_dashboard_user', array('dashboardid' => $this->id));

            // Delete assigned cohorts.
            $DB->delete_records('totara_dashboard_cohort', array('dashboardid' => $this->id));

            // Delete master block instances.
            $this->delete_dashboard_blocks();

            // Delete dashboard.
            $DB->delete_records('totara_dashboard', array('id' => $this->id));
        }
    }

    /**
     * Check if user has own modification of dashboard
     *
     * @param int $userid
     * @return int $userpageid
     */
    public function get_user_pageid($userid) {
        global $DB;
        $record = $DB->get_record('totara_dashboard_user', array('dashboardid' => $this->id, 'userid' => $userid));
        if ($record) {
            return $record->id;
        }
        return 0;
    }

    /**
     * Make user copy of dashboard
     * If it is already exists it will be returned.
     *
     * @param int $userid
     * @return int User copy page id
     */
    public function user_copy($userid) {
        global $DB;
        // Check if it is already exists.
        $record = $DB->get_record('totara_dashboard_user', array('dashboardid' => $this->id, 'userid' => $userid));
        if ($record) {
            return $record->id;
        }

        // Make new copy.
        $newrecord = new stdClass();
        $newrecord->dashboardid = $this->id;
        $newrecord->userid = $userid;
        $userpageid = $DB->insert_record('totara_dashboard_user', $newrecord);

        // Copy block instances.
        $systemcontext = context_system::instance();
        $usercontext = context_user::instance($userid);

        // Copy instances.
        $blockinstances = $DB->get_records('block_instances', array('parentcontextid' => $systemcontext->id,
                                                                    'pagetypepattern' => 'totara-dashboard-' . $this->id,
                                                                    'subpagepattern' => 'default'));
        $clonedids = array();
        foreach ($blockinstances as $instance) {
            $originalid = $instance->id;
            unset($instance->id);
            $instance->parentcontextid = $usercontext->id;
            $instance->subpagepattern = $userpageid;
            $instance->id = $DB->insert_record('block_instances', $instance);
            context_block::instance($instance->id);  // Just creates the context record.
            $block = block_instance($instance->blockname, $instance);
            $block->instance_copy($originalid);
            $clonedids[$originalid] = $instance->id;
        }

        // Copy positions of system blocks.
        $blockpositions = $DB->get_records('block_positions', array(
            'contextid' => $systemcontext->id,
            'pagetype' => 'totara-dashboard-' . $this->id,
            'subpage' => 'default'
        ));
        if (!empty($blockpositions)) {
            foreach ($blockpositions as $position) {
                unset($position->id);
                $position->contextid = $usercontext->id;
                $position->subpage = $userpageid;
                // If new block was created, we need to change its id as well.
                if (!empty($clonedids[$position->blockinstanceid])) {
                    $position->blockinstanceid = $clonedids[$position->blockinstanceid];
                }
                $position->id = $DB->insert_record('block_positions', $position);
            }
        }

        return $userpageid;
    }


    /**
     * Remove user modifications to dashboard
     *
     * @param int $userid
     */
    public function user_reset($userid) {
        global $DB;

        $pageid = $this->get_user_pageid($userid);
        if ($pageid) {
            $context = context_user::instance($userid);
            if ($blocks = $DB->get_records('block_instances', array('parentcontextid' => $context->id,
                    'pagetypepattern' => 'totara-dashboard-' . $this->id))) {
                foreach ($blocks as $block) {
                    if (is_null($block->subpagepattern) || $block->subpagepattern == $pageid) {
                        blocks_delete_instance($block);
                    }
                }
            }
            $DB->delete_records('block_positions', array(
                'contextid' => $context->id,
                'pagetype' => 'totara-dashboard-' . $this->id,
                'subpage' => $pageid
            ));
            $DB->delete_records('totara_dashboard_user', array('id' => $pageid));
        }
    }

    /**
     * Reset modifications to current dashboard for all users
     */
    public function reset_all() {
        global $DB;
        $userpages = $DB->get_records('totara_dashboard_user', array('dashboardid' => $this->id));
        if (!empty($userpages)) {
            foreach ($userpages as $page) {
                $this->user_reset($page->userid);
            }
        }
    }

    /**
     * Add "totara_dashboard" block to current dashboard.
     */
    public function add_naviation_block() {
        global $CFG;
        require_once($CFG->libdir . '/blocklib.php');

        $page = new moodle_page();
        $page->set_context(context_system::instance());
        $page->set_pagelayout('mydashboard');
        $page->set_pagetype('totara-dashboard-' . $this->id);
        $page->set_subpage('default');

        $blockman = $page->blocks;
        $blockman->add_block('totara_dashboard', $blockman->get_default_region(), -1, false, null, 'default');
    }

    /**
     * Remove all blocks that related to dashboard master layout.
     */
    protected function delete_dashboard_blocks() {
        global $DB;

        if ($blocks = $DB->get_records('block_instances', array('pagetypepattern' => 'totara-dashboard-' . $this->id))) {
            foreach ($blocks as $block) {
                blocks_delete_instance($block);
            }
        }
    }

    /**
    * Prints an error if Totara Dashboard is not enabled.
    */
    public static function check_feature_enabled() {
        if (totara_feature_disabled('totaradashboard')) {
            print_error('totaradashboarddisabled', 'totara_dashboard');
        }
    }
}

/**
 * Return the block pagetype options for Totara dashboards.
 * @param string $pagetype
 * @param context $parentcontext
 * @param context $currentcontext
 * @return array
 */
function totara_dashboard_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $result = [];
    if (strpos($pagetype, 'totara-dashboard-') === 0) {
        $result[$pagetype] = get_string('pagetype-this-dashboard', 'totara_dashboard');
    }
    return $result;
}