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
 * @package    totara_completionimport
 * @author     Brendan Cox <brendan.cox@totaralearning.com>
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/totara/completionimport/lib.php');

/**
 * Class totara_completionimport_lib_testcase.
 *
 * Tests functions within the totara/completionimport/lib.php file.
 */
class totara_completionimport_lib_testcase extends advanced_testcase {

    /**
     * DataProvider for test_import_data_checks_date_formats
     *
     * Each set of data supplied to the test will have
     * - a date format.
     * - a set of completion dates in the various formats, plus some extras that will always fail.
     * - a set of expected results (true or false) for each completion date.
     *
     * @return array
     */
    public function data_provider_date_formats() {
        $csvdateformats = array(
            'Y-m-d', 'Y/m/d', 'Y.m.d', 'Y m d',
            'y-m-d', 'y/m/d', 'y.m.d', 'y m d',
            'd-m-Y', 'd/m/Y', 'd.m.Y', 'd m Y',
            'd-m-y', 'd/m/y', 'd.m.y', 'd m y',
            'm-d-Y', 'm/d/Y', 'm.d.Y', 'm d Y',
            'm-d-y', 'm/d/y', 'm.d.y', 'm d y',
        );

        // Users will be created in test_import_data_checks_date_formats
        // that will each have one of the completion dates.
        $completiondates_canbevalid = array(
            'Y-m-d' => '1998-08-30',
            'Y/m/d' => '1998/08/30',
            'Y.m.d' => '1998.08.30',
            'Y m d' => '1998 08 30',
            'y-m-d' => '98-08-30',
            'y/m/d' => '98/08/30',
            'y.m.d' => '98.08.30',
            'y m d' => '98 08 30',
            'd-m-Y' => '30-08-1998',
            'd/m/Y' => '30/08/1998',
            'd.m.Y' => '30.08.1998',
            'd m Y' => '30 08 1998',
            'd-m-y' => '30-08-98',
            'd/m/y' => '30/08/98',
            'd.m.y' => '30.08.98',
            'd m y' => '30 08 98',
            'm-d-Y' => '08-30-1998',
            'm/d/Y' => '08/30/1998',
            'm.d.Y' => '08.30.1998',
            'm d Y' => '08 30 1998',
            'm-d-y' => '08-30-98',
            'm/d/y' => '08/30/98',
            'm.d.y' => '08.30.98',
            'm d y' => '08 30 98',
            'd/m/Y - singledigit' => '30/8/1998',
            'd-m-y - singledigit' => '30-8-98',
            'm d Y - singledigit' => '8 30 1998',
            'd.m.y or m.d.y - singledigits' => '8.6.98',
            'd/m/Y - leapyear' => '29/02/2016'
        );

        $completiondates_nevervalid = array(
            'nonsensicalnumbers' => '52.86.6452',
            'letters' => 'one day',
            'empty' => '',
            'd/m/Y - non-leapyear' => '29/02/2015',
            'd.m.y - 32 day month' => '32/05/2016',
            'Y-m-d - 13 month year' => '2014/13/15'
        );

        // Create an array with the same keys as above, and the expected outcome for each.
        // By default, this is false. When we put the data sets together, we'll overwrite the expected
        // result for valid formats with true.
        $expectedresults = array();
        foreach($completiondates_canbevalid as $key => $completiondate) {
            $expectedresults[$key] = false;
        }
        foreach($completiondates_nevervalid as $key => $completiondate) {
            $expectedresults[$key] = false;
        }

        // Build the data array
        $data = array();
        foreach($csvdateformats as $csvdateformat) {
            $thisexpectedresults = $expectedresults;
            // Below, we set the expected result to true if the format of the corresponding completion date
            // will be valid.
            foreach($completiondates_canbevalid as $key => $completiondate) {
                if (strpos($key, $csvdateformat) !== false) {
                    // If the format of the $completiondate is exactly the same
                    // as $csvdateformat, the result should return true.
                    $thisexpectedresults[$key] = true;
                }
            }

            $completiondates = array_merge($completiondates_canbevalid, $completiondates_nevervalid);

            // Add the data set.
            $data[] = array(
                $csvdateformat,
                $completiondates,
                $thisexpectedresults
            );
        }
        return $data;
    }

