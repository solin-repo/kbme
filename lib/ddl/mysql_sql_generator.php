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
 * Mysql specific SQL code generator.
 *
 * @package    core_ddl
 * @copyright  1999 onwards Martin Dougiamas     http://dougiamas.com
 *             2001-3001 Eloy Lafuente (stronk7) http://contiento.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/ddl/sql_generator.php');

/**
 * This class generate SQL code to be used against MySQL
 * It extends XMLDBgenerator so everything can be
 * overridden as needed to generate correct SQL.
 *
 * @package    core_ddl
 * @copyright  1999 onwards Martin Dougiamas     http://dougiamas.com
 *             2001-3001 Eloy Lafuente (stronk7) http://contiento.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mysql_sql_generator extends sql_generator {

    // Only set values that are different from the defaults present in XMLDBgenerator

    /** @var string Used to quote names. */
    public $quote_string = '`';

    /** @var string To define the default to set for NOT NULLs CHARs without default (null=do nothing).*/
    public $default_for_char = '';

    /** @var bool To specify if the generator must use some DEFAULT clause to drop defaults.*/
    public $drop_default_value_required = true;

    /** @var string The DEFAULT clause required to drop defaults.*/
    public $drop_default_value = null;

    /** @var string To force primary key names to one string (null=no force).*/
    public $primary_key_name = '';

    /** @var string Template to drop PKs. 'TABLENAME' and 'KEYNAME' will be replaced from this template.*/
    public $drop_primary_key = 'ALTER TABLE TABLENAME DROP PRIMARY KEY';

    /** @var string Template to drop UKs. 'TABLENAME' and 'KEYNAME' will be replaced from this template.*/
    public $drop_unique_key = 'ALTER TABLE TABLENAME DROP KEY KEYNAME';

    /** @var string Template to drop FKs. 'TABLENAME' and 'KEYNAME' will be replaced from this template.*/
    public $drop_foreign_key = 'ALTER TABLE TABLENAME DROP FOREIGN KEY KEYNAME';

    /** @var bool True if the generator needs to add extra code to generate the sequence fields.*/
    public $sequence_extra_code = false;

    /** @var string The particular name for inline sequences in this generator.*/
    public $sequence_name = 'auto_increment';

    public $add_after_clause = true; // Does the generator need to add the after clause for fields

    /** @var string Characters to be used as concatenation operator.*/
    public $concat_character = null;

    /** @var string The SQL template to alter columns where the 'TABLENAME' and 'COLUMNSPECS' keywords are dynamically replaced.*/
    public $alter_column_sql = 'ALTER TABLE TABLENAME MODIFY COLUMN COLUMNSPECS';

    /** @var string SQL sentence to drop one index where 'TABLENAME', 'INDEXNAME' keywords are dynamically replaced.*/
    public $drop_index_sql = 'ALTER TABLE TABLENAME DROP INDEX INDEXNAME';

    /** @var string SQL sentence to rename one index where 'TABLENAME', 'OLDINDEXNAME' and 'NEWINDEXNAME' are dynamically replaced.*/
    public $rename_index_sql = null;

    /** @var string SQL sentence to rename one key 'TABLENAME', 'OLDKEYNAME' and 'NEWKEYNAME' are dynamically replaced.*/
    public $rename_key_sql = null;

    /** Maximum size of InnoDB row in Antelope file format */
    const ANTELOPE_MAX_ROW_SIZE = 8126;

    /**
     * Reset a sequence to the id field of a table.
     *
     * @param xmldb_table|string $table name of table or the table object.
     * @return array of sql statements
     */
    public function getResetSequenceSQL($table) {

        if ($table instanceof xmldb_table) {
            $tablename = $table->getName();
        } else {
            $tablename = $table;
        }

        // From http://dev.mysql.com/doc/refman/5.0/en/alter-table.html
        $value = (int)$this->mdb->get_field_sql('SELECT MAX(id) FROM {'.$tablename.'}');
        $value++;
        return array("ALTER TABLE $this->prefix$tablename AUTO_INCREMENT = $value");
    }

    /**
     * Calculate proximate row size when using InnoDB
     * tables in Antelope row format.
     *
     * Note: the returned value is a bit higher to compensate for
     *       errors and changes of column data types.
     *
     * @deprecated since Moodle 2.9 MDL-49723 - please do not use this function any more.
     * @param xmldb_field[]|database_column_info[] $columns
     * @return int approximate row size in bytes
     */
    public function guess_antolope_row_size(array $columns) {
        debugging('guess_antolope_row_size() is deprecated, please use guess_antelope_row_size() instead.', DEBUG_DEVELOPER);
        return $this->guess_antelope_row_size($columns);
    }

    /**
     * Calculate proximate row size when using InnoDB tables in Antelope row format.
     *
     * Note: the returned value is a bit higher to compensate for errors and changes of column data types.
     *
     * @param xmldb_field[]|database_column_info[] $columns
     * @return int approximate row size in bytes
     */
    public function guess_antelope_row_size(array $columns) {

        if (empty($columns)) {
            return 0;
        }

        $size = 0;
        $first = reset($columns);

        if (count($columns) > 1) {
            // Do not start with zero because we need to cover changes of field types and
            // this calculation is most probably not be accurate.
            $size += 1000;
        }

        if ($first instanceof xmldb_field) {
            foreach ($columns as $field) {
                switch ($field->getType()) {
                    case XMLDB_TYPE_TEXT:
                        $size += 768;
                        break;
                    case XMLDB_TYPE_BINARY:
                        $size += 768;
                        break;
                    case XMLDB_TYPE_CHAR:
                        $bytes = $field->getLength() * 3;
                        if ($bytes > 768) {
                            $bytes = 768;
                        }
                        $size += $bytes;
                        break;
                    default:
                        // Anything else is usually maximum 8 bytes.
                        $size += 8;
                }
            }

        } else if ($first instanceof database_column_info) {
            foreach ($columns as $column) {
                switch ($column->meta_type) {
                    case 'X':
                        $size += 768;
                        break;
                    case 'B':
                        $size += 768;
                        break;
                    case 'C':
                        $bytes = $column->max_length * 3;
                        if ($bytes > 768) {
                            $bytes = 768;
                        }
                        $size += $bytes;
                        break;
                    default:
                        // Anything else is usually maximum 8 bytes.
                        $size += 8;
                }
            }
        }

        return $size;
    }

    /**
     * Given one correct xmldb_table, returns the SQL statements
     * to create it (inside one array).
     *
     * @param xmldb_table $xmldb_table An xmldb_table instance.
     * @return array An array of SQL statements, starting with the table creation SQL followed
     * by any of its comments, indexes and sequence creation SQL statements.
     */
    public function getCreateTableSQL($xmldb_table) {
        // First find out if want some special db engine.
        $engine = $this->mdb->get_dbengine();
        // Do we know collation?
        $collation = $this->mdb->get_dbcollation();

        // Do we need to use compressed format for rows?
        $rowformat = "";
        $size = $this->guess_antelope_row_size($xmldb_table->getFields());
        if ($size > self::ANTELOPE_MAX_ROW_SIZE) {
            if ($this->mdb->is_compressed_row_format_supported()) {
                $rowformat = "\n ROW_FORMAT=Compressed";
            }
        }

        $sqlarr = parent::getCreateTableSQL($xmldb_table);

        // This is a very nasty hack that tries to use just one query per created table
        // because MySQL is stupidly slow when modifying empty tables.
        // Note: it is safer to inject everything on new lines because there might be some trailing -- comments.
        $sqls = array();
        $prevcreate = null;
        $matches = null;
        foreach ($sqlarr as $sql) {
            if (preg_match('/^CREATE TABLE ([^ ]+)/', $sql, $matches)) {
                $prevcreate = $matches[1];
                $sql = preg_replace('/\s*\)\s*$/s', '/*keyblock*/)', $sql);
                // Let's inject the extra MySQL tweaks here.
                if ($engine) {
                    $sql .= "\n ENGINE = $engine";
                }
                if ($collation) {
                    if (strpos($collation, 'utf8_') === 0) {
                        $sql .= "\n DEFAULT CHARACTER SET utf8";
                    }
                    $sql .= "\n DEFAULT COLLATE = $collation";
                }
                if ($rowformat) {
                    $sql .= $rowformat;
                }
                $sqls[] = $sql;
                continue;
            }
            if ($prevcreate) {
                if (preg_match('/^ALTER TABLE '.$prevcreate.' COMMENT=(.*)$/s', $sql, $matches)) {
                    $prev = array_pop($sqls);
                    $prev .= "\n COMMENT=$matches[1]";
                    $sqls[] = $prev;
                    continue;
                }
                if (preg_match('/^CREATE INDEX ([^ ]+) ON '.$prevcreate.' (.*)$/s', $sql, $matches)) {
                    $prev = array_pop($sqls);
                    if (strpos($prev, '/*keyblock*/')) {
                        $prev = str_replace('/*keyblock*/', "\n, KEY $matches[1] $matches[2]/*keyblock*/", $prev);
                        $sqls[] = $prev;
                        continue;
                    } else {
                        $sqls[] = $prev;
                    }
                }
                if (preg_match('/^CREATE UNIQUE INDEX ([^ ]+) ON '.$prevcreate.' (.*)$/s', $sql, $matches)) {
                    $prev = array_pop($sqls);
                    if (strpos($prev, '/*keyblock*/')) {
                        $prev = str_replace('/*keyblock*/', "\n, UNIQUE KEY $matches[1] $matches[2]/*keyblock*/", $prev);
                        $sqls[] = $prev;
                        continue;
                    } else {
                        $sqls[] = $prev;
                    }
                }
            }
            $prevcreate = null;
            $sqls[] = $sql;
        }

        foreach ($sqls as $key => $sql) {
            $sqls[$key] = str_replace('/*keyblock*/', "\n", $sql);
        }

        return $sqls;
    }

    /**
     * Given one xmldb_table and one xmldb_field, return the SQL statements needed to add the field to the table.
     *
     * @param xmldb_table $xmldb_table The table related to $xmldb_field.
     * @param xmldb_field $xmldb_field The instance of xmldb_field to create the SQL from.
     * @param string $skip_type_clause The type clause on alter columns, NULL by default.
     * @param string $skip_default_clause The default clause on alter columns, NULL by default.
     * @param string $skip_notnull_clause The null/notnull clause on alter columns, NULL by default.
     * @return array The SQL statement for adding a field to the table.
     */
    public function getAddFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {
        $sqls = parent::getAddFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause, $skip_default_clause, $skip_notnull_clause);

        if ($this->table_exists($xmldb_table)) {
            $tablename = $xmldb_table->getName();

            $size = $this->guess_antelope_row_size($this->mdb->get_columns($tablename));
            $size += $this->guess_antelope_row_size(array($xmldb_field));

            if ($size > self::ANTELOPE_MAX_ROW_SIZE) {
                if ($this->mdb->is_compressed_row_format_supported()) {
                    $format = strtolower($this->mdb->get_row_format($tablename));
                    if ($format === 'compact' or $format === 'redundant') {
                        // Change the format before conversion so that we do not run out of space.
                        array_unshift($sqls, "ALTER TABLE {$this->prefix}$tablename ROW_FORMAT=Compressed");
                    }
                }
            }
        }

        return $sqls;
    }

    /**
     * Given one correct xmldb_table, returns the SQL statements
     * to create temporary table (inside one array).
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @return array of sql statements
     */
    public function getCreateTempTableSQL($xmldb_table) {
        // Do we know collation?
        $collation = $this->mdb->get_dbcollation();
        $this->temptables->add_temptable($xmldb_table->getName());

        $sqlarr = parent::getCreateTableSQL($xmldb_table);

        // Let's inject the extra MySQL tweaks.
        foreach ($sqlarr as $i=>$sql) {
            if (strpos($sql, 'CREATE TABLE ') === 0) {
                // We do not want the engine hack included in create table SQL.
                $sqlarr[$i] = preg_replace('/^CREATE TABLE (.*)/s', 'CREATE TEMPORARY TABLE $1', $sql);
                if ($collation) {
                    if (strpos($collation, 'utf8_') === 0) {
                        $sqlarr[$i] .= " DEFAULT CHARACTER SET utf8";
                    }
                    $sqlarr[$i] .= " DEFAULT COLLATE $collation";
                }
            }
        }

        return $sqlarr;
    }

    /**
     * Given one correct xmldb_table, returns the SQL statements
     * to drop it (inside one array).
     *
     * @param xmldb_table $xmldb_table The table to drop.
     * @return array SQL statement(s) for dropping the specified table.
     */
    public function getDropTableSQL($xmldb_table) {
        $sqlarr = parent::getDropTableSQL($xmldb_table);
        if ($this->temptables->is_temptable($xmldb_table->getName())) {
            $sqlarr = preg_replace('/^DROP TABLE/', "DROP TEMPORARY TABLE", $sqlarr);
            $this->temptables->delete_temptable($xmldb_table->getName());
        }
        return $sqlarr;
    }

    /**
     * Given one XMLDB Type, length and decimals, returns the DB proper SQL type.
     *
     * @param int $xmldb_type The xmldb_type defined constant. XMLDB_TYPE_INTEGER and other XMLDB_TYPE_* constants.
     * @param int $xmldb_length The length of that data type.
     * @param int $xmldb_decimals The decimal places of precision of the data type.
     * @return string The DB defined data type.
     */
    public function getTypeSQL($xmldb_type, $xmldb_length=null, $xmldb_decimals=null) {

        switch ($xmldb_type) {
            case XMLDB_TYPE_INTEGER:    // From http://mysql.com/doc/refman/5.0/en/numeric-types.html!
                if (empty($xmldb_length)) {
                    $xmldb_length = 10;
                }
                if ($xmldb_length > 9) {
                    $dbtype = 'BIGINT';
                } else if ($xmldb_length > 6) {
                    $dbtype = 'INT';
                } else if ($xmldb_length > 4) {
                    $dbtype = 'MEDIUMINT';
                } else if ($xmldb_length > 2) {
                    $dbtype = 'SMALLINT';
                } else {
                    $dbtype = 'TINYINT';
                }
                $dbtype .= '(' . $xmldb_length . ')';
                break;
            case XMLDB_TYPE_NUMBER:
                $dbtype = $this->number_type;
                if (!empty($xmldb_length)) {
                    $dbtype .= '(' . $xmldb_length;
                    if (!empty($xmldb_decimals)) {
                        $dbtype .= ',' . $xmldb_decimals;
                    }
                    $dbtype .= ')';
                }
                break;
            case XMLDB_TYPE_FLOAT:
                $dbtype = 'DOUBLE';
                if (!empty($xmldb_decimals)) {
                    if ($xmldb_decimals < 6) {
                        $dbtype = 'FLOAT';
                    }
                }
                if (!empty($xmldb_length)) {
                    $dbtype .= '(' . $xmldb_length;
                    if (!empty($xmldb_decimals)) {
                        $dbtype .= ',' . $xmldb_decimals;
                    } else {
                        $dbtype .= ', 0'; // In MySQL, if length is specified, decimals are mandatory for FLOATs
                    }
                    $dbtype .= ')';
                }
                break;
            case XMLDB_TYPE_CHAR:
                $dbtype = 'VARCHAR';
                if (empty($xmldb_length)) {
                    $xmldb_length='255';
                }
                $dbtype .= '(' . $xmldb_length . ')';
                if ($collation = $this->mdb->get_dbcollation()) {
                    if (strpos($collation, 'utf8_') === 0) {
                        $dbtype .= " CHARACTER SET utf8";
                    }
                    $dbtype .= " COLLATE $collation";
                }
                break;
            case XMLDB_TYPE_TEXT:
                $dbtype = 'LONGTEXT';
                if ($collation = $this->mdb->get_dbcollation()) {
                    if (strpos($collation, 'utf8_') === 0) {
                        $dbtype .= " CHARACTER SET utf8";
                    }
                    $dbtype .= " COLLATE $collation";
                }
                break;
            case XMLDB_TYPE_BINARY:
                $dbtype = 'LONGBLOB';
                break;
            case XMLDB_TYPE_DATETIME:
                $dbtype = 'DATETIME';
        }
        return $dbtype;
    }

    /**
     * Given one xmldb_table and one xmldb_field, return the SQL statements needed to add its default
     * (usually invoked from getModifyDefaultSQL()
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @param xmldb_field $xmldb_field The xmldb_field object instance.
     * @return array Array of SQL statements to create a field's default.
     */
    public function getCreateDefaultSQL($xmldb_table, $xmldb_field) {
        // Just a wrapper over the getAlterFieldSQL() function for MySQL that
        // is capable of handling defaults
        return $this->getAlterFieldSQL($xmldb_table, $xmldb_field);
    }

    /**
     * Given one correct xmldb_field and the new name, returns the SQL statements
     * to rename it (inside one array).
     *
     * @param xmldb_table $xmldb_table The table related to $xmldb_field.
     * @param xmldb_field $xmldb_field The instance of xmldb_field to get the renamed field from.
     * @param string $newname The new name to rename the field to.
     * @return array The SQL statements for renaming the field.
     */
    public function getRenameFieldSQL($xmldb_table, $xmldb_field, $newname) {
        // NOTE: MySQL is pretty different from the standard to justify this overloading.

        // Need a clone of xmldb_field to perform the change leaving original unmodified
        $xmldb_field_clone = clone($xmldb_field);

        // Change the name of the field to perform the change
        $xmldb_field_clone->setName($newname);

        $fieldsql = $this->getFieldSQL($xmldb_table, $xmldb_field_clone);

        $sql = 'ALTER TABLE ' . $this->getTableName($xmldb_table) . ' CHANGE ' .
               $xmldb_field->getName() . ' ' . $fieldsql;

        return array($sql);
    }

    /**
     * Given one xmldb_table and one xmldb_field, return the SQL statements needed to drop its default
     * (usually invoked from getModifyDefaultSQL()
     *
     * Note that this method may be dropped in future.
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @param xmldb_field $xmldb_field The xmldb_field object instance.
     * @return array Array of SQL statements to create a field's default.
     *
     * @todo MDL-31147 Moodle 2.1 - Drop getDropDefaultSQL()
     */
    public function getDropDefaultSQL($xmldb_table, $xmldb_field) {
        // Just a wrapper over the getAlterFieldSQL() function for MySQL that
        // is capable of handling defaults
        return $this->getAlterFieldSQL($xmldb_table, $xmldb_field);
    }

    /**
     * Returns the code (array of statements) needed to add one comment to the table.
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @return array Array of SQL statements to add one comment to the table.
     */
    function getCommentSQL ($xmldb_table) {
        $comment = '';

        if ($xmldb_table->getComment()) {
            $comment .= 'ALTER TABLE ' . $this->getTableName($xmldb_table);
            $comment .= " COMMENT='" . $this->addslashes(substr($xmldb_table->getComment(), 0, 60)) . "'";
        }
        return array($comment);
    }

    /**
     * Given one object name and it's type (pk, uk, fk, ck, ix, uix, seq, trg).
     *
     * (MySQL requires the whole xmldb_table object to be specified, so we add it always)
     *
     * This is invoked from getNameForObject().
     * Only some DB have this implemented.
     *
     * @param string $object_name The object's name to check for.
     * @param string $type The object's type (pk, uk, fk, ck, ix, uix, seq, trg).
     * @param string $table_name The table's name to check in
     * @return bool If such name is currently in use (true) or no (false)
     */
    public function isNameInUse($object_name, $type, $table_name) {

        switch($type) {
            case 'ix':
            case 'uix':
                // First of all, check table exists
                $metatables = $this->mdb->get_tables();
                if (isset($metatables[$table_name])) {
                    // Fetch all the indexes in the table
                    if ($indexes = $this->mdb->get_indexes($table_name)) {
                        // Look for existing index in array
                        if (isset($indexes[$object_name])) {
                            return true;
                        }
                    }
                }
                break;
        }
        return false; //No name in use found
    }

    /**
     * Returns an array of reserved words (lowercase) for this DB.
     *
     * @return array An array of database specific reserved words
     */
    public static function getReservedWords() {
        $reserved_words = array_merge(self::get_mysql_reserved_words(), self::get_mariadb_reserved_words());
        $reserved_words = array_map('strtolower', $reserved_words);
        return $reserved_words;
    }

    /**
     * Returns an array of MySQL reserved words.
     *
     * This is separated here as the MySQL generator also deals with MariaDB which has its own list.
     *
     * This is up-to-date as of MySQL 8.0.2, 5.7.18
     *
     *   - https://dev.mysql.com/doc/refman/8.0/en/keywords.html
     *   - https://dev.mysql.com/doc/refman/5.7/en/keywords.html
     *   - https://dev.mysql.com/doc/refman/5.6/en/keywords.html
     *
     * @return array
     */
    private static function get_mysql_reserved_words() {
        return [
            'ACCESSIBLE',
            'ADD',
            'ADMIN', // Added in 8.0
            'ALL',
            'ALTER',
            'ANALYZE',
            'AND',
            'ARRAY', // Added in 8.0.17
            'AS',
            'ASC',
            'ASENSITIVE',
            'BEFORE',
            'BETWEEN',
            'BIGINT',
            'BINARY',
            'BLOB',
            'BOTH',
            'BY',
            'CALL',
            'CASCADE',
            'CASE',
            'CHANGE',
            'CHAR',
            'CHARACTER',
            'CHECK',
            'COLLATE',
            'COLUMN',
            'CONDITION',
            'CONSTRAINT',
            'CONTINUE',
            'CONVERT',
            'CREATE',
            'CROSS',
            'CUBE', // Added in 8.0.1
            'CUME_DIST', // Added in 8.0.2
            'CURRENT_DATE',
            'CURRENT_TIME',
            'CURRENT_TIMESTAMP',
            'CURRENT_USER',
            'CURSOR',
            'DATABASE',
            'DATABASES',
            'DAY_HOUR',
            'DAY_MICROSECOND',
            'DAY_MINUTE',
            'DAY_SECOND',
            'DEC',
            'DECIMAL',
            'DECLARE',
            'DEFAULT',
            'DELAYED',
            'DELETE',
            'DENSE_RANK', // Added in 8.0.2
            'DESC',
            'DESCRIBE',
            'DETERMINISTIC',
            'DISTINCT',
            'DISTINCTROW',
            'DIV',
            'DOUBLE',
            'DROP',
            'DUAL',
            'EACH',
            'ELSE',
            'ELSEIF',
            'EMPTY', // Added in 8.0.4
            'ENCLOSED',
            'ESCAPED',
            'EXCEPT', // Added in 8.0
            'EXISTS',
            'EXIT',
            'EXPLAIN',
            'FALSE',
            'FETCH',
            'FIRST_VALUE', // Added in 8.0
            'FLOAT',
            'FLOAT4',
            'FLOAT8',
            'FOR',
            'FORCE',
            'FOREIGN',
            'FROM',
            'FULLTEXT',
            'FUNCTION', // Became reserved in 8.0.1
            'GENERATED', // Added in 5.7.6
            'GET', // Added in 5.6.4
            'GRANT',
            'GROUP',
            'GROUPING', // Added in 8.0.1
            'GROUPS', // Added in 8.0.2
            'HAVING',
            'HIGH_PRIORITY',
            'HOUR_MICROSECOND',
            'HOUR_MINUTE',
            'HOUR_SECOND',
            'IF',
            'IGNORE',
            'IN',
            'INDEX',
            'INFILE',
            'INNER',
            'INOUT',
            'INSENSITIVE',
            'INSERT',
            'INT',
            'INT1',
            'INT2',
            'INT3',
            'INT4',
            'INT8',
            'INTEGER',
            'INTERVAL',
            'INTO',
            'IO_AFTER_GTIDS', // Added in 5.6.5
            'IO_BEFORE_GTIDS', // Added in 5.6.5
            'IS',
            'ITERATE',
            'JOIN',
            'JSON_TABLE', // Added in 8.0.4
            'KEY',
            'KEYS',
            'KILL',
            'LAG', // Added in 8.0.2.
            'LAST_VALUE', // Added in 8.0.2.
            'LATERAL', // Added in 8.0.4
            'LEAD', // Added in 8.0.2.
            'LEADING',
            'LEAVE',
            'LEFT',
            'LIKE',
            'LIMIT',
            'LINEAR',
            'LINES',
            'LOAD',
            'LOCALTIME',
            'LOCALTIMESTAMP',
            'LOCK',
            'LONG',
            'LONGBLOB',
            'LONGTEXT',
            'LOOP',
            'LOW_PRIORITY',
            'MASTER_BIND', // Added in 5.6.1
            'MASTER_SSL_VERIFY_SERVER_CERT',
            'MATCH',
            'MAXVALUE',
            'MEDIUMBLOB',
            'MEDIUMINT',
            'MEDIUMTEXT',
            'MEMBER', // Added in 8.0.17
            'MIDDLEINT',
            'MINUTE_MICROSECOND',
            'MINUTE_SECOND',
            'MOD',
            'MODIFIES',
            'NATURAL',
            'NOT',
            'NO_WRITE_TO_BINLOG',
            'NTH_VALUE', // Added in 8.0.2
            'NTILE', // Added in 8.0.2
            'NULL',
            'NUMERIC',
            'OF', // Added in 8.0.1
            'ON',
            'OPTIMIZE',
            'OPTIMIZER_COSTS', // Added in 5.7.5
            'OPTION',
            'OPTIONALLY',
            'OR',
            'ORDER',
            'OUT',
            'OUTER',
            'OUTFILE',
            'OVER', // Added in 8.0.2
            'PARSE_GCOL_EXPR', // Added in 5.7.6, no longer reserved as of 5.7.8
            'PARTITION',
            'PERCENT_RANK', // Added in 8.0.2
            'PERSIST',
            'PERSIST_ONLY', // Added in 8.0.2
            'PRECISION',
            'PRIMARY',
            'PROCEDURE',
            'PURGE',
            'RANGE',
            'RANK', // Added in 8.0.2
            'READ',
            'READS',
            'READ_WRITE',
            'REAL',
            'RECURSIVE', // Added in 8.0.1
            'REFERENCES',
            'REGEXP',
            'RELEASE',
            'RENAME',
            'REPEAT',
            'REPLACE',
            'REQUIRE',
            'RESIGNAL',
            'RESTRICT',
            'RETURN',
            'REVOKE',
            'RIGHT',
            'RLIKE',
            'ROW', // Added in 8.0.2
            'ROWS', // Added in 8.0.2
            'ROW_NUMBER', // Added in 8.0.2
            'SCHEMA',
            'SCHEMAS',
            'SECOND_MICROSECOND',
            'SELECT',
            'SENSITIVE',
            'SEPARATOR',
            'SET',
            'SHOW',
            'SIGNAL',
            'SMALLINT',
            'SPATIAL',
            'SPECIFIC',
            'SQL',
            'SQLEXCEPTION',
            'SQLSTATE',
            'SQLWARNING',
            'SQL_BIG_RESULT',
            'SQL_CALC_FOUND_ROWS',
            'SQL_SMALL_RESULT',
            'SSL',
            'STARTING',
            'STORED', // Added in 5.7.6
            'STRAIGHT_JOIN',
            'TABLE',
            'TERMINATED',
            'THEN',
            'TINYBLOB',
            'TINYINT',
            'TINYTEXT',
            'TO',
            'TRAILING',
            'TRIGGER',
            'TRUE',
            'UNDO',
            'UNION',
            'UNIQUE',
            'UNLOCK',
            'UNSIGNED',
            'UPDATE',
            'USAGE',
            'USE',
            'USING',
            'UTC_DATE',
            'UTC_TIME',
            'UTC_TIMESTAMP',
            'VALUES',
            'VARBINARY',
            'VARCHAR',
            'VARCHARACTER',
            'VARYING',
            'VIRTUAL', // Added in 5.7.6
            'WHEN',
            'WHERE',
            'WHILE',
            'WINDOW', // Added in 8.0.2
            'WITH',
            'WRITE',
            'XOR',
            'YEAR_MONTH',
            'ZEROFILL',
        ];
    }

    /**
     * Returns an array of MariaDB reserved words.
     *
     * This is separated here as the MySQL generator deals with both MySQL and MariaDB.
     *
     * This is up-to-date as of MariaDB 10.2.6
     *
     *   https://mariadb.com/kb/en/mariadb/reserved-words/
     *
     * @return array
     */
    private static function get_mariadb_reserved_words() {
        return [
            'ACCESSIBLE',
            'ADD',
            'ALL',
            'ALTER',
            'ANALYZE',
            'AND',
            'AS',
            'ASC',
            'ASENSITIVE',
            'BEFORE',
            'BETWEEN',
            'BIGINT',
            'BINARY',
            'BLOB',
            'BOTH',
            'BY',
            'CALL',
            'CASCADE',
            'CASE',
            'CHANGE',
            'CHAR',
            'CHARACTER',
            'CHECK',
            'COLLATE',
            'COLUMN',
            'CONDITION',
            'CONSTRAINT',
            'CONTINUE',
            'CONVERT',
            'CREATE',
            'CROSS',
            'CURRENT_DATE',
            'CURRENT_TIME',
            'CURRENT_TIMESTAMP',
            'CURRENT_USER',
            'CURSOR',
            'DATABASE',
            'DATABASES',
            'DAY_HOUR',
            'DAY_MICROSECOND',
            'DAY_MINUTE',
            'DAY_SECOND',
            'DEC',
            'DECIMAL',
            'DECLARE',
            'DEFAULT',
            'DELAYED',
            'DELETE',
            'DESC',
            'DESCRIBE',
            'DETERMINISTIC',
            'DISTINCT',
            'DISTINCTROW',
            'DIV',
            'DOUBLE',
            'DROP',
            'DUAL',
            'EACH',
            'ELSE',
            'ELSEIF',
            'ENCLOSED',
            'ESCAPED',
            'EXISTS',
            'EXIT',
            'EXPLAIN',
            'FALSE',
            'FETCH',
            'FLOAT',
            'FLOAT4',
            'FLOAT8',
            'FOR',
            'FORCE',
            'FOREIGN',
            'FROM',
            'FULLTEXT',
            'GENERAL', // Added in MariaDB 5.5
            'GRANT',
            'GROUP',
            'HAVING',
            'HIGH_PRIORITY',
            'HOUR_MICROSECOND',
            'HOUR_MINUTE',
            'HOUR_SECOND',
            'IF',
            'IGNORE',
            'IGNORE_SERVER_IDS', // Added in MariaDB 5.5
            'IN',
            'INDEX',
            'INFILE',
            'INNER',
            'INOUT',
            'INSENSITIVE',
            'INSERT',
            'INT',
            'INT1',
            'INT2',
            'INT3',
            'INT4',
            'INT8',
            'INTEGER',
            'INTERVAL',
            'INTO',
            'IS',
            'ITERATE',
            'JOIN',
            'KEY',
            'KEYS',
            'KILL',
            'LEADING',
            'LEAVE',
            'LEFT',
            'LIKE',
            'LIMIT',
            'LINEAR',
            'LINES',
            'LOAD',
            'LOCALTIME',
            'LOCALTIMESTAMP',
            'LOCK',
            'LONG',
            'LONGBLOB',
            'LONGTEXT',
            'LOOP',
            'LOW_PRIORITY',
            'MASTER_HEARTBEAT_PERIOD', // Added in MariaDB 5.5
            'MASTER_SSL_VERIFY_SERVER_CERT',
            'MATCH',
            'MAXVALUE', // Added in MariaDB 5.5
            'MEDIUMBLOB',
            'MEDIUMINT',
            'MEDIUMTEXT',
            'MIDDLEINT',
            'MINUTE_MICROSECOND',
            'MINUTE_SECOND',
            'MOD',
            'MODIFIES',
            'NATURAL',
            'NOT',
            'NO_WRITE_TO_BINLOG',
            'NULL',
            'NUMERIC',
            'ON',
            'OPTIMIZE',
            'OPTION',
            'OPTIONALLY',
            'OR',
            'ORDER',
            'OUT',
            'OUTER',
            'OUTFILE',
            'PARTITION', // Added in MariaDB 10.0
            'PRECISION',
            'PRIMARY',
            'PROCEDURE',
            'PURGE',
            'RANGE',
            'READ',
            'READS',
            'READ_WRITE',
            'REAL',
            'RECURSIVE', // Added in MariaDB 10.2.0
            'REFERENCES',
            'REGEXP',
            'RELEASE',
            'RENAME',
            'REPEAT',
            'REPLACE',
            'REQUIRE',
            'RESIGNAL', // Added in MariaDB 5.5
            'RESTRICT',
            'RETURN',
            'REVOKE',
            'RIGHT',
            'RLIKE',
            'ROWS', // Added in MariaDB 10.2.4
            'SCHEMA',
            'SCHEMAS',
            'SECOND_MICROSECOND',
            'SELECT',
            'SENSITIVE',
            'SEPARATOR',
            'SET',
            'SHOW',
            'SIGNAL', // Added in MariaDB 5.5
            'SLOW', // Added in MariaDB 5.5
            'SMALLINT',
            'SPATIAL',
            'SPECIFIC',
            'SQL',
            'SQLEXCEPTION',
            'SQLSTATE',
            'SQLWARNING',
            'SQL_BIG_RESULT',
            'SQL_CALC_FOUND_ROWS',
            'SQL_SMALL_RESULT',
            'SSL',
            'STARTING',
            'STRAIGHT_JOIN',
            'TABLE',
            'TERMINATED',
            'THEN',
            'TINYBLOB',
            'TINYINT',
            'TINYTEXT',
            'TO',
            'TRAILING',
            'TRIGGER',
            'TRUE',
            'UNDO',
            'UNION',
            'UNIQUE',
            'UNLOCK',
            'UNSIGNED',
            'UPDATE',
            'USAGE',
            'USE',
            'USING',
            'UTC_DATE',
            'UTC_TIME',
            'UTC_TIMESTAMP',
            'VALUES',
            'VARBINARY',
            'VARCHAR',
            'VARCHARACTER',
            'VARYING',
            'WHEN',
            'WHERE',
            'WHILE',
            'WITH',
            'WRITE',
            'XOR',
            'YEAR_MONTH',
            'ZEROFILL',
        ];
    }
}
