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
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Abstract base class to be extended to create report builder sources
 *
 * @property string $base
 * @property rb_join[] $joinlist
 * @property rb_column_option[] $columnoptions
 * @property rb_filter_option[] $filteroptions
 */
abstract class rb_base_source {

    /*
     * Used in default pre_display_actions function.
     */
    public $needsredirect, $redirecturl, $redirectmessage;

    /** @var array of component used for lookup of classes */
    protected $usedcomponents = array();

    /** @var rb_column[] */
    public $requiredcolumns;

    /** @var rb_global_restriction_set with active restrictions, ignore if null */
    protected $globalrestrictionset = null;

    /** @var rb_join[] list of global report restriction joins  */
    public $globalrestrictionjoins = array();

    /** @var array named query params used in global restriction joins */
    public $globalrestrictionparams = array();

    /**
     * TODO - it would be nice to make this definable in the config or something.
     * @var string $uniqueseperator - A string unique enough to use as a seperator for textareas
     */
    protected $uniquedelimiter = '^|:';

    /**
     * Class constructor
     *
     * Call from the constructor of all child classes with:
     *
     *  parent::__construct()
     *
     * to ensure child class has implemented everything necessary to work.
     */
    public function __construct() {
        // Extending classes should add own component to this array before calling parent constructor,
        // this allows us to lookup display classes at more locations.
        $this->usedcomponents[] = 'totara_reportbuilder';

        // check that child classes implement required properties
        $properties = array(
            'base',
            'joinlist',
            'columnoptions',
            'filteroptions',
        );
        foreach ($properties as $property) {
            if (!property_exists($this, $property)) {
                $a = new stdClass();
                $a->property = $property;
                $a->class = get_class($this);
                throw new ReportBuilderException(get_string('error:propertyxmustbesetiny', 'totara_reportbuilder', $a));
            }
        }

        // set sensible defaults for optional properties
        $defaults = array(
            'paramoptions' => array(),
            'requiredcolumns' => array(),
            'contentoptions' => array(),
            'preproc' => null,
            'grouptype' => 'none',
            'groupid' => null,
            'selectable' => true,
            'scheduleable' => true,
            'cacheable' => true,
            'hierarchymap' => array()
        );
        foreach ($defaults as $property => $default) {
            if (!property_exists($this, $property)) {
                $this->$property = $default;
            } else if ($this->$property === null) {
                $this->$property = $default;
            }
        }

        // basic sanity checking of joinlist
        $this->validate_joinlist();
        //create array to store the join functions and join table
        $joindata = array();
        $base = $this->base;
        //if any of the join tables are customfield-related, ensure the customfields are added
        foreach ($this->joinlist as $join) {
            //tables can be joined multiple times so we set elements of an associative array as joinfunction => jointable
            $table = $join->table;
            switch ($table) {
                case '{user}':
                    $joindata['add_custom_user_fields'] = 'auser';
                    break;
                case '{course}':
                    $joindata['add_custom_course_fields'] = 'course';
                    break;
                case '{prog}':
                    $joindata['add_custom_prog_fields'] = 'prog';
                    break;
                case '{org}':
                    $joindata['add_custom_organisation_fields'] = 'org';
                    break;
                case '{pos}':
                    $joindata['add_custom_position_fields'] = 'pos';
                    break;
                case '{comp}':
                    $joindata['add_custom_competency_fields'] = 'comp';
                    break;
                case '{goal}':
                    $joindata['add_custom_goal_fields'] = 'goal';
                    break;
                case '{goal_personal}':
                    $joindata['add_custom_personal_goal_fields'] = 'goal_personal';
                    break;
            }
        }
        //now ensure customfields fields are added if there are no joins but the base table is customfield-related
        switch ($base) {
            case '{user}':
                $joindata['add_custom_user_fields'] = 'base';
                break;
            case '{course}':
                $joindata['add_custom_course_fields'] = 'base';
                break;
            case '{prog}':
                $joindata['add_custom_prog_fields'] = 'base';
                break;
            case '{org}':
                $joindata['add_custom_organisation_fields'] = 'base';
                break;
            case '{pos}':
                $joindata['add_custom_position_fields'] = 'base';
                break;
            case '{comp}':
                $joindata['add_custom_competency_fields'] = 'base';
                break;
            case '{goal}':
                $joindata['add_custom_goal_fields'] = 'base';
                break;
            case '{goal_personal}':
                $joindata['add_custom_personal_goal_fields'] = 'base';
        }
        //and then use the flags to call the appropriate add functions
        foreach ($joindata as $joinfunction => $jointable) {
            $this->$joinfunction($this->joinlist,
                                 $this->columnoptions,
                                 $this->filteroptions,
                                 $jointable
                                );

        }
    }

    /**
     * Is this report source usable?
     *
     * Override and return true if the source should be hidden
     * in all user interfaces. For example when the source
     * requires some subsystem to be enabled.
     *
     * @return bool
     */
    public function is_ignored() {
        return false;
    }

    /**
     * Are the global report restrictions implemented in the source?
     *
     * Return values mean:
     *   - true: this report source supports global report restrictions.
     *   - false: this report source does NOT support global report restrictions.
     *   - null: this report source has not been converted to use global report restrictions yet.
     *
     * @return null|bool
     */
    public function global_restrictions_supported() {
        // Null means not converted yet, override in sources with true or false.
        return null;
    }

    /**
     * Set redirect url and (optionally) message for use in default pre_display_actions function.
     *
     * When pre_display_actions is call it will redirect to the specified url (unless pre_display_actions
     * is overridden, in which case it performs those actions instead).
     *
     * @param mixed $url moodle_url or url string
     * @param string $message
     */
    protected function set_redirect($url, $message = null) {
        $this->redirecturl = $url;
        $this->redirectmessage = $message;
    }


    /**
     * Set whether redirect needs to happen in pre_display_actions.
     *
     * @param bool $truth true if redirect is needed
     */
    protected function needs_redirect($truth = true) {
        $this->needsredirect = $truth;
    }


    /**
     * Default pre_display_actions - if needsredirect is true then redirect to the specified
     * page, otherwise do nothing.
     *
     * This function is called after post_config and before report data is generated. This function is
     * not called when report data is not generated, such as on report setup pages.
     * If you want to perform a different action after post_config then override this function and
     * set your own private variables (e.g. to signal a result from post_config) in your report source.
     */
    public function pre_display_actions() {
        if ($this->needsredirect && isset($this->redirecturl)) {
            if (isset($this->redirectmessage)) {
                totara_set_notification($this->redirectmessage, $this->redirecturl, array('class' => 'notifymessage'));
            } else {
                redirect($this->redirecturl);
            }
        }
    }


    /**
     * Create a link that when clicked will display additional information inserted in a box below the clicked row.
     *
     * @param string|stringable $columnvalue the value to display in the column
     * @param string $expandname the name of the function (prepended with 'rb_expand_') that will generate the contents
     * @param array $params any parameters that the content generator needs
     * @param string|moodle_url $alternateurl url to link to in case js is not available
     * @param array $attributes
     * @return type
     */
    protected function create_expand_link($columnvalue, $expandname, $params, $alternateurl = '', $attributes = array()) {
        global $OUTPUT;

        // Serialize the data so that it can be passed as a single value.
        $paramstring = http_build_query($params, '', '&');

        $class_link = 'rb-display-expand-link ';
        if (array_key_exists('class', $attributes)) {
            $class_link .=  $attributes['class'];
        }

        $attributes['class'] = 'rb-display-expand';
        $attributes['data-name'] = $expandname;
        $attributes['data-param'] = $paramstring;
        $attributes['style'] = 'background-image:url(' . $OUTPUT->pix_url('i/info') . ')';

        // Create the result.
        $link = html_writer::link($alternateurl, format_string($columnvalue), array('class' => $class_link));
        return html_writer::div($link, 'rb-display-expand', $attributes);
    }


    /**
     * Check the joinlist for invalid dependencies and duplicate names
     *
     * @return True or throws exception if problem found
     */
    private function validate_joinlist() {
        $joinlist = $this->joinlist;
        $joins_used = array();

        // don't let source define join with same name as an SQL
        // reserved word
        // from http://docs.moodle.org/en/XMLDB_reserved_words
        $reserved_words = explode(', ', 'access, accessible, add, all, alter, analyse, analyze, and, any, array, as, asc, asensitive, asymmetric, audit, authorization, autoincrement, avg, backup, before, begin, between, bigint, binary, blob, both, break, browse, bulk, by, call, cascade, case, cast, change, char, character, check, checkpoint, close, cluster, clustered, coalesce, collate, column, comment, commit, committed, compress, compute, condition, confirm, connect, connection, constraint, contains, containstable, continue, controlrow, convert, count, create, cross, current, current_date, current_role, current_time, current_timestamp, current_user, cursor, database, databases, date, day_hour, day_microsecond, day_minute, day_second, dbcc, deallocate, dec, decimal, declare, default, deferrable, delayed, delete, deny, desc, describe, deterministic, disk, distinct, distinctrow, distributed, div, do, double, drop, dual, dummy, dump, each, else, elseif, enclosed, end, errlvl, errorexit, escape, escaped, except, exclusive, exec, execute, exists, exit, explain, external, false, fetch, file, fillfactor, float, float4, float8, floppy, for, force, foreign, freetext, freetexttable, freeze, from, full, fulltext, function, goto, grant, group, having, high_priority, holdlock, hour_microsecond, hour_minute, hour_second, identified, identity, identity_insert, identitycol, if, ignore, ilike, immediate, in, increment, index, infile, initial, initially, inner, inout, insensitive, insert, int, int1, int2, int3, int4, int8, integer, intersect, interval, into, is, isnull, isolation, iterate, join, key, keys, kill, leading, leave, left, level, like, limit, linear, lineno, lines, load, localtime, localtimestamp, lock, long, longblob, longtext, loop, low_priority, master_heartbeat_period, master_ssl_verify_server_cert, match, max, maxextents, mediumblob, mediumint, mediumtext, middleint, min, minus, minute_microsecond, minute_second, mirrorexit, mlslabel, mod, mode, modifies, modify, national, natural, new,' .
            ' no_write_to_binlog, noaudit, nocheck, nocompress, nonclustered, not, notnull, nowait, null, nullif, number, numeric, of, off, offline, offset, offsets, old, on, once, online, only, open, opendatasource, openquery, openrowset, openxml, optimize, option, optionally, or, order, out, outer, outfile, over, overlaps, overwrite, pctfree, percent, perm, permanent, pipe, pivot, placing, plan, precision, prepare, primary, print, prior, privileges, proc, procedure, processexit, public, purge, raid0, raiserror, range, raw, read, read_only, read_write, reads, readtext, real, reconfigure, references, regexp, release, rename, repeat, repeatable, replace, replication, require, resource, restore, restrict, return, returning, revoke, right, rlike, rollback, row, rowcount, rowguidcol, rowid, rownum, rows, rule, save, schema, schemas, second_microsecond, select, sensitive, separator, serializable, session, session_user, set, setuser, share, show, shutdown, similar, size, smallint, some, soname, spatial, specific, sql, sql_big_result, sql_calc_found_rows, sql_small_result, sqlexception, sqlstate, sqlwarning, ssl, start, starting, statistics, straight_join, successful, sum, symmetric, synonym, sysdate, system_user, table, tape, temp, temporary, terminated, textsize, then, tinyblob, tinyint, tinytext, to, top, trailing, tran, transaction, trigger, true, truncate, tsequal, uid, uncommitted, undo, union, unique, unlock, unsigned, update, updatetext, upgrade, usage, use, user, using, utc_date, utc_time, utc_timestamp, validate, values, varbinary, varchar, varchar2, varcharacter, varying, verbose, view, waitfor, when, whenever, where, while, with, work, write, writetext, x509, xor, year_month, zerofill');

        foreach ($joinlist as $item) {
            // check join list for duplicate names
            if (in_array($item->name, $joins_used)) {
                $a = new stdClass();
                $a->join = $item->name;
                $a->source = get_class($this);
                throw new ReportBuilderException(get_string('error:joinxusedmorethanonceiny', 'totara_reportbuilder', $a));
            } else {
                $joins_used[] = $item->name;
            }

            if (in_array($item->name, $reserved_words)) {
                $a = new stdClass();
                $a->join = $item->name;
                $a->source = get_class($this);
                throw new ReportBuilderException(get_string('error:joinxisreservediny', 'totara_reportbuilder', $a));
            }
        }

        foreach ($joinlist as $item) {
            // check that dependencies exist
            if (isset($item->dependencies) &&
                is_array($item->dependencies)) {

                foreach ($item->dependencies as $dep) {
                    if ($dep == 'base') {
                        continue;
                    }
                    if (!in_array($dep, $joins_used)) {
                        $a = new stdClass();
                        $a->join = $item->name;
                        $a->source = get_class($this);
                        $a->dependency = $dep;
                        throw new ReportBuilderException(get_string('error:joinxhasdependencyyinz', 'totara_reportbuilder', $a));
                    }
                }
            } else if (isset($item->dependencies) &&
                $item->dependencies != 'base') {

                if (!in_array($item->dependencies, $joins_used)) {
                    $a = new stdClass();
                    $a->join = $item->name;
                    $a->source = get_class($this);
                    $a->dependency = $item->dependencies;
                    throw new ReportBuilderException(get_string('error:joinxhasdependencyyinz', 'totara_reportbuilder', $a));
                }
            }
        }
        return true;
    }


    //
    //
    // General purpose source specific methods
    //
    //

    /**
     * Returns a new rb_column object based on a column option from this source
     *
     * If $heading is given use it for the heading property, otherwise use
     * the default heading property from the column option
     *
     * @param string $type The type of the column option to use
     * @param string $value The value of the column option to use
     * @param int $transform
     * @param int $aggregate
     * @param string $heading Heading for the new column
     * @param boolean $customheading True if the heading has been customised
     * @return rb_column A new rb_column object with details copied from this rb_column_option
     */
    public function new_column_from_option($type, $value, $transform, $aggregate, $heading=null, $customheading = true, $hidden=0) {
        $columnoptions = $this->columnoptions;
        $joinlist = $this->joinlist;
        if ($coloption =
            reportbuilder::get_single_item($columnoptions, $type, $value)) {

            // make sure joins are defined before adding column
            if (!reportbuilder::check_joins($joinlist, $coloption->joins)) {
                $a = new stdClass();
                $a->type = $coloption->type;
                $a->value = $coloption->value;
                $a->source = get_class($this);
                throw new ReportBuilderException(get_string('error:joinsfortypexandvalueynotfoundinz', 'totara_reportbuilder', $a));
            }

            if ($heading === null) {
                $heading = ($coloption->defaultheading !== null) ?
                    $coloption->defaultheading : $coloption->name;
            }

            return new rb_column(
                $type,
                $value,
                $heading,
                $coloption->field,
                array(
                    'joins' => $coloption->joins,
                    'displayfunc' => $coloption->displayfunc,
                    'extrafields' => $coloption->extrafields,
                    'required' => false,
                    'capability' => $coloption->capability,
                    'noexport' => $coloption->noexport,
                    'grouping' => $coloption->grouping,
                    'grouporder' => $coloption->grouporder,
                    'nosort' => $coloption->nosort,
                    'style' => $coloption->style,
                    'class' => $coloption->class,
                    'hidden' => $hidden,
                    'customheading' => $customheading,
                    'transform' => $transform,
                    'aggregate' => $aggregate,
                    'extracontext' => $coloption->extracontext
                )
            );
        } else {
            $a = new stdClass();
            $a->type = $type;
            $a->value = $value;
            $a->source = get_class($this);
            throw new ReportBuilderException(get_string('error:columnoptiontypexandvalueynotfoundinz', 'totara_reportbuilder', $a));
        }
    }

    /**
     * Returns list of used components.
     *
     * The list includes frankenstyle component names of the
     * current source and all parents.
     *
     * @return string[]
     */
    public function get_used_components() {
        return $this->usedcomponents;
    }

    //
    //
    // Generic column display methods
    //
    //

    /**
     * Format row record data for display.
     *
     * @param stdClass $row
     * @param string $format
     * @param reportbuilder $report
     * @return array of strings usually, values may be arrays for Excel format for example.
     */
    public function process_data_row(stdClass $row, $format, reportbuilder $report) {
        $results = array();
        $isexport = ($format !== 'html');

        foreach ($report->columns as $column) {
            if (!$column->display_column($isexport)) {
                continue;
            }

            $type = $column->type;
            $value = $column->value;
            $field = "{$type}_{$value}";

            if (!property_exists($row, $field)) {
                $results[] = get_string('unknown', 'totara_reportbuilder');
                continue;
            }

            $classname = $column->get_display_class($report);
            $results[] = $classname::display($row->$field, $format, $row, $column, $report);
        }

        return $results;
    }

    /**
     * Reformat a timestamp into a time, showing nothing if invalid or null
     *
     * @param integer $date Unix timestamp
     * @param object $row Object containing all other fields for this row
     *
     * @return string Time in a nice format
     */
    function rb_display_nice_time($date, $row) {
        if ($date && is_numeric($date)) {
            return userdate($date, get_string('strftimeshort', 'langconfig'));
        } else {
            return '';
        }
    }

    /**
     * Reformat a timestamp and timezone into a datetime, showing nothing if invalid or null
     *
     * @param integer $date Unix timestamp
     * @param object $row Object containing all other fields for this row (which should include a timezone field)
     *
     * @return string Date and time in a nice format
     */
    function rb_display_nice_datetime_in_timezone($date, $row) {
        if ($date && is_numeric($date)) {
            if (empty($row->timezone)) {
                $targetTZ = core_date::get_user_timezone();
                $tzstring = get_string('nice_time_unknown_timezone', 'totara_reportbuilder');
            } else {
                $targetTZ = core_date::normalise_timezone($row->timezone);
                $tzstring = core_date::get_localised_timezone($targetTZ);
            }
            $date = userdate($date, get_string('strftimedatetime', 'langconfig'), $targetTZ) . ' ';
            return $date . $tzstring;
        } else {
            return '';
        }
    }

