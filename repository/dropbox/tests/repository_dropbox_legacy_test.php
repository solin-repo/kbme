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
 * @author Nathan Lewis <nathan.lewis@totaralearning.com>
 * @package repository_dropbox
 */

global $CFG;
require_once("$CFG->dirroot/repository/lib.php");

class repository_dropbox_legacy_testcase extends advanced_testcase {

    /**
     * Test the get_option function.
     */
    public function test_get_option() {
        $this->resetAfterTest(true);

        // Create the legacy dropbox object.
        $dropboxplugin = new repository_type('dropbox', array(), true);
        $dropboxplugin->create(true);
        $repoid = $this->getDataGenerator()->create_repository('dropbox')->id;
        $dropbox = new repository_dropbox_legacy($repoid);

        // Set up the test data.
        set_config('dropbox_key', 'testdata1   ', 'dropbox');
        set_config('dropbox_secret', '    testdata2', 'dropbox');
        $dropbox->cachelimit = 12345;

        // Test.
        $this->assertEquals('testdata1', $dropbox->get_option('dropbox_key'));
        $this->assertEquals('testdata2', $dropbox->get_option('dropbox_secret'));
        $this->assertEquals(12345, $dropbox->get_option('dropbox_cachelimit'));

        $expectall = array(
            'dropbox_key' => 'testdata1',
            'dropbox_secret' => 'testdata2',
            'dropbox_cachelimit' => 12345,
        );
        $this->assertEquals($expectall, $dropbox->get_option());
    }
}
