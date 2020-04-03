<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2016 onwards Totara Learning Solutions LTD
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
 * @author Alastair Munro <alastair.munro@totaralearning.com>
 * @package totara_program
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use \Behat\Behat\Context\Step\Given;
use \Behat\Mink\Exception\ExpectationException;
use \Behat\Gherkin\Node\TableNode as TableNode;

class behat_totara_program extends behat_base {

    /**
     * Adds a courseset to a program with the given courses as content.
     *
     * This definition requires the specified program and courses to exist.
     *
     * @Given /^I add a courseset with courses "([^"]*)" to "([^"]*)":$/
     * @param String $courses A comma separated list of courses
     * @param String $programname
     * @param TableNode $data
     */
    public function i_add_a_courseset_with_the_following_courses_to_program($courses, $programname, TableNode $data) {
        global $CFG, $DB;

        // Now that we need them require the data generators.
        require_once($CFG->dirroot . '/totara/program/program.class.php');

        // Get program record
        $program_record = $DB->get_record('prog', array('shortname' => $programname));
        $program = new program($program_record->id);

        $coursenames = explode(',', $courses);
        list($insql, $inparams) = $DB->get_in_or_equal($coursenames);
        $sql = "SELECT * FROM {course} WHERE shortname {$insql}";
        $courses = $DB->get_records_sql($sql, $inparams);

        foreach ($coursenames as $coursename) {
            // Check each course exists.
            $found = false;

            foreach ($courses as $course) {
                if ($course->shortname == $coursename) {
                    $found = true;
                }
            }

            if (!$found) {
                throw new Exception('Course with shortname "' . $coursename . '" does not exist.');
            }
        }

        $progcontent = new prog_content($program->id);
        $progcontent->add_set(CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $datahash = $data->getRowsHash();
        foreach ($datahash as $option => $value) {
            switch ($option) {
            case "Set name":
                $coursesets[0]->label = $value;
                break;
            case "Learner must complete":
                if ($value == "One course") {
                    $coursesets[0]->completiontype = COMPLETIONTYPE_ANY;
                } else if ($value == "All courses") {
                    $coursesets[0]->completiontype = COMPLETIONTYPE_ALL;
                } else if ($value == "Some courses") {
                    $coursesets[0]->completiontype = COMPLETIONTYPE_SOME;
                } else if ($value == "All courses are optional") {
                    $coursesets[0]->completiontype = COMPLETIONTYPE_OPTIONAL;
                } else {
                    throw new Exception('Invalid completion type "' . $value . '" given for course set');
                }
                break;
            case "Minimum time required":
                $coursesets[0]->timeallowed = $value * DAYSECS; // Number of days allowed.
                break;
            }
        }

        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[0]->nextsetoperator = NEXTSETOPERATOR_AND;

        foreach ($courses as $course) {
            $coursedata = new stdClass();
            $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $course->id;
            $progcontent->add_course($coursesets[0]->sortorder, $coursedata);
        }

        $progcontent->save_content();
    }
}
