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
 * @author Aaron Wells <aaronw@catalyst.net.nz>
 * @package totara
 * @subpackage cohort/rules
 */
/**
 * This class is an ajax back-end for deleting a single rule
 */
define('AJAX_SCRIPT', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot.'/cohort/lib.php');

$ruleid = required_param('ruleid', PARAM_INT);

require_login();
require_sesskey();

//todo don't delete while this cohort is being processed?

$rule = $DB->get_record('cohort_rules', array('id' => $ruleid), '*', MUST_EXIST);

$sql = "SELECT crc.*
    FROM {cohort_rulesets} crs
    INNER JOIN {cohort_rule_collections} crc ON crs.rulecollectionid = crc.id
    WHERE crs.id = ?";
$colldetails = $DB->get_record_sql($sql, array($rule->rulesetid));

// Get cohort.
$cohort = $DB->get_record('cohort', array('id' => $colldetails->cohortid));

// Get context based on the context level of the cohort.
$context = context::instance_by_id($cohort->contextid);
require_capability('totara/cohort:managerules', $context);

$success = $DB->delete_records('cohort_rules', array('id' => $ruleid)) && $DB->delete_records('cohort_rule_params', array('ruleid' => $ruleid));

// Trigger rule_deleted event.
$info = "ruleid={$ruleid}&rulesetid={$rule->rulesetid}&ruletype={$rule->ruletype}&rulename={$rule->name}";
$event = \totara_cohort\event\rule_deleted::create_from_instance($rule, $cohort);
$event->set_legacy_logdata(array(SITEID, 'cohort', 'delete rule', 'totara/cohort/rules.php?id='.$cohort->id, $info));
$event->add_record_snapshot('cohort_rules', $rule);
$event->trigger();

// See if the ruleset has any remaining rules; delete it if not.
if ($DB->record_exists('cohort_rules', array('rulesetid' => $rule->rulesetid))) {
    echo json_encode(array('action'=>'delrule','ruleid'=>$ruleid));
} else {
    $ruleset = $DB->get_record('cohort_rulesets', array('id' => $rule->rulesetid));
    $DB->delete_records('cohort_rulesets', array('id' => $rule->rulesetid));
    $event = \totara_cohort\event\ruleset_deleted::create_from_instance($ruleset, $cohort);
    $event->add_record_snapshot('cohort_rulesets', $ruleset);
    $event->trigger();
    echo json_encode(array('action'=>'delruleset', 'rulesetid'=>$rule->rulesetid));
}

// Update rule collection status.
$colldetails->timemodified = time();
$colldetails->modifierid = $USER->id;
$colldetails->status = COHORT_COL_STATUS_DRAFT_CHANGED;
$DB->update_record('cohort_rule_collections', $colldetails);

exit();