    /**
     * Reformat a timestamp into a date and time (including seconds), showing nothing if invalid or null
     *
     * @param integer $date Unix timestamp
     * @param object $row Object containing all other fields for this row
     *
     * @return string Date and time (including seconds) in a nice format
     */
    function rb_display_nice_datetime_seconds($date, $row) {
        if ($date && is_numeric($date)) {
            return userdate($date, get_string('strftimedateseconds', 'langconfig'));
        } else {
            return '';
        }
    }

    // convert floats to 2 decimal places
    function rb_display_round2($item, $row) {
        return ($item === null or $item === '') ? '-' : sprintf('%.2f', $item);
    }

    // converts number to percentage with 1 decimal place
    function rb_display_percent($item, $row) {
        return ($item === null or $item === '') ? '-' : sprintf('%.1f%%', $item);
    }

    // Displays a comma separated list of strings as one string per line.
    // Assumes you used "'grouping' => 'comma_list'", which concatenates with ', ', to construct the string.
    function rb_display_list_to_newline($list, $row) {
        $items = explode(', ', $list);
        foreach ($items as $key => $item) {
            if (empty($item)) {
                $items[$key] = '-';
            }
        }
        return implode($items, "\n");
    }

    // Displays a delimited list of strings as one string per line.
    // Assumes you used "'grouping' => 'sql_aggregate'", which concatenates with $uniquedelimiter to construct a pre-ordered string.
    function rb_display_orderedlist_to_newline($list, $row) {
        $output = array();
        $items = explode($this->uniquedelimiter, $list);
        foreach ($items as $item) {
            if (empty($item) || $item === '-') {
                $output[] = '-';
            } else {
                $output[] = $item;
            }
        }
        return implode($output, "\n");
    }

    // Displays a comma separated list of ints as one nice_date per line.
    // Assumes you used "'grouping' => 'comma_list'", which concatenates with ', ', to construct the string.
    function rb_display_list_to_newline_date($datelist, $row) {
        $items = explode(', ', $datelist);
        foreach ($items as $key => $item) {
            if (empty($item) || $item === '-') {
                $items[$key] = '-';
            } else {
                $items[$key] = $this->rb_display_nice_date($item, $row);
            }
        }
        return implode($items, "\n");
    }

    // Displays a delimited list of ints as one nice_date per line, based off nice_date_list.
    // Assumes you used "'grouping' => 'sql_aggregate'", which concatenates with $uniquedelimiter to construct a pre-ordered string.
    function rb_display_orderedlist_to_newline_date($datelist, $row) {
        $output = array();
        $items = explode($this->uniquedelimiter, $datelist);
        foreach ($items as $item) {
            if (empty($item) || $item === '-') {
                $output[] = '-';
            } else {
                $output[] = userdate($item, get_string('strfdateshortmonth', 'langconfig'));
            }
        }
        return implode($output, "\n");
    }

    /**
     * Display correct course grade via grade or RPL as a percentage string
     *
     * @param string $item A number to convert
     * @param object $row Object containing all other fields for this row
     *
     * @return string The percentage with 1 decimal place
     */
    function rb_display_course_grade_percent($item, $row) {
        if ($row->status == COMPLETION_STATUS_COMPLETEVIARPL && !empty($row->rplgrade)) {
            // If RPL then print the RPL grade.
            return sprintf('%.1f%%', $row->rplgrade);
        } else if (!empty($row->maxgrade) && !empty($item)) {

            $maxgrade = (float)$row->maxgrade;
            $mingrade = 0.0;
            if (!empty($row->mingrade)) {
                $mingrade = (float)$row->mingrade;
            }

            // Create a percentage using the max grade.
            $percent = ((($item - $mingrade) / ($maxgrade - $mingrade)) * 100);

            return sprintf('%.1f%%', $percent);
        } else if ($item !== null && $item !== '') {
            // If the item has a value show it.
            return $item;
        } else {
            // Otherwise show a '-'
            return '-';
        }
    }

    /**
     * A rb_column_options->displayfunc helper function for showing a user's name and links to their profile.
     * To pass the correct data, first:
     *      $usednamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      array_merge(array('id' => $base . '.id'),
     *                  $allnamefields)
     * When exporting, only the user's full name is displayed (without link).
     *
     * @param string $user Unused
     * @param object $row All the data required to display a user's name
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_link_user($user, $row, $isexport = false) {

        // Process obsolete calls to this display function.
        if (isset($row->user_id)) {
            $fullname = $user;
        } else {
            $fullname = fullname($row);
        }

        // Don't show links in spreadsheet.
        if ($isexport) {
            return $fullname;
        }

        $url = new moodle_url('/user/view.php', array('id' => $row->id));
        return html_writer::link($url, $fullname);
    }

    /**
     * A rb_column_options->displayfunc helper function for showing a user's profile picture, name and links to their profile.
     * To pass the correct data, first:
     *      $usednamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      array_merge(array('id' => $base . '.id',
     *                        'picture' => $base . '.picture',
     *                        'imagealt' => $base . '.imagealt',
     *                        'email' => $base . '.email'),
     *                  $allnamefields)
     * When exporting, only the user's full name is displayed (without icon or link).
     *
     * @param string $user Unused
     * @param object $row All the data required to display a user's name, icon and link
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_link_user_icon($user, $row, $isexport = false) {
        global $OUTPUT;

        // Process obsolete calls to this display function.
        if (isset($row->userpic_picture)) {
            $picuser = new stdClass();
            $picuser->id = $row->user_id;
            $picuser->picture = $row->userpic_picture;
            $picuser->imagealt = $row->userpic_imagealt;
            $picuser->firstname = $row->userpic_firstname;
            $picuser->firstnamephonetic = $row->userpic_firstnamephonetic;
            $picuser->middlename = $row->userpic_middlename;
            $picuser->lastname = $row->userpic_lastname;
            $picuser->lastnamephonetic = $row->userpic_lastnamephonetic;
            $picuser->alternatename = $row->userpic_alternatename;
            $picuser->email = $row->userpic_email;
            $row = $picuser;
        }

        if ($row->id == 0) {
            return '';
        }

        // Don't show picture in spreadsheet.
        if ($isexport) {
            return fullname($row);
        }

        $url = new moodle_url('/user/view.php', array('id' => $row->id));
        return $OUTPUT->user_picture($row, array('courseid' => 1)) . "&nbsp;" . html_writer::link($url, $user);
    }

    /**
     * A rb_column_options->displayfunc helper function for showing a user's profile picture.
     * To pass the correct data, first:
     *      $usernamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      array_merge(array('id' => $base . '.id',
     *                        'picture' => $base . '.picture',
     *                        'imagealt' => $base . '.imagealt',
     *                        'email' => $base . '.email'),
     *                  $allnamefields)
     * When exporting, only the user's full name is displayed (instead of picture).
     *
     * @param string $user Unused
     * @param object $row All the data required to display a user's name and icon
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_user_picture($user, $row, $isexport = false) {
        global $OUTPUT;

        // Process obsolete calls to this display function.
        if (isset($row->userpic_picture)) {
            $picuser = new stdClass();
            $picuser->id = $user;
            $picuser->picture = $row->userpic_picture;
            $picuser->imagealt = $row->userpic_imagealt;
            $picuser->firstname = $row->userpic_firstname;
            $picuser->firstnamephonetic = $row->userpic_firstnamephonetic;
            $picuser->middlename = $row->userpic_middlename;
            $picuser->lastname = $row->userpic_lastname;
            $picuser->lastnamephonetic = $row->userpic_lastnamephonetic;
            $picuser->alternatename = $row->userpic_alternatename;
            $picuser->email = $row->userpic_email;
            $row = $picuser;
        }

        // Don't show picture in spreadsheet.
        if ($isexport) {
            return fullname($row);
        } else {
            return $OUTPUT->user_picture($row, array('courseid' => 1));
        }
    }

    /**
     * A rb_column_options->displayfunc helper function for showing a user's name.
     * To pass the correct data, first:
     *      $usednamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      $allnamefields
     *
     * @param string $user Unused
     * @param object $row All the data required to display a user's name
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_user($user, $row, $isexport = false) {
        return fullname($row);
    }

    /**
     * Convert a course name into an expanding link.
     *
     * @param string $course
     * @param array $row
     * @param bool $isexport
     * @return html|string
     */
    public function rb_display_course_expand($course, $row, $isexport = false) {
        if ($isexport) {
            return format_string($course);
        }

        $attr = array('class' => totara_get_style_visibility($row, 'course_visible', 'course_audiencevisible'));
        $alturl = new moodle_url('/course/view.php', array('id' => $row->course_id));
        return $this->create_expand_link($course, 'course_details', array('expandcourseid' => $row->course_id), $alturl, $attr);
    }

    /**
     * Convert a program/certification name into an expanding link.
     *
     * @param string $program
     * @param array $row
     * @param bool $isexport
     * @return html|string
     */
    public function rb_display_program_expand($program, $row, $isexport = false) {
        if ($isexport) {
            return format_string($program);
        }

        $attr = array('class' => totara_get_style_visibility($row, 'prog_visible', 'prog_audiencevisible'));
        $alturl = new moodle_url('/totara/program/view.php', array('id' => $row->prog_id));
        return $this->create_expand_link($program, 'prog_details',
                array('expandprogid' => $row->prog_id), $alturl, $attr);
    }

    /**
     * Certification display the certification path as string.
     *
     * @param string $certifpath    CERTIFPATH_X constant to describe cert or recert coursesets
     * @param array $row            The record used to generate the table row
     * @return string
     */
    function rb_display_certif_certifpath($certifpath, $row) {
        global $CERTIFPATH;
        if ($certifpath && isset($CERTIFPATH[$certifpath])) {
            return get_string($CERTIFPATH[$certifpath], 'totara_certification');
        }
    }

    /**
     * Certification display the certification renewal status as string.
     *
     * @param string $renewalstatus CERTIFRENEWALSTATUS_X constant to describe current renewal status
     * @param array $row            The record used to generate the table row
     * @return string
     */
    function rb_display_certif_renewalstatus($renewalstatus, $row) {
        global $CERTIFRENEWALSTATUS;

        if (!empty($row->unassigned)) {
            return '';
        } else if (!empty($row->status) && $row->status == CERTIFSTATUS_ASSIGNED) {
            // Just assigned.
            return '';
        } else if (!empty($row->status) && $row->status == CERTIFSTATUS_INPROGRESS && $renewalstatus == CERTIFRENEWALSTATUS_NOTDUE) {
            // First assignment and have made some progress.
            return '';
        } else {
            return get_string($CERTIFRENEWALSTATUS[$renewalstatus], 'totara_certification');
        }
    }

    /**
     * Expanding content to display when clicking a course.
     * Will be placed inside a table cell which is the width of the table.
     * Call required_param to get any param data that is needed.
     * Make sure to check that the data requested is permitted for the viewer.
     *
     * @return string
     */
    public function rb_expand_course_details() {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/totara/reportbuilder/report_forms.php');
        require_once($CFG->dirroot . '/course/renderer.php');
        require_once($CFG->dirroot . '/lib/coursecatlib.php');

        $courseid = required_param('expandcourseid', PARAM_INT);
        $userid = $USER->id;

        if (!totara_course_is_viewable($courseid)) {
            ajax_result(false, get_string('coursehidden'));
            exit();
        }

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

        $chelper = new coursecat_helper();

        $formdata = array(
            // The following are required.
            'summary' => $chelper->get_course_formatted_summary(new course_in_list($course)),
            'status' => null,
            'courseid' => $courseid,

            // The following are optional, and depend upon state.
            'inlineenrolmentelements' => null,
            'enroltype' => null,
            'progress' => null,
            'enddate' => null,
            'grade' => null,
            'action' => null,
            'url' => null,
        );

        $coursecontext = context_course::instance($course->id, MUST_EXIST);
        $enrolled = is_enrolled($coursecontext);

        $inlineenrolments = array();
        if ($enrolled) {
            $ccompl = new completion_completion(array('userid' => $userid, 'course' => $courseid));
            $complete = $ccompl->is_complete();
            if ($complete) {
                $sql = 'SELECT gg.*
                          FROM {grade_grades} gg
                          JOIN {grade_items} gi
                            ON gg.itemid = gi.id
                         WHERE gg.userid = ?
                           AND gi.courseid = ?';
                $grade = $DB->get_record_sql($sql, array($userid, $courseid));
                $coursecompletion = $DB->get_record('course_completions', array('userid' => $userid, 'course' => $courseid));
                $coursecompletedon = userdate($coursecompletion->timecompleted, get_string('strfdateshortmonth', 'langconfig'));

                $formdata['status'] = get_string('coursestatuscomplete', 'totara_reportbuilder');
                $formdata['progress'] = get_string('coursecompletedon', 'totara_reportbuilder', $coursecompletedon);
                if ($grade) {
                    if (!isset($grade->finalgrade)) {
                        $formdata['grade'] = '-';
                    } else {
                        $formdata['grade'] = get_string('xpercent', 'totara_core', $grade->finalgrade);
                    }
                }
            } else {
                $formdata['status'] = get_string('coursestatusenrolled', 'totara_reportbuilder');

                list($statusdpsql, $statusdpparams) = $this->get_dp_status_sql($userid, $courseid);
                $statusdp = $DB->get_record_sql($statusdpsql, $statusdpparams);
                $progress = totara_display_course_progress_icon($userid, $courseid,
                    $statusdp->course_completion_statusandapproval);
                // Highlight if the item has not yet been approved.
                if ($statusdp->approved == DP_APPROVAL_UNAPPROVED
                        || $statusdp->approved == DP_APPROVAL_REQUESTED) {
                    $progress .= $this->rb_display_plan_item_status($statusdp->approved);
                }
                $formdata['progress'] = $progress;

                // Course not finished, so no end date for course.
                $formdata['enddate'] = '';
            }
            $formdata['url'] = new moodle_url('/course/view.php', array('id' => $courseid));
            $formdata['action'] =  get_string('launchcourse', 'totara_program');
        } else {
            $formdata['status'] = get_string('coursestatusnotenrolled', 'totara_reportbuilder');

            $instances = enrol_get_instances($courseid, true);
            $plugins = enrol_get_plugins(true);

            $enrolmethodlist = array();
            foreach ($instances as $instance) {
                if (!isset($plugins[$instance->enrol])) {
                    continue;
                }
                $plugin = $plugins[$instance->enrol];
                if (enrol_is_enabled($instance->enrol)) {
                    $enrolmethodlist[] = $plugin->get_instance_name($instance);
                    // If the enrolment plugin has a course_expand_hook then add to a list to process.
                    if (method_exists($plugin, 'course_expand_get_form_hook')
                        && method_exists($plugin, 'course_expand_enrol_hook')) {
                        $enrolment = array ('plugin' => $plugin, 'instance' => $instance);
                        $inlineenrolments[$instance->id] = (object) $enrolment;
                    }
                }
            }
            $enrolmethodstr = implode(', ', $enrolmethodlist);
            $realuser = \core\session\manager::get_realuser();

            $inlineenrolmentelements = $this->get_inline_enrolment_elements($inlineenrolments);
            $formdata['inlineenrolmentelements'] = $inlineenrolmentelements;
            $formdata['enroltype'] = $enrolmethodstr;

            if (is_viewing($coursecontext, $realuser->id) || is_siteadmin($realuser->id)) {
                $formdata['action'] = get_string('viewcourse', 'totara_program');
                $formdata['url'] = new moodle_url('/course/view.php', array('id' => $courseid));
            }
        }

        $mform = new report_builder_course_expand_form(null, $formdata);

        if (!empty($inlineenrolments)) {
            $this->process_enrolments($mform, $inlineenrolments);
        }

        return $mform->render();
    }

    /**
     * @param $inlineenrolments array of objects containing matching instance/plugin pairs
     * @return array of form elements
     */
    private function get_inline_enrolment_elements(array $inlineenrolments) {
        global $CFG;

        require_once($CFG->dirroot . '/lib/pear/HTML/QuickForm/button.php');
        require_once($CFG->dirroot . '/lib/pear/HTML/QuickForm/static.php');

        $retval = array();
        foreach ($inlineenrolments as $inlineenrolment) {
            $instance = $inlineenrolment->instance;
            $plugin = $inlineenrolment->plugin;
            $enrolform = $plugin->course_expand_get_form_hook($instance);

            $nameprefix = 'instanceid_' . $instance->id . '_';

            // Currently, course_expand_get_form_hook check if the user can self enrol before creating the form, if not, it will
            // return the result of the can_self_enrol function which could be false or a string.
            if (!$enrolform || is_string($enrolform)) {
                $retval[] = new HTML_QuickForm_static(null, null, $enrolform);
                continue;
            }

            if ($enrolform instanceof moodleform) {
                foreach ($enrolform->_form->_elements as $element) {
                    if ($element->_type == 'button' || $element->_type == 'submit') {
                        continue;
                    } else if ($element->_type == 'group') {
                        $newelements = array();
                        foreach ($element->getElements() as $subelement) {
                            if ($subelement->_type == 'button' || $subelement->_type == 'submit') {
                                continue;
                            }
                            $elementname = $subelement->getName();
                            $newelement  = $nameprefix . $elementname;
                            $subelement->setName($newelement);
                            if (!empty($enrolform->_form->_types[$elementname]) && $subelement instanceof MoodleQuickForm_hidden) {
                                $subelement->setType($newelement, $enrolform->_form->_types[$elementname]);
                            }
                            $newelements[] = $subelement;
                        }
                        if (count($newelements)>0) {
                            $element->setElements($newelements);
                            $retval[] = $element;
                        }
                    } else {
                        $elementname = $element->getName();
                        $newelement  = $nameprefix . $elementname;
                        $element->setName($newelement);
                        if (!empty($enrolform->_form->_types[$elementname]) && $element instanceof MoodleQuickForm_hidden) {
                            $element->setType($newelement, $enrolform->_form->_types[$elementname]);
                        }
                        $retval[] = $element;
                    }
                }
            }

            if (count($inlineenrolments) > 1) {
                $enrollabel = get_string('enrolusing', 'totara_reportbuilder', $plugin->get_instance_name($instance->id));
            } else {
                $enrollabel = get_string('enrol', 'totara_reportbuilder');
            }
            $name = $instance->id;

            $retval[] = new HTML_QuickForm_button($name, $enrollabel, array('class' => 'expandenrol'));
        }
        return $retval;
    }

