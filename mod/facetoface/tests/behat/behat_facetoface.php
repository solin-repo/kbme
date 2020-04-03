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
 * @author Maria Torres <maria.torres@totaralms.com>
 * @package mod_facetoface
 */

// NOTE: no MOODLE_INTERNAL used, this file may be required by behat before including /config.php.
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given;
use Behat\Gherkin\Node\TableNode as TableNode;

/**
 * Contains functions used by behat to test functionality.
 *
 * @package    mod_facetoface
 * @category   test
 */
class behat_facetoface extends behat_base {

    /**
     * Create a session in the future based on the current date.
     *
     * Please note that this will only work with the first date (Sessions with multiple times will not work). Given that there no
     * multiple time sessions using this step in 2.7 & 2.9 (it is done slightly differently in 9.0 and up), I saw no need to fix it.
     *
     * @Given /^I fill facetoface session with relative date in form data:$/
     * @param TableNode $data
     */
    public function i_fill_facetoface_session_with_relative_date_in_form_data(TableNode $data) {

        $behatformcontext = behat_context_helper::get('behat_forms');
        $dataclone = clone $data;
        $rowday = array();
        $rows = array();
        $timestartday = '';
        $timestartmonth = '';
        $timestartyear = '';
        $timestarthour = '';
        $timestartmin = '';
        $timestartzone = '';
        $timefinishday = '';
        $timefinishmonth = '';
        $timefinishyear = '';
        $timefinishhour = '';
        $timefinishmin = '';
        $timefinishzone = '';

        foreach ($dataclone->getRows() as $row) {
            switch ($row[0]) {
                case 'timestart[0][day]':
                    $timestartday = (!empty($row[1]) ? $row[1] . ' days': '');
                    break;
                case 'timestart[0][month]':
                    $timestartmonth = (!empty($row[1]) ? $row[1] . ' months': '');
                    break;
                case 'timestart[0][year]':
                    $timestartyear = (!empty($row[1]) ? $row[1] . ' years' : '');
                    break;
                case 'timestart[0][hour]':
                    $timestarthour = (!empty($row[1]) ? $row[1] . ' hours': '');
                    break;
                case 'timestart[0][minute]':
                    $timestartmin = (!empty($row[1]) ? $row[1] . ' minutes': '');
                    break;
                case 'timestart[0][timezone]':
                    $timestartzone = (!empty($row[1]) ? $row[1] : '');
                    $rows[] = $row;
                    break;
                case 'timefinish[0][day]':
                    $timefinishday = (!empty($row[1]) ? $row[1]  . ' days': '');
                    break;
                case 'timefinish[0][month]':
                    $timefinishmonth = (!empty($row[1]) ? $row[1]  . ' months': '');
                    break;
                case 'timefinish[0][year]':
                    $timefinishyear = (!empty($row[1]) ? $row[1]  . ' years' : '');
                    break;
                case 'timefinish[0][hour]':
                    $timefinishhour = (!empty($row[1]) ? $row[1] . ' hours': '');
                    break;
                case 'timefinish[0][minute]':
                    $timefinishmin = (!empty($row[1]) ? $row[1] . ' minutes': '');
                    break;
                case 'timefinish[0][timezone]':
                    $timestartzone = (!empty($row[1]) ? $row[1] : '');
                    $rows[] = $row;
                    break;
                case 'datetimeknown':
                    $behatformcontext->i_set_the_field_to($row[0], $row[1]);
                    break;
                default:
                    $rows[] = $row;
                    break;
            }
        }

        if ($timestartzone !== '') {
            date_default_timezone_set($timestartzone);
        }
        $now = time();
        $newdate = strtotime("{$timestartmonth} {$timestartday} {$timestartyear} {$timestarthour} {$timestartmin}" , $now) ;
        $startdate = new DateTime(date('Y-m-d H:i' , $newdate));

        // Values for the minutes field should be multiple of 5 (from 00 to 55). So we need to fix these values.
        $startmin = $startdate->format("i");
        $minutes = (($startmin % 5 ) !== 0) ? floor($startmin / 5) * 5 + 5 : ($startmin / 5) * 5;

        if ($minutes > 55) {
            $minutes = 0;
            $startdate->add(new DateInterval('PT1H'));
        }

        $startdate->setTime($startdate->format('H'), $minutes);

        if ($timefinishzone !== '') {
            date_default_timezone_set($timefinishzone);
        }
        $newdate = strtotime("{$timefinishmonth} {$timefinishday} {$timefinishyear} {$timefinishhour} {$timefinishmin}" , $now) ;
        $finishdate = new DateTime(date('Y-m-d H:i' , $newdate));

        $finishmin = $finishdate->format('i');
        $minutes = (($finishmin % 5 ) !== 0) ? floor($finishmin / 5) * 5 + 5 : ($finishmin / 5) * 5;
        if ($minutes > 55) {
            $minutes = 0;
            $finishdate->add(new DateInterval('PT1H'));
        }
        $finishdate->setTime($finishdate->format('H'), $minutes);

        // Replace values for timestart.
        $rowday[] = array('timestart[0][day]', (int) $startdate->format('d'));
        $rows[] = array('timestart[0][month]', (int) $startdate->format('m'));
        $rows[] = array('timestart[0][day]', (int) $startdate->format('d'));
        $rows[] = array('timestart[0][year]', (int) $startdate->format('Y'));
        $rows[] = array('timestart[0][hour]', (int) $startdate->format('H'));
        $rows[] = array('timestart[0][minute]', (int) $startdate->format('i'));

        // Replace values for timefinish.
        $rowday[] = array('timefinish[0][day]', (int) $finishdate->format('d'));
        $rows[] = array('timefinish[0][month]', (int) $finishdate->format('m'));
        $rows[] = array('timefinish[0][day]', (int) $finishdate->format('d'));
        $rows[] = array('timefinish[0][year]', (int) $finishdate->format('Y'));
        $rows[] = array('timefinish[0][hour]', (int) $finishdate->format('H'));
        $rows[] = array('timefinish[0][minute]', (int) $finishdate->format('i'));

        // Set the the rows back to data.
        $dataclone->setRows($rows);
        $dataday = new TableNode();
        $dataday->setRows($rowday);

        $behatformcontext->i_set_the_following_fields_to_these_values($dataday);
        $behatformcontext->i_set_the_following_fields_to_these_values($dataclone);
    }

