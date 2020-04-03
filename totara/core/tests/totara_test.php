<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2017 onwards Totara Learning Solutions LTD
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
 * @author Maria Torres <maria.torres@totaralearning.com>
 * @package totara_core
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Test function from totara/core/totara.php file.
 */
class totara_core_totara_testcase extends advanced_testcase {
    /**
     * Data provider to check visibility of an item.
     *
     * @return array $data Data to be used by test_totara_is_item_visibility_hidden.
     */
    public function visibility_data() {
        $data = array(
            array(0, 1, COHORT_VISIBLE_NOUSERS, false), // Audiencevisibility off, Visible true, audiencevisible set to nonusers.
            array(0, 1, COHORT_VISIBLE_ALL, false), // Audiencevisibility off, Visible true, audiencevisible set to all.
            array(0, 1, COHORT_VISIBLE_AUDIENCE, false), // Audiencevisibility off, Visible true, audiencevisible set to audience.
            array(0, 1, COHORT_VISIBLE_ENROLLED, false), // Audiencevisibility off, Visible true, audiencevisible set to enrolled.
            array(0, 0, COHORT_VISIBLE_NOUSERS, true), // Audiencevisibility off, Visible false, audiencevisible set to nonusers.
            array(1, 0, COHORT_VISIBLE_NOUSERS, true), // Audiencevisibility on, Visible false, audiencevisible set to nonusers.
            array(1, 0, COHORT_VISIBLE_AUDIENCE, false), // Audiencevisibility on, Visible false, audiencevisible set to audience.
            array(1, 0, COHORT_VISIBLE_ENROLLED, false), // Audiencevisibility on, Visible false, audiencevisible set to enrolled.
            array(1, 1, COHORT_VISIBLE_NOUSERS, true), // Audiencevisibility on, Visible true, audiencevisible set to nonusers.
            array(1, 1, COHORT_VISIBLE_AUDIENCE, false), // Audiencevisibility on, Visible true, audiencevisible set to audience.
        );
        return $data;
    }

    /**
     * Test that totara_is_item_visibility_hidden is working as expected.
     * @param bool $audiencevisibilitysetting Setting for audience visibility (1 => ON, 0 => OFF)
     * @param bool $visible Value for normal visibility (0 => Hidden, 1 => visible)
     * @param bool $audiencevisibility Value for audience visibility.
     * @dataProvider visibility_data
     */
    public function test_totara_is_item_visibility_hidden($audiencevisibilitysetting, $visible, $audiencevisibility, $expected) {
        global $CFG;
        $this->resetAfterTest(true);

        // Create course.
        $record = array('visible' => $visible, 'audiencevisible' => $audiencevisibility);
        $course = $this->getDataGenerator()->create_course($record);

        // Set audiencevisibility setting.
        set_config('audiencevisibility', $audiencevisibilitysetting);
        $this->assertEquals($CFG->audiencevisibility, $audiencevisibilitysetting);

        // Call totara_is_item_visibility_hidden and check against the expected result.
        $this->assertEquals($expected, totara_is_item_visibility_hidden($course));
    }

    /**
     * Call totara_is_item_visibility_hidden passing an array instead of an object.
     * @expectedException coding_exception
     */
    public function test_totara_is_item_visibility_hidden_no_object() {
        $this->resetAfterTest(true);

        $item = array('visible' => 1, 'audiencevisible' => 1);
        totara_is_item_visibility_hidden($item);
    }

    /**
     * Call totara_is_item_visibility_hidden passing an object without visible property.
     * @expectedException coding_exception
     */
    public function test_totara_is_item_visibility_hidden_without_visible_property() {
        $this->resetAfterTest(true);

        $item = new stdClass();
        $item->audiencevisible = 1;
        totara_is_item_visibility_hidden($item);
    }

    /**
     * Call totara_is_item_visibility_hidden passing an object without audiencevisible property.
     * @expectedException coding_exception
     */
    public function test_totara_is_item_visibility_hidden_without_audiencevisible_property() {
        $this->resetAfterTest(true);

        $item = new stdClass();
        $item->visible = 1;
        totara_is_item_visibility_hidden($item);
    }
}