    /**
     * Expanding content to display when clicking a program.
     * Will be placed inside a table cell which is the width of the table.
     * Call required_param to get any param data that is needed.
     * Make sure to check that the data requested is permitted for the viewer.
     *
     * @return string
     */
    public function rb_expand_prog_details() {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/totara/reportbuilder/report_forms.php');
        require_once($CFG->dirroot . '/totara/program/renderer.php');

        $progid = required_param('expandprogid', PARAM_INT);
        $userid = $USER->id;

        if (!$program = new program($progid)) {
            ajax_result(false, get_string('error:programid', 'totara_program'));
            exit();
        }

        if (!$program->is_viewable()) {
            ajax_result(false, get_string('error:inaccessible', 'totara_program'));
            exit();
        }

        $formdata = $DB->get_record('prog', array('id' => $progid));

        $phelper = new programcat_helper();
        $formdata->summary = $phelper->get_program_formatted_summary(new program_in_list($formdata));

        $formdata->assigned = $DB->record_exists('prog_user_assignment', array('userid' => $userid, 'programid' => $progid));

        $mform = new report_builder_program_expand_form(null, (array)$formdata);

        return $mform->render();
    }

    /**
     * Get course progress status for user according his record of learning
     *
     * @param int $userid
     * @param int $courseid
     * @return array
     */
    public function get_dp_status_sql($userid, $courseid) {
        global $CFG;
        require_once($CFG->dirroot.'/totara/plan/rb_sources/rb_source_dp_course.php');
        // Use base query from rb_source_dp_course, and column/joins of statusandapproval.
        $base_sql = $this->get_dp_status_base_sql();
        $sql = "SELECT CASE WHEN dp_course.planstatus = " . DP_PLAN_STATUS_COMPLETE . "
                            THEN dp_course.completionstatus
                            ELSE course_completion.status
                            END AS course_completion_statusandapproval,
                       dp_course.approved AS approved
                 FROM ".$base_sql. " base
                 LEFT JOIN {course_completions} course_completion
                   ON (base.courseid = course_completion.course
                  AND base.userid = course_completion.userid)
                 LEFT JOIN (SELECT p.userid AS userid, p.status AS planstatus,
                                   pc.courseid AS courseid, pc.approved AS approved,
                                   pc.completionstatus AS completionstatus
                              FROM {dp_plan} p
                             INNER JOIN {dp_plan_course_assign} pc ON p.id = pc.planid) dp_course
                   ON dp_course.userid = base.userid AND dp_course.courseid = base.courseid
                WHERE base.userid = ? AND base.courseid = ?";
        return array($sql, array($userid, $courseid));
    }

