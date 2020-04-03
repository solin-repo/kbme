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
 * Testing util classes
 *
 * @abstract
 * @package    core
 * @category   test
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Utils for test sites creation
 *
 * @package   core
 * @category  test
 * @copyright 2012 Petr Skoda {@link http://skodak.org}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class testing_util {

    /**
     * @var testing_data_generator
     */
    protected static $generator = null;

    /**
     * @var string current version hash from php files
     */
    protected static $versionhash = null;

    /**
     * @var array original content of all database tables
     */
    protected static $tabledata = null;

    /**
     * @var array original structure of all database tables
     */
    protected static $tablestructure = null;

    /**
     * @var array keep list of sequenceid used in a table.
     */
    private static $tablesequences = array();

    /**
     * @var array original structure of all database tables
     */
    protected static $sequencenames = null;

    /**
     * @var int next sequence value for a single test cycle.
     */
    protected static $sequencenextstartingid = null;

    /**
     * Returns the testing framework name
     * @static
     * @return string
     */
    protected static final function get_framework() {
        $classname = get_called_class();
        return substr($classname, 0, strpos($classname, '_'));
    }

    /**
     * Get data generator
     * @static
     * @return testing_data_generator
     */
    public static function get_data_generator() {
        if (is_null(self::$generator)) {
            require_once(__DIR__.'/../generator/lib.php');
            self::$generator = new testing_data_generator();
        }
        return self::$generator;
    }

    /**
     * Does this site (db and dataroot) appear to be used for production?
     * We try very hard to prevent accidental damage done to production servers!!
     *
     * @static
     * @return bool
     */
    public static function is_test_site() {
        global $DB, $CFG;

        $framework = self::get_framework();

        if (!file_exists($CFG->dataroot . '/' . $framework . 'testdir.txt')) {
            // this is already tested in bootstrap script,
            // but anyway presence of this file means the dataroot is for testing
            return false;
        }

        $tables = $DB->get_tables(false);
        if ($tables) {
            if (!$DB->get_manager()->table_exists('config')) {
                return false;
            }
            if (!get_config('core', $framework . 'test')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns whether test database and dataroot were created using the current version codebase
     *
     * @return bool
     */
    public static function is_test_data_updated() {
        global $DB, $CFG;

        $framework = self::get_framework();

        $datarootpath = $CFG->dataroot . '/' . $framework;
        if (!file_exists($datarootpath . '/tabledata.ser') or !file_exists($datarootpath . '/tablestructure.ser')) {
            return false;
        }

        if (!file_exists($datarootpath . '/versionshash.txt')) {
            return false;
        }

        $hash = core_component::get_all_versions_hash();
        $oldhash = file_get_contents($datarootpath . '/versionshash.txt');

        if ($hash !== $oldhash) {
            return false;
        }

        // A direct database request must be used to avoid any possible caching of an older value.
        $dbhash = $DB->get_field('config', 'value', array('name' => $framework . 'test'));
        if ($hash !== $dbhash) {
            return false;
        }

        return true;
    }

    /**
     * Stores the status of the database
     *
     * Serializes the contents and the structure and
     * stores it in the test framework space in dataroot
     */
    protected static function store_database_state() {
        global $DB, $CFG;

        $framework = self::get_framework();

        // store data for all tables
        $data = array();
        $structure = array();
        $tables = $DB->get_tables();
        foreach ($tables as $table) {
            $columns = $DB->get_columns($table);
            $structure[$table] = $columns;
            if (isset($columns['id']) and $columns['id']->auto_increment) {
                $data[$table] = $DB->get_records($table, array(), 'id ASC');
            } else {
                // there should not be many of these
                $data[$table] = $DB->get_records($table, array());
            }
        }
        $data = serialize($data);
        $datafile = $CFG->dataroot . '/' . $framework . '/tabledata.ser';
        file_put_contents($datafile, $data);
        testing_fix_file_permissions($datafile);

        $structure = serialize($structure);
        $structurefile = $CFG->dataroot . '/' . $framework . '/tablestructure.ser';
        file_put_contents($structurefile, $structure);
        testing_fix_file_permissions($structurefile);
    }

    /**
     * Stores the version hash in both database and dataroot
     */
    protected static function store_versions_hash() {
        global $CFG;

        $framework = self::get_framework();
        $hash = core_component::get_all_versions_hash();

        // add test db flag
        set_config($framework . 'test', $hash);

        // hash all plugin versions - helps with very fast detection of db structure changes
        $hashfile = $CFG->dataroot . '/' . $framework . '/versionshash.txt';
        file_put_contents($hashfile, $hash);
        testing_fix_file_permissions($hashfile);
    }

    /**
     * Returns contents of all tables right after installation.
     * @static
     * @return array  $table=>$records
     */
    protected static function get_tabledata() {
        global $CFG;

        if (!isset(self::$tabledata)) {
            $framework = self::get_framework();

            $datafile = $CFG->dataroot . '/' . $framework . '/tabledata.ser';
            if (!file_exists($datafile)) {
                // Not initialised yet.
                return array();
            }

            $data = file_get_contents($datafile);
            self::$tabledata = unserialize($data);
        }

        if (!is_array(self::$tabledata)) {
            testing_error(1, 'Can not read dataroot/' . $framework . '/tabledata.ser or invalid format, reinitialize test database.');
        }

        return self::$tabledata;
    }

    /**
     * Returns structure of all tables right after installation.
     * @static
     * @return array $table=>$records
     */
    public static function get_tablestructure() {
        global $CFG;

        if (!isset(self::$tablestructure)) {
            $framework = self::get_framework();

            $structurefile = $CFG->dataroot . '/' . $framework . '/tablestructure.ser';
            if (!file_exists($structurefile)) {
                // Not initialised yet.
                return array();
            }

            $data = file_get_contents($structurefile);
            self::$tablestructure = unserialize($data);
        }

        if (!is_array(self::$tablestructure)) {
            testing_error(1, 'Can not read dataroot/' . $framework . '/tablestructure.ser or invalid format, reinitialize test database.');
        }

        return self::$tablestructure;
    }

    /**
     * Returns the names of sequences for each autoincrementing id field in all standard tables.
     * @static
     * @return array $table=>$sequencename
     */
    public static function get_sequencenames() {
        global $DB;

        if (isset(self::$sequencenames)) {
            return self::$sequencenames;
        }

        if (!$structure = self::get_tablestructure()) {
            return array();
        }

        self::$sequencenames = array();
        foreach ($structure as $table => $ignored) {
            $name = $DB->get_manager()->generator->getSequenceFromDB(new xmldb_table($table));
            if ($name !== false) {
                self::$sequencenames[$table] = $name;
            }
        }

        return self::$sequencenames;
    }

    /**
     * Returns list of tables that are unmodified and empty.
     *
     * @static
     * @return array of table names, empty if unknown
     */
    protected static function guess_unmodified_empty_tables() {
        global $DB;

        $dbfamily = $DB->get_dbfamily();

        if ($dbfamily === 'mysql') {
            $empties = array();
            $prefix = $DB->get_prefix();
            $rs = $DB->get_recordset_sql("SHOW TABLE STATUS LIKE ?", array($prefix.'%'));
            foreach ($rs as $info) {
                $table = strtolower($info->name);
                if (strpos($table, $prefix) !== 0) {
                    // incorrect table match caused by _
                    continue;
                }

                if (!is_null($info->auto_increment)) {
                    $table = preg_replace('/^'.preg_quote($prefix, '/').'/', '', $table);
                    if (isset(self::$tablesequences[$table]) && ($info->auto_increment == self::$tablesequences[$table])) {
                        $empties[$table] = $table;
                    }
                }
            }
            $rs->close();
            return $empties;

        } else if ($dbfamily === 'mssql') {
            $empties = array();
            $prefix = $DB->get_prefix();
            $sql = "SELECT t.name
                      FROM sys.identity_columns i
                      JOIN sys.tables t ON t.object_id = i.object_id
                     WHERE t.name LIKE ?
                       AND i.name = 'id'
                       AND i.last_value IS NULL";
            $rs = $DB->get_recordset_sql($sql, array($prefix.'%'));
            foreach ($rs as $info) {
                $table = strtolower($info->name);
                if (strpos($table, $prefix) !== 0) {
                    // incorrect table match caused by _
                    continue;
                }
                $table = preg_replace('/^'.preg_quote($prefix, '/').'/', '', $table);
                $empties[$table] = $table;
            }
            $rs->close();
            return $empties;

        } else if ($dbfamily === 'oracle') {
            $sequences = self::get_sequencenames();
            $sequences = array_map('strtoupper', $sequences);
            $lookup = array_flip($sequences);
            $empties = array();
            list($seqs, $params) = $DB->get_in_or_equal($sequences);
            $sql = "SELECT sequence_name FROM user_sequences WHERE last_number = 1 AND sequence_name $seqs";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $seq) {
                $table = $lookup[$seq->sequence_name];
                $empties[$table] = $table;
            }
            $rs->close();
            return $empties;

        } else {
            return array();
        }
    }

    /**
     * Determine the next unique starting id sequences.
     *
     * @static
     * @param array $records The records to use to determine the starting value for the table.
     * @param string $table table name.
     * @return int The value the sequence should be set to.
     */
    private static function get_next_sequence_starting_value($records, $table) {
        if (defined('BEHAT_UTIL') or defined('BEHAT_TEST')) {
            // Totara still relies on ids in behat - see TL-8981.
            if (empty($records)) {
                return 1;
            } else {
                $lastrecord = end($records);
                return $lastrecord->id + 1;
            }
        }

        if (isset(self::$tablesequences[$table])) {
            return self::$tablesequences[$table];
        }

        $id = self::$sequencenextstartingid;

        // If there are records, calculate the minimum id we can use.
        // It must be bigger than the last record's id.
        if (!empty($records)) {
            $lastrecord = end($records);
            $id = max($id, $lastrecord->id + 1);
        }

        self::$sequencenextstartingid = $id + 1000;

        self::$tablesequences[$table] = $id;

        return $id;
    }

    /**
     * Reset all database sequences to initial values.
     *
     * @static
     * @param array $empties tables that are known to be unmodified and empty
     * @return void
     */
    public static function reset_all_database_sequences(array $empties = null) {
        global $DB;

        if (!$data = self::get_tabledata()) {
            // Not initialised yet.
            return;
        }
        if (!$structure = self::get_tablestructure()) {
            // Not initialised yet.
            return;
        }

        // If all starting Id's are the same, it's difficult to detect coding and testing
        // errors that use the incorrect id in tests.  The classic case is cmid vs instance id.
        // To reduce the chance of the coding error, we start sequences at different values where possible.
        // In a attempt to avoid tables with existing id's we start at a high number.
        // Reset the value each time all database sequences are reset.
        if (defined('PHPUNIT_SEQUENCE_START') and PHPUNIT_SEQUENCE_START) {
            self::$sequencenextstartingid = PHPUNIT_SEQUENCE_START;
        } else {
            self::$sequencenextstartingid = 100000;
        }

        $dbfamily = $DB->get_dbfamily();
        if ($dbfamily === 'postgres') {
            $queries = array();
            $prefix = $DB->get_prefix();
            foreach ($data as $table => $records) {
                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    $nextid = self::get_next_sequence_starting_value($records, $table);
                    $queries[] = "ALTER SEQUENCE {$prefix}{$table}_id_seq RESTART WITH $nextid";
                }
            }
            if ($queries) {
                $DB->change_database_structure(implode(';', $queries));
            }

        } else if ($dbfamily === 'mysql') {
            $sequences = array();
            $prefix = $DB->get_prefix();
            $rs = $DB->get_recordset_sql("SHOW TABLE STATUS LIKE ?", array($prefix.'%'));
            foreach ($rs as $info) {
                $table = strtolower($info->name);
                if (strpos($table, $prefix) !== 0) {
                    // incorrect table match caused by _
                    continue;
                }
                if (!is_null($info->auto_increment)) {
                    $table = preg_replace('/^'.preg_quote($prefix, '/').'/', '', $table);
                    $sequences[$table] = $info->auto_increment;
                }
            }
            $rs->close();
            $prefix = $DB->get_prefix();
            foreach ($data as $table => $records) {
                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    if (isset($sequences[$table])) {
                        $nextid = self::get_next_sequence_starting_value($records, $table);
                        if ($sequences[$table] != $nextid) {
                            $DB->change_database_structure("ALTER TABLE {$prefix}{$table} AUTO_INCREMENT = $nextid");
                        }
                    } else {
                        // some problem exists, fallback to standard code
                        $DB->get_manager()->reset_sequence($table);
                    }
                }
            }

        } else if ($dbfamily === 'oracle') {
            $sequences = self::get_sequencenames();
            $sequences = array_map('strtoupper', $sequences);
            $lookup = array_flip($sequences);

            $current = array();
            list($seqs, $params) = $DB->get_in_or_equal($sequences);
            $sql = "SELECT sequence_name, last_number FROM user_sequences WHERE sequence_name $seqs";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $seq) {
                $table = $lookup[$seq->sequence_name];
                $current[$table] = $seq->last_number;
            }
            $rs->close();

            foreach ($data as $table => $records) {
                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    $lastrecord = end($records);
                    if ($lastrecord) {
                        $nextid = $lastrecord->id + 1;
                    } else {
                        $nextid = 1;
                    }
                    if (!isset($current[$table])) {
                        $DB->get_manager()->reset_sequence($table);
                    } else if ($nextid == $current[$table]) {
                        continue;
                    }
                    // reset as fast as possible - alternatively we could use http://stackoverflow.com/questions/51470/how-do-i-reset-a-sequence-in-oracle
                    $seqname = $sequences[$table];
                    $cachesize = $DB->get_manager()->generator->sequence_cache_size;
                    $DB->change_database_structure("DROP SEQUENCE $seqname");
                    $DB->change_database_structure("CREATE SEQUENCE $seqname START WITH $nextid INCREMENT BY 1 NOMAXVALUE CACHE $cachesize");
                }
            }

        } else {
            // note: does mssql support any kind of faster reset?
            // This also implies mssql will not use unique sequence values.
            if (is_null($empties)) {
                $empties = self::guess_unmodified_empty_tables();
            }
            foreach ($data as $table => $records) {
                if (isset($empties[$table])) {
                    continue;
                }
                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    $DB->get_manager()->reset_sequence($table);
                }
            }
        }
    }

    /**
     * Reset all database tables to default values.
     * @static
     * @return bool true if reset done, false if skipped
     */
    public static function reset_database() {
        global $DB;

        $tables = $DB->get_tables(false);
        if (!$tables or empty($tables['config'])) {
            // not installed yet
            return false;
        }

        if (!$data = self::get_tabledata()) {
            // not initialised yet
            return false;
        }
        if (!$structure = self::get_tablestructure()) {
            // not initialised yet
            return false;
        }

        $empties = self::guess_unmodified_empty_tables();

        $borkedmysql = false;
        if ($DB->get_dbfamily() === 'mysql') {
            $version = $DB->get_server_info();
            if (version_compare($version['version'], '5.6.0') == 1 and version_compare($version['version'], '5.6.16') == -1) {
                // Everything that comes from Oracle is evil!
                //
                // See http://dev.mysql.com/doc/refman/5.6/en/alter-table.html
                // You cannot reset the counter to a value less than or equal to to the value that is currently in use.
                //
                // From 5.6.16 release notes:
                //   InnoDB: The ALTER TABLE INPLACE algorithm would fail to decrease the auto-increment value.
                //           (Bug #17250787, Bug #69882)
                $borkedmysql = true;

            } else if (version_compare($version['version'], '10.0.0') == 1) {
                // And MariaDB is no better!
                // Let's hope they pick the patch sometime later...
                $borkedmysql = true;
            }
        }

        if ($borkedmysql) {
            $mysqlsequences = array();
            $prefix = $DB->get_prefix();
            $rs = $DB->get_recordset_sql("SHOW TABLE STATUS LIKE ?", array($prefix.'%'));
            foreach ($rs as $info) {
                $table = strtolower($info->name);
                if (strpos($table, $prefix) !== 0) {
                    // Incorrect table match caused by _ char.
                    continue;
                }
                if (!is_null($info->auto_increment)) {
                    $table = preg_replace('/^'.preg_quote($prefix, '/').'/', '', $table);
                    $mysqlsequences[$table] = $info->auto_increment;
                }
            }
        }

        foreach ($data as $table => $records) {
            if ($borkedmysql) {
                if (empty($records)) {
                    if (!isset($empties[$table])) {
                        // Table has been modified and is not empty.
                        $DB->delete_records($table, null);
                    }
                    continue;
                }

                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    $current = $DB->get_records($table, array(), 'id ASC');
                    if ($current == $records) {
                        if (isset($mysqlsequences[$table]) and $mysqlsequences[$table] == $structure[$table]['id']->auto_increment) {
                            continue;
                        }
                    }
                }

                // Use TRUNCATE as a workaround and reinsert everything.
                $DB->delete_records($table, null);
                foreach ($records as $record) {
                    $DB->import_record($table, $record, false, true);
                }
                continue;
            }

            if (empty($records)) {
                if (!isset($empties[$table])) {
                    // Table has been modified and is not empty.
                    $DB->delete_records($table, array());
                }
                continue;
            }

            if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                $currentrecords = $DB->get_records($table, array(), 'id ASC');
                $changed = false;
                foreach ($records as $id => $record) {
                    if (!isset($currentrecords[$id])) {
                        $changed = true;
                        break;
                    }
                    if ((array)$record != (array)$currentrecords[$id]) {
                        $changed = true;
                        break;
                    }
                    unset($currentrecords[$id]);
                }
                if (!$changed) {
                    if ($currentrecords) {
                        $lastrecord = end($records);
                        $DB->delete_records_select($table, "id > ?", array($lastrecord->id));
                        continue;
                    } else {
                        continue;
                    }
                }
            }

            $DB->delete_records($table, array());
            foreach ($records as $record) {
                $DB->import_record($table, $record, false, true);
            }
        }

        // reset all next record ids - aka sequences
        self::reset_all_database_sequences($empties);

        // remove extra tables
        foreach ($tables as $table) {
            if (!isset($data[$table])) {
                $DB->get_manager()->drop_table(new xmldb_table($table));
            }
        }

        return true;
    }

    /**
     * Purge dataroot directory
     * @static
     * @return void
     */
    public static function reset_dataroot() {
        global $CFG;

        // Totara: do not clear stat cache here, we do not want to slow down phpunit.

        $framework = self::get_framework();

        $datarootskiponreset = array('.', '..', '.htaccess', 'filedir', 'trashdir', 'temp', 'cache', 'localcache');
        $datarootskiponreset[] = $framework;
        $datarootskiponreset[] = $framework . 'testdir.txt';

        // Clean up the dataroot folder.
        $files = scandir($CFG->dataroot);
        foreach ($files as $item) {
            if (in_array($item, $datarootskiponreset)) {
                continue;
            }
            if (is_dir("$CFG->dataroot/$item")) {
                remove_dir("$CFG->dataroot/$item", false);
            } else {
                unlink("$CFG->dataroot/$item");
            }
        }

        // Totara: there is no need to purge the file dir during tests!

        // Reset the cache and temp dirs if not empty.
        if (!file_exists("$CFG->dataroot/temp")) {
            make_temp_directory('');
        } else if (count(scandir("$CFG->dataroot/temp")) > 2) {
            remove_dir("$CFG->dataroot/temp", true);
        }
        if (!file_exists("$CFG->dataroot/cache")) {
            make_cache_directory('');
        } else if (count(scandir("$CFG->dataroot/cache")) > 2) {
            remove_dir("$CFG->dataroot/cache", true);
        }
        if (!file_exists("$CFG->dataroot/localcache")) {
            make_localcache_directory('');
        } else if (count(scandir("$CFG->dataroot/cache")) > 2) {
            remove_dir("$CFG->dataroot/localcache", true);
        }
    }

    /**
     * Gets a text-based site version description.
     *
     * @return string The site info
     */
    public static function get_site_info() {
        global $CFG;

        $output = '';

        // All developers have to understand English, do not localise!
        $env = self::get_environment();

        $output .= "Totara ".$env['totararelease'];
        if ($hash = self::get_git_hash()) {
            $output .= ", $hash";
        }
        $output .= "\n";

        // Add php version.
        require_once($CFG->libdir.'/environmentlib.php');
        $output .= "PHP: ". normalize_version($env['phpversion']);

        // Add database type and version.
        $output .= ", " . $env['dbtype'] . ": " . $env['dbversion'];

        // OS details.
        $output .= ", OS: " . $env['os'] . "\n";

        return $output;
    }

    /**
     * Try to get current git hash of the Moodle in $CFG->dirroot.
     * @return string null if unknown, sha1 hash if known
     */
    public static function get_git_hash() {
        global $CFG;

        // This is a bit naive, but it should mostly work for all platforms.

        if (!file_exists("$CFG->dirroot/.git/HEAD")) {
            return null;
        }

        $headcontent = file_get_contents("$CFG->dirroot/.git/HEAD");
        if ($headcontent === false) {
            return null;
        }

        $headcontent = trim($headcontent);

        // If it is pointing to a hash we return it directly.
        if (strlen($headcontent) === 40) {
            return $headcontent;
        }

        if (strpos($headcontent, 'ref: ') !== 0) {
            return null;
        }

        $ref = substr($headcontent, 5);

        if (!file_exists("$CFG->dirroot/.git/$ref")) {
            return null;
        }

        $hash = file_get_contents("$CFG->dirroot/.git/$ref");

        if ($hash === false) {
            return null;
        }

        $hash = trim($hash);

        if (strlen($hash) != 40) {
            return null;
        }

        return $hash;
    }

    /**
     * Drop the whole test database
     * @static
     * @param bool $displayprogress
     */
    protected static function drop_database($displayprogress = false) {
        global $DB;

        $tables = $DB->get_tables(false);
        if (isset($tables['config'])) {
            // config always last to prevent problems with interrupted drops!
            unset($tables['config']);
            $tables['config'] = 'config';
        }

        if ($displayprogress) {
            echo "Dropping tables:\n";
        }
        $dotsonline = 0;
        foreach ($tables as $tablename) {
            $table = new xmldb_table($tablename);
            $DB->get_manager()->drop_table($table);

            if ($dotsonline == 60) {
                if ($displayprogress) {
                    echo "\n";
                }
                $dotsonline = 0;
            }
            if ($displayprogress) {
                echo '.';
            }
            $dotsonline += 1;
        }
        if ($displayprogress) {
            echo "\n";
        }
    }

    /**
     * Drops the test framework dataroot
     * @static
     */
    protected static function drop_dataroot() {
        global $CFG;

        remove_dir($CFG->dataroot, false);
    }

    /**
     * Return list of environment versions on which tests will run.
     * Environment includes:
     * - moodleversion
     * - phpversion
     * - dbtype
     * - dbversion
     * - os
     *
     * @return array
     */
    public static function get_environment() {
        global $CFG, $DB;

        $env = array();

        // Add moodle version.
        $release = null;
        $TOTARA = null;
        require("$CFG->dirroot/version.php");
        $env['moodleversion'] = $release;
        $env['totararelease'] = $TOTARA->release;

        // Add php version.
        $phpversion = phpversion();
        $env['phpversion'] = $phpversion;

        // Add database type and version.
        $dbtype = $DB->get_dbvendor();
        $dbinfo = $DB->get_server_info();
        $dbversion = $dbinfo['version'];
        $env['dbtype'] = $dbtype; // Totara: No case magic here!!!
        $env['dbversion'] = $dbversion;

        // OS details.
        $osdetails = php_uname('s') . " " . php_uname('r') . " " . php_uname('m');
        $env['os'] = $osdetails;

        return $env;
    }
}
