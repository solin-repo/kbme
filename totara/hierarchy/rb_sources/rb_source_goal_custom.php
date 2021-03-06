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
 * @author Ryan Lafferty <ryanl@learningpool.com>
 * @package totara
 * @subpackage reportbuilder
 */

defined('MOODLE_INTERNAL') || die();

class rb_source_goal_custom extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions, $paramoptions;
    public $defaultcolumns, $defaultfilters, $embeddedparams;
    public $sourcetitle, $shortname, $scheduleable, $cacheable;

    public function __construct($groupid, rb_global_restriction_set $globalrestrictionset = null) {
        if ($groupid instanceof rb_global_restriction_set) {
            throw new coding_exception('Wrong parameter orders detected during report source instantiation.');
        }
        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;

        // Apply global user restrictions.
        $this->add_global_report_restriction_join('base', 'userid');

        $this->base = "(SELECT g.id, g.fullname AS name, gua.userid,
            'company' AS personalcompany, COALESCE(t.fullname, 'notype') AS typename
                FROM {goal} g JOIN {goal_user_assignment} gua ON g.id = gua.goalid
                LEFT JOIN {goal_type} t ON t.id = g.typeid
                UNION
                SELECT gp.id, gp.name, gp.userid, 'personal' AS personalcompany, COALESCE(ut.fullname, 'notype') AS typename
                FROM {goal_personal} gp
                LEFT JOIN {goal_user_type} ut ON ut.id = gp.typeid
                WHERE deleted = 0)";
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->embeddedparams = $this->define_embeddedparams();
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_goal_custom');
        $this->shortname = 'goal_custom';
        $this->cacheable = false;

        parent::__construct();
    }

    /**
     * Global report restrictions are implemented in this source.
     * @return boolean
     */
    public function global_restrictions_supported() {
        return true;
    }

    public function is_ignored() {
        return !totara_feature_visible('goals');
    }

    protected function define_joinlist() {
        $joinlist = array(
            // This join is required to keep the joining of company personal goal custom fields happy.
            new rb_join(
                'goal',
                'LEFT',
                '{goal}',
                'base.id = goal.id AND personalcompany = \'company\'',
                REPORT_BUILDER_RELATION_ONE_TO_ONE
            ),

            // This join is required to keep the joining of personal goal custom fields happy.
            new rb_join(
                'goal_personal',
                'LEFT',
                '{goal_personal}',
                'base.id = goal_personal.id AND personalcompany = \'personal\'',
                REPORT_BUILDER_RELATION_ONE_TO_ONE
            ),
            new rb_join(
                'buser',
                'INNER',
                '{user}',
                'base.userid = buser.id',
                REPORT_BUILDER_RELATION_MANY_TO_ONE
            ),
            new rb_join(
                'user_type',
                'LEFT',
                '{goal_user_type}',
                'base.typeid = user_type.id AND personalcompany = \'personal\'',
                REPORT_BUILDER_RELATION_MANY_TO_ONE
            ),
            new rb_join(
                'goal_type',
                'LEFT',
                '{goal_type}',
                'base.typeid = goal_type.id AND personalcompany = \'company\'',
                REPORT_BUILDER_RELATION_MANY_TO_ONE
            )
        );
        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');

        return $joinlist;
    }

    public function post_params(reportbuilder $report) {
        global $DB;

        $custompersonalgoals = $DB->get_records('goal_user_info_field', array('hidden' => 0));

        foreach ($custompersonalgoals as $custompersonalgoal) {
            $this->joinlist[] =
                new rb_join(
                    "goal_user_goalrecord" . $custompersonalgoal->id,
                    "LEFT",
                    "(SELECT *
                        FROM {goal_user_info_data}
                       WHERE fieldid = {$custompersonalgoal->id}
                        )",
                    "goal_user_goalrecord" . $custompersonalgoal->id . ".goal_userid = base.id AND personalcompany='personal' "
                );
        }

        $customcompanygoals = $DB->get_records('goal_type_info_field', array('hidden' => 0));

        foreach ($customcompanygoals as $customcompanygoal) {
            $this->joinlist[] =
                new rb_join(
                    "goal_type_goalrecord" . $customcompanygoal->id,
                    "LEFT",
                    "(SELECT *
                        FROM {goal_type_info_data}
                       WHERE fieldid = {$customcompanygoal->id}
                        )",
                    "goal_type_goalrecord" . $customcompanygoal->id . ".goalid = base.id AND personalcompany='company' "
                );
        }
    }

    protected function define_columnoptions() {
        $columnoptions = array(
            new rb_column_option(
                'goal',
                'goalname',
                get_string('goalname', 'rb_source_goal_custom'),
                'base.name'
            ),
            new rb_column_option(
                'goal',
                'personalcompany',
                get_string('personalcompany', 'rb_source_goal_custom'),
                'base.personalcompany',
                array(
                    'displayfunc' => 'personal_company'
                )
            ),
            new rb_column_option(
                'goal',
                'typename',
                get_string('typename', 'rb_source_goal_custom'),
                'base.typename',
                array(
                    'displayfunc' => 'user_type_name'
                )
            ),
            new rb_column_option(
                'goal',
                'allpersonalgoalcustomfields',
                get_string('allpersonalgoalcustomfields', 'rb_source_goal_custom'),
                'allpersonalgoalcustomfields_',
                array(
                    'columngenerator' => 'allpersonalgoalcustomfields'
                )
            ),
            new rb_column_option(
                'goal',
                'allcompanygoalcustomfields',
                get_string('allcompanygoalcustomfields', 'rb_source_goal_custom'),
                'allcompanygoalcustomfields_',
                array(
                    'columngenerator' => 'allcompanygoalcustomfields'
                )
            )
        );

        $this->add_user_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array(
            new rb_filter_option(
                'goal',
                'goalname',
                get_string('goalname', 'rb_source_goal_custom'),
                'text'
            ),
            new rb_filter_option(
                'goal',
                'personalcompany',
                get_string('personalcompany', 'rb_source_goal_custom'),
                'select',
                array(
                    'selectfunc' => 'personal_company'
                )
            ),
            new rb_filter_option(
                'goal',
                'typename',
                get_string('typename', 'rb_source_goal_custom'),
                'multicheck',
                array(
                    'selectfunc' => 'goal_type'
                )
            )
        );
        $this->add_user_fields_to_filters($filteroptions);
        return $filteroptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
            new rb_param_option(
                'userid',
                'base.userid'
            )
        );

        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
            array(
                'type' => 'user',
                'value' => 'namelink'
            ),
            array(
                'type' => 'goal',
                'value' => 'goalname'
            ),
            array(
                'type' => 'goal',
                'value' => 'personalcompany'
            ),
            array(
                'type' => 'goal',
                'value' => 'typename'
            )

        );

        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array();

        return $defaultfilters;
    }

    protected function define_embeddedparams() {
        $embeddedparams = array();

        return $embeddedparams;
    }

    public function rb_display_user_type_name($type, $row) {
        if ($type === 'notype') {
            return get_string('notype', 'rb_source_goal_custom');
        } else {
            return $type;
        }
    }

    public function rb_display_personal_company($type, $row) {
        if ($type === 'company') {
            return get_string('company', 'rb_source_goal_custom');
        } else {
            return get_string('personal', 'rb_source_goal_custom');
        }
    }

    public function rb_filter_personal_company() {
        return array('company' => get_string('company', 'rb_source_goal_custom'),
                     'personal' => get_string('personal', 'rb_source_goal_custom')
        );
    }

    public function rb_filter_goal_type() {
        global $DB;

        $sql = "SELECT t.fullname AS typename FROM {goal_type} t
                UNION
                SELECT ut.fullname AS typename FROM {goal_user_type} ut";

        $goaltypes = $DB->get_fieldset_sql($sql);
        $goalarray = array();

        foreach ($goaltypes as $goaltype) {
            $goalarray[$goaltype] = $goaltype;
        }
        $goalarray["notype"] = get_string('notype', 'rb_source_goal_custom');

        return $goalarray;
    }

    public function rb_cols_generator_allpersonalgoalcustomfields($columnoption, $hidden) {
        global $DB;

        $custompersonalgoals = $DB->get_records('goal_user_info_field', array('hidden' => 0));

        $results = array();
        foreach ($custompersonalgoals as $custompersonalgoal) {

            $results[] = $this->create_column($custompersonalgoal, $hidden, 'goal_user');
        }
        return $results;
    }

    public function rb_cols_generator_allcompanygoalcustomfields($columnoption, $hidden) {
        global $DB;

        $customcompanygoals = $DB->get_records('goal_type_info_field', array('hidden' => 0));

        $results = array();
        foreach ($customcompanygoals as $customcompanygoal) {
            $results[] = $this->create_column($customcompanygoal, $hidden, 'goal_type');
        }
        return $results;
    }

    public function create_column($customgoal, $hidden, $type) {
        $displayfunc = '';
        $multi = '';
        $extrafields = '';

        switch($customgoal->datatype) {
            case 'checkbox':
                $displayfunc = "yes_no";
                break;
            case 'multiselect':
                $multi = "_text";
                $displayfunc = "customfield_multiselect_text";
                $extrafields = array(
                    "{$type}_all_custom_field_{$customgoal->id}_text_json" => "{$type}_goalrecord{$customgoal->id}.data"
                );
                break;
            case 'datetime':
                if ($customgoal->param3) {
                    $displayfunc = 'nice_datetime';
                } else {
                    $displayfunc = 'nice_date';
                }
                break;
            case 'file':
                $displayfunc = 'customfield_file';
                $extrafields = array(
                    "{$type}_all_custom_field_{$customgoal->id}_itemid" => "{$type}_goalrecord{$customgoal->id}.id"
                );
                break;
            case 'textarea':
                $displayfunc = 'customfield_textarea';
                $extrafields = array(
                    "{$type}_all_custom_field_{$customgoal->id}_itemid" => "{$type}_goalrecord{$customgoal->id}.id"
                );
                break;
        }

        return new rb_column(
            $type,
            'all_custom_field_'.$customgoal->id . $multi,
            $customgoal->fullname,
            $type . "_goalrecord" . $customgoal->id . ".data",
            array(
                'joins' => array($type . "_goalrecord" . $customgoal->id),
                'hidden' => $hidden,
                'displayfunc' => $displayfunc,
                'extrafields' => $extrafields
            )
        );
    }
}