    /**
     * Get base sql for course record of learning.
     * @return string
     */
    public function get_dp_status_base_sql() {
        global $DB;

        // Apply global user restrictions.
        $global_restriction_join_ue = $this->get_global_report_restriction_join('ue', 'userid');
        $global_restriction_join_cc = $this->get_global_report_restriction_join('cc', 'userid');
        $global_restriction_join_p1 = $this->get_global_report_restriction_join('p1', 'userid');

        $uniqueid = $DB->sql_concat_join("','", array(sql_cast2char('userid'), sql_cast2char('courseid')));
        return  "(SELECT " . $uniqueid . " AS id, userid, courseid
                    FROM (SELECT ue.userid AS userid, e.courseid AS courseid
                           FROM {user_enrolments} ue
                           JOIN {enrol} e ON ue.enrolid = e.id
                           {$global_restriction_join_ue}
                          UNION
                         SELECT cc.userid AS userid, cc.course AS courseid
                           FROM {course_completions} cc
                           {$global_restriction_join_cc}
                          WHERE cc.status > " . COMPLETION_STATUS_NOTYETSTARTED . "
                          UNION
                         SELECT p1.userid AS userid, pca1.courseid AS courseid
                           FROM {dp_plan_course_assign} pca1
                           JOIN {dp_plan} p1 ON pca1.planid = p1.id
                           {$global_restriction_join_p1}
                    )
                basesub)";
    }

    // convert a course name into a link to that course
    function rb_display_link_course($course, $row, $isexport = false) {
        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');

        if ($isexport) {
            return format_string($course);
        }

        $courseid = $row->course_id;
        $attr = array('class' => totara_get_style_visibility($row, 'course_visible', 'course_audiencevisible'));
        $url = new moodle_url('/course/view.php', array('id' => $courseid));
        return html_writer::link($url, $course, $attr);
    }

    // convert a course name into a link to that course and shows
    // the course icon next to it
    function rb_display_link_course_icon($course, $row, $isexport = false) {
        global $CFG, $OUTPUT;
        require_once($CFG->dirroot . '/cohort/lib.php');

        if ($isexport) {
            return format_string($course);
        }

        $courseid = $row->course_id;
        $courseicon = !empty($row->course_icon) ? $row->course_icon : 'default';
        $cssclass = totara_get_style_visibility($row, 'course_visible', 'course_audiencevisible');
        $icon = html_writer::empty_tag('img', array('src' => totara_get_icon($courseid, TOTARA_ICON_TYPE_COURSE),
            'class' => 'course_icon', 'alt' => ''));
        $link = $OUTPUT->action_link(
            new moodle_url('/course/view.php', array('id' => $courseid)),
            $icon . $course, null, array('class' => $cssclass)
        );
        return $link;
    }

    // display an icon based on the course icon field
    function rb_display_course_icon($icon, $row, $isexport = false) {
        if ($isexport) {
            return format_string($row->course_name);
        }

        $coursename = format_string($row->course_name);
        $courseicon = html_writer::empty_tag('img', array('src' => totara_get_icon($row->course_id, TOTARA_ICON_TYPE_COURSE),
            'class' => 'course_icon', 'alt' => $coursename));
        return $courseicon;
    }

    // display an icon for the course type
    function rb_display_course_type_icon($type, $row, $isexport = false) {
        global $OUTPUT;

        if ($isexport) {
            switch ($type) {
                case TOTARA_COURSE_TYPE_ELEARNING:
                    return get_string('elearning', 'rb_source_dp_course');
                case TOTARA_COURSE_TYPE_BLENDED:
                    return get_string('blended', 'rb_source_dp_course');
                case TOTARA_COURSE_TYPE_FACETOFACE:
                    return get_string('facetoface', 'rb_source_dp_course');
            }
            return '';
        }

        switch ($type) {
        case null:
            return null;
            break;
        case 0:
            $image = 'elearning';
            break;
        case 1:
            $image = 'blended';
            break;
        case 2:
            $image = 'facetoface';
            break;
        }
        $alt = get_string($image, 'rb_source_dp_course');
        $icon = $OUTPUT->pix_icon('/msgicons/' . $image . '-regular', $alt, 'totara_core', array('title' => $alt));

        return $icon;
    }

    /**
     * Display course type text
     * @param string $type
     * @param array $row
     * @param bool $isexport
     * @return string
     */
    public function rb_display_course_type($type, $row, $isexport = false) {
        $types = $this->rb_filter_course_types();
        if (isset($types[$type])) {
            return $types[$type];
        }
        return '';
    }

    // convert a course category name into a link to that category's page
    function rb_display_link_course_category($category, $row, $isexport = false) {
        if ($isexport) {
            return format_string($category);
        }

        $catid = $row->cat_id;
        $category = format_string($category);
        if ($catid == 0 || !$catid) {
            return '';
        }
        $attr = (isset($row->cat_visible) && $row->cat_visible == 0) ? array('class' => 'dimmed') : array();
        $columns = array('coursecount' => 'course', 'programcount' => 'program', 'certifcount' => 'certification');
        foreach ($columns as $field => $viewtype) {
            if (isset($row->{$field})) {
                break;
            }
        }
        switch ($viewtype) {
            case 'program':
            case 'certification':
                $url = new moodle_url('/totara/program/index.php', array('categoryid' => $catid, 'viewtype' => $viewtype));
                break;
            default:
                $url = new moodle_url('/course/index.php', array('categoryid' => $catid));
                break;
        }
        return html_writer::link($url, $category, $attr);
    }


    public function rb_display_audience_visibility($visibility, $row, $isexport = false) {
        global $COHORT_VISIBILITY;

        return $COHORT_VISIBILITY[$visibility];
    }


    /**
     * Generate the plan title with a link to the plan
     * @param string $planname
     * @param object $row
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    public function rb_display_planlink($planname, $row, $isexport = false) {

        // no text
        if (strlen($planname) == 0) {
            return '';
        }

        // invalid id - show without a link
        if (empty($row->plan_id)) {
            return $planname;
        }

        if ($isexport) {
            return $planname;
        }
        $url = new moodle_url('/totara/plan/view.php', array('id' => $row->plan_id));
        return html_writer::link($url, $planname);
    }


    /**
     * Display the plan's status (for use as a column displayfunc)
     *
     * @global object $CFG
     * @param int $status
     * @param object $row
     * @return string
     */
    public function rb_display_plan_status($status, $row) {
        global $CFG;
        require_once($CFG->dirroot . '/totara/plan/lib.php');

        switch ($status) {
            case DP_PLAN_STATUS_UNAPPROVED:
                return get_string('unapproved', 'totara_plan');
                break;
            case DP_PLAN_STATUS_PENDING:
                return get_string('pendingapproval', 'totara_plan');
                break;
            case DP_PLAN_STATUS_APPROVED:
                return get_string('approved', 'totara_plan');
                break;
            case DP_PLAN_STATUS_COMPLETE:
                return get_string('complete', 'totara_plan');
                break;
        }
    }


    /**
     * Column displayfunc to convert a plan item's status to a
     * human-readable string
     *
     * @param int $status
     * @return string
     */
    public function rb_display_plan_item_status($status) {
        global $CFG;
        require_once($CFG->dirroot . '/totara/plan/lib.php');

        switch($status) {
        case DP_APPROVAL_DECLINED:
            return get_string('declined', 'totara_plan');
        case DP_APPROVAL_UNAPPROVED:
            return get_string('unapproved', 'totara_plan');
        case DP_APPROVAL_REQUESTED:
            return get_string('pendingapproval', 'totara_plan');
        case DP_APPROVAL_APPROVED:
            return get_string('approved', 'totara_plan');
        default:
            return '';
        }
    }


    function rb_display_yes_no($item, $row) {
        if ($item === null or $item === '') {
            return '';
        } else if ($item) {
            return get_string('yes');
        } else {
            return get_string('no');
        }
    }

    // convert an integer number of minutes into a
    // formatted duration (e.g. 90 mins => 1h 30m)
    function rb_display_hours_minutes($mins, $row) {
        if ($mins === null or $mins === '') {
            return '';
        } else {
            $totalminutes = abs((int) $mins);
            $hours = floor($totalminutes / 60);
            $minutes = $totalminutes - ($hours * 60);
            $a = (object)array('hours' => $hours, 'minutes' => $minutes);
            return get_string('xhxm', 'facetoface', $a);
        }
    }

    // convert a 2 digit country code into the country name
    function rb_display_country_code($code, $row) {
        $countries = get_string_manager()->get_list_of_countries();

        if (isset($countries[$code])) {
            return $countries[$code];
        }
        return $code;
    }

    // indicates if the user is deleted or not
    function rb_display_deleted_status($status, $row) {
        switch($status) {
            case 1:
                return get_string('deleteduser', 'totara_reportbuilder');
            case 2:
                return get_string('suspendeduser', 'totara_reportbuilder');
            default:
                return get_string('activeuser', 'totara_reportbuilder');
        }
    }

    /**
     * Column displayfunc to show a hierarchy path as a human-readable string
     * @param $path the path string of delimited ids e.g. 1/3/7
     * @param $row data row
     */
    function rb_display_nice_hierarchy_path($path, $row) {
        global $DB;
        if (empty($path)) {
            return '';
        }
        $displaypath = '';
        $parentid = 0;
        // Make sure we know what we are looking for, and that the private var is populated (in source constructor).
        if (isset($row->hierarchytype) && isset($this->hierarchymap[$row->hierarchytype])) {
            $paths = explode('/', substr($path, 1));
            $map = $this->hierarchymap[$row->hierarchytype];
            foreach ($paths as $path) {
                if ($parentid !== 0) {
                    // Include ' > ' before name except on top element.
                    $displaypath .= ' &gt; ';
                }
                if (isset($map[$path])) {
                    $displaypath .= $map[$path];
                } else {
                    // Should not happen if paths are correct!
                    $displaypath .= get_string('unknown', 'totara_reportbuilder');
                }
                $parentid = $path;
            }
        }

        return $displaypath;
    }

    /**
     * Column displayfunc to convert a language code to a human-readable string
     * @param $code Language code
     * @param $row data row - unused in this function
     * @return string
     */
    function rb_display_language_code($code, $row) {
            global $CFG;
        static $languages = array();
        $strmgr = get_string_manager();
        // Populate the static variable if empty
        if (count($languages) == 0) {
            // Return all languages available in system (adapted from stringmanager->get_list_of_translations()).
            $langdirs = get_list_of_plugins('', '', $CFG->langotherroot);
            $langdirs = array_merge($langdirs, array("{$CFG->dirroot}/lang/en"=>'en'));
            $curlang = current_language();
            // Loop through all langs and get info.
            foreach ($langdirs as $lang) {
                if (isset($languages[$lang])){
                    continue;
                }
                if (strstr($lang, '_local') !== false) {
                    continue;
                }
                if (strstr($lang, '_utf8') !== false) {
                    continue;
                }
                $string = $strmgr->load_component_strings('langconfig', $lang);
                if (!empty($string['thislanguage'])) {
                    $languages[$lang] = $string['thislanguage'];
                    // If not the current language, provide the English translation also.
                    if(strpos($lang, $curlang) === false) {
                        $languages[$lang] .= ' ('. $string['thislanguageint'] .')';
                    }
                }
                unset($string);
            }
        }

        if (empty($code)) {
            return get_string('notspecified', 'totara_reportbuilder');
        }
        if (strpos($code, '_') !== false) {
            list($langcode, $langvariant) = explode('_', $code);
        } else {
            $langcode = $code;
        }

        // Now see if we have a match in "localname (English)" format.
        if (isset($languages[$code])) {
            return $languages[$code];
        } else {
            // Not an installed language - may have been uninstalled, as last resort try the get_list_of_languages silly function.
            $langcodes = $strmgr->get_list_of_languages();
            if (isset($langcodes[$langcode])) {
                $a = new stdClass();
                $a->code = $langcode;
                $a->name = $langcodes[$langcode];
                return get_string('uninstalledlanguage', 'totara_reportbuilder', $a);
            } else {
                return get_string('unknownlanguage', 'totara_reportbuilder', $code);
            }
        }
    }

    function rb_display_user_email($email, $row, $isexport = false) {
        if (empty($email)) {
            return '';
        }
        $maildisplay = $row->maildisplay;
        $emaildisabled = $row->emailstop;

        // respect users email privacy setting
        // at some point we may want to allow admins to view anyway
        if ($maildisplay != 1) {
            return get_string('useremailprivate', 'totara_reportbuilder');
        }

        if ($isexport) {
            return $email;
        } else {
            // obfuscate email to avoid spam if printing to page
            return obfuscate_mailto($email, '', (bool) $emaildisabled);
        }
    }

    public function rb_display_user_email_unobscured($email, $row, $isexport = false) {
        if ($isexport) {
            return $email;
        } else {
            // Obfuscate email to avoid spam if printing to page.
            return obfuscate_mailto($email);
        }
    }

    function rb_display_link_program_icon($program, $row, $isexport = false) {
        global $OUTPUT;

        if ($isexport) {
            return $program;
        }

        $programid = $row->program_id;
        $programicon = !empty($row->program_icon) ? $row->program_icon : 'default';
        $programobj = (object) $row;
        $class = 'course_icon ' . totara_get_style_visibility($programobj, 'program_visible', 'program_audiencevisible');
        $icon = html_writer::empty_tag('img', array('src' => totara_get_icon($programid, TOTARA_ICON_TYPE_PROGRAM),
            'class' => $class, 'alt' => ''));
        $link = $OUTPUT->action_link(
            new moodle_url('/totara/program/view.php', array('id' => $programid)),
            $icon . $program, null, array('class' => $class)
        );
        return $link;
    }

    /**
     * Generates the HTML to display the due/expiry date of a program/certification.
     *
     * @deprecated since 2.7 - use $this->usedcomponents[] = 'totara_program' and 'displayfunc' => 'programduedate' instead
     * @param int $time     The duedate of the program
     * @param record $row   The whole row, including some required fields
     * @return html
     */
    public function rb_display_program_duedate($time, $row, $isexport = false) {
        // Get the necessary fields out of the row.
        $duedate = $time;
        $userid = $row->userid;
        $progid = $row->programid;
        $status = $row->status;
        $certifpath = isset($row->certifpath) ? $row->certifpath : null;
        $certifstatus = isset($row->certifstatus) ? $row->certifstatus : null;

        return prog_display_duedate($duedate, $progid, $userid, $certifpath, $certifstatus, $status, $isexport);
    }

    /**
     * Generates the HTML to display the due/expiry date of a certification.
     *
     * @deprecated since 2.7 - use $this->usedcomponents[] = 'totara_program' and 'displayfunc' => 'programduedate' instead
     * @param int $time     The duedate of the program
     * @param record $row   The whole row, including some required fields
     * @return html
     */
    public function rb_display_certification_duedate($time, $row) {
        global $OUTPUT, $CFG;

        if (empty($row->timeexpires)) {
            if (empty($row->timedue) || $row->timedue == COMPLETION_TIME_NOT_SET) {
                // There is no time due set.
                return get_string('duedatenotset', 'totara_program');
            } else if ($row->timedue > time() && $row->certifpath == CERTIFPATH_CERT) {
                // User is still in the first stage of certification, not overdue yet.
                return $this->rb_display_program_duedate($time, $row);
            } else {
                // Looks like the certification has expired, overdue!
                $out = '';
                $out .= userdate($row->timedue, get_string('strfdateshortmonth', 'langconfig'), 99, false);
                $out .= html_writer::empty_tag('br');
                $out .= $OUTPUT->error_text(get_string('overdue', 'totara_program'));
                return $out;
            }
        } else {
            return $this->rb_display_program_duedate($time, $row);
        }

        return '';
    }

    // Display grade along with passing grade if it is known.
    function rb_display_grade_string($item, $row) {
        $passgrade = isset($row->gradepass) ? sprintf('%d', $row->gradepass) : null;

        $usergrade = (int)$item;
        $grademin = 0;
        $grademax = 100;
        if (isset($row->grademin)) {
            $grademin = $row->grademin;
        }
        if (isset($row->grademax)) {
            $grademax = $row->grademax;
        }

        $usergrade = sprintf('%.1f', ((($usergrade - $grademin) / ($grademax - $grademin)) * 100));

        if ($item === null or $item === '') {
            return '';
        } else if ($passgrade === null) {
            return "{$usergrade}%";
        } else {
            $a = new stdClass();
            $a->grade = $usergrade;
            $a->pass = sprintf('%.1f', ((($passgrade - $grademin) / ($grademax - $grademin)) * 100));
            return get_string('gradeandgradetocomplete', 'totara_reportbuilder', $a);
        }
    }

    //
    //
    // Generic select filter methods
    //
    //

    function rb_filter_yesno_list() {
        $yn = array();
        $yn[1] = get_string('yes');
        $yn[0] = get_string('no');
        return $yn;
    }

    function rb_filter_modules_list() {
        global $DB, $OUTPUT, $CFG;

        $out = array();
        $mods = $DB->get_records('modules', array('visible' => 1), 'id', 'id, name');
        foreach ($mods as $mod) {
            if (get_string_manager()->string_exists('pluginname', $mod->name)) {
                $modname = get_string('pluginname', $mod->name);
            } else {
                continue;
            }
            if (file_exists($CFG->dirroot . '/mod/' . $mod->name . '/pix/icon.gif') ||
                file_exists($CFG->dirroot . '/mod/' . $mod->name . '/pix/icon.png')) {
                $icon = $OUTPUT->pix_icon('icon', $modname, $mod->name) . '&nbsp;';
            } else {
                $icon = '';
            }

            $out[$mod->name] = $icon . $modname;
        }
        return $out;
    }

    function rb_filter_tags_list() {
        global $DB, $OUTPUT, $CFG;

        return $DB->get_records_menu('tag', array('tagtype' => 'official'), 'name', 'id, name');
    }

    function rb_filter_organisations_list($report) {
        global $CFG, $USER, $DB;

        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/organisation/lib.php');

        $contentmode = $report->contentmode;
        $contentoptions = $report->contentoptions;
        $reportid = $report->_id;

        // show all options if no content restrictions set
        if ($contentmode == REPORT_BUILDER_CONTENT_MODE_NONE) {
            $hierarchy = new organisation();
            $hierarchy->make_hierarchy_list($orgs, null, true, false);
            return $orgs;
        }

        $baseorg = null; // default to top of tree

        $localset = false;
        $nonlocal = false;
        // are enabled content restrictions local or not?
        if (isset($contentoptions) && is_array($contentoptions)) {
            foreach ($contentoptions as $option) {
                $name = $option->classname;
                $classname = 'rb_' . $name . '_content';
                $settingname = $name . '_content';
                if (class_exists($classname)) {
                    if ($name == 'completed_org' || $name == 'current_org') {
                        if (reportbuilder::get_setting($reportid, $settingname, 'enable')) {
                            $localset = true;
                        }
                    } else {
                        if (reportbuilder::get_setting($reportid, $settingname, 'enable')) {
                            $nonlocal = true;
                        }
                    }
                }
            }
        }

        if ($contentmode == REPORT_BUILDER_CONTENT_MODE_ANY) {
            if ($localset && !$nonlocal) {
                // only restrict the org list if all content restrictions are local ones
                if ($orgid = $DB->get_field('pos_assignment', 'organisationid', array('userid' => $USER->id))) {
                    $baseorg = $orgid;
                }
            }
        } else if ($contentmode == REPORT_BUILDER_CONTENT_MODE_ALL) {
            if ($localset) {
                // restrict the org list if any content restrictions are local ones
                if ($orgid = $DB->get_field('pos_assignment', 'organisationid', array('userid' => $USER->id))) {
                    $baseorg = $orgid;
                }
            }
        }

        $hierarchy = new organisation();
        $hierarchy->make_hierarchy_list($orgs, $baseorg, true, false);

        return $orgs;

    }

    function rb_filter_positions_list() {
        global $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/position/lib.php');

        $hierarchy = new position();
        $hierarchy->make_hierarchy_list($positions, null, true, false);

        return $positions;

    }

    function rb_filter_course_categories_list() {
        global $CFG;
        require_once($CFG->libdir . '/coursecatlib.php');
        $cats = coursecat::make_categories_list();

        return $cats;
    }


    function rb_filter_competency_type_list() {
        global $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/competency/lib.php');

        $competencyhierarchy = new competency();
        $unclassified_option = array(0 => get_string('unclassified', 'totara_hierarchy'));
        $typelist = $unclassified_option + $competencyhierarchy->get_types_list();

        return $typelist;
    }


    function rb_filter_position_type_list() {
        global $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/position/lib.php');

        $positionhierarchy = new position();
        $unclassified_option = array(0 => get_string('unclassified', 'totara_hierarchy'));
        $typelist = $unclassified_option + $positionhierarchy->get_types_list();

        return $typelist;
    }


    function rb_filter_organisation_type_list() {
        global $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/organisation/lib.php');

        $organisationhierarchy = new organisation();
        $unclassified_option = array(0 => get_string('unclassified', 'totara_hierarchy'));
        $typelist = $unclassified_option + $organisationhierarchy->get_types_list();

        return $typelist;
    }

    function rb_filter_course_languages() {
        global $DB;
        $out = array();
        $langs = $DB->get_records_sql("SELECT DISTINCT lang
            FROM {course} ORDER BY lang");
        foreach ($langs as $row) {
            $out[$row->lang] = $this->rb_display_language_code($row->lang, array());
        }

        return $out;
    }

    /**
     *
     * @return array possible course types
     */
    public function rb_filter_course_types() {
        global $TOTARA_COURSE_TYPES;
        $coursetypeoptions = array();
        foreach ($TOTARA_COURSE_TYPES as $k => $v) {
            $coursetypeoptions[$v] = get_string($k, 'totara_core');
        }
        return $coursetypeoptions;
    }

    /*
     * Generate a list of options for the plan status menu.
     * @return array plan status menu options.
     */
    public function rb_filter_plan_status() {
        return array (
            DP_PLAN_STATUS_UNAPPROVED => get_string('unapproved', 'totara_plan'),
            DP_PLAN_STATUS_PENDING => get_string('pendingapproval', 'totara_plan'),
            DP_PLAN_STATUS_APPROVED => get_string('approved', 'totara_plan'),
            DP_PLAN_STATUS_COMPLETE => get_string('complete', 'totara_plan')
        );
    }

    //
    //
    // Generic grouping methods for aggregation
    //
    //

    function rb_group_count($field) {
        return "COUNT($field)";
    }

    function rb_group_unique_count($field) {
        return "COUNT(DISTINCT $field)";
    }

    function rb_group_sum($field) {
        return "SUM($field)";
    }

    function rb_group_average($field) {
        return "AVG($field)";
    }

    function rb_group_max($field) {
        return "MAX($field)";
    }

    function rb_group_min($field) {
        return "MIN($field)";
    }

    function rb_group_stddev($field) {
        return "STDDEV($field)";
    }

    // can be used to 'fake' a percentage, if matching values return 1 and
    // all other values return 0 or null
    function rb_group_percent($field) {
        global $DB;

        return $DB->sql_round("AVG($field*100.0)", 0);
    }

    /**
     * This function calls the databases native implementations of
     * group_concat where possible and requires an additional $orderby
     * variable. If you create another one you should add it to the
     * $sql_functions array() in the get_fields() function in the rb_columns class.
     *
     * @param string $field         The expression to use as the select
     * @param string $orderby       The comma deliminated fields to order by
     * @return string               The native sql for a group concat
     */
    function rb_group_sql_aggregate($field, $orderby) {
        global $DB;

        return $DB->sql_group_concat($field, $this->uniquedelimiter, $orderby);
    }

    // return list as single field, separated by commas
    function rb_group_comma_list($field) {
        return sql_group_concat($field);
    }

    // Return list as single field, without a separator delimiter.
    function rb_group_list_nodelimiter($field) {
        return sql_group_concat($field, '');
    }

    // return unique list items as single field, separated by commas
    function rb_group_comma_list_unique($field) {
        return sql_group_concat($field, ', ', true);
    }

    // return list as single field, one per line
    function rb_group_list($field) {
        return sql_group_concat($field, html_writer::empty_tag('br'));
    }

    // return unique list items as single field, one per line
    function rb_group_list_unique($field) {
        return sql_group_concat($field, html_writer::empty_tag('br'), true);
    }

    // return list as single field, separated by a line with - on (in HTML)
    function rb_group_list_dash($field) {
        return sql_group_concat($field, html_writer::empty_tag('br') . '-' . html_writer::empty_tag('br'));
    }

    //
    //
    // Methods for adding commonly used data to source definitions
    //
    //

    //
    // Wrapper functions to add columns/fields/joins in one go
    //
    //

    /**
     * Populate the hierarchymap private variable to look up Hierarchy names from ids
     * e.g. when converting a hierarchy path from ids to human-readable form
     *
     * @param array $hierarchies array of all the hierarchy types we want to populate (pos, org, comp, goal etc)
     *
     * @return boolean True
     */
    function populate_hierarchy_name_map($hierarchies) {
        global $DB;
        foreach ($hierarchies as $hierarchy) {
            $this->hierarchymap["{$hierarchy}"] = $DB->get_records_menu($hierarchy, null, 'id', 'id, fullname');
        }
        return true;
    }

    /**
     * Returns true if global report restrictions can be used with this source.
     *
     * @return bool
     */
    protected function can_global_report_restrictions_be_used() {
        global $CFG;
        return (!empty($CFG->enableglobalrestrictions) && $this->global_restrictions_supported()
                && $this->globalrestrictionset);
    }

    /**
     * Returns global restriction SQL fragment that can be used in complex joins for example.
     *
     * @return string SQL fragment
     */
    protected function get_global_report_restriction_query() {
        // First ensure that global report restrictions can be used with this source.
        if (!$this->can_global_report_restrictions_be_used()) {
            return '';
        }

        list($query, $parameters) = $this->globalrestrictionset->get_join_query();

        if ($parameters) {
            $this->globalrestrictionparams = array_merge($this->globalrestrictionparams, $parameters);
        }

        return $query;
    }

    /**
     * Adds global restriction join to the report.
     *
     * @param string $join Name of the join that provides the 'user id' field
     * @param string $field Name of user id field to join on
     * @param mixed $dependencies join dependencies
     * @return bool
     */
    protected function add_global_report_restriction_join($join, $field, $dependencies = 'base') {
        // First ensure that global report restrictions can be used with this source.
        if (!$this->can_global_report_restrictions_be_used()) {
            return false;
        }

        list($query, $parameters) = $this->globalrestrictionset->get_join_query();

        if ($query === '') {
            return false;
        }

        static $counter = 0;
        $counter++;
        $joinname = 'globalrestrjoin_' . $counter;

        $this->globalrestrictionjoins[] = new rb_join(
            $joinname,
            'INNER',
            "($query)",
            "$joinname.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_MANY,
            $dependencies
        );

        if ($parameters) {
            $this->globalrestrictionparams = array_merge($this->globalrestrictionparams, $parameters);
        }

        return true;
    }

    /**
     * Get global restriction join SQL to the report. All parameters will be inline.
     *
     * @param string $join Name of the join that provides the 'user id' field
     * @param string $field Name of user id field to join on
     * @return string
     */
    protected function get_global_report_restriction_join($join, $field) {
        // First ensure that global report restrictions can be used with this source.
        if (!$this->can_global_report_restrictions_be_used()) {
            return  '';
        }

        list($query, $parameters) = $this->globalrestrictionset->get_join_query();

        if (empty($query)) {
            return '';
        }

        if ($parameters) {
            $this->globalrestrictionparams = array_merge($this->globalrestrictionparams, $parameters);
        }

        static $counter = 0;
        $counter++;
        $joinname = 'globalinlinerestrjoin_' . $counter;

        $joinsql = " INNER JOIN ($query) $joinname ON ($joinname.id = $join.$field) ";
        return $joinsql;
    }

    /**
     * Adds the user table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'user id' field
     * @param string $field Name of user id field to join on
     * @param string $alias Use custom user table alias
     * @return boolean True
     */
    protected function add_user_table_to_joinlist(&$joinlist, $join, $field, $alias = 'auser') {
        // join uses 'auser' as name because 'user' is a reserved keyword
        $joinlist[] = new rb_join(
            $alias,
            'LEFT',
            '{user}',
            "{$alias}.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        return true;
    }


    /**
     * Adds some common user field to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $join Name of the join that provides the 'user' table
     * @param string $groupname The group to add fields to. If you are defining
     *                          a custom group name, you must define a language
     *                          string with the key "type_{$groupname}" in your
     *                          report source language file.
     * @param boolean $$addtypetoheading Add the column type to the column heading
     *                          to differentiate between fields with the same name.
     *
     * @return True
     */
    protected function add_user_fields_to_columns(&$columnoptions,
        $join='auser', $groupname = 'user', $addtypetoheading = false) {
        global $DB, $CFG;

        $usednamefields = totara_get_all_user_name_fields_join($join, null, true);
        $allnamefields = totara_get_all_user_name_fields_join($join);

        $columnoptions[] = new rb_column_option(
            $groupname,
            'fullname',
            get_string('userfullname', 'totara_reportbuilder'),
            "CASE WHEN {$join}.id IS NULL THEN NULL ELSE " . $DB->sql_concat_join("' '", $usednamefields) . " END",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text',
                  'extrafields' => $allnamefields,
                  'displayfunc' => 'user',
                  'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'namelink',
            get_string('usernamelink', 'totara_reportbuilder'),
            $DB->sql_concat_join("' '", $usednamefields),
            array(
                'joins' => $join,
                'displayfunc' => 'link_user',
                'defaultheading' => get_string('userfullname', 'totara_reportbuilder'),
                'extrafields' => array_merge(array('id' => "$join.id"), $allnamefields),
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'namelinkicon',
            get_string('usernamelinkicon', 'totara_reportbuilder'),
            $DB->sql_concat_join("' '", $usednamefields),
            array(
                'joins' => $join,
                'displayfunc' => 'link_user_icon',
                'defaultheading' => get_string('userfullname', 'totara_reportbuilder'),
                'extrafields' => array_merge(array('id' => "$join.id",
                                                   'picture' => "$join.picture",
                                                   'imagealt' => "$join.imagealt",
                                                   'email' => "$join.email"),
                                             $allnamefields),
                'style' => array('white-space' => 'nowrap'),
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'email',
            get_string('useremail', 'totara_reportbuilder'),
            // use CASE to include/exclude email in SQL
            // so search won't reveal hidden results
            "CASE WHEN $join.maildisplay <> 1 THEN '-' ELSE $join.email END",
            array(
                'joins' => $join,
                'displayfunc' => 'user_email',
                'extrafields' => array(
                    'emailstop' => "$join.emailstop",
                    'maildisplay' => "$join.maildisplay",
                ),
                'dbdatatype' => 'char',
                'outputformat' => 'text',
                'addtypetoheading' => $addtypetoheading
            )
        );
        // Only include this column if email is among fields allowed by showuseridentity setting or
        // if the current user has the 'moodle/site:config' capability.
        $canview = !empty($CFG->showuseridentity) && in_array('email', explode(',', $CFG->showuseridentity));
        $canview |= has_capability('moodle/site:config', context_system::instance());
        if ($canview) {
            $columnoptions[] = new rb_column_option(
                $groupname,
                'emailunobscured',
                get_string('useremailunobscured', 'totara_reportbuilder'),
                "$join.email",
                array(
                    'joins' => $join,
                    'displayfunc' => 'user_email_unobscured',
                    // Users must have viewuseridentity to see the
                    // unobscured email address.
                    'capability' => 'moodle/site:viewuseridentity',
                    'dbdatatype' => 'char',
                    'outputformat' => 'text',
                    'addtypetoheading' => $addtypetoheading
                )
            );
        }
        $columnoptions[] = new rb_column_option(
            $groupname,
            'lastlogin',
            get_string('userlastlogin', 'totara_reportbuilder'),
            // See MDL-22481 for why currentlogin is used instead of lastlogin
            "$join.currentlogin",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'firstaccess',
            get_string('userfirstaccess', 'totara_reportbuilder'),
            "$join.firstaccess",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_datetime',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'lang',
            get_string('userlang', 'totara_reportbuilder'),
            "$join.lang",
            array(
                'joins' => $join,
                'displayfunc' => 'language_code',
                'addtypetoheading' => $addtypetoheading
            )
        );
        // auto-generate columns for user fields
        $fields = array(
            'firstname' => get_string('userfirstname', 'totara_reportbuilder'),
            'firstnamephonetic' => get_string('userfirstnamephonetic', 'totara_reportbuilder'),
            'middlename' => get_string('usermiddlename', 'totara_reportbuilder'),
            'lastname' => get_string('userlastname', 'totara_reportbuilder'),
            'lastnamephonetic' => get_string('userlastnamephonetic', 'totara_reportbuilder'),
            'alternatename' => get_string('useralternatename', 'totara_reportbuilder'),
            'username' => get_string('username', 'totara_reportbuilder'),
            'phone1' => get_string('userphone', 'totara_reportbuilder'),
            'institution' => get_string('userinstitution', 'totara_reportbuilder'),
            'department' => get_string('userdepartment', 'totara_reportbuilder'),
            'address' => get_string('useraddress', 'totara_reportbuilder'),
            'city' => get_string('usercity', 'totara_reportbuilder'),
        );
        foreach ($fields as $field => $name) {
            $columnoptions[] = new rb_column_option(
                $groupname,
                $field,
                $name,
                "$join.$field",
                array('joins' => $join,
                      'dbdatatype' => 'char',
                      'outputformat' => 'text',
                      'addtypetoheading' => $addtypetoheading
                )
            );
        }

        $columnoptions[] = new rb_column_option(
            $groupname,
            'idnumber',
            get_string('useridnumber', 'totara_reportbuilder'),
            "$join.idnumber",
            array('joins' => $join,
                'displayfunc' => 'plaintext',
                'dbdatatype' => 'char',
                'outputformat' => 'text')
        );

        $columnoptions[] = new rb_column_option(
            $groupname,
            'id',
            get_string('userid', 'totara_reportbuilder'),
            "$join.id",
            array('joins' => $join,
                  'addtypetoheading' => $addtypetoheading
            )
        );

        // add country option
        $columnoptions[] = new rb_column_option(
            $groupname,
            'country',
            get_string('usercountry', 'totara_reportbuilder'),
            "$join.country",
            array(
                'joins' => $join,
                'displayfunc' => 'country_code',
                'addtypetoheading' => $addtypetoheading
            )
        );

        // add deleted option
        $columnoptions[] = new rb_column_option(
            $groupname,
            'deleted',
            get_string('userstatus', 'totara_reportbuilder'),
            "CASE WHEN $join.deleted = 0 and $join.suspended = 1 THEN 2 ELSE $join.deleted END",
            array(
                'joins' => $join,
                'displayfunc' => 'deleted_status',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'timecreated',
            get_string('usertimecreated', 'totara_reportbuilder'),
            "$join.timecreated",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_datetime',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'timemodified',
            get_string('usertimemodified', 'totara_reportbuilder'),
            "$join.timemodified",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_datetime',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );

        return true;
    }


    /**
     * Adds some common user field to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $groupname Name of group to filter. If you are defining
     *                          a custom group name, you must define a language
     *                          string with the key "type_{$groupname}" in your
     *                          report source language file.
     * @return True
     */
    protected function add_user_fields_to_filters(&$filteroptions, $groupname = 'user', $addtypetoheading = false) {
        global $CFG;
        // auto-generate filters for user fields
        $fields = array(
            'fullname' => get_string('userfullname', 'totara_reportbuilder'),
            'firstname' => get_string('userfirstname', 'totara_reportbuilder'),
            'firstnamephonetic' => get_string('userfirstnamephonetic', 'totara_reportbuilder'),
            'middlename' => get_string('usermiddlename', 'totara_reportbuilder'),
            'lastname' => get_string('userlastname', 'totara_reportbuilder'),
            'lastnamephonetic' => get_string('userlastnamephonetic', 'totara_reportbuilder'),
            'alternatename' => get_string('useralternatename', 'totara_reportbuilder'),
            'username' => get_string('username'),
            'idnumber' => get_string('useridnumber', 'totara_reportbuilder'),
            'phone1' => get_string('userphone', 'totara_reportbuilder'),
            'institution' => get_string('userinstitution', 'totara_reportbuilder'),
            'department' => get_string('userdepartment', 'totara_reportbuilder'),
            'address' => get_string('useraddress', 'totara_reportbuilder'),
            'city' => get_string('usercity', 'totara_reportbuilder'),
            'email' => get_string('useremail', 'totara_reportbuilder'),
        );
        // Only include this filter if email is among fields allowed by showuseridentity setting or
        // if the current user has the 'moodle/site:config' capability.
        $canview = !empty($CFG->showuseridentity) && in_array('email', explode(',', $CFG->showuseridentity));
        $canview |= has_capability('moodle/site:config', context_system::instance());
        if ($canview) {
            $fields['emailunobscured'] = get_string('useremailunobscured', 'totara_reportbuilder');
        }

        foreach ($fields as $field => $name) {
            $filteroptions[] = new rb_filter_option(
                $groupname,
                $field,
                $name,
                'text',
                array('addtypetoheading' => $addtypetoheading)
            );
        }

        // pulldown with list of countries
        $select_width_options = rb_filter_option::select_width_limiter();
        $filteroptions[] = new rb_filter_option(
            $groupname,
            'country',
            get_string('usercountry', 'totara_reportbuilder'),
            'select',
            array(
                'selectchoices' => get_string_manager()->get_list_of_countries(),
                'attributes' => $select_width_options,
                'simplemode' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );
        $filteroptions[] = new rb_filter_option(
            $groupname,
            'deleted',
            get_string('userstatus', 'totara_reportbuilder'),
            'select',
            array(
                'selectchoices' => array(0 => get_string('activeonly', 'totara_reportbuilder'),
                                         1 => get_string('deletedonly', 'totara_reportbuilder'),
                                         2 => get_string('suspendedonly', 'totara_reportbuilder')),
                'attributes' => $select_width_options,
                'simplemode' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'lastlogin',
            get_string('userlastlogin', 'totara_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'firstaccess',
            get_string('userfirstaccess', 'totara_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'timecreated',
            get_string('usertimecreated', 'totara_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'timemodified',
            get_string('usertimemodified', 'totara_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        return true;
    }


    /**
     * Adds the course table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'course id' field
     * @param string $field Name of course id field to join on
     * @param string $jointype Type of Join (INNER, LEFT, RIGHT)
     * @return boolean True
     */
    protected function add_course_table_to_joinlist(&$joinlist, $join, $field, $jointype = 'LEFT') {

        $joinlist[] = new rb_join(
            'course',
            $jointype,
            '{course}',
            "course.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );
    }

    /**
     * Adds the course table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'course id' field
     * @param string $field Name of course id field to join on
     * @param int $contextlevel Name of course id field to join on
     * @param string $jointype Type of join (INNER, LEFT, RIGHT)
     * @return boolean True
     */
    protected function add_context_table_to_joinlist(&$joinlist, $join, $field, $contextlevel, $jointype = 'LEFT') {

        $joinlist[] = new rb_join(
            'ctx',
            $jointype,
            '{context}',
            "ctx.instanceid = $join.$field AND ctx.contextlevel = $contextlevel",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );
    }


    /**
     * Adds some common course info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $join Name of the join that provides the 'course' table
     *
     * @return True
     */
    protected function add_course_fields_to_columns(&$columnoptions, $join='course') {
        global $DB;

        $columnoptions[] = new rb_column_option(
            'course',
            'fullname',
            get_string('coursename', 'totara_reportbuilder'),
            "$join.fullname",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'courselink',
            get_string('coursenamelinked', 'totara_reportbuilder'),
            "$join.fullname",
            array(
                'joins' => $join,
                'displayfunc' => 'link_course',
                'defaultheading' => get_string('coursename', 'totara_reportbuilder'),
                'extrafields' => array('course_id' => "$join.id",
                                       'course_visible' => "$join.visible",
                                       'course_audiencevisible' => "$join.audiencevisible")
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'courseexpandlink',
            get_string('courseexpandlink', 'totara_reportbuilder'),
            "$join.fullname",
            array(
                'joins' => $join,
                'displayfunc' => 'course_expand',
                'defaultheading' => get_string('coursename', 'totara_reportbuilder'),
                'extrafields' => array(
                    'course_id' => "$join.id",
                    'course_visible' => "$join.visible",
                    'course_audiencevisible' => "$join.audiencevisible"
                )
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'courselinkicon',
            get_string('coursenamelinkedicon', 'totara_reportbuilder'),
            "$join.fullname",
            array(
                'joins' => $join,
                'displayfunc' => 'link_course_icon',
                'defaultheading' => get_string('coursename', 'totara_reportbuilder'),
                'extrafields' => array(
                    'course_id' => "$join.id",
                    'course_icon' => "$join.icon",
                    'course_visible' => "$join.visible",
                    'course_audiencevisible' => "$join.audiencevisible"
                )
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'visible',
            get_string('coursevisible', 'totara_reportbuilder'),
            "$join.visible",
            array(
                'joins' => $join,
                'displayfunc' => 'yes_no'
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'audvis',
            get_string('audiencevisibility', 'totara_reportbuilder'),
            "$join.audiencevisible",
            array(
                'joins' => $join,
                'displayfunc' => 'audience_visibility'
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'icon',
            get_string('courseicon', 'totara_reportbuilder'),
            "$join.icon",
            array(
                'joins' => $join,
                'displayfunc' => 'course_icon',
                'defaultheading' => get_string('courseicon', 'totara_reportbuilder'),
                'extrafields' => array(
                    'course_name' => "$join.fullname",
                    'course_id' => "$join.id",
                )
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'shortname',
            get_string('courseshortname', 'totara_reportbuilder'),
            "$join.shortname",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'idnumber',
            get_string('courseidnumber', 'totara_reportbuilder'),
            "$join.idnumber",
            array('joins' => $join,
                  'displayfunc' => 'plaintext',
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'id',
            get_string('courseid', 'totara_reportbuilder'),
            "$join.id",
            array('joins' => $join)
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'startdate',
            get_string('coursestartdate', 'totara_reportbuilder'),
            "$join.startdate",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp'
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'name_and_summary',
            get_string('coursenameandsummary', 'totara_reportbuilder'),
            // Case used to merge even if one value is null.
            "CASE WHEN $join.fullname IS NULL THEN $join.summary
                WHEN $join.summary IS NULL THEN $join.fullname
                ELSE " . $DB->sql_concat("$join.fullname", "'" . html_writer::empty_tag('br') . "'",
                    "$join.summary") . ' END',
            array(
                'joins' => $join,
                'displayfunc' => 'tinymce_textarea',
                'extrafields' => array(
                    'filearea' => '\'summary\'',
                    'component' => '\'course\'',
                    'context' => '\'context_course\'',
                    'recordid' => "$join.id"
                )
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'summary',
            get_string('coursesummary', 'totara_reportbuilder'),
            "$join.summary",
            array(
                'joins' => $join,
                'displayfunc' => 'tinymce_textarea',
                'extrafields' => array(
                    'format' => "$join.summaryformat",
                    'filearea' => '\'summary\'',
                    'component' => '\'course\'',
                    'context' => '\'context_course\'',
                    'recordid' => "$join.id"
                ),
                'dbdatatype' => 'text',
                'outputformat' => 'text'
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'coursetypeicon',
            get_string('coursetypeicon', 'totara_reportbuilder'),
            "$join.coursetype",
            array(
                'joins' => $join,
                'displayfunc' => 'course_type_icon',
                'defaultheading' => get_string('coursetypeicon', 'totara_reportbuilder'),
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'coursetype',
            get_string('coursetype', 'totara_reportbuilder'),
            "$join.coursetype",
            array(
                'joins' => $join,
                'displayfunc' => 'course_type',
                'defaultheading' => get_string('coursetype', 'totara_reportbuilder'),
            )
        );
        // add language option
        $columnoptions[] = new rb_column_option(
            'course',
            'language',
            get_string('courselanguage', 'totara_reportbuilder'),
            "$join.lang",
            array(
                'joins' => $join,
                'displayfunc' => 'language_code'
            )
        );

        return true;
    }


    /**
     * Adds some common course filters to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_course_fields_to_filters(&$filteroptions) {
        $filteroptions[] = new rb_filter_option(
            'course',
            'fullname',
            get_string('coursename', 'totara_reportbuilder'),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'shortname',
            get_string('courseshortname', 'totara_reportbuilder'),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'idnumber',
            get_string('courseidnumber', 'totara_reportbuilder'),
            'text'
        );
        $audvisibility = get_config(null, 'audiencevisibility');
        if (empty($audvisibility)) {
            $coursevisiblestring = get_string('coursevisible', 'totara_reportbuilder');
            $audvisiblilitystring = get_string('audiencevisibilitydisabled', 'totara_reportbuilder');
        } else {
            $coursevisiblestring = get_string('coursevisibledisabled', 'totara_reportbuilder');
            $audvisiblilitystring = get_string('audiencevisibility', 'totara_reportbuilder');
        }
        $filteroptions[] = new rb_filter_option(
            'course',
            'visible',
            $coursevisiblestring,
            'select',
            array(
                'selectchoices' => array(0 => get_string('no'), 1 => get_string('yes')),
                'simplemode' => true
            )
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'audvis',
            $audvisiblilitystring,
            'select',
            array(
                'selectchoices' => array(
                    COHORT_VISIBLE_NOUSERS => get_string('visiblenousers', 'totara_cohort'),
                    COHORT_VISIBLE_ENROLLED => get_string('visibleenrolled', 'totara_cohort'),
                    COHORT_VISIBLE_AUDIENCE => get_string('visibleaudience', 'totara_cohort'),
                    COHORT_VISIBLE_ALL => get_string('visibleall', 'totara_cohort')),
                'simplemode' => true
            )
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'startdate',
            get_string('coursestartdate', 'totara_reportbuilder'),
            'date',
            array('castdate' => true)
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'name_and_summary',
            get_string('coursenameandsummary', 'totara_reportbuilder'),
            'textarea'
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'coursetype',
            get_string('coursetype', 'totara_reportbuilder'),
            'multicheck',
            array(
                'selectfunc' => 'course_types',
                'simplemode' => true,
                'showcounts' => array(
                        'joins' => array("LEFT JOIN {course} coursetype_filter ON base.id = coursetype_filter.id"),
                        'dataalias' => 'coursetype_filter',
                        'datafield' => 'coursetype')
            )
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'language',
            get_string('courselanguage', 'totara_reportbuilder'),
            'select',
            array(
                'selectfunc' => 'course_languages',
                'attributes' => rb_filter_option::select_width_limiter(),
            )
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'id',
            get_string('coursemultiitem', 'totara_reportbuilder'),
            'course_multi'
        );
        return true;
    }

    /**
     * Adds the program table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'program id' field
     * @param string $field Name of table containing program id field to join on
     * @return boolean True
     */
    protected function add_program_table_to_joinlist(&$joinlist, $join, $field) {

        $joinlist[] = new rb_join(
            'program',
            'LEFT',
            '{prog}',
            "program.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );
    }


    /**
     * Adds some common program info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $join Name of the join that provides the 'program' table
     * @param string $langfile Source for translation, totara_program or totara_certification
     *
     * @return True
     */
    protected function add_program_fields_to_columns(&$columnoptions, $join = 'program', $langfile = 'totara_program') {
        global $DB;

        $columnoptions[] = new rb_column_option(
            'prog',
            'fullname',
            get_string('programname', $langfile),
            "$join.fullname",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'shortname',
            get_string('programshortname', $langfile),
            "$join.shortname",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'idnumber',
            get_string('programidnumber', $langfile),
            "$join.idnumber",
            array('joins' => $join,
                  'displayfunc' => 'plaintext',
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'id',
            get_string('programid', $langfile),
            "$join.id",
            array('joins' => $join)
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'summary',
            get_string('programsummary', $langfile),
            "$join.summary",
            array(
                'joins' => $join,
                'displayfunc' => 'tinymce_textarea',
                'extrafields' => array(
                    'filearea' => '\'summary\'',
                    'component' => '\'totara_program\'',
                    'context' => '\'context_program\'',
                    'recordid' => "$join.id",
                    'fileid' => 0
                ),
                'dbdatatype' => 'text',
                'outputformat' => 'text'
            )
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'availablefrom',
            get_string('availablefrom', $langfile),
            "$join.availablefrom",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp'
            )
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'availableuntil',
            get_string('availableuntil', $langfile),
            "$join.availableuntil",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp'
            )
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'proglinkicon',
            get_string('prognamelinkedicon', $langfile),
            "$join.fullname",
            array(
                'joins' => $join,
                'displayfunc' => 'link_program_icon',
                'defaultheading' => get_string('programname', $langfile),
                'extrafields' => array(
                    'program_id' => "$join.id",
                    'program_icon' => "$join.icon",
                    'program_visible' => "$join.visible",
                    'program_audiencevisible' => "$join.audiencevisible",
                )
            )
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'progexpandlink',
            get_string('programexpandlink', $langfile),
            "$join.fullname",
            array(
                'joins' => $join,
                'displayfunc' => 'program_expand',
                'defaultheading' => get_string('programname', $langfile),
                'extrafields' => array(
                    'prog_id' => "$join.id",
                    'prog_visible' => "$join.visible",
                    'prog_audiencevisible' => "$join.audiencevisible",
                    'prog_certifid' => "$join.certifid")
            )
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'visible',
            get_string('programvisible', $langfile),
            "$join.visible",
            array(
                'joins' => $join,
                'displayfunc' => 'yes_no'
            )
        );
        $columnoptions[] = new rb_column_option(
            'prog',
            'audvis',
            get_string('audiencevisibility', 'totara_reportbuilder'),
            "$join.audiencevisible",
            array(
                'joins' => $join,
                'displayfunc' => 'audience_visibility'
            )
        );
        return true;
    }

    /**
     * Adds some common program filters to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $langfile Source for translation, totara_program or totara_certification
     * @return True
     */
    protected function add_program_fields_to_filters(&$filteroptions, $langfile = 'totara_program') {
        $filteroptions[] = new rb_filter_option(
            'prog',
            'fullname',
            get_string('programname', $langfile),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'prog',
            'shortname',
            get_string('programshortname', $langfile),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'prog',
            'idnumber',
            get_string('programidnumber', $langfile),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'prog',
            'summary',
            get_string('programsummary', $langfile),
            'textarea'
        );
        $filteroptions[] = new rb_filter_option(
            'prog',
            'availablefrom',
            get_string('availablefrom', $langfile),
            'date'
        );
        $filteroptions[] = new rb_filter_option(
            'prog',
            'availableuntil',
            get_string('availableuntil', $langfile),
            'date'
        );
        return true;
    }

    /**
     * Adds the certification table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'certif id' field
     * @param string $field Name of table containing program id field to join on
     */
    protected function add_certification_table_to_joinlist(&$joinlist, $join, $field) {

        $joinlist[] = new rb_join(
            'certif',
            'inner',
            '{certif}',
            "certif.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );
    }

    /**
     * Adds some common certification info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $join Name of the join that provides the 'program' table
     * @param string $langfile Source for translation, totara_program or totara_certification
     *
     * @return Boolean
     */
    protected function add_certification_fields_to_columns(&$columnoptions, $join = 'certif', $langfile = 'totara_certification') {
        $columnoptions[] = new rb_column_option(
            'certif',
            'recertifydatetype',
            get_string('recertdatetype', 'totara_certification'),
            "$join.recertifydatetype",
            array(
                'joins' => $join,
                'displayfunc' => 'recertifydatetype',
            )
        );

        $columnoptions[] = new rb_column_option(
            'certif',
            'activeperiod',
            get_string('activeperiod', 'totara_certification'),
            "$join.activeperiod",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );

        $columnoptions[] = new rb_column_option(
            'certif',
            'windowperiod',
            get_string('windowperiod', 'totara_certification'),
            "$join.windowperiod",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );

        return true;
    }

    public function rb_display_recertifydatetype($recertifydatetype, $row) {
        switch ($recertifydatetype) {
            case CERTIFRECERT_COMPLETION:
                return get_string('editdetailsrccmpl', 'totara_certification');
            case CERTIFRECERT_EXPIRY:
                return get_string('editdetailsrcexp', 'totara_certification');
            case CERTIFRECERT_FIXED:
                return get_string('editdetailsrcfixed', 'totara_certification');
        }
        return "Error - Recertification method not found";
    }

    /**
     * Adds some common certification filters to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $langfile Source for translation, totara_program or totara_certification
     * @return boolean
     */
    protected function add_certification_fields_to_filters(&$filteroptions, $langfile = 'totara_certification') {

        $filteroptions[] = new rb_filter_option(
            'certif',
            'recertifydatetype',
            get_string('recertdatetype', 'totara_certification'),
            'select',
            array(
                'selectfunc' => 'recertifydatetype',
            )
        );

        $filteroptions[] = new rb_filter_option(
            'certif',
            'activeperiod',
            get_string('activeperiod', 'totara_certification'),
            'text'
        );

        $filteroptions[] = new rb_filter_option(
            'certif',
            'windowperiod',
            get_string('windowperiod', 'totara_certification'),
            'text'
        );

        return true;
    }

    public function rb_filter_recertifydatetype() {
        return array(
            CERTIFRECERT_COMPLETION => get_string('editdetailsrccmpl', 'totara_certification'),
            CERTIFRECERT_EXPIRY => get_string('editdetailsrcexp', 'totara_certification'),
            CERTIFRECERT_FIXED => get_string('editdetailsrcfixed', 'totara_certification')
        );
    }

    /**
     * Adds the course_category table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include course_category
     * @param string $join Name of the join that provides the 'course' table
     * @param string $field Name of category id field to join on
     * @return boolean True
     */
    protected function add_course_category_table_to_joinlist(&$joinlist,
        $join, $field) {

        $joinlist[] = new rb_join(
            'course_category',
            'LEFT',
            '{course_categories}',
            "course_category.id = $join.$field",
            REPORT_BUILDER_RELATION_MANY_TO_ONE,
            $join
        );

        return true;
    }


    /**
     * Adds some common course category info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $catjoin Name of the join that provides the
     *                        'course_categories' table
     * @param string $coursejoin Name of the join that provides the
     *                           'course' table
     * @return True
     */
    protected function add_course_category_fields_to_columns(&$columnoptions,
        $catjoin='course_category', $coursejoin='course', $column='coursecount') {
        $columnoptions[] = new rb_column_option(
            'course_category',
            'name',
            get_string('coursecategory', 'totara_reportbuilder'),
            "$catjoin.name",
            array('joins' => $catjoin,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'course_category',
            'namelink',
            get_string('coursecategorylinked', 'totara_reportbuilder'),
            "$catjoin.name",
            array(
                'joins' => $catjoin,
                'displayfunc' => 'link_course_category',
                'defaultheading' => get_string('category', 'totara_reportbuilder'),
                'extrafields' => array('cat_id' => "$catjoin.id",
                                        'cat_visible' => "$catjoin.visible",
                                        $column => "{$catjoin}.{$column}")
            )
        );
        $columnoptions[] = new rb_column_option(
            'course_category',
            'id',
            get_string('coursecategoryid', 'totara_reportbuilder'),
            "$coursejoin.category",
            array('joins' => $coursejoin)
        );
        return true;
    }


    /**
     * Adds some common course category filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_course_category_fields_to_filters(&$filteroptions) {
        $filteroptions[] = new rb_filter_option(
            'course_category',
            'id',
            get_string('coursecategory', 'totara_reportbuilder'),
            'select',
            array(
                'selectfunc' => 'course_categories_list',
                'attributes' => rb_filter_option::select_width_limiter(),
            )
        );
        $filteroptions[] = new rb_filter_option(
            'course_category',
            'path',
            get_string('coursecategorymultichoice', 'totara_reportbuilder'),
            'category',
            array(),
            'course_category.path',
            'course_category'
        );
        return true;
    }


    /**
     * Adds the pos_assignment, pos and org tables to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the 'user' table
     * @param string $field Name of user id field to join on
     * @return boolean True
     */
    protected function add_position_tables_to_joinlist(&$joinlist,
        $join, $field) {

        global $CFG;

        // to get access to position type constants
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/position/lib.php');

        $joinlist[] =new rb_join(
            'position_assignment',
            'LEFT',
            '{pos_assignment}',
            "(position_assignment.userid = $join.$field AND " .
            'position_assignment.type = ' . POSITION_TYPE_PRIMARY . ')',
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        $joinlist[] = new rb_join(
            'organisation',
            'LEFT',
            '{org}',
            'organisation.id = position_assignment.organisationid',
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            'position_assignment'
        );

        $joinlist[] = new rb_join(
            'position',
            'LEFT',
            '{pos}',
            'position.id = position_assignment.positionid',
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            'position_assignment'
        );

        $joinlist[] = new rb_join(
                'pos_type',
                'LEFT',
                '{pos_type}',
                'position.typeid = pos_type.id',
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'position'
        );

        $joinlist[] = new rb_join(
                'org_type',
                'LEFT',
                '{org_type}',
                'organisation.typeid = org_type.id',
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'organisation'
        );

        $joinlist[] = new rb_join(
            'org_framework',
            'LEFT',
            '{org_framework}',
            'organisation.frameworkid = org_framework.id',
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            'organisation'
        );

        $joinlist[] = new rb_join(
            'pos_framework',
            'LEFT',
            '{pos_framework}',
            'position.frameworkid = pos_framework.id',
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            'position'
        );


        return true;
    }


    /**
     * Adds some common user position info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $posassign Name of the join that provides the
     *                          'pos_assignment' table.
     * @param string $org Name of the join that provides the 'org' table.
     * @param string $pos Name of the join that provides the 'pos' table.
     *
     * @return True
     */
    protected function add_position_fields_to_columns(&$columnoptions,
        $posassign='position_assignment',
        $org='organisation', $pos='position') {

        $columnoptions[] = new rb_column_option(
            'user',
            'organisationid',
            get_string('usersorgid', 'totara_reportbuilder'),
            "$posassign.organisationid",
            array('joins' => $posassign, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'organisationid2',
            get_string('usersorgid', 'totara_reportbuilder'),
            "$posassign.organisationid",
            array('joins' => $posassign, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'organisationidnumber',
            get_string('usersorgidnumber', 'totara_reportbuilder'),
            "$org.idnumber",
            array('joins' => $org,
                  'selectable' => true,
                  'displayfunc' => 'plaintext',
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'organisationpath',
            get_string('usersorgpathids', 'totara_reportbuilder'),
            "$org.path",
            array('joins' => $org, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'organisation',
            get_string('usersorgname', 'totara_reportbuilder'),
            "$org.fullname",
            array('joins' => $org,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'org_type',
            get_string('organisationtype', 'totara_reportbuilder'),
            'org_type.fullname',
            array(
                'joins' => 'org_type',
                'dbdatatype' => 'char',
                'outputformat' => 'text'
            )
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'org_type_id',
            get_string('organisationtypeid', 'totara_reportbuilder'),
            'organisation.typeid',
            array('joins' => $org, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'positionid',
            get_string('usersposid', 'totara_reportbuilder'),
            "$posassign.positionid",
            array('joins' => $posassign, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'positionid2',
            get_string('usersposid', 'totara_reportbuilder'),
            "$posassign.positionid",
            array('joins' => $posassign, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'positionidnumber',
            get_string('usersposidnumber', 'totara_reportbuilder'),
            "$pos.idnumber",
            array('joins' => $pos,
                  'selectable' => true,
                  'displayfunc' => 'plaintext',
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'positionpath',
            get_string('userspospathids', 'totara_reportbuilder'),
            "$pos.path",
            array('joins' => $pos, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'position',
            get_string('userspos', 'totara_reportbuilder'),
            "$pos.fullname",
            array('joins' => $pos,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'pos_type',
            get_string('positiontype', 'totara_reportbuilder'),
            'pos_type.fullname',
            array(
                'joins' => 'pos_type',
                 'dbdatatype' => 'char',
                'outputformat' => 'text'
            )
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'pos_type_id',
            get_string('positiontypeid', 'totara_reportbuilder'),
            'position.typeid',
            array('joins' => $pos, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'title',
            get_string('usersjobtitle', 'totara_reportbuilder'),
            "$posassign.fullname",
            array('joins' => $posassign,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'posstartdate',
            get_string('posstartdate', 'totara_reportbuilder'),
            "$posassign.timevalidfrom",
            array(
                'joins' => $posassign,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp',
            )
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'posenddate',
            get_string('posenddate', 'totara_reportbuilder'),
            "$posassign.timevalidto",
            array(
                'joins' => $posassign,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp',
            )
        );

        $columnoptions[] = new rb_column_option(
            'user',
            'positionframework',
            get_string('positionframework', 'totara_reportbuilder'),
            'pos_framework.fullname',
            array(
                'joins' => array(
                    $posassign,
                    'pos_framework'
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'user',
            'positionframeworkid',
            get_string('positionframeworkid', 'totara_reportbuilder'),
            'pos_framework.id',
            array(
                'joins' => array(
                    $posassign,
                    'pos_framework'
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'user',
            'positionframeworkidnumber',
            get_string('positionframeworkidnumber', 'totara_reportbuilder'),
            'pos_framework.idnumber',
            array(
                'joins' => array(
                    $posassign,
                    'pos_framework'
                ),
                'displayfunc' => 'plaintext'
            )
        );

        $columnoptions[] = new rb_column_option(
            'user',
            'positionframeworkdescription',
            get_string('positionframeworkdescription', 'totara_reportbuilder'),
            'pos_framework.description',
            array(
                'joins' => array(
                    $posassign,
                    'pos_framework'
                ),
                'displayfunc' => 'tinymce_textarea',
                'extrafields' => array(
                    'filearea' => '\'pos_framework\'',
                    'component' => '\'totara_hierarchy\'',
                    'fileid' => 'pos_framework.id'
                ),
                'dbdatatype' => 'text',
                'outputformat' => 'text'
            )
        );

        $columnoptions[] = new rb_column_option(
            'user',
            'organisationframework',
            get_string('organisationframework', 'totara_reportbuilder'),
            'org_framework.fullname',
            array(
                'joins' => array(
                    $org,
                    'org_framework'
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'user',
            'organisationframeworkid',
            get_string('organisationframeworkid', 'totara_reportbuilder'),
            'org_framework.id',
            array(
                'joins' => array(
                    $org,
                    'org_framework'
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'user',
            'organisationframeworkidnumber',
            get_string('organisationframeworkidnumber', 'totara_reportbuilder'),
            'org_framework.idnumber',
            array(
                'joins' => array(
                    $org,
                    'org_framework'
                ),
                'displayfunc' => 'plaintext'
            )
        );

        $columnoptions[] = new rb_column_option(
            'user',
            'organisationframeworkdescription',
            get_string('organisationframeworkdescription', 'totara_reportbuilder'),
            'org_framework.description',
            array(
                'joins' => array(
                    $org,
                    'org_framework'
                ),
                'displayfunc' => 'tinymce_textarea',
                'extrafields' => array(
                    'filearea' => '\'org_framework\'',
                    'component' => '\'totara_hierarchy\'',
                    'fileid' => 'org_framework.id'
                ),
                'dbdatatype' => 'text',
                'outputformat' => 'text'
            )
        );


        return true;
    }


    /**
     * Adds some common user position filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_position_fields_to_filters(&$filteroptions) {
        $filteroptions[] = new rb_filter_option(
            'user',
            'title',
            get_string('usersjobtitle', 'totara_reportbuilder'),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'positionpath',
            get_string('userspos', 'totara_reportbuilder'),
            'hierarchy',
            array(
                'hierarchytype' => 'pos',
            )
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'positionid',
            get_string('usersposbasic', 'totara_reportbuilder'),
            'select',
            array(
                'selectfunc' => 'positions_list',
                'attributes' => rb_filter_option::select_width_limiter(),
            )
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'positionid2',
            get_string('usersposmulti', 'totara_reportbuilder'),
            'hierarchy_multi',
            array(
                'hierarchytype' => 'pos',
            )
        );
        $filteroptions[] = new rb_filter_option(
                'user',
                'pos_type_id',
                get_string('positiontype', 'totara_reportbuilder'),
                'select',
                array(
                    'selectfunc' => 'position_type_list',
                    'attributes' => rb_filter_option::select_width_limiter(),
                )
        );
        $filteroptions[] = new rb_filter_option(
                'user',
                'posstartdate',
                get_string('posstartdate', 'totara_reportbuilder'),
                'date'
        );
        $filteroptions[] = new rb_filter_option(
                'user',
                'posenddate',
                get_string('posenddate', 'totara_reportbuilder'),
                'date'
        );

        $filteroptions[] = new rb_filter_option(
                'user',
                'positionframework',
                get_string('positionframework', 'totara_reportbuilder'),
                'text'
            );
        $filteroptions[] = new rb_filter_option(
                'user',
                'positionframeworkid',
                get_string('positionframeworkid', 'totara_reportbuilder'),
                'number'
        );
        $filteroptions[] = new rb_filter_option(
                'user',
                'positionframeworkidnumber',
                get_string('positionframeworkidnumber', 'totara_reportbuilder'),
                'text'
        );
        $filteroptions[] = new rb_filter_option(
                'user',
                'positionframeworkdescription',
                get_string('positionframeworkdescription', 'totara_reportbuilder'),
                'text'
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'organisationpath',
            get_string('usersorg', 'totara_reportbuilder'),
            'hierarchy',
            array(
                'hierarchytype' => 'org',
            )
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'organisationid',
            get_string('usersorgbasic', 'totara_reportbuilder'),
            'select',
            array(
                'selectfunc' => 'organisations_list',
                'attributes' => rb_filter_option::select_width_limiter(),
            )
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'organisationid2',
            get_string('usersorgmulti', 'totara_reportbuilder'),
            'hierarchy_multi',
            array(
                'hierarchytype' => 'org',
            )
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'org_type_id',
            get_string('organisationtype', 'totara_reportbuilder'),
            'select',
            array(
                'selectfunc' => 'organisation_type_list',
                'attributes' => rb_filter_option::select_width_limiter(),
            )
        );
        $filteroptions[] = new rb_filter_option(
                'user',
                'organisationframework',
                get_string('organisationframework', 'totara_reportbuilder'),
                'text'
            );
        $filteroptions[] = new rb_filter_option(
                'user',
                'organisationframeworkid',
                get_string('organisationframeworkid', 'totara_reportbuilder'),
                'text'
        );
        $filteroptions[] = new rb_filter_option(
                'user',
                'organisationframeworkidnumber',
                get_string('organisationframeworkidnumber', 'totara_reportbuilder'),
                'text'
        );
        $filteroptions[] = new rb_filter_option(
                'user',
                'organisationframeworkdescription',
                get_string('organisationframeworkdescription', 'totara_reportbuilder'),
                'text'
        );

        return true;
    }

    /**
     * Converts a list to an array given a list and a separator
     * duplicate values are ignored
     *
     * Example;
     * list_to_array('some-thing-some', '-'); =>
     * array('some' => 'some', 'thing' => 'thing');
     *
     * @param string $list List of items
     * @param string $sep Symbol or string that separates list items
     * @return array $result array of list items
     */
    function list_to_array($list, $sep) {
        $base = explode($sep, $list);
        return array_combine($base, $base);
    }

    /**
     * Generic function for adding custom fields to the reports
     * Intentionally optimized into one function to reduce number of db queries
     *
     * @param string $cf_prefix - prefix for custom field table e.g. everything before '_info_field' or '_info_data'
     * @param string $join - join table in joinlist used as a link to main query
     * @param string $joinfield - joinfield in data table used to link with main table
     * @param array $joinlist - array of joins passed by reference
     * @param array $columnoptions - array of columnoptions, passed by reference
     * @param array $filteroptions - array of filters, passed by reference
     */
    protected function add_custom_fields_for($cf_prefix, $join, $joinfield,
        array &$joinlist, array &$columnoptions, array &$filteroptions) {

        global $CFG, $DB;

        $seek = false;
        foreach ($joinlist as $object) {
            $seek = ($object->name == $join);
            if ($seek) {
                break;
            }
        }

        if ($join == 'base') {
            $seek = 'base';
        }

        if (!$seek) {
            $a = new stdClass();
            $a->join = $join;
            $a->source = get_class($this);
            throw new ReportBuilderException(get_string('error:missingdependencytable', 'totara_reportbuilder', $a));
        }

        // Build the table names for this sort of custom field data.
        $fieldtable = $cf_prefix.'_info_field';
        $datatable = $cf_prefix.'_info_data';

        // Check if there are any visible custom fields of this type.
        if ($cf_prefix == 'user') {
            // For user fields include them all - below we require moodle/user:update to actually display the column.
            $items = $DB->get_recordset($fieldtable);
        } else {
            $items = $DB->get_recordset($fieldtable, array('hidden' => '0'));
        }

        if (empty($items)) {
            $items->close();
            return false;
        }

        foreach ($items as $record) {
            $id   = $record->id;
            $joinname = "{$cf_prefix}_{$id}";
            $value = "custom_field_{$id}";
            $name = isset($record->fullname) ? $record->fullname : $record->name;
            $column_options = array('joins' => $joinname);
            // If profile field isn't available to everyone require a capability to display the column.
            if ($cf_prefix == 'user' && $record->visible === PROFILE_VISIBLE_NONE) {
                $column_options['capability'] = 'moodle/user:viewalldetails';
            }
            $filtertype = 'text'; // default filter type
            $filter_options = array();

            $columnsql = "{$joinname}.data";

            if ($record->datatype == 'multiselect') {
                $filtertype = 'multicheck';

                require_once($CFG->dirroot . '/totara/customfield/definelib.php');
                require_once($CFG->dirroot . '/totara/customfield/field/multiselect/field.class.php');
                require_once($CFG->dirroot . '/totara/customfield/field/multiselect/define.class.php');

                $cfield = new customfield_define_multiselect();
                $cfield->define_load_preprocess($record);
                $filter_options['concat'] = true;
                $filter_options['simplemode'] = true;

                $joinlist[] = new rb_join(
                        $joinname,
                        'LEFT',
                        '(SELECT '.sql_group_concat(sql_cast2char('cfidp.value'), '|', true).' AS data,
                                 cfid.'.$joinfield.' AS joinid, '.sql_cast2char('cfid.data').' AS jsondata
                            FROM {'.$datatable.'} cfid
                            LEFT JOIN {'.$datatable.'_param} cfidp ON (cfidp.dataid = cfid.id)
                           WHERE cfid.fieldid = '.$id.'
                           GROUP BY cfid.'.$joinfield.', '.sql_cast2char('cfid.data').')',
                        "$joinname.joinid = {$join}.id ",
                        REPORT_BUILDER_RELATION_ONE_TO_ONE,
                        $join
                    );

                $columnoptions[] = new rb_column_option(
                        $cf_prefix,
                        $value.'_icon',
                        get_string('multiselectcolumnicon', 'totara_customfield', $name),
                        "$joinname.data",
                        array('joins' => $joinname,
                              'displayfunc' => 'customfield_multiselect_icon',
                              'extrafields' => array(
                                  "{$cf_prefix}_{$value}_icon_json" => "{$joinname}.jsondata"
                              ),
                              'defaultheading' => $name
                        )
                    );

                $columnoptions[] = new rb_column_option(
                        $cf_prefix,
                        $value.'_text',
                        get_string('multiselectcolumntext', 'totara_customfield', $name),
                        "$joinname.data",
                        array('joins' => $joinname,
                              'displayfunc' => 'customfield_multiselect_text',
                              'extrafields' => array(
                                  "{$cf_prefix}_{$value}_text_json" => "{$joinname}.jsondata"
                              ),
                              'defaultheading' => $name
                        )
                    );

                $selectchoices = array();
                foreach ($record->multiselectitem as $selectchoice) {
                    $selectchoices[md5($selectchoice['option'])] = format_string($selectchoice['option']);
                }
                $filter_options['selectchoices'] = $selectchoices;
                $filter_options['showcounts'] = array(
                        'joins' => array(
                                "LEFT JOIN (SELECT id, {$joinfield} FROM {{$cf_prefix}_info_data} " .
                                            "WHERE fieldid = {$id}) {$cf_prefix}_idt_{$id} " .
                                       "ON base_{$cf_prefix}_idt_{$id} = {$cf_prefix}_idt_{$id}.{$joinfield}",
                                "LEFT JOIN {{$cf_prefix}_info_data_param} {$cf_prefix}_idpt_{$id} " .
                                       "ON {$cf_prefix}_idt_{$id}.id = {$cf_prefix}_idpt_{$id}.dataid"),
                        'basefields' => array("{$join}.id AS base_{$cf_prefix}_idt_{$id}"),
                        'dependency' => $join,
                        'dataalias' => "{$cf_prefix}_idpt_{$id}",
                        'datafield' => "value");
                $filteroptions[] = new rb_filter_option(
                        $cf_prefix,
                        $value.'_text',
                        get_string('multiselectcolumntext', 'totara_customfield', $name),
                        $filtertype,
                        $filter_options
                    );

                $iconselectchoices = array();
                foreach ($record->multiselectitem as $selectchoice) {
                    $iconselectchoices[md5($selectchoice['option'])] =
                            customfield_multiselect::get_item_string(format_string($selectchoice['option']), $selectchoice['icon'], 'list-icon');
                }
                $filter_options['selectchoices'] = $iconselectchoices;
                $filter_options['showcounts'] = array(
                        'joins' => array(
                                "LEFT JOIN (SELECT id, {$joinfield} FROM {{$cf_prefix}_info_data} " .
                                            "WHERE fieldid = {$id}) {$cf_prefix}_idi_{$id} " .
                                       "ON base_{$cf_prefix}_idi_{$id} = {$cf_prefix}_idi_{$id}.{$joinfield}",
                                "LEFT JOIN {{$cf_prefix}_info_data_param} {$cf_prefix}_idpi_{$id} " .
                                       "ON {$cf_prefix}_idi_{$id}.id = {$cf_prefix}_idpi_{$id}.dataid"),
                        'basefields' => array("{$join}.id AS base_{$cf_prefix}_idi_{$id}"),
                        'dependency' => $join,
                        'dataalias' => "{$cf_prefix}_idpi_{$id}",
                        'datafield' => "value");
                $filteroptions[] = new rb_filter_option(
                        $cf_prefix,
                        $value.'_icon',
                        get_string('multiselectcolumnicon', 'totara_customfield', $name),
                        $filtertype,
                        $filter_options
                    );
                continue;
            }

            switch ($record->datatype) {
                case 'file':
                    $column_options['displayfunc'] = 'customfield_file';
                    $column_options['extrafields'] = array(
                            "{$cf_prefix}_custom_field_{$id}_itemid" => "{$joinname}.id"
                    );
                    break;

                case 'textarea':
                    $filtertype = 'textarea';
                    if ($cf_prefix == 'user') {
                        $column_options['displayfunc'] = 'userfield_textarea';
                    } else {
                        $column_options['displayfunc'] = 'customfield_textarea';
                    }
                    $column_options['extrafields'] = array(
                            "{$cf_prefix}_custom_field_{$id}_itemid" => "{$joinname}.id"
                    );
                    if ($cf_prefix === 'user') {
                        $column_options['extrafields']["{$cf_prefix}_custom_field_{$id}_format"] = "{$joinname}.dataformat";
                    }
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    break;

                case 'menu':
                    $default = $record->defaultdata;
                    if ($default !== '' and $default !== null) {
                        // Note: there is no safe way to inject the default value into the query, use extra join instead.
                        $fieldjoin = $joinname . '_fielddefault';
                        $joinlist[] = new rb_join(
                            $fieldjoin,
                            'INNER',
                            "{{$fieldtable}}",
                            "{$fieldjoin}.id = {$id}",
                            REPORT_BUILDER_RELATION_MANY_TO_ONE
                        );
                        $columnsql = "COALESCE({$columnsql}, {$fieldjoin}.defaultdata)";
                        $column_options['joins'] = (array)$column_options['joins'];
                        $column_options['joins'][] = $fieldjoin;
                    }
                    $filtertype = 'menuofchoices';
                    $filter_options['selectchoices'] = $this->list_to_array($record->param1,"\n");
                    $filter_options['simplemode'] = true;
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    break;

                case 'checkbox':
                    $default = $record->defaultdata;
                    $columnsql = "CASE WHEN ( {$columnsql} IS NULL OR {$columnsql} = '' ) THEN {$default} ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    $filtertype = 'select';
                    $filter_options['selectchoices'] = array(0 => get_string('no'), 1 => get_string('yes'));
                    $filter_options['simplemode'] = true;
                    $column_options['displayfunc'] = 'yes_no';
                    break;

                case 'datetime':
                    $filtertype = 'date';
                    $columnsql = "CASE WHEN {$columnsql} = '' THEN NULL ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    if ($record->param3) {
                        $column_options['displayfunc'] = 'nice_datetime';
                        $column_options['dbdatatype'] = 'timestamp';
                        $filter_options['includetime'] = true;
                    } else {
                        $column_options['displayfunc'] = 'nice_date';
                        $column_options['dbdatatype'] = 'timestamp';
                    }
                    break;

                case 'date': // Midday in UTC, date without timezone.
                    $filtertype = 'date';
                    $columnsql = "CASE WHEN {$columnsql} = '' THEN NULL ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    $column_options['displayfunc'] = 'nice_date_no_timezone';
                    $column_options['dbdatatype'] = 'timestamp';
                    break;

                case 'text':
                    $default = $record->defaultdata;
                    if ($default !== '' and $default !== null) {
                        // Note: there is no safe way to inject the default value into the query, use extra join instead.
                        $fieldjoin = $joinname . '_fielddefault';
                        $joinlist[] = new rb_join(
                            $fieldjoin,
                            'INNER',
                            "{{$fieldtable}}",
                            "{$fieldjoin}.id = {$id}",
                            REPORT_BUILDER_RELATION_MANY_TO_ONE
                        );
                        $columnsql = "COALESCE({$columnsql}, {$fieldjoin}.defaultdata)";
                        $column_options['joins'] = (array)$column_options['joins'];
                        $column_options['joins'][] = $fieldjoin;
                    }
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    break;

                default:
                    // Unsupported customfields.
                    continue 2;
            }

            if ($cf_prefix === 'user') {
                $column_options['displayfunc'] = 'user_customfield';
                $column_options['extracontext']['visible'] = $record->visible;
                $column_options['extracontext']['datatype'] = $record->datatype;
            }

            $joinlist[] = new rb_join(
                    $joinname,
                    'LEFT',
                    "{{$datatable}}",
                    "{$joinname}.{$joinfield} = {$join}.id AND {$joinname}.fieldid = {$id}",
                    REPORT_BUILDER_RELATION_ONE_TO_ONE,
                    $join
                );
            $columnoptions[] = new rb_column_option(
                    $cf_prefix,
                    $value,
                    $name,
                    $columnsql,
                    $column_options
                );

            if ($record->datatype == 'file') {
                // No filter options for files yet.
                continue;
            } else {
                $filteroptions[] = new rb_filter_option(
                        $cf_prefix,
                        $value,
                        $name,
                        $filtertype,
                        $filter_options
                    );
            }

        }

        $items->close();

        return true;

    }

    /**
     * Adds user custom fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @param string $basetable
     * @return boolean
     */
    protected function add_custom_user_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions, $basetable = 'auser') {
        return $this->add_custom_fields_for('user',
                                            $basetable,
                                            'userid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);
    }


    /**
     * Adds course custom fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @param string $basetable
     * @return boolean
     */
    protected function add_custom_course_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions, $basetable = 'course') {
        return $this->add_custom_fields_for('course',
                                            $basetable,
                                            'courseid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);
    }

    /**
     * Adds course custom fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @param string $basetable
     * @return boolean
     */
    protected function add_custom_prog_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions, $basetable = 'prog') {
        return $this->add_custom_fields_for('prog',
                                            $basetable,
                                            'programid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);
    }

    /**
     * Adds custom organisation fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @return boolean
     */
    protected function add_custom_organisation_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions) {
        return $this->add_custom_fields_for('org_type',
                                            'organisation',
                                            'organisationid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);
    }

    /**
     * Adds custom goal fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @return boolean
     */
    protected function add_custom_goal_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions) {
        return $this->add_custom_fields_for('goal_type',
                                            'goal',
                                            'goalid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);
    }

    /**
     * Adds custom personal goal fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @return boolean
     */
    protected function add_custom_personal_goal_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions) {
        return $this->add_custom_fields_for('goal_user',
                                            'goal_personal',
                                            'goal_userid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);
    }


    /**
     * Adds custom position fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @return boolean
     */
    protected function add_custom_position_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions) {
        return $this->add_custom_fields_for('pos_type',
                                            'position',
                                            'positionid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);

    }


    /**
     * Adds custom competency fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @return boolean
     */
    protected function add_custom_competency_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions) {
        return $this->add_custom_fields_for('comp_type',
                                            'competency',
                                            'competencyid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);

    }

    /**
     * Adds the manager_role_assignment and manager tables to the $joinlist
     * array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'position_assignment' table
     * @param string $field Name of reportstoid field to join on
     * @return boolean True
     */
    protected function add_manager_tables_to_joinlist(&$joinlist,
        $join, $field) {

        global $CFG;

        // only include these joins if the manager role is defined
        if ($managerroleid = $CFG->managerroleid) {
            $joinlist[] = new rb_join(
                'manager_role_assignment',
                'LEFT',
                '{role_assignments}',
                "(manager_role_assignment.id = $join.$field" .
                    ' AND manager_role_assignment.roleid = ' .
                    $managerroleid . ')',
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'position_assignment'
            );
            $joinlist[] = new rb_join(
                'manager',
                'LEFT',
                '{user}',
                'manager.id = manager_role_assignment.userid',
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'manager_role_assignment'
            );
        }

        return true;
    }


    /**
     * Adds some common user manager info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $manager Name of the join that provides the
     *                          'manager' table.
     * @param string $org Name of the join that provides the 'org' table.
     * @param string $pos Name of the join that provides the 'pos' table.
     *
     * @return True
     */
    protected function add_manager_fields_to_columns(&$columnoptions,
        $manager='manager') {
        global $CFG, $DB;

        $usednamefields = totara_get_all_user_name_fields_join($manager, null, true);
        $allnamefields = totara_get_all_user_name_fields_join($manager);

        // The manager full names are formatted in PHP but we need something for SQL searches,
        // for no manager return NULL instead of random spaces.
        $rawfullnamefield = "CASE WHEN {$manager}.id IS NULL THEN NULL ELSE " . $DB->sql_concat_join("' '", $usednamefields) . " END";

        $columnoptions[] = new rb_column_option(
            'user',
            'managername',
            get_string('usersmanagername', 'totara_reportbuilder'),
            $rawfullnamefield,
            array('joins' => $manager,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text',
                  'extrafields' => $allnamefields,
                  'displayfunc' => 'user')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'managerfirstname',
            get_string('usersmanagerfirstname', 'totara_reportbuilder'),
            "$manager.firstname",
            array('joins' => $manager,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'managerlastname',
            get_string('usersmanagerlastname', 'totara_reportbuilder'),
            "$manager.lastname",
            array('joins' => $manager,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'managerid',
            get_string('usersmanagerid', 'totara_reportbuilder'),
            "$manager.id",
            array('joins' => $manager)
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'manageridnumber',
            get_string('usersmanageridnumber', 'totara_reportbuilder'),
            "$manager.idnumber",
            array('joins' => $manager,
                  'displayfunc' => 'plaintext',
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'user',
            'manageremail',
            get_string('usersmanageremail', 'totara_reportbuilder'),
            // use CASE to include/exclude email in SQL
            // so search won't reveal hidden results
            "CASE WHEN $manager.maildisplay <> 1 THEN '-' ELSE $manager.email END",
            array(
                'joins' => $manager,
                'displayfunc' => 'user_email',
                'extrafields' => array(
                    'emailstop' => "$manager.emailstop",
                    'maildisplay' => "$manager.maildisplay",
                ),
                'dbdatatype' => 'char',
                'outputformat' => 'text'
            )
        );
        // Only include this column if email is among fields allowed by showuseridentity setting or
        // if the current user has the 'moodle/site:config' capability.
        $canview = !empty($CFG->showuseridentity) && in_array('email', explode(',', $CFG->showuseridentity));
        $canview |= has_capability('moodle/site:config', context_system::instance());
        if ($canview) {
            $columnoptions[] = new rb_column_option(
                'user',
                'manageremailunobscured',
                get_string('usersmanageremailunobscured', 'totara_reportbuilder'),
                "$manager.email",
                array(
                    'joins' => $manager,
                    'displayfunc' => 'user_email_unobscured',
                    // Users must have viewuseridentity to see the
                    // unobscured email address.
                    'capability' => 'moodle/site:viewuseridentity',
                    'dbdatatype' => 'char',
                    'outputformat' => 'text'
                )
            );
        }
        return true;
    }


    /**
     * Adds some common manager filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_manager_fields_to_filters(&$filteroptions) {
        global $CFG;
        $filteroptions[] = new rb_filter_option(
            'user',
            'managername',
            get_string('managername', 'totara_reportbuilder'),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'managerid',
            get_string('usersmanagerid', 'totara_reportbuilder'),
            'number'
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'manageridnumber',
            get_string('usersmanageridnumber', 'totara_reportbuilder'),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'user',
            'manageremail',
            get_string('usersmanageremail', 'totara_reportbuilder'),
            'text'
        );
        // Only include this filter if email is among fields allowed by showuseridentity setting or
        // if the current user has the 'moodle/site:config' capability.
        $canview = !empty($CFG->showuseridentity) && in_array('email', explode(',', $CFG->showuseridentity));
        $canview |= has_capability('moodle/site:config', context_system::instance());
        if ($canview) {
            $filteroptions[] = new rb_filter_option(
                'user',
                'manageremailunobscured',
                get_string('usersmanageremailunobscured', 'totara_reportbuilder'),
                'text'
            );
        }
        return true;
    }


    /**
     * Adds the tag tables to the $joinlist array
     *
     * @param string $type tag itemtype
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     $type table
     * @param string $field Name of course id field to join on
     * @return boolean True
     */
    protected function add_tag_tables_to_joinlist($type, &$joinlist, $join, $field) {

        global $DB;

        $joinlist[] = new rb_join(
            'tagids',
            'LEFT',
            // subquery as table name
            "(SELECT til.id AS tilid, " .
                sql_group_concat(sql_cast2char('t.id'), '|') .
                " AS idlist FROM {{$type}} til
                LEFT JOIN {tag_instance} ti
                    ON til.id = ti.itemid AND ti.itemtype = '{$type}'
                LEFT JOIN {tag} t
                    ON ti.tagid = t.id AND t.tagtype = 'official'
                GROUP BY til.id)",
            "tagids.tilid = {$join}.{$field}",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        $joinlist[] = new rb_join(
            'tagnames',
            'LEFT',
            // subquery as table name
            "(SELECT tnl.id AS tnlid, " .
                sql_group_concat(sql_cast2char('t.name'), ', ') .
                " AS namelist FROM {{$type}} tnl
                LEFT JOIN {tag_instance} ti
                    ON tnl.id = ti.itemid AND ti.itemtype = '{$type}'
                LEFT JOIN {tag} t
                    ON ti.tagid = t.id AND t.tagtype = 'official'
                GROUP BY tnl.id)",
            "tagnames.tnlid = {$join}.{$field}",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        // create a join for each official tag
        $tags = $DB->get_records('tag', array('tagtype' => 'official'));
        foreach ($tags as $tag) {
            $tagid = $tag->id;
            $name = "{$type}_tag_$tagid";
            $joinlist[] = new rb_join(
                $name,
                'LEFT',
                '{tag_instance}',
                "($name.itemid = $join.$field AND $name.tagid = $tagid " .
                    "AND $name.itemtype = '{$type}')",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                $join
            );
        }

        return true;
    }


    /**
     * Adds some common tag info to the $columnoptions array
     *
     * @param string $type tag itemtype
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $tagids name of the join that provides the 'tagids' table.
     * @param string $tagnames name of the join that provides the 'tagnames' table.
     *
     * @return True
     */
    protected function add_tag_fields_to_columns($type, &$columnoptions, $tagids='tagids', $tagnames='tagnames') {
        global $DB;

        $columnoptions[] = new rb_column_option(
            'tags',
            'tagids',
            get_string('tagids', 'totara_reportbuilder'),
            "$tagids.idlist",
            array('joins' => $tagids, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'tags',
            'tagnames',
            get_string('tags', 'totara_reportbuilder'),
            "$tagnames.namelist",
            array('joins' => $tagnames,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );

        // create a on/off field for every official tag
        $tags = $DB->get_records('tag', array('tagtype' => 'official'));
        foreach ($tags as $tag) {
            $tagid = $tag->id;
            $name = $tag->name;
            $join = "{$type}_tag_$tagid";
            $columnoptions[] = new rb_column_option(
                'tags',
                $join,
                get_string('taggedx', 'totara_reportbuilder', $name),
                "CASE WHEN $join.id IS NOT NULL THEN 1 ELSE 0 END",
                array(
                    'joins' => $join,
                    'displayfunc' => 'yes_no',
                )
            );
        }
        return true;
    }


    /**
     * Adds some common tag filters to the $filteroptions array
     *
     * @param string $type tag itemtype
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_tag_fields_to_filters($type, &$filteroptions) {
        global $DB;

        // create a yes/no filter for every official tag
        $tags = $DB->get_records('tag', array('tagtype' => 'official'));
        foreach ($tags as $tag) {
            $tagid = $tag->id;
            $name = $tag->name;
            $join = "{$type}_tag_{$tagid}";
            $filteroptions[] = new rb_filter_option(
                'tags',
                $join,
                get_string('taggedx', 'totara_reportbuilder', $name),
                'select',
                array(
                    'selectchoices' => array(1 => get_string('yes'), 0 => get_string('no')),
                    'simplemode' => true,
                )
            );
        }

        // create a tag list selection filter
        $filteroptions[] = new rb_filter_option(
            'tags',         // type
            'tagids',           // value
            get_string('tags', 'totara_reportbuilder'), // label
            'multicheck',     // filtertype
            array(            // options
                'selectchoices' => $this->rb_filter_tags_list(),
                'concat' => true, // Multicheck filter needs to know that we are working with concatenated values
                'showcounts' => array(
                        'joins' => array("LEFT JOIN (SELECT ti.itemid, ti.tagid FROM {{$type}} base " .
                                                      "LEFT JOIN {tag_instance} ti ON base.id = ti.itemid " .
                                                            "AND ti.itemtype = '{$type}'" .
                                                      "LEFT JOIN {tag} tag ON ti.tagid = tag.id " .
                                                            "AND tag.tagtype = 'official')\n {$type}_tagids_filter " .
                                                "ON base.id = {$type}_tagids_filter.itemid"),
                        'dataalias' => $type.'_tagids_filter',
                        'datafield' => 'tagid')
            )
        );
        return true;
    }


    /**
     * Adds the cohort user tables to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'user' table
     * @param string $field Name of user id field to join on
     * @return boolean True
     */
    protected function add_cohort_user_tables_to_joinlist(&$joinlist,
                                                          $join, $field) {

        $joinlist[] = new rb_join(
            'cohortuser',
            'LEFT',
            // subquery as table name
            "(SELECT cm.userid AS userid, " .
                sql_group_concat(sql_cast2char('cm.cohortid'),'|', true) .
                " AS idlist FROM {cohort_members} cm
                GROUP BY cm.userid)",
            "cohortuser.userid = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        return true;
    }

    /**
     * Adds the cohort course tables to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'course' table
     * @param string $field Name of course id field to join on
     * @return boolean True
     */
    protected function add_cohort_course_tables_to_joinlist(&$joinlist,
                                                            $join, $field) {

        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');

        $joinlist[] = new rb_join(
            'cohortenrolledcourse',
            'LEFT',
            // subquery as table name
            "(SELECT courseid AS course, " .
                sql_group_concat(sql_cast2char('customint1'), '|', true) .
                " AS idlist FROM {enrol} e
                WHERE e.enrol = 'cohort'
                GROUP BY courseid)",
            "cohortenrolledcourse.course = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        return true;
    }


    /**
     * Adds the cohort program tables to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     table containing the program id
     * @param string $field Name of program id field to join on
     * @return boolean True
     */
    protected function add_cohort_program_tables_to_joinlist(&$joinlist,
                                                             $join, $field) {

        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');

        $joinlist[] = new rb_join(
            'cohortenrolledprogram',
            'LEFT',
            // subquery as table name
            "(SELECT programid AS program, " .
                sql_group_concat(sql_cast2char('assignmenttypeid'), '|', true) .
                " AS idlist FROM {prog_assignment} pa
                WHERE assignmenttype = " . ASSIGNTYPE_COHORT . "
                GROUP BY programid)",
            "cohortenrolledprogram.program = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        return true;
    }


    /**
     * Adds some common cohort user info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $cohortids Name of the join that provides the
     *                          'cohortuser' table.
     *
     * @return True
     */
    protected function add_cohort_user_fields_to_columns(&$columnoptions,
                                                         $cohortids='cohortuser') {

        $columnoptions[] = new rb_column_option(
            'cohort',
            'usercohortids',
            get_string('usercohortids', 'totara_reportbuilder'),
            "$cohortids.idlist",
            array('joins' => $cohortids, 'selectable' => false)
        );

        return true;
    }


    /**
     * Adds some common cohort course info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $cohortenrolledids Name of the join that provides the
     *                          'cohortenrolledcourse' table.
     *
     * @return True
     */
    protected function add_cohort_course_fields_to_columns(&$columnoptions, $cohortenrolledids='cohortenrolledcourse') {
        $columnoptions[] = new rb_column_option(
            'cohort',
            'enrolledcoursecohortids',
            get_string('enrolledcoursecohortids', 'totara_reportbuilder'),
            "$cohortenrolledids.idlist",
            array('joins' => $cohortenrolledids, 'selectable' => false)
        );

        return true;
    }


    /**
     * Adds some common cohort program info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $cohortenrolledids Name of the join that provides the
     *                          'cohortenrolledprogram' table.
     *
     * @return True
     */
    protected function add_cohort_program_fields_to_columns(&$columnoptions, $cohortenrolledids='cohortenrolledprogram') {
        $columnoptions[] = new rb_column_option(
            'cohort',
            'enrolledprogramcohortids',
            get_string('enrolledprogramcohortids', 'totara_reportbuilder'),
            "$cohortenrolledids.idlist",
            array('joins' => $cohortenrolledids, 'selectable' => false)
        );

        return true;
    }

    /**
     * Adds some common user cohort filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_cohort_user_fields_to_filters(&$filteroptions) {

        if (!has_capability('moodle/cohort:view', context_system::instance())) {
            return true;
        }

        $filteroptions[] = new rb_filter_option(
            'cohort',
            'usercohortids',
            get_string('userincohort', 'totara_reportbuilder'),
            'cohort'
        );
        return true;
    }

    /**
     * Adds some common course cohort filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_cohort_course_fields_to_filters(&$filteroptions) {

        if (!has_capability('moodle/cohort:view', context_system::instance())) {
            return true;
        }

        $filteroptions[] = new rb_filter_option(
            'cohort',
            'enrolledcoursecohortids',
            get_string('courseenrolledincohort', 'totara_reportbuilder'),
            'cohort'
        );

        return true;
    }


    /**
     * Adds some common program cohort filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $langfile Source for translation, totara_program or totara_certification
     *
     * @return True
     */
    protected function add_cohort_program_fields_to_filters(&$filteroptions, $langfile) {

        if (!has_capability('moodle/cohort:view', context_system::instance())) {
            return true;
        }

        $filteroptions[] = new rb_filter_option(
            'cohort',
            'enrolledprogramcohortids',
            get_string('programenrolledincohort', $langfile),
            'cohort'
        );

        return true;
    }

    /**
     * @return array
     */
    protected function define_columnoptions() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_filteroptions() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_defaultcolumns() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_defaultfilters() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_contentoptions() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_paramoptions() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_requiredcolumns() {
        return array();
    }

    /**
     * Called after parameters have been read, allows the source to configure itself,
     * such as source title, additional tables, column definitions, etc.
     *
     * If post_params fails it needs to set redirect.
     *
     * @param reportbuilder $report
     */
    public function post_params(reportbuilder $report) {
    }

    /**
     * This method is called at the very end of reportbuilder class constructor
     * right before marking it ready.
     *
     * This method allows sources to add extra restrictions by calling
     * the following method on the $report object:
     *  {@link $report->set_post_config_restrictions()}    Extra WHERE clause
     *
     * If post_config fails it needs to set redirect.
     *
     * NOTE: do NOT modify the list of columns here.
     *
     * @param reportbuilder $report
     */
    public function post_config(reportbuilder $report) {
    }

    /**
     * Returns an array of js objects that need to be included with this report.
     *
     * @return array(object)
     */
    public function get_required_jss() {
        return array();
    }

    protected function get_advanced_aggregation_classes($type) {
        global $CFG;

        $classes = array();

        foreach (scandir("{$CFG->dirroot}/totara/reportbuilder/classes/rb/{$type}") as $filename) {
            if (substr($filename, -4) !== '.php') {
                continue;
            }
            if ($filename === 'base.php') {
                continue;
            }
            $name = str_replace('.php', '', $filename);
            $classname = "\\totara_reportbuilder\\rb\\{$type}\\$name";
            if (!class_exists($classname)) {
                debugging("Invalid aggregation class $name found", DEBUG_DEVELOPER);
                continue;
            }
            $classes[$name] = $classname;
        }

        return $classes;
    }

    /**
     * Get list of allowed advanced options for each column option.
     *
     * @return array of group select column values that are grouped
     */
    public function get_allowed_advanced_column_options() {
        $allowed = array();

        foreach ($this->columnoptions as $option) {
            $key = $option->type . '-' . $option->value;
            $allowed[$key] = array('');

            $classes = $this->get_advanced_aggregation_classes('transform');
            foreach ($classes as $name => $classname) {
                if ($classname::is_column_option_compatible($option)) {
                    $allowed[$key][] = 'transform_'.$name;
                }
            }

            $classes = $this->get_advanced_aggregation_classes('aggregate');
            foreach ($classes as $name => $classname) {
                if ($classname::is_column_option_compatible($option)) {
                    $allowed[$key][] = 'aggregate_'.$name;
                }
            }
        }
        return $allowed;
    }

    /**
     * Get list of grouped columns.
     *
     * @return array of group select column values that are grouped
     */
    public function get_grouped_column_options() {
        $grouped = array();
        foreach ($this->columnoptions as $option) {
            if ($option->grouping !== 'none') {
                $grouped[] = $option->type . '-' . $option->value;
            }
        }
        return $grouped;
    }

    /**
     * Returns list of advanced aggregation/transformation options.
     *
     * @return array nested array suitable for groupselect forms element
     */
    public function get_all_advanced_column_options() {
        $advoptions = array();
        $advoptions[get_string('none')][''] = '-';

        foreach (array('transform', 'aggregate') as $type) {
            $classes = $this->get_advanced_aggregation_classes($type);
            foreach ($classes as $name => $classname) {
                $advoptions[$classname::get_typename()][$type . '_' . $name] = get_string("{$type}type{$name}_name",
                            'totara_reportbuilder');
            }
        }

        foreach ($advoptions as $k => $unused) {
            \core_collator::asort($advoptions[$k]);
        }

        return $advoptions;
    }

    /**
     * Set up necessary $PAGE stuff for columns.php page.
     */
    public function columns_page_requires() {
        \totara_reportbuilder\rb\aggregate\base::require_column_heading_strings();
        \totara_reportbuilder\rb\transform\base::require_column_heading_strings();
    }

    /**
     * @param $mform
     * @param $inlineenrolments
     */
    private function process_enrolments($mform, $inlineenrolments) {
        global $CFG;

        if ($formdata = $mform->get_data()) {
            $submittedinstance = required_param('instancesubmitted', PARAM_INT);
            $inlineenrolment = $inlineenrolments[$submittedinstance];
            $instance = $inlineenrolment->instance;
            $plugin = $inlineenrolment->plugin;
            $nameprefix = 'instanceid_' . $instance->id . '_';
            $nameprefixlength = strlen($nameprefix);

            $valuesforenrolform = array();
            foreach ($formdata as $name => $value) {
                if (substr($name, 0, $nameprefixlength) === $nameprefix) {
                    $name = substr($name, $nameprefixlength);
                    $valuesforenrolform[$name] = $value;
                }
            }
            $enrolform = $plugin->course_expand_get_form_hook($instance);

            $enrolform->_form->updateSubmission($valuesforenrolform, null);

            $enrolled = $plugin->course_expand_enrol_hook($enrolform, $instance);
            if ($enrolled) {
                $mform->_form->addElement('hidden', 'redirect', $CFG->wwwroot . '/course/view.php?id=' . $instance->courseid);
            }

            foreach ($enrolform->_form->_errors as $errorname => $error) {
                $mform->_form->_errors[$nameprefix . $errorname] = $error;
            }
        }
    }
}
