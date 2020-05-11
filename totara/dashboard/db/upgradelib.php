<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2014 onwards Totara Learning Solutions LTD
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
 * @author Simon Coggins <simon.coggins@totaralearning.com>
 * @package totara_dashboard
 */

/**
 * Upgrade code to move contents of My Learning page into a new
 * dashboard.
 *
 * This script must migrate the default my learning page as well
 * as any user modified My Learning pages.
 *
 * @since 9.0 My Learning was removed in 9.0.
 *
 */
function totara_dashboard_migrate_my_learning_on_upgrade() {
    global $DB, $CFG;

    // Prevent this running multiple times.
    if (!empty($CFG->totaramylearningmigrated)) {
        return;
    }

    // We want all or nothing here.
    $transaction = $DB->start_delegated_transaction();

    // Create legacy dashboard.
    $todb = new stdClass();
    $todb->name = get_string('legacymylearning', 'totara_dashboard');
    $todb->published = 0;
    $todb->locked = 0;
    // Put this dashboard at the end of the list.
    $maxsort = $DB->get_field('totara_dashboard', 'MAX(sortorder)', []);
    $sortorder = ($maxsort === false) ? 0 : $maxsort + 1;
    $todb->sortorder = $sortorder;
    $mylearningdashboardid = $DB->insert_record('totara_dashboard', $todb);

    // Use block instance count for progress as it is the most fine grained measure.
    $pagecount = $DB->count_records('my_pages', ['private' => 1]);
    $pbar = new progress_bar('migratemylearning', 500, true);
    $i = 0;

    // Use a record set as their could be one per user.
    $pages = $DB->get_recordset('my_pages', ['private' => 1]);
    foreach ($pages as $page) {
        // Migrate the my_page record to dashboard_user.
        // This is skipped for the default page because dashboards don't store a
        // record for them.
        if (!empty($page->userid)) {
            $todb = new stdClass();
            $todb->dashboardid = $mylearningdashboardid;
            $todb->userid = $page->userid;
            $dashuserid = $DB->insert_record('totara_dashboard_user', $todb);
        } else {
            $dashuserid = 'default';
        }

        // Delete the existing record.
        $DB->delete_records('my_pages', ['id' => $page->id]);

        // Now update all block instances on this page.
        // For now we are leaving defaultregion as 'content' - that will be handled
        // later in the upgrade.
        $sql = "UPDATE {block_instances}
            SET pagetypepattern = :pagetypepattern,
            subpagepattern = :dashuserid
            WHERE pagetypepattern = 'my-index'
            AND subpagepattern = :pageid";
        $params = [
            'pagetypepattern' => "my-totara-dashboard-{$mylearningdashboardid}",
            'dashuserid' => $dashuserid,
            'pageid' => $page->id
        ];
        $DB->execute($sql, $params);

        $i++;
        $pbar->update($i, $pagecount, "Migrating My Learning pages - {$i}/{$pagecount}.");
    }
    $pages->close();
    $pbar->update($pagecount, $pagecount, "Migrating My Learning pages - complete.");

    $transaction->allow_commit();

    // Record that this has run successfully.
    set_config('totaramylearningmigrated', 1);
}

/**
 * Upgrade code to add my learning dashboards.
 *
 * @since 9.0 Handles default dashboard creation for upgrade from version before 9.0.
 */
function totara_dashboard_add_my_learning_dashboard_on_upgrade() {
    global $DB, $CFG;

    // Prevent this running multiple times.
    if (!empty($CFG->totaramylearningdashboardconfigured)) {
        return;
    }

    // We want all or nothing here.
    $transaction = $DB->start_delegated_transaction();

    // Make room for new dashboard at start as we want it to appear first.
    // Only required if there are existing dashboards.
    if ($DB->record_exists('totara_dashboard', [])) {
        $sql = "UPDATE {totara_dashboard} SET sortorder = sortorder + 1";
        $DB->execute($sql);
    }

    $todb = [
        'name' => get_string('mylearning', 'totara_core'), // Multi-lang sites will need to update this themselves.
        'published' => 2, // For all logged in users.
        'locked' => 0,
        'sortorder' => 0
    ];
    $dashboardid = $DB->insert_record('totara_dashboard', $todb);

    $defaultblockinstances = [
        [
            'blockname' => 'last_course_accessed',
            'defaultregion' => 'side-pre',
            'defaultweight' => -3
        ],
        [
            'blockname' => 'totara_dashboard',
            'defaultregion' => 'side-pre',
            'defaultweight' => -2
        ],
        [
            'blockname' => 'totara_my_learning_nav',
            'defaultregion' => 'side-pre',
            'defaultweight' => -1
        ],
        [
            'blockname' => 'current_learning',
            'defaultregion' => 'main',
            'defaultweight' => -2
        ],
        [
            'blockname' => 'totara_tasks',
            'defaultregion' => 'main',
            'defaultweight' => -1
        ],
        [
            'blockname' => 'totara_alerts',
            'defaultregion' => 'main',
            'defaultweight' => 0
        ],
        [
            'blockname' => 'news_items',
            'defaultregion' => 'side-post',
            'defaultweight' => 0
        ],
        [
            'blockname' => 'calendar_upcoming',
            'defaultregion' => 'side-post',
            'defaultweight' => -1
        ],
        [
            'blockname' => 'badges',
            'defaultregion' => 'side-post',
            'defaultweight' => -2
        ],
    ];

    $availableblocks = $DB->get_records_menu('block', ['visible' => 1], '', 'id,name');

    foreach ($defaultblockinstances as $blockinstance) {
        // Check this block type is installed and enabled before adding.
        if (!in_array($blockinstance['blockname'], $availableblocks)) {
            continue;
        }

        // Add common properties.
        $blockinstance['parentcontextid'] = context_system::instance()->id; // System context.
        $blockinstance['showinsubcontexts'] = 0;
        $blockinstance['pagetypepattern'] = 'my-totara-dashboard-' . $dashboardid; // Determines the dashboard the block is included on.
        $blockinstance['subpagepattern'] = 'default'; // Indicates this is the site wide default, not a user dashboard.
        $blockinstance['configdata'] = '';

        // Add the block instances.
        $biid = $DB->insert_record('block_instances', $blockinstance);

        // Ensure context is properly created.
        context_block::instance($biid, MUST_EXIST);
    }

    $transaction->allow_commit();

    // Record that this has run successfully.
    set_config('totaramylearningdashboardconfigured', 1);
}

