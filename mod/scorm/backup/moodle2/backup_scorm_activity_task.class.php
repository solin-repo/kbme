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
 * Defines backup_scorm_activity_task class
 *
 * @package     mod_scorm
 * @category    backup
 * @copyright   2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/scorm/backup/moodle2/backup_scorm_stepslib.php');

/**
 * Provides the steps to perform one complete backup of the SCORM instance
 */
class backup_scorm_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the scorm.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_scorm_activity_structure_step('scorm_structure', 'scorm.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content, backup_task $task = null) {

        if (!self::has_scripts_in_content($content, 'mod/scorm', ['index.php', 'view.php'])) {
            // No scripts present in the content, simply continue.
            return $content;
        }

        if (empty($task)) {
            // No task has been provided, lets just encode everything, must be some old school backup code.
            $content = self::encode_content_link_basic_id($content, "/mod/scorm/index.php?id=", 'SCORMINDEX');
            $content = self::encode_content_link_basic_id($content, "/mod/scorm/view.php?id=", 'SCORMVIEWBYID');
        } else {
            // OK we have a valid task, we can translate just those links belonging to content that is being backed up.
            $content = self::encode_content_link_basic_id($content, "/mod/scorm/index.php?id=", 'SCORMINDEX', $task->get_courseid());
            foreach ($task->get_tasks_of_type_in_plan('backup_scorm_activity_task') as $task) {
                /** @var backup_scorm_activity_task $task */
                $content = self::encode_content_link_basic_id($content, "/mod/scorm/view.php?id=", 'SCORMVIEWBYID', $task->get_moduleid());
            }
        }

        return $content;
    }
}
