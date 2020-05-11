<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2015 onwards Totara Learning Solutions LTD
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
 * @author Petr Skoda <petr.skoda@totaralms.com>
 * @package tool_totara_sync
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/totara_sync/lib.php');
require_once($CFG->dirroot . '/totara/core/totara.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/position/lib.php');

/**
 * Class tool_totara_sync_user_csv_testcase
 *
 * To test, run this from the command line from the $CFG->dirroot.
 * vendor/bin/phpunit --verbose tool_totara_sync_user_csv_testcase admin/tool/totara_sync/tests/user_csv_test.php
 */
class tool_totara_sync_user_csv_testcase extends advanced_testcase {

    private $filedir = null;
    private $configcsv = array();
    private $config = array();

    protected function tearDown() {
        $this->filedir = null;
        $this->configcsv = null;
        $this->config = null;
        parent::tearDown();
    }

    public function setUp() {
        global $CFG;

        parent::setup();

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->filedir = $CFG->dataroot . '/totara_sync';
        mkdir($this->filedir . '/csv/ready', 0777, true);

        set_config('element_user_enabled', 1, 'totara_sync');
        set_config('source_user', 'totara_sync_source_user_csv', 'totara_sync');
        set_config('fileaccess', FILE_ACCESS_DIRECTORY, 'totara_sync');
        set_config('filesdir', $this->filedir, 'totara_sync');

        $this->configcsv = array(
            'csvuserencoding' => 'UTF-8',
            'delimiter' => ',',
            'fieldmapping_address' => '',
            'fieldmapping_alternatename' => '',
            'fieldmapping_appraiseridnumber' => '',
            'fieldmapping_auth' => '',
            'fieldmapping_city' => '',
            'fieldmapping_country' => '',
            'fieldmapping_deleted' => '',
            'fieldmapping_department' => '',
            'fieldmapping_description' => '',
            'fieldmapping_email' => '',
            'fieldmapping_emailstop' => '',
            'fieldmapping_firstname' => '',
            'fieldmapping_firstnamephonetic' => '',
            'fieldmapping_idnumber' => '',
            'fieldmapping_institution' => '',
            'fieldmapping_jobassignmentenddate' => '',
            'fieldmapping_jobassignmentfullname' => '',
            'fieldmapping_jobassignmentidnumber' => '',
            'fieldmapping_jobassignmentstartdate' => '',
            'fieldmapping_lang' => '',
            'fieldmapping_lastname' => '',
            'fieldmapping_lastnamephonetic' => '',
            'fieldmapping_manageridnumber' => '',
            'fieldmapping_middlename' => '',
            'fieldmapping_orgidnumber' => '',
            'fieldmapping_password' => '',
            'fieldmapping_phone1' => '',
            'fieldmapping_phone2' => '',
            'fieldmapping_posidnumber' => '',
            'fieldmapping_suspended' => '',
            'fieldmapping_timemodified' => '',
            'fieldmapping_timezone' => '',
            'fieldmapping_url' => '',
            'fieldmapping_username' => '',
            'import_address' => '0',
            'import_alternatename' => '0',
            'import_appraiseridnumber' => '0',
            'import_auth' => '0',
            'import_city' => '0',
            'import_country' => '0',
            'import_deleted' => '0',
            'import_department' => '0',
            'import_description' => '0',
            'import_email' => '0',
            'import_emailstop' => '0',
            'import_firstname' => '1',
            'import_firstnamephonetic' => '0',
            'import_idnumber' => '1',
            'import_institution' => '0',
            'import_jobassignmentenddate' => '0',
            'import_jobassignmentfullname' => '0',
            'import_jobassignmentidnumber' => '0',
            'import_jobassignmentstartdate' => '0',
            'import_lang' => '0',
            'import_lastname' => '1',
            'import_lastnamephonetic' => '0',
            'import_manageridnumber' => '0',
            'import_middlename' => '0',
            'import_orgidnumber' => '0',
            'import_password' => '0',
            'import_phone1' => '0',
            'import_phone2' => '0',
            'import_posidnumber' => '0',
            'import_suspended' => '0',
            'import_timemodified' => '1',
            'import_timezone' => '0',
            'import_url' => '0',
            'import_username' => '1',
        );
        $this->config = array(
            'allow_create' => '1',
            'allow_delete' => '0',
            'allow_update' => '1',
            'allowduplicatedemails' => '0',
            'defaultsyncemail' => '',
            'forcepwchange' => '0',
            'undeletepwreset' => '0',
            'ignoreexistingpass' => '0',
            'sourceallrecords' => '0',
            'csvsaveemptyfields' => true,
        );
    }

