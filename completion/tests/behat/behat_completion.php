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
 * Completion steps definitions.
 *
 * @package    core_completion
 * @category   test
 * @copyright  2013 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given,
    Behat\Behat\Context\Step\Then,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Steps definitions to deal with course and activities completion.
 *
 * @package    core_completion
 * @category   test
 * @copyright  2013 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_completion extends behat_base {

    /**
     * Checks that the specified user has completed the specified activity of the current course.
     *
     * @Then /^"(?P<user_fullname_string>(?:[^"]|\\")*)" user has completed "(?P<activity_name_string>(?:[^"]|\\")*)" activity$/
     * @param string $userfullname
     * @param string $activityname
     */
    public function user_has_completed_activity($userfullname, $activityname) {

        // Will throw an exception if the element can not be hovered.
        $titleliteral = $this->getSession()->getSelectorsHandler()->xpathLiteral($userfullname . ", " . $activityname . ": Completed");
        $xpath = "//table[@id='completion-progress']" .
            "/descendant::span[contains(., $titleliteral)]";

        return array(
            new Given('I go to the current course activity completion report'),
            new Then('"' . $this->escape($xpath) . '" "xpath_element" should exist')
        );
    }

    /**
     * Checks that the specified user has not completed the specified activity of the current course.
     *
     * @Then /^"(?P<user_fullname_string>(?:[^"]|\\")*)" user has not completed "(?P<activity_name_string>(?:[^"]|\\")*)" activity$/
     * @param string $userfullname
     * @param string $activityname
     */
    public function user_has_not_completed_activity($userfullname, $activityname) {

        // Will throw an exception if the element can not be hovered.
        $titleliteral = $this->getSession()->getSelectorsHandler()->xpathLiteral($userfullname . ", " . $activityname . ": Not completed");
        $xpath = "//table[@id='completion-progress']" .
            "/descendant::span[contains(., $titleliteral)]";
        return array(
            new Given('I go to the current course activity completion report'),
            new Then('"' . $this->escape($xpath) . '" "xpath_element" should exist')
        );

        return $steps;
    }

    /**
     * Goes to the current course activity completion report.
     *
     * @Given /^I go to the current course activity completion report$/
     */
    public function go_to_the_current_course_activity_completion_report() {

        $steps = array();

        // Expand reports node if we can't see the link.
        try {
            $this->find('xpath', "//div[@id='settingsnav']" .
                "/descendant::li" .
                "/descendant::li[not(contains(concat(' ', normalize-space(@class), ' '), ' collapsed '))]" .
                "/descendant::p[contains(., '" . get_string('pluginname', 'report_progress') . "')]");
        } catch (ElementNotFoundException $e) {
            $steps[] = new Given('I expand "' . get_string('reports') . '" node');
        }

        $steps[] = new Given('I follow "' . get_string('pluginname', 'report_progress') . '"');

        return $steps;
    }

    /**
     * Toggles site-wide completion tracking
     *
     * @When /^completion tracking is "(?P<completion_status_string>([Ee]nabled|[Dd]isabled)*)" site\-wide$/
     * @param string $completionstatus
     */
    public function completion_is_toggled_sitewide($completionstatus) {

        $toggle = strtolower($completionstatus) == 'enabled' ? 'check' : 'uncheck';

        return array(
            new Given('I log in as "admin"'),
            new Given('I am on homepage'),
            new Given('I follow "Advanced features"'),
            new Given('I '.$toggle.' "Enable completion tracking"'),
            new Given('I press "Save changes"'),
            new Given('I log out')
        );
    }

    /**
     * Toggles completion tracking for course being in the course page.
     *
     * @When /^completion tracking is "(?P<completion_status_string>Enabled|Disabled)" in current course$/
     * @param string $completionstatus The status, enabled or disabled.
     */
    public function completion_is_toggled_in_course($completionstatus) {

        $toggle = strtolower($completionstatus) == 'enabled' ? get_string('yes') : get_string('no');

        return array(
            new Given('I follow "'.get_string('editsettings').'"'),
            new Given('I expand all fieldsets'),
            new Given('I set the field "'.get_string('enablecompletion', 'completion').'" to "'.$toggle.'"'),
            new Given('I press "'.get_string('savechangesanddisplay').'"')
        );
    }

    /**
     * Checks if the activity with specified name is maked as complete.
     *
     * @Given /^the "(?P<activityname_string>(?:[^"]|\\")*)" "(?P<activitytype_string>(?:[^"]|\\")*)" activity with "(manual|auto)" completion should be marked as complete$/
     * @return array
     */
    public function activity_marked_as_complete($activityname, $activitytype, $completiontype) {
        if ($completiontype == "manual") {
            $imgalttext = get_string("completion-alt-manual-y", 'core_completion', $activityname);
        } else {
            $imgalttext = get_string("completion-alt-auto-y", 'core_completion', $activityname);
        }
        $csselementforactivitytype = "li.modtype_".strtolower($activitytype);

        return new Given('"//span[contains(., \''.$imgalttext.'\')]" "xpath_element" ' .
            'should exist in the "'.$csselementforactivitytype.'" "css_element"');
    }

    /**
     * Checks if the activity with specified name is maked as complete.
     *
     * @Given /^the "(?P<activityname_string>(?:[^"]|\\")*)" "(?P<activitytype_string>(?:[^"]|\\")*)" activity with "(manual|auto)" completion should be marked as not complete$/
     * @return array
     */
    public function activity_marked_as_not_complete($activityname, $activitytype, $completiontype) {
        if ($completiontype == "manual") {
            $imgalttext = get_string("completion-alt-manual-n", 'core_completion', $activityname);
        } else {
            $imgalttext = get_string("completion-alt-auto-n", 'core_completion', $activityname);
        }
        $csselementforactivitytype = "li.modtype_".strtolower($activitytype);

        return new Given('"//span[contains(., \''.$imgalttext.'\')]" "xpath_element" ' .
            'should exist in the "'.$csselementforactivitytype.'" "css_element"');
    }

    /**
     * Add completion records for the specified users and courses
     *
     * @Given /^the following courses are completed:$/
     * @throws Exception
     * @throws coding_exception
     */
    public function the_following_courses_are_completed(TableNode $table) {
        global $DB;

        $required = array(
            'user',
            'course', // Course shortname
            'timecompleted',
        );
        $optional = array(
            'timeenrolled',
            'timestarted',
        );
        $datevalues = array('timecompleted', 'timeenrolled', 'timestarted');

        $data = $table->getHash();
        $firstrow = reset($data);

        // Check required fields are present.
        foreach ($required as $reqname) {
            if (!isset($firstrow[$reqname])) {
                throw new Exception('Course completions require the field '.$reqname.' to be set');
            }
        }

        foreach ($data as $row) {
            // Copy values, ready to pass on to the generator.
            $record = array();
            foreach ($row as $fieldname => $value) {
                if (in_array($fieldname, $required)) {
                    $record[$fieldname] = $value;
                } else if (in_array($fieldname, $optional)) {
                    $record[$fieldname] = $value;
                } else {
                    throw new Exception('Unknown field '.$fieldname.' in course completion');
                }
            }

            if (!$userid = $DB->get_field('user', 'id', array('username' => $record['user']))) {
                throw new Exception('Unknown user '. $record['user']);
            }
            if (!$courseid = $DB->get_field('course', 'id', array('shortname' => $record['course']))) {
                throw new Exception('Unknown course '. $record['course']);
            }

            foreach($datevalues as $item) {
                $convertkey = isset($record[$item]) ? $item : 'timecompleted';
                switch(strtolower($record[$convertkey])) {
                    case 'today':
                        $record[$item] = time();
                        break;

                    case 'tomorrow':
                        $record[$item] = strtotime("+1 day");
                        break;

                    case 'yesterday':
                        $record[$item] = strtotime("-1 day");
                        break;

                    case 'last week':
                        $record[$item] = strtotime("-1 week");
                        break;

                    case 'last month':
                        $record[$item] = strtotime("-1 month");
                        break;

                    default:
                        $record[$item] = $record[$convertkey];
                }
            }

            $params = array(
                'userid' => $userid,
                'course' => $courseid,
                'timeenrolled' => $record['timeenrolled'],
                'timestarted' => $record['timestarted'],
                'timecompleted' => $record['timecompleted'],
                'reaggregate' => 0,
                'status' => COMPLETION_STATUS_COMPLETEVIARPL,
                'rplgrade' => 100,
            );

            $existing = $DB->get_record('course_completions', array('userid' => $userid, 'course' => $courseid), '*', IGNORE_MISSING);
            if ($existing) {
                $params['id'] = $existing->id;
                $DB->update_record('course_completions', $params);
            }
            else {
                $DB->insert_record('course_completions', $params);
            }
        }
    }
}
