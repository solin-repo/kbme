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
 * @package totara_reportbuilder
 */

/**
 * Page for report cloning
 */

define('REPORT_BUILDER_IGNORE_PAGE_PARAMETERS', true); // We are setting up report here, do not accept source params.

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL); // Confirm clone.

admin_externalpage_setup('rbmanagereports');

$output = $PAGE->get_renderer('totara_reportbuilder');

global $USER;

$returnurl = $CFG->wwwroot . '/totara/reportbuilder/index.php';

$report = new reportbuilder($id);

// Clone report.
if ($confirm) {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', 'error');
    }

    $origname = $report->fullname;

    if (reportbuilder_clone_report($report, get_string('clonenamepattern', 'totara_reportbuilder', $origname))) {
        \totara_reportbuilder\event\report_cloned::create_from_report($report)->trigger();
        totara_set_notification(get_string('clonecompleted', 'totara_reportbuilder'), $returnurl,
                array('class' => 'notifysuccess'));

    } else {
        totara_set_notification(get_string('clonefailed', 'totara_reportbuilder'), $returnurl);
    }
}
echo $output->header();
echo $output->heading(get_string('clonereport', 'totara_reportbuilder'));
echo $output->confirm_clone($report);
echo $output->footer();