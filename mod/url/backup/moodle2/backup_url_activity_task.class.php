<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines backup_url_activity_task class
 *
 * @package     mod_url
 * @category    backup
 * @copyright   2010 onwards Andrew Davis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/url/backup/moodle2/backup_url_stepslib.php');

/**
 * Provides all the settings and steps to perform one complete backup of the activity
 */
class backup_url_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the url.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_url_activity_structure_step('url_structure', 'url.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content, backup_task $task = null) {

        if (!self::has_scripts_in_content($content, 'mod/url', ['index.php', 'view.php'])) {
            // No scripts present in the content, simply continue.
            return $content;
        }

        if (empty($task)) {
            // No task has been provided, lets just encode everything, must be some old school backup code.
            $content = self::encode_content_link_basic_id($content, "/mod/url/index.php?id=", 'URLINDEX');
            $content = self::encode_content_link_basic_id($content, "/mod/url/view.php?id=", 'URLVIEWBYID');
            $content = self::encode_content_link_basic_id($content, "/mod/url/view.php?u=", 'URLVIEWBYU');
        } else {
            // OK we have a valid task, we can translate just those links belonging to content that is being backed up.
            $content = self::encode_content_link_basic_id($content, "/mod/url/index.php?id=", 'URLINDEX', $task->get_courseid());
            foreach ($task->get_tasks_of_type_in_plan('backup_url_activity_task') as $task) {
                /** @var backup_url_activity_task $task */
                $content = self::encode_content_link_basic_id($content, "/mod/url/view.php?id=", 'URLVIEWBYID', $task->get_moduleid());
                $content = self::encode_content_link_basic_id($content, "/mod/url/view.php?u=", 'URLVIEWBYU', $task->get_activityid());
            }
        }

        return $content;
    }
}