    /**
     * Tests that totara_completionimport_validate_date returns the correct values for various dates
     * and formats.
     *
     * @param string $csvdateformat - the format for the completion import, e.g. 'Y-m-d'.
     * @param array $completiondates - contains completion dates in different formats. Keys describe the format.
     * @param array $expectedresults - has the same keys as $completiondates, this contains the expected result for
     * each one.
     *
     * @dataProvider data_provider_date_formats
     */
    public function test_totara_completionimport_validate_date($csvdateformat, $completiondates, $expectedresults) {
        $this->resetAfterTest(true);

        $this->assertEquals(count($completiondates), count($expectedresults));

        foreach($completiondates as $key => $completiondate) {
            $result = totara_completionimport_validate_date($csvdateformat, $completiondate);
            $this->assertEquals($expectedresults[$key], $result, 'Failed for completion date with format: ' . $key);
        }
    }

    /**
     * Tests move_sourcefile().
     *
     * If the config setting 'completionimportdir' has not been set, the function should return
     * false as it we don't want to move files without it.
     */
    public function test_move_sourcefile_noconfig() {
        $this->resetAfterTest(true);
        global $CFG;

        // Config setting should be empty by default.
        $this->assertTrue(empty($CFG->completionimportdir));

        $dirpath = $CFG->dirroot . '/totara/completionimport/tests/fixtures/';
        $filename = $dirpath . 'course_single_upload.csv';

        // The temp file name shouldn't be used as the function will return true before using it in
        // a php unit test. But we'll set it somewhere safe in case that doesn't happen.
        $tempfilename = $dirpath . 'new_course_single_upload.csv';

        ob_start();
        $result = move_sourcefile($filename, $tempfilename);
        $this->assertFalse($result);
        $output = ob_get_clean();
        $this->assertContains('Additional configuration settings are required', $output);

        // No temp file should have been created whether this was a unit test or not.
        $this->assertFalse(is_readable($tempfilename));
    }

    /**
     * Tests move_sourcefile().
     *
     * The config setting is given a value. The file supplied to the function we're testing
     * is in the directory given in the config setting. The function should return true.
     */
    public function test_move_sourcefile_configset_valid_no_subdir() {
        $this->resetAfterTest(true);
        global $CFG;

        $dirpath = $CFG->dirroot . '/totara/completionimport/tests/fixtures/';

        $CFG->completionimportdir = $dirpath;

        $filename = $dirpath . 'course_single_upload.csv';

        // The temp file name shouldn't be used as the function will return true before using it in
        // a php unit test. But we'll set it somewhere safe in case that doesn't happen.
        $tempfilename = $dirpath . 'new_course_single_upload.csv';

        ob_start();
        $result = move_sourcefile($filename, $tempfilename);
        $this->assertTrue($result);
        $output = ob_get_clean();
        $this->assertEmpty($output);

        // For a live site, the temp file would have been created. But this should not happen for a unit test.
        $this->assertFalse(is_readable($tempfilename));
    }

    /**
     * Tests move_sourcefile().
     *
     * The config setting is given a value. The file supplied to the function we're testing
     * is in a subdirectory of the what's in the config setting. The function should return true.
     */
    public function test_move_sourcefile_configset_valid_in_subdir() {
        $this->resetAfterTest(true);
        global $CFG;

        $dirpath = $CFG->dirroot . '/totara/completionimport/tests/fixtures/';

        $CFG->completionimportdir = $dirpath;

        $filename = $dirpath . 'subdir/course_single_upload.csv';

        // The temp file name shouldn't be used as the function will return true before using it in
        // a php unit test. But we'll set it somewhere safe in case that doesn't happen.
        $tempfilename = $dirpath . 'new_course_single_upload.csv';

        ob_start();
        $result = move_sourcefile($filename, $tempfilename);
        $this->assertTrue($result);
        $output = ob_get_clean();
        $this->assertEmpty($output);

        // For a live site, the temp file would have been created. But this should not happen for a unit test.
        $this->assertFalse(is_readable($tempfilename));
    }

    /**
     * Tests move_sourcefile().
     *
     * The config setting is set. But the filename supplied to the function we're
     * testing has a different path.
     */
    public function test_move_sourcefile_configset_invalid() {
        $this->resetAfterTest(true);
        global $CFG;

        $dirpath = $CFG->dirroot . '/totara/completionimport/tests/fixtures/';

        $CFG->completionimportdir = $dirpath;

        $filename = '/totara/completionimport/tests/behat/course_single_upload.csv';

        // The temp file name shouldn't be used as the function will return true before using it in
        // a php unit test. But we'll set it somewhere safe in case that doesn't happen.
        $tempfilename = $dirpath . 'new_course_single_upload.csv';

        ob_start();
        $result = move_sourcefile($filename, $tempfilename);
        $this->assertFalse($result);
        $output = ob_get_clean();
        $this->assertContains('The source file name must include the full path to the file and begin with ', $output);

        // No temp file should have been created whether this was a unit test or not.
        $this->assertFalse(is_readable($tempfilename));
    }
}