    public function test_sync_changed_users() {
        global $DB;

        $this->resetAfterTest();

        $this->assertCount(2, $DB->get_records('user'));
        $this->assertCount(0, $DB->get_records('user', array('deleted' => 1)));

        set_config('authdeleteusers', 'full');

        $configcsv = array_merge($this->configcsv, array('import_deleted' => '1'));
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        // Try no action first.
        $config = array_merge($this->config, array('allow_create' => '0', 'allow_update' => '0'));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_changed_1.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(2, $users);

        // Try adding new some users.
        $config = array_merge($this->config, array('allow_update' => '0'));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_changed_1.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(5, $users);
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));

        // Add more users.
        $config = array_merge($this->config, array('allow_update' => '0'));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_changed_2.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        // Modify users with no deletes.
        $config = $this->config;
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_changed_3.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'username' => 'xxxx001')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'username' => 'import002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'username' => 'import003')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'username' => 'import004')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'username' => 'import005')));

        // Suspend deleted users.
        $config = array_merge($this->config, array('allow_delete' => '2')); // Suspend flag, do not use constant here!
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_changed_4.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 1)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'username' => 'xxxx001'))); // No udpate expected.
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'username' => 'import002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'username' => 'import003')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'username' => 'import004')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'username' => 'import005')));

        // Full user delete.
        set_config('authdeleteusers', 'full');

        $config = array_merge($this->config, array('allow_delete' => '1')); // Delete flag, do not use constant here!
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_changed_5.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 1)));
        $this->assertFalse($DB->record_exists('user', array('idnumber' => 'imp002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertCount(1, $DB->get_records('user', array('deleted' => 1)));

        // Legacy partial delete.
        set_config('authdeleteusers', 'partial');

        $config = array_merge($this->config, array('allow_delete' => '1')); // Delete flag, do not use constant here!
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_changed_6.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 1)));
        $this->assertFalse($DB->record_exists('user', array('idnumber' => 'imp002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 1, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertCount(2, $DB->get_records('user', array('deleted' => 1)));

        // Legacy undelete after partial delete.
        set_config('authdeleteusers', 'partial');

        $config = array_merge($this->config, array('allow_delete' => '1')); // Delete flag, do not use constant here!
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_changed_7.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 1)));
        $this->assertFalse($DB->record_exists('user', array('idnumber' => 'imp002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertCount(1, $DB->get_records('user', array('deleted' => 1)));
    }

    public function test_sync_all_users() {
        global $DB;

        $this->resetAfterTest();

        $this->assertCount(2, $DB->get_records('user'));
        $this->assertCount(0, $DB->get_records('user', array('deleted' => 1)));

        set_config('authdeleteusers', 'full');

        $configcsv = $this->configcsv;
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        // Try no action first.
        $config = array_merge($this->config, array(
            'allow_create' => '0',
            'allow_update' => '0',
            'sourceallrecords' => '1'));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_all_1.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(2, $users);

        // Try adding new some users.
        $config = array_merge($this->config, array('sourceallrecords' => '1'));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_all_1.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(5, $users);
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));

        // Add more users and update existing, ignore deleted flag.
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_all_2.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'username' => 'xxxx001')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'username' => 'import002'))); // No timemodified, change ignored.
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'username' => 'import003')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'username' => 'import004')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'username' => 'import005')));

        // Suspend deleted users.
        $config['allow_delete'] = '2'; // Suspend flag, do not use constant here!
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_all_3.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 1)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'username' => 'xxxx001'))); // Suspended not updated!
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'username' => 'import002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'username' => 'import003')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'username' => 'import004')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'username' => 'import005')));


        // Unsuspend deleted users.
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_all_4.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'username' => 'import001')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'username' => 'import002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'username' => 'import003')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'username' => 'import004')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'username' => 'import005')));

        // Full user delete.
        set_config('authdeleteusers', 'full');

        $config['allow_delete'] = '1'; // Delete flag, do not use constant here!
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_all_5.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertFalse($DB->record_exists('user', array('idnumber' => 'imp002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertCount(1, $DB->get_records('user', array('deleted' => 1)));
        // Legacy partial delete.

        set_config('authdeleteusers', 'partial');

        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_all_6.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertFalse($DB->record_exists('user', array('idnumber' => 'imp002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 1, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertCount(2, $DB->get_records('user', array('deleted' => 1)));

        // Legacy undelete after partial delete.
        set_config('authdeleteusers', 'partial');

        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_all_5.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        $users = $DB->get_records('user');
        $this->assertCount(7, $users);

        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertFalse($DB->record_exists('user', array('idnumber' => 'imp002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));

        $this->assertCount(1, $DB->get_records('user', array('deleted' => 1)));
    }

    public function test_csv_import_with_quotes() {
        global $DB;

        $this->resetAfterTest();

        $this->assertCount(2, $DB->get_records('user'));
        $this->assertCount(0, $DB->get_records('user', array('deleted' => 1)));

        set_config('authdeleteusers', 'full');

        $configcsv = $this->configcsv;
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = array_merge($this->config, array('allow_delete' => '1', 'sourceallrecords' => '1'));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/users.02.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        // Check we have admin + guest + 7 users from the CSV
        $this->assertCount(9, $DB->get_records('user'));

        $this->assertSame('User001', $DB->get_field('user', 'lastname', array('idnumber' => 'imp001', 'deleted' => 0, 'suspended' => 0)));
        $this->assertSame('User002 " Double Quote', $DB->get_field('user', 'lastname', array('idnumber' => 'imp002', 'deleted' => 0, 'suspended' => 0)));
        $this->assertSame('User003 \' Single Quote', $DB->get_field('user', 'lastname', array('idnumber' => 'imp003', 'deleted' => 0, 'suspended' => 0)));
        $this->assertSame('User004 \\ Backslash', $DB->get_field('user', 'lastname', array('idnumber' => 'imp004', 'deleted' => 0, 'suspended' => 0)));
        $this->assertSame('User005 " Double " Quote', $DB->get_field('user', 'lastname', array('idnumber' => 'imp005', 'deleted' => 0, 'suspended' => 0)));
        $this->assertSame('User006 \\\' Unnessecary escape', $DB->get_field('user', 'lastname', array('idnumber' => 'imp006', 'deleted' => 0, 'suspended' => 0)));
        $this->assertSame('User007 " Double Quote', $DB->get_field('user', 'lastname', array('idnumber' => 'imp007', 'deleted' => 0, 'suspended' => 0)));
    }

    /**
     * Check that usernames with mixed case characters are imported to
     * lowercase usernames when unique.
     */
    public function test_csv_mixed_case_usernames() {
        global $DB;

        $this->resetAfterTest();

        $this->assertCount(2, $DB->get_records('user'));

        set_config('authdeleteusers', 'full');

        $configcsv = array_merge($this->configcsv, array('import_deleted' => '1'));
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = $this->config;
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /* @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_mixed_case_1.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertFalse($result);

        // Check we have the right number of users. We should have the admin and guest
        // plus three more from the import.
        $this->assertCount(5, $DB->get_records('user'));

        // The 'Admin' and 'LowerCase' users should not be created.
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'User4', 'username' => 'mixedcase1')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'User5', 'username' => 'mixedcase2')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'User6', 'username' => 'mixedcase3')));

        $data = file_get_contents(__DIR__ . '/fixtures/user_mixed_case_2.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        // We shouldn't have an error on this sync. The csae should be ignored
        // and the data imported.
        $result = $element->sync();
        $this->assertTrue($result);

        // The number of users should not change from before.
        $this->assertCount(5, $DB->get_records('user'));

        // Only User4 should be updated with the username being lowercase.
        $this->assertTrue($DB->record_exists('user',
            array('idnumber' => 'User4', 'username' => 'mixedcase1', 'firstname' => 'Charles')));
    }

    public function test_user_sync_disabled_setting() {
        global $DB;

        $this->resetAfterTest();

        $configcsv = $this->configcsv;
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = array_merge($this->config, array('allow_delete' => '1', 'sourceallrecords' => '1'));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        // Run the first sync to add users.
        $data = file_get_contents(__DIR__ . '/fixtures/users.01.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertTrue($result);

        // Check we have admin + guest + 3 users from the CSV
        $this->assertCount(5, $DB->get_records('user'));

        // Update user import001 to turn off the HR Import setting.
        $user = $DB->get_record('user', array('username' => 'import001'));
        $user->totarasync = 0;
        $DB->update_record('user', $user);

        // Run the sync again with an updated CSV. ('-edited' has been appended to their firstname).
        $data = file_get_contents(__DIR__ . '/fixtures/users-edited.01.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        $this->assertFalse($result);

        // Check we have admin + guest + 3 users from the CSV
        $this->assertCount(5, $DB->get_records('user'));

        // Check that user import001 has not been updated.
        $user = $DB->get_record('user', array('username' => 'import001'));
        $this->assertSame('Import', $user->firstname);

        // Check that an import log entry has been created.
        $info = get_string('usersyncdisabled', 'tool_totara_sync', $user);
        $this->assertTrue($DB->record_exists('totara_sync_log', array('info' => $info)));

        // Check the other users have been updated.
        $this->assertSame('Import-edited', $DB->get_field('user', 'firstname', array('username' => 'import002')));
        $this->assertSame('Import-edited', $DB->get_field('user', 'firstname', array('username' => 'import003')));

    }

    /**
     * Test record with missing idnumber is correctly skipped and
     * doesn't effect import of any other records.
     */
    public function test_csv_with_missing_idnumber() {
        global $DB, $CFG;

        $this->resetAfterTest();

        $this->assertCount(2, $DB->get_records('user'));

        $configcsv = array_merge($this->configcsv, array('import_manageridnumber' => '1'));
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = array_merge($this->config, array(
            'sourceallrecords' => '1', // Source contains all records.
            'allow_delete' => '1', // Full delete.
        ));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /** @var totara_sync_element_user $element */
        $element = $elements['user'];

        // This file will addd 3 users.
        $data = file_get_contents(__DIR__ . '/fixtures/user_missing_idnumber_1.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();

        // We have circular management structure.
        $this->assertFalse($result, 'Totara sync succeeded, but one user was missing an id number.');

        $this->assertCount(5, $DB->get_records('user'));
    }

    /**
     * Check that circular management structure are correctly detected.
     */
    public function test_csv_small_circular_management_structure() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $this->assertCount(2, $DB->get_records('user'));
        // No need to check job assignment records for the 3 import users, because the users don't exist yet!

        $configcsv = array_merge($this->configcsv, array('import_deleted' => '1'));
        $configcsv = array_merge($configcsv, array('import_manageridnumber' => '1'));
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = $this->config;
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /* @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_circular_management_1.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        // We have circular managment structure.
        $this->assertFalse($element->sync());

        // Check that all 3 users were imported, and job assignment records were created.
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'username' => 'import001')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'username' => 'import002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'username' => 'import003')));

        $user1 = $DB->get_record('user', array('idnumber' => 'imp001'));
        $this->assertNotEmpty($user1);
        $user1jas = \totara_job\job_assignment::get_all($user1->id);
        $this->assertCount(1, $user1jas);
        $user1ja = reset($user1jas);

        $user2 = $DB->get_record('user', array('idnumber' => 'imp002'));
        $this->assertNotEmpty($user2);
        $user2jas = \totara_job\job_assignment::get_all($user2->id);
        $this->assertCount(1, $user2jas);
        $user2ja = reset($user2jas);

        $user3 = $DB->get_record('user', array('idnumber' => 'imp003'));
        $this->assertNotEmpty($user3);
        $user3jas = \totara_job\job_assignment::get_all($user3->id);
        $this->assertCount(1, $user3jas);
        $user3ja = reset($user3jas);

        // User1 has no manager.
        $this->assertEmpty($user1ja->managerid);

        // For users 2 and 3, the import managed to import one of the managers, but it's random which one.
        if (empty($user2ja->managerid)) {
            $this->assertEquals($user2->id, $user3ja->managerid);
        } else if (empty($user3ja->managerid)) {
            $this->assertEquals($user3->id, $user2ja->managerid);
        } else {
            $this->assertTrue(false, 'One of the managers should have not been imported!');
        }
    }

    /**
     * Check that circular management structure are correctly detected (or not) when there is existing data.
     *
     * Cases:
     * 1) imp001 is managed by imp002. In import set imp001's manager to empty and imp002's manager to imp001. Import
     *    of imp001 succeeds and removes the manager (empty means erase existing value), and imp002 succeeds.
     * 2) imp004 is managed by imp003. In import, set imp003's manager to imp004 and change imp004's manager to imp005.
     *    This should succeed, because all changes happen simultaneously so the potential loop never actually exists.
     */
    public function test_csv_circular_management_with_existing_data() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $this->assertCount(2, $DB->get_records('user'));

        $user1 = $this->getDataGenerator()->create_user(array('idnumber' => 'imp001', 'totarasync' => 1));
        $user2 = $this->getDataGenerator()->create_user(array('idnumber' => 'imp002', 'totarasync' => 1));
        $user3 = $this->getDataGenerator()->create_user(array('idnumber' => 'imp003', 'totarasync' => 1));
        $user4 = $this->getDataGenerator()->create_user(array('idnumber' => 'imp004', 'totarasync' => 1));
        $user5 = $this->getDataGenerator()->create_user(array('idnumber' => 'imp005', 'totarasync' => 1));

        $user2ja = \totara_job\job_assignment::create_default($user2->id);
        $user3ja = \totara_job\job_assignment::create_default($user3->id);
        // Assign user2 to be user1's manager.
        \totara_job\job_assignment::create_default($user1->id, array('managerjaid' => $user2ja->id));
        // Assign user3 to be user4's manager.
        \totara_job\job_assignment::create_default($user4->id, array('managerjaid' => $user3ja->id));

        $this->assertCount(7, $DB->get_records('user'));

        $user1jas = \totara_job\job_assignment::get_all($user1->id);
        $this->assertCount(1, $user1jas);
        $user1ja = reset($user1jas);
        $this->assertEquals($user2->id, $user1ja->managerid);

        $user2jas = \totara_job\job_assignment::get_all($user2->id);
        $this->assertCount(1, $user2jas);
        $user2ja = reset($user2jas);
        $this->assertEmpty($user2ja->managerid);

        $user3jas = \totara_job\job_assignment::get_all($user3->id);
        $this->assertCount(1, $user3jas);
        $user3ja = reset($user3jas);
        $this->assertEmpty($user3ja->managerid);

        $user4jas = \totara_job\job_assignment::get_all($user4->id);
        $this->assertCount(1, $user4jas);
        $user4ja = reset($user4jas);
        $this->assertEquals($user3->id, $user4ja->managerid);

        $user5jas = \totara_job\job_assignment::get_all($user5->id);
        $this->assertCount(0, $user5jas);

        $configcsv = array_merge($this->configcsv, array('import_deleted' => '1'));
        $configcsv = array_merge($configcsv, array('import_email' => '1'));
        $configcsv = array_merge($configcsv, array('import_manageridnumber' => '1'));
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = $this->config;
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /* @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_circular_management_4.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        // We expect no problems during import.
        $this->assertTrue($element->sync());

        // Check the resulting records.
        $this->assertCount(7, $DB->get_records('user'));
        $this->assertNotEmpty($DB->get_record('user', array('id' => $user1->id, 'email' => 'imp001b@local.host'))); // Updated.
        $this->assertNotEmpty($DB->get_record('user', array('id' => $user2->id, 'email' => 'imp002b@local.host'))); // Updated.
        $this->assertNotEmpty($DB->get_record('user', array('id' => $user3->id, 'email' => 'imp003b@local.host'))); // Updated.
        $this->assertNotEmpty($DB->get_record('user', array('id' => $user4->id, 'email' => 'imp004b@local.host'))); // Updated.

        $user1jas = \totara_job\job_assignment::get_all($user1->id);
        $this->assertCount(1, $user1jas);
        $user1ja = reset($user1jas);
        $this->assertEmpty($user1ja->managerid); // Removed manager.

        $user2jas = \totara_job\job_assignment::get_all($user2->id);
        $this->assertCount(1, $user2jas);
        $user2ja = reset($user2jas);
        $this->assertEquals($user1->id, $user2ja->managerid); // Set manager.

        $user3jas = \totara_job\job_assignment::get_all($user3->id);
        $this->assertCount(1, $user3jas);
        $user3ja = reset($user3jas);
        $this->assertEquals($user4->id, $user3ja->managerid); // Set manager.

        $user4jas = \totara_job\job_assignment::get_all($user4->id);
        $this->assertCount(1, $user4jas);
        $user4ja = reset($user4jas);
        $this->assertEquals($user5->id, $user4ja->managerid); // Changed manager.

        $user5jas = \totara_job\job_assignment::get_all($user5->id);
        $this->assertCount(1, $user5jas);
        $user5ja = reset($user5jas);
        $this->assertEmpty($user5ja->managerid); // Created default.
    }

    /**
     * Check that circular management structure are correctly detected
     * (using a bigger set of users in the loop).
     */
    public function test_csv_big_circular_management_structure() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $this->assertCount(2, $DB->get_records('user'));
        // No need to check job assignment records for the 10 import users, because the users don't exist yet!

        $configcsv = array_merge($this->configcsv, array('import_deleted' => '1'));
        $configcsv = array_merge($configcsv, array('import_manageridnumber' => '1'));
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = $this->config;
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /* @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_circular_management_2.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();
        // We have circular managment structure.
        $this->assertFalse($result);

        // Check that all 10 users were imported, and job assignment records were created.
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp001', 'username' => 'import001')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp002', 'username' => 'import002')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp003', 'username' => 'import003')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp004', 'username' => 'import004')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp005', 'username' => 'import005')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp006', 'username' => 'import006')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp007', 'username' => 'import007')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp008', 'username' => 'import008')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp009', 'username' => 'import009')));
        $this->assertTrue($DB->record_exists('user', array('idnumber' => 'imp010', 'username' => 'import010')));

        $user1 = $DB->get_record('user', array('idnumber' => 'imp001'));
        $this->assertNotEmpty($user1);
        $user1jas = \totara_job\job_assignment::get_all($user1->id);
        $this->assertCount(1, $user1jas);
        $user1ja = reset($user1jas);

        $user2 = $DB->get_record('user', array('idnumber' => 'imp002'));
        $this->assertNotEmpty($user2);
        $user2jas = \totara_job\job_assignment::get_all($user2->id);
        $this->assertCount(1, $user2jas);
        $user2ja = reset($user2jas);

        $user3 = $DB->get_record('user', array('idnumber' => 'imp003'));
        $this->assertNotEmpty($user3);
        $user3jas = \totara_job\job_assignment::get_all($user3->id);
        $this->assertCount(1, $user3jas);
        $user3ja = reset($user3jas);

        $user4 = $DB->get_record('user', array('idnumber' => 'imp004'));
        $this->assertNotEmpty($user4);
        $user4jas = \totara_job\job_assignment::get_all($user4->id);
        $this->assertCount(1, $user4jas);
        $user4ja = reset($user4jas);

        $user5 = $DB->get_record('user', array('idnumber' => 'imp005'));
        $this->assertNotEmpty($user5);
        $user5jas = \totara_job\job_assignment::get_all($user5->id);
        $this->assertCount(1, $user5jas);
        $user5ja = reset($user5jas);

        $user6 = $DB->get_record('user', array('idnumber' => 'imp006'));
        $this->assertNotEmpty($user6);
        $user6jas = \totara_job\job_assignment::get_all($user6->id);
        $this->assertCount(1, $user6jas);
        $user6ja = reset($user6jas);

        $user7 = $DB->get_record('user', array('idnumber' => 'imp007'));
        $this->assertNotEmpty($user7);
        $user7jas = \totara_job\job_assignment::get_all($user7->id);
        $this->assertCount(1, $user7jas);
        $user7ja = reset($user7jas);

        $user8 = $DB->get_record('user', array('idnumber' => 'imp008'));
        $this->assertNotEmpty($user8);
        $user8jas = \totara_job\job_assignment::get_all($user8->id);
        $this->assertCount(1, $user8jas);
        $user8ja = reset($user8jas);

        $user9 = $DB->get_record('user', array('idnumber' => 'imp009'));
        $this->assertNotEmpty($user9);
        $user9jas = \totara_job\job_assignment::get_all($user9->id);
        $this->assertCount(1, $user9jas);
        $user9ja = reset($user9jas);

        $user10 = $DB->get_record('user', array('idnumber' => 'imp010'));
        $this->assertNotEmpty($user10);
        $user10jas = \totara_job\job_assignment::get_all($user10->id);
        $this->assertCount(1, $user10jas);
        $user10ja = reset($user10jas);

        // User1 has no manager.
        $this->assertEmpty($user1ja->managerid);

        // User2's manager is user1.
        $this->assertEquals($user1->id, $user2ja->managerid);

        // The import managed to import one of the managers, but it's random which one.
        if (empty($user3ja->managerid)) {
            $this->assertEquals($user3->id, $user4ja->managerid);
            $this->assertEquals($user4->id, $user5ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
            $this->assertEquals($user6->id, $user7ja->managerid);
            $this->assertEquals($user7->id, $user8ja->managerid);
            $this->assertEquals($user8->id, $user9ja->managerid);
            $this->assertEquals($user9->id, $user10ja->managerid);
        } else if (empty($user4ja->managerid)) {
            $this->assertEquals($user10->id, $user3ja->managerid);
            $this->assertEquals($user4->id, $user5ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
            $this->assertEquals($user6->id, $user7ja->managerid);
            $this->assertEquals($user7->id, $user8ja->managerid);
            $this->assertEquals($user8->id, $user9ja->managerid);
            $this->assertEquals($user9->id, $user10ja->managerid);
        } else if (empty($user5ja->managerid)) {
            $this->assertEquals($user10->id, $user3ja->managerid);
            $this->assertEquals($user3->id, $user4ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
            $this->assertEquals($user6->id, $user7ja->managerid);
            $this->assertEquals($user7->id, $user8ja->managerid);
            $this->assertEquals($user8->id, $user9ja->managerid);
            $this->assertEquals($user9->id, $user10ja->managerid);
        } else if (empty($user6ja->managerid)) {
            $this->assertEquals($user10->id, $user3ja->managerid);
            $this->assertEquals($user3->id, $user4ja->managerid);
            $this->assertEquals($user4->id, $user5ja->managerid);
            $this->assertEquals($user6->id, $user7ja->managerid);
            $this->assertEquals($user7->id, $user8ja->managerid);
            $this->assertEquals($user8->id, $user9ja->managerid);
            $this->assertEquals($user9->id, $user10ja->managerid);
        } else if (empty($user7ja->managerid)) {
            $this->assertEquals($user10->id, $user3ja->managerid);
            $this->assertEquals($user3->id, $user4ja->managerid);
            $this->assertEquals($user4->id, $user5ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
            $this->assertEquals($user7->id, $user8ja->managerid);
            $this->assertEquals($user8->id, $user9ja->managerid);
            $this->assertEquals($user9->id, $user10ja->managerid);
        } else if (empty($user8ja->managerid)) {
            $this->assertEquals($user10->id, $user3ja->managerid);
            $this->assertEquals($user3->id, $user4ja->managerid);
            $this->assertEquals($user4->id, $user5ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
            $this->assertEquals($user6->id, $user7ja->managerid);
            $this->assertEquals($user8->id, $user9ja->managerid);
            $this->assertEquals($user9->id, $user10ja->managerid);
        } else if (empty($user9ja->managerid)) {
            $this->assertEquals($user10->id, $user3ja->managerid);
            $this->assertEquals($user3->id, $user4ja->managerid);
            $this->assertEquals($user4->id, $user5ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
            $this->assertEquals($user6->id, $user7ja->managerid);
            $this->assertEquals($user7->id, $user8ja->managerid);
            $this->assertEquals($user9->id, $user10ja->managerid);
        } else if (empty($user10ja->managerid)) {
            $this->assertEquals($user10->id, $user3ja->managerid);
            $this->assertEquals($user3->id, $user4ja->managerid);
            $this->assertEquals($user4->id, $user5ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
            $this->assertEquals($user6->id, $user7ja->managerid);
            $this->assertEquals($user7->id, $user8ja->managerid);
            $this->assertEquals($user8->id, $user9ja->managerid);
        } else {
            $this->assertTrue(false, 'One of the managers should have not been imported!');
        }
    }

    /**
     * Check that circular management structure are correctly detected with numeric id numbers.
     *
     * We know that the user_circular_management_2.csv file contains a circular reference.
     * We will create 5 users, and one position:
     *  - User 1 has User 3 as their manager.
     *
     * Then we will try to import:
     *   - User 3 has User 1 as their manager.
     *
     * That is a circular reference, as we are creating u1 => u3 => u1.
     *
     * We expect that sync() will return false, because it will find this circular reference.
     * We will verify that all users involved in the reference issue will remain, and not be deleted.
     */
    public function test_csv_small_circular_management_structure_with_numeric_ids() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $this->assertCount(2, $DB->get_records('user'));

        $user1 = $this->getDataGenerator()->create_user(array('idnumber' => '1', 'totarasync' => 1));
        $user2 = $this->getDataGenerator()->create_user(array('idnumber' => '2', 'totarasync' => 1));
        $user3 = $this->getDataGenerator()->create_user(array('idnumber' => '3', 'totarasync' => 1));
        $user4 = $this->getDataGenerator()->create_user(array('idnumber' => '4', 'totarasync' => 1));
        $user5 = $this->getDataGenerator()->create_user(array('idnumber' => '5', 'totarasync' => 1));

        $user3ja = \totara_job\job_assignment::create_default($user3->id);
        \totara_job\job_assignment::create_default($user1->id, array('managerjaid' => $user3ja->id));

        $this->assertCount(7, $DB->get_records('user'));

        $user1jas = \totara_job\job_assignment::get_all($user1->id);
        $this->assertCount(1, $user1jas);
        $user1ja = reset($user1jas);
        $this->assertEquals($user3->id, $user1ja->managerid);

        $user2jas = \totara_job\job_assignment::get_all($user2->id);
        $this->assertCount(0, $user2jas);

        $user3jas = \totara_job\job_assignment::get_all($user3->id);
        $this->assertCount(1, $user3jas);
        $user3ja = reset($user3jas);
        $this->assertEmpty($user3ja->managerid);

        $user4jas = \totara_job\job_assignment::get_all($user4->id);
        $this->assertCount(0, $user4jas);

        $user5jas = \totara_job\job_assignment::get_all($user5->id);
        $this->assertCount(0, $user5jas);

        $configcsv = array_merge($this->configcsv, array('import_manageridnumber' => '1'));
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = array_merge($this->config, array(
            'sourceallrecords' => '1', // Source contains all records.
            'allow_delete' => '1', // Full delete.
        ));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /* @var totara_sync_element_user $element */
        $element = $elements['user'];

        // This file is going to add one user (6), and delete one user (2).
        $data = file_get_contents(__DIR__ . '/fixtures/user_circular_management_3.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        // We have circular management structure.
        $this->assertFalse($element->sync(), 'Totara sync succeeded, but we know there was a circular reference.');

        // Load user 6 first.
        $user6 = $DB->get_record('user', array('idnumber' => '6'));

        // Check that all 5 users were imported, and job assignment records were created. User 2 was deleted.
        $user1 = $DB->get_record('user', array('id' => $user1->id));
        $this->assertNotEmpty($user1);
        $this->assertEquals('1', $user1->idnumber);
        $this->assertEquals(0, $user1->deleted);
        $user1jas = \totara_job\job_assignment::get_all($user1->id);
        $this->assertCount(1, $user1jas);
        $user1ja = reset($user1jas);

        $user2 = $DB->get_record('user', array('id' => $user2->id));
        $this->assertNotEmpty($user2);
        $this->assertEquals(1, $user2->deleted);
        $user2jas = \totara_job\job_assignment::get_all($user2->id);
        $this->assertCount(0, $user2jas);

        $user3 = $DB->get_record('user', array('id' => $user3->id));
        $this->assertNotEmpty($user3);
        $this->assertEquals('3', $user3->idnumber);
        $this->assertEquals(0, $user3->deleted);
        $user3jas = \totara_job\job_assignment::get_all($user3->id);
        $this->assertCount(1, $user3jas);
        $user3ja = reset($user3jas);

        $user4 = $DB->get_record('user', array('id' => $user4->id));
        $this->assertNotEmpty($user4);
        $this->assertEquals('4', $user4->idnumber);
        $this->assertEquals(0, $user4->deleted);
        $user4jas = \totara_job\job_assignment::get_all($user4->id);
        $this->assertCount(1, $user4jas);
        $user4ja = reset($user4jas);

        $user5 = $DB->get_record('user', array('id' => $user5->id));
        $this->assertNotEmpty($user5);
        $this->assertEquals('5', $user5->idnumber);
        $this->assertEquals(0, $user5->deleted);
        $user5jas = \totara_job\job_assignment::get_all($user5->id);
        $this->assertCount(1, $user5jas);
        $user5ja = reset($user5jas);

        $this->assertNotEmpty($user6);
        $this->assertEquals('6', $user6->idnumber);
        $this->assertEquals(0, $user6->deleted);
        $user6jas = \totara_job\job_assignment::get_all($user6->id);
        $this->assertCount(1, $user6jas);
        $user6ja = reset($user6jas);

        // For users 1 and 3, the import managed to import one of the managers, but it's random which one.
        if (empty($user1ja->managerid)) {
            $this->assertEquals($user1->id, $user3ja->managerid);
        } else if (empty($user3ja->managerid)) {
            $this->assertEquals($user3->id, $user1ja->managerid);
        } else {
            $this->assertTrue(false, 'One of the managers should have not been imported!');
        }

        // For users 4, 5 and 6, the import managed to import two of the managers, but it's random which two.
        if (empty($user4ja->managerid)) {
            $this->assertEquals($user4->id, $user5ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
        } else if (empty($user5ja->managerid)) {
            $this->assertEquals($user6->id, $user4ja->managerid);
            $this->assertEquals($user5->id, $user6ja->managerid);
        } else if (empty($user6ja->managerid)) {
            $this->assertEquals($user6->id, $user4ja->managerid);
            $this->assertEquals($user4->id, $user5ja->managerid);
        } else {
            $this->assertTrue(false, 'One of the managers should have not been imported!');
        }

    }

    /**
     * Test that a source set to contain all records, and delete set to full delete does in fact delete
     * users that are missing from the source.
     */
    public function test_csv_source_contains_all_records_deletes_users() {
        global $DB;

        set_config('authdeleteusers', 'full');

        $this->resetAfterTest();

        $this->assertSame(2, $DB->count_records('user', ['deleted' => '0']));

        $this->getDataGenerator()->create_user(array('idnumber' => 'u1', 'totarasync' => 1));
        $this->getDataGenerator()->create_user(array('idnumber' => 'u2', 'totarasync' => 1));
        $this->getDataGenerator()->create_user(array('idnumber' => 'u3', 'totarasync' => 1));

        $this->assertSame(5, $DB->count_records('user', ['deleted' => '0']));

        $configcsv = array_merge($this->configcsv, array());
        foreach ($configcsv as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_csv');
        }

        $config = array_merge($this->config, array(
            'sourceallrecords' => '1', // Source contains all records.
            'allow_delete' => '1', // Full delete.
        ));
        foreach ($config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }

        $elements = totara_sync_get_elements(true);
        /* @var totara_sync_element_user $element */
        $element = $elements['user'];

        $data = file_get_contents(__DIR__ . '/fixtures/user_small_complete_source.csv');
        $filepath = $this->filedir . '/csv/ready/user.csv';
        file_put_contents($filepath, $data);

        $result = $element->sync();

        // We have circular management structure.
        $this->assertTrue($result);

        // One user added, one deleted, the number should still be 5.
        $this->assertSame(5, $DB->count_records('user', ['deleted' => '0']));
        $menu = $DB->get_records_menu('user', ['deleted' => '0'], 'id', 'id, idnumber');
        $expected = ['u1', 'u3', 'u4'];
        foreach ($menu as $id => $idnumber) {
            if ($idnumber === '') {
                continue;
            }
            $this->assertContains($idnumber, $expected);
        }
    }
}
