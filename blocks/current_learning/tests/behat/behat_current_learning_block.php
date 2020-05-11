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
 * @author Simon Player <simon.player@totaralearning.com>
 * @package block_current_learning
 */

// NOTE: no MOODLE_INTERNAL used, this file may be required by behat before including /config.php.
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Then as Then;

class behat_current_learning_block extends behat_base {

    /**
     * Click on the expand item for a program or certification if the current learning block.
     *
     * @Given /^I toggle "([^"]*)" in the current learning block$/
     */
    public function i_toggle_item_in_current_learning_block($program) {
        $program_xpath = $this->getSession()->getSelectorsHandler()->xpathLiteral($program);
        $xpath = ".//li[div[@class[contains(.,'block_current_learning-row-item')]][.//text()[.=" . $program_xpath . "]]]";
        $row = $this->find(
            'xpath',
            $xpath,
            new \Behat\Mink\Exception\ExpectationException('Could not find item row for "'.$program.'" in the current learning block' . $xpath, $this->getSession())
        );

        $xpath = "//*[@class[contains(.,'expand-collapse-icon-wrap')]]";
        $node = $row->find(
            'xpath',
            $xpath,
            new \Behat\Mink\Exception\ExpectationException('Could not find specific expand icon for "'.$program.'" in the current learning block' . $xpath, $this->getSession())
        );
        $node->click();
    }

    /**
     * Check if a course exists within a program or certification in the current learning block.
     *
     * @Given /^I should see "([^"]*)" in "([^"]*)" within the current learning block$/
     */
    public function i_should_see_course_in_program_within_the_current_learning_block($course, $program) {
        $program_xpath = $this->getSession()->getSelectorsHandler()->xpathLiteral($program);
        $xpath = ".//li[div[@class[contains(.,'block_current_learning-row-item')]][.//text()[.=" . $program_xpath . "]]]";
        return new Then('I should see "' . $course .'" in the "' . $xpath .'" "xpath_element"');
    }

    /**
     * Check if a course exists within a program or certification in the current learning block.
     *
     * @Given /^I should not see "([^"]*)" in "([^"]*)" within the current learning block$/
     */
    public function i_should_not_see_course_in_program_within_the_current_learning_block($course, $program) {
        $program_xpath = $this->getSession()->getSelectorsHandler()->xpathLiteral($program);
        $xpath = ".//li[div[@class[contains(.,'block_current_learning-row-item')]][.//text()[.=" . $program_xpath . "]]]";
        return new Then('I should not see "' . $course .'" in the "' . $xpath .'" "xpath_element"');
    }

}
