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
 * @author Simon Player <simon.player@totaralms.com>
 * @package totara_sync
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/totara_sync/lib.php');
require_once($CFG->dirroot . '/admin/tool/totara_sync/sources/databaselib.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/position/lib.php');

/**
 * Class tool_totara_sync_user_external_database_testcase
 *
 * These tests require an external database to be configured and defined in the sites config.
 * See the README.md file in /admin/tool/totara_sync/
 *
 * These tests will be skipped if an external database is not defined.
 *
 * To test, run this from the command line from the $CFG->dirroot.
 * vendor/bin/phpunit --verbose tool_totara_sync_user_external_database_testcase admin/tool/totara_sync/tests/user_external_database_test.php
 */
class tool_totara_sync_user_external_database_testcase extends advanced_testcase {

    private $configdb = array();
    private $config = array();
    private $configexists = false;

    private $ext_dbconnection = null;

    // Database variables for connection.
    private $dbtype = '';
    private $dbhost = '';
    private $dbport = '';
    private $dbname = '';
    private $dbuser = '';
    private $dbpass = '';
    private $dbtable = '';

    // The fields to import and test.
    private $fieldstoimport = array(
        'idnumber' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => '1',
        ),
        'username' => array(
            'maxfieldsize' => 100,
            'type' => 'string',
            'data' => 'user1',
        ),
        'firstname' => array(
            'maxfieldsize' => 100,
            'type' => 'string',
            'data' => 'firstname',
        ),
        'lastname' => array(
            'maxfieldsize' => 100,
            'type' => 'string',
            'data' => 'lastname',
        ),
        'firstnamephonetic' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => 'firstnamephonetic',
        ),
        'lastnamephonetic' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => 'lastnamephonetic',
        ),
        'middlename' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => 'middlename',
        ),
        'alternatename' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => 'alternatename',
        ),
        'email' => array(
            'maxfieldsize' => 100,
            'type' => 'string',
            'data' => 'email@example.com',
        ),
        'city' => array(
            'maxfieldsize' => 120,
            'type' => 'string',
            'data' => 'Brighton',
        ),
        'url' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => 'https://www.totaralms.com/',
        ),
        'institution' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => 'institution',
        ),
        'department' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => 'department',
        ),
        'phone1' => array(
            'maxfieldsize' => 20,
            'type' => 'string',
            'data' => '0123456789',
        ),
        'phone2' => array(
            'maxfieldsize' => 20,
            'type' => 'string',
            'data' => '9876543210',
        ),
        'address' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => 'address',
        ),
        'password' => array(
            'maxfieldsize' => 255,
            'type' => 'string',
            'data' => '!Passw0rd$#!'
        ),
    );

    public function setUp() {
        global $CFG;

        parent::setup();

        $this->resetAfterTest(true);
        $this->preventResetByRollback();
        $this->setAdminUser();

        if (defined('TEST_SYNC_DB_TYPE') ||
            defined('TEST_SYNC_DB_HOST') ||
            defined('TEST_SYNC_DB_PORT') ||
            defined('TEST_SYNC_DB_NAME') ||
            defined('TEST_SYNC_DB_USER') ||
            defined('TEST_SYNC_DB_PASS') ||
            defined('TEST_SYNC_DB_TABLE')) {
            $this->dbtype = defined('TEST_SYNC_DB_TYPE') ? TEST_SYNC_DB_TYPE : '';
            $this->dbhost = defined('TEST_SYNC_DB_HOST') ? TEST_SYNC_DB_HOST : '';
            $this->dbport = defined('TEST_SYNC_DB_PORT') ? TEST_SYNC_DB_PORT : '';
            $this->dbname = defined('TEST_SYNC_DB_NAME') ? TEST_SYNC_DB_NAME : '';
            $this->dbuser = defined('TEST_SYNC_DB_USER') ? TEST_SYNC_DB_USER : '';
            $this->dbpass = defined('TEST_SYNC_DB_PASS') ? TEST_SYNC_DB_PASS : '';
            $this->dbtable = defined('TEST_SYNC_DB_TABLE') ? TEST_SYNC_DB_TABLE : '';
        } else {
            $this->dbtype = $CFG->dbtype;
            $this->dbhost = $CFG->dbhost;
            $this->dbport = !empty($CFG->dboptions['dbport']) ? $CFG->dboptions['dbport'] : '';
            $this->dbname = $CFG->dbname;
            $this->dbuser = $CFG->dbuser;
            $this->dbpass = !empty($CFG->dbpass) ? $CFG->dbpass : '';
            $this->dbtable = $CFG->phpunit_prefix . 'totara_sync_user_source';
        }

        if (!empty($this->dbtype) &&
            !empty($this->dbhost) &&
            !empty($this->dbname) &&
            !empty($this->dbuser) &&
            !empty($this->dbtable)) {
            // All necessary config variables are set.
            $this->configexists = true;
            $this->ext_dbconnection = setup_sync_DB($this->dbtype, $this->dbhost, $this->dbname, $this->dbuser, $this->dbpass, array('dbport' => $this->dbport));
        } else {
            $this->assertTrue(false, 'HR Import database test configuration was only partially provided');
        }

        set_config('element_user_enabled', 1, 'totara_sync');
        set_config('source_user', 'totara_sync_source_user_database', 'totara_sync');

        $this->configdb = array(
            'database_dbtype' => $this->dbtype,
            'database_dbhost' => $this->dbhost,
            'database_dbname' => $this->dbname,
            'database_dbuser' => $this->dbuser,
            'database_dbpass' => $this->dbpass,
            'database_dbport' => $this->dbport,
            'database_dbtable' => $this->dbtable,
            'csvuserencoding' => 'UTF-8',
            'delimiter' => ',',
            'import_deleted' => '1',
            'import_timemodified' => '1',
        );
        $this->config = array(
            'allow_create' => '1',
            'allow_delete' => '0',
            'allow_update' => '1',
            'allowduplicatedemails' => '0',
            'defaultsyncemail' => '',
            'forcepwchange' => '0',
            'ignoreexistingpass' => '0',
            'sourceallrecords' => '0',
        );

        // Update the config to set fields to import.
        foreach ($this->fieldstoimport as $field => $fieldsettings) {
            $this->configdb['import_' . $field] = '1';
        }

        // Set the config.
        set_config('timezone', $this->setTimezone());
        set_config('database_dateformat', 'Y-m-d', 'totara_sync_source_user_database');
        foreach ($this->configdb as $k => $v) {
            set_config($k, $v, 'totara_sync_source_user_database');
        }
        foreach ($this->config as $k => $v) {
            set_config($k, $v, 'totara_sync_element_user');
        }
    }

    protected function tearDown() {
        if ($this->configexists) {
            // Drop sync table.
            $dbman = $this->ext_dbconnection->get_manager();
            $table = new xmldb_table($this->dbtable);
            if ($dbman->table_exists($this->dbtable)) {
                $dbman->drop_table($table, $this->dbtable);
            }
        }
        $this->configdb = null;
        $this->config = null;
        $this->configexists = null;
        $this->ext_dbconnection = null;
        $this->dbtype = null;
        $this->dbhost = null;
        $this->dbport = null;
        $this->dbname = null;
        $this->dbuser = null;
        $this->dbpass = null;
        $this->dbtable = null;
        $this->fieldstoimport = null;
        parent::tearDown();
    }

    protected function create_external_user_table() {
        $dbman = $this->ext_dbconnection->get_manager();
        $table = new xmldb_table($this->dbtable);

        // Drop table first, if it exists.
        if ($dbman->table_exists($this->dbtable)) {
            $dbman->drop_table($table, $this->dbtable);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        // Create fields from fieldstoimport array.
        foreach ($this->fieldstoimport as $field => $fieldsettings) {
            $type = $fieldsettings['type'] == 'integer' ? XMLDB_TYPE_INTEGER : XMLDB_TYPE_CHAR;
            $table->add_field($field, $type, $fieldsettings['maxfieldsize']);
        }

        // Add keys.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Create the table.
        $dbman->create_table($table, false, false);
    }

    protected function populate_external_user_table($skipcreatetable = false) {
        if (!$skipcreatetable) {
            // First, lets create user table.
            $this->create_external_user_table();
        }

        // Data to insert.
        $data = array(
            "timemodified" => 0,
            "deleted" => 0
        );

        // Additional data.
        foreach ($this->fieldstoimport as $field => $fieldsettings) {
            $data[$field] = $fieldsettings['data'];
        }

        $this->ext_dbconnection->insert_record($this->dbtable, $data, true);
    }

    protected function generate_data($type, $max, $isemail = null) {
        $data = '';
        if ($type == 'string') {
            $items = range('a', 'z');
        } else {
            $items = range(0, 9);
        }
        $numitems = count($items);

        for ($i = 1; $i <= $max; $i++) {
            $data .= $items[rand(0,$numitems-1)];
        }

        if ($isemail) {
            $data = substr($data, 0, -12) . "@example.com";
        }

        return $data;
    }

    protected function run_sync() {
        $elements = totara_sync_get_elements(true);
        $element = $elements['user'];
        $result = $element->sync();
        return $result;
    }

    public function test_sync_database_connect() {
        if (!$this->configexists) {
            $this->markTestSkipped();
        }
        $this->assertInstanceOf('moodle_database', $this->ext_dbconnection);
    }

    // Test the sync using data from fieldstoimport array.
    public function test_sync() {
        global $DB;
        if (!$this->configexists) {
            $this->markTestSkipped();
        }

        // Populate the external db table.
        $this->populate_external_user_table();

        // Run and test the sync.
        $this->assertTrue($this->run_sync());

        // Check data synced correctly.
        $data = array();
        foreach ($this->fieldstoimport as $field => $fieldsettings) {
            if ($field != "password") {
                $data[$field] = $fieldsettings['data'];
            }
        }
        $this->assertTrue($DB->record_exists('user', $data));
    }

    // Test the sync using data from fieldstoimport array but with the data length set to its maximum length.
    public function test_sync_max_fieldsize() {
        global $DB;
        if (!$this->configexists) {
            $this->markTestSkipped();
        }

        // Set import data to maximum length.
        foreach ($this->fieldstoimport as $field => $fieldsettings) {
            $isemail = $field == 'email' ? true : false;
            $this->fieldstoimport[$field]['data'] = $this->generate_data($this->fieldstoimport[$field]['type'], $this->fieldstoimport[$field]['maxfieldsize'], $isemail);
        }

        // Populate the external db table.
        $this->populate_external_user_table();

        // Run and test the sync.
        $this->assertTrue($this->run_sync());

        // Check data synced correctly.
        $data = array();
        foreach ($this->fieldstoimport as $field => $fieldsettings) {
            if ($field !== "password") {
                $data[$field] = $fieldsettings['data'];
            }
        }
        $this->assertTrue($DB->record_exists('user', $data));
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
    public function test_circular_management_with_empty_manageridnumber() {
        global $DB;
        if (!$this->configexists) {
            $this->markTestSkipped();
        }

        $this->assertCount(2, $DB->get_records('user'));

        $this->fieldstoimport['manageridnumber'] = array(
            'maxfieldsize' => 20,
            'type' => 'string',
            'data' => 'firstname',
        );
        set_config('import_manageridnumber', '1', 'totara_sync_source_user_database');

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

        // Configure and create the user1 import record.
        $this->fieldstoimport['idnumber']['data'] = 'imp001';
        $this->fieldstoimport['username']['data'] = 'import001';
        $this->fieldstoimport['firstname']['data'] = 'Import';
        $this->fieldstoimport['lastname']['data'] = 'User001';
        $this->fieldstoimport['email']['data'] = 'imp001b@local.host';
        $this->fieldstoimport['manageridnumber']['data'] = '';
        $this->populate_external_user_table(false); // First user, create the db table as well.

        // Configure and create the user2 import record.
        $this->fieldstoimport['idnumber']['data'] = 'imp002';
        $this->fieldstoimport['username']['data'] = 'import002';
        $this->fieldstoimport['firstname']['data'] = 'Import';
        $this->fieldstoimport['lastname']['data'] = 'User002';
        $this->fieldstoimport['email']['data'] = 'imp002b@local.host';
        $this->fieldstoimport['manageridnumber']['data'] = 'imp001';
        $this->populate_external_user_table(true);

        // Configure and create the user3 import record.
        $this->fieldstoimport['idnumber']['data'] = 'imp003';
        $this->fieldstoimport['username']['data'] = 'import003';
        $this->fieldstoimport['firstname']['data'] = 'Import';
        $this->fieldstoimport['lastname']['data'] = 'User003';
        $this->fieldstoimport['email']['data'] = 'imp003b@local.host';
        $this->fieldstoimport['manageridnumber']['data'] = 'imp004';
        $this->populate_external_user_table(true);

        // Configure and create the user4 import record.
        $this->fieldstoimport['idnumber']['data'] = 'imp004';
        $this->fieldstoimport['username']['data'] = 'import004';
        $this->fieldstoimport['firstname']['data'] = 'Import';
        $this->fieldstoimport['lastname']['data'] = 'User004';
        $this->fieldstoimport['email']['data'] = 'imp004b@local.host';
        $this->fieldstoimport['manageridnumber']['data'] = 'imp005';
        $this->populate_external_user_table(true);

        // We expect no problems during import.
        $this->assertTrue($this->run_sync());

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
     * Check that circular management structure are correctly detected (or not) when there is existing data.
     *
     * Cases:
     * 1) imp001 is managed by imp002. In import set imp001's manager to null and imp002's manager to imp001. Import
     *    of imp001 succeeds and but does not remove the manager (null means do nothing), and imp002 fails because it
     *    would create a management loop.
     * 2) imp004 is managed by imp003. In import, set imp003's manager to imp004 and change imp004's manager to imp005.
     *    This should succeed, because all changes happen simultaneously so the potential loop never actually exists.
     */
    public function test_circular_management_with_null_manageridnumber() {
        global $DB;
        if (!$this->configexists) {
            $this->markTestSkipped();
        }

        $this->assertCount(2, $DB->get_records('user'));

        $this->fieldstoimport['manageridnumber'] = array(
            'maxfieldsize' => 20,
            'type' => 'string',
            'data' => 'firstname',
        );
        set_config('import_manageridnumber', '1', 'totara_sync_source_user_database');

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

        // Configure and create the user1 import record.
        $this->fieldstoimport['idnumber']['data'] = 'imp001';
        $this->fieldstoimport['username']['data'] = 'import001';
        $this->fieldstoimport['firstname']['data'] = 'Import';
        $this->fieldstoimport['lastname']['data'] = 'User001';
        $this->fieldstoimport['email']['data'] = 'imp001b@local.host';
        $this->fieldstoimport['manageridnumber']['data'] = null;
        $this->populate_external_user_table(false); // First user, create the db table as well.

        // Configure and create the user2 import record.
        $this->fieldstoimport['idnumber']['data'] = 'imp002';
        $this->fieldstoimport['username']['data'] = 'import002';
        $this->fieldstoimport['firstname']['data'] = 'Import';
        $this->fieldstoimport['lastname']['data'] = 'User002';
        $this->fieldstoimport['email']['data'] = 'imp002b@local.host';
        $this->fieldstoimport['manageridnumber']['data'] = 'imp001';
        $this->populate_external_user_table(true);

        // Configure and create the user3 import record.
        $this->fieldstoimport['idnumber']['data'] = 'imp003';
        $this->fieldstoimport['username']['data'] = 'import003';
        $this->fieldstoimport['firstname']['data'] = 'Import';
        $this->fieldstoimport['lastname']['data'] = 'User003';
        $this->fieldstoimport['email']['data'] = 'imp003b@local.host';
        $this->fieldstoimport['manageridnumber']['data'] = 'imp004';
        $this->populate_external_user_table(true);

        // Configure and create the user4 import record.
        $this->fieldstoimport['idnumber']['data'] = 'imp004';
        $this->fieldstoimport['username']['data'] = 'import004';
        $this->fieldstoimport['firstname']['data'] = 'Import';
        $this->fieldstoimport['lastname']['data'] = 'User004';
        $this->fieldstoimport['email']['data'] = 'imp004b@local.host';
        $this->fieldstoimport['manageridnumber']['data'] = 'imp005';
        $this->populate_external_user_table(true);

        // We expect an error occurred due to a management loop.
        $this->assertFalse($this->run_sync());

        // Check the resulting records.
        $this->assertCount(7, $DB->get_records('user'));
        $this->assertNotEmpty($DB->get_record('user', array('id' => $user1->id, 'email' => 'imp001b@local.host'))); // Updated.
        $this->assertNotEmpty($DB->get_record('user', array('id' => $user2->id, 'email' => 'imp002b@local.host'))); // Updated user data, but not manager...
        $this->assertNotEmpty($DB->get_record('user', array('id' => $user3->id, 'email' => 'imp003b@local.host'))); // Updated.
        $this->assertNotEmpty($DB->get_record('user', array('id' => $user4->id, 'email' => 'imp004b@local.host'))); // Updated.

        $user1jas = \totara_job\job_assignment::get_all($user1->id);
        $this->assertCount(1, $user1jas);
        $user1ja = reset($user1jas);
        $this->assertEquals($user2->id, $user1ja->managerid); // Manager was not changed.

        $user2jas = \totara_job\job_assignment::get_all($user2->id);
        $this->assertCount(1, $user2jas);
        $user2ja = reset($user2jas);
        $this->assertEmpty($user2ja->managerid); // Manager was not remvoed.

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
}