    /**
     * Click on a selected link that is located in a table row.
     *
     * @Given /^I click on the link "([^"]*)" in row (\d+)$/
     */
    public function i_click_on_the_link_in_row($text, $row) {
        $xpath = "//table//tbody//tr[{$row}]//a[text()='{$text}']";
        $node = $this->find(
            'xpath',
            $xpath,
            new \Behat\Mink\Exception\ExpectationException('Could not find specific link "'.$text.'" in the row' . $row . $xpath, $this->getSession())
        );
        $node->click();
    }

    /**
     * Select to approve the given user.
     *
     * @Given /^I select to approve "([^"]*)"$/
     */
    public function i_select_to_approve($user) {
        return array(
            new Given('I click on "input[value=\'2\']" "css_element" in the "'.$user.'" "table_row"')
        );
    }
    /**
     * Make duplicates of notification title (in all seminar activities of all courses). Titles must match exactly.
     *
     * @Given /^I make duplicates of seminar notification "([^"]*)"$/
     */
    public function i_make_duplicates_of_seminar_notification($title) {
        global $DB;
        $notifications = $DB->get_records('facetoface_notification', array('title' => $title));
        foreach ($notifications as $note) {
            $note->id = null;
            $DB->insert_record('facetoface_notification', $note);
        }
    }

    /**
     * Clicks on the "Edit session" link for a Facetoface session.
     *
     * @throws \Behat\Mink\Exception\ExpectationException
     * @When /^I click to edit the facetoface session in row (\d+)$/
     * @param int $row
     */
    public function i_click_to_edit_the_facetoface_session_in_row($row) {
        $summaryliteral = $this->getSession()->getSelectorsHandler()->xpathLiteral(get_string('previoussessionslist', 'facetoface'));
        $altliteral = $this->getSession()->getSelectorsHandler()->xpathLiteral(get_string('editsession', 'facetoface'));
        $xpath = "//table[@summary={$summaryliteral}]/tbody/tr[{$row}]//a/img[@alt={$altliteral}]/parent::a";
        /** @var \Behat\Mink\Element\NodeElement[] $nodes */
        $nodes = $this->find_all('xpath', $xpath);
        if (empty($nodes) || count($nodes) > 1) {
            throw new \Behat\Mink\Exception\ExpectationException('Unable to find the edit session link on row '.$row, $this->getSession());
        }
        $node = reset($nodes);
        $node->click();
    }

    /**
     * @When /^I visit the attendees page for session "([^"]*)" with action "([^"]*)"$/
     * @param int $sessionid Face to face session ID
     * @param string $action The action to perform
     */
    public function i_visit_the_attendees_page_for_session_with_action($sessionid, $action){
        $path = "/mod/facetoface/attendees.php?s={$sessionid}&action={$action}";
        $this->getSession()->visit($this->locate_path($path));
        $this->wait_for_pending_js();
    }
}
