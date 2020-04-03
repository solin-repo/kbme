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
 * @author Sam Hemelryk <sam.hemelryk@totaralms.com>
 * @package totara_cohort
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/totara/cohort/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/profile/definelib.php');
require_once($CFG->dirroot . '/user/profile/field/checkbox/define.class.php');

/**
 * Test audience rules.
 *
 * NOTE: the numbers are coming straight from Totara 2.7.2 which is the baseline for us,
 *       any changes in results need to be documented here.
 */
class totara_cohort_user_custom_profile_field_checkbox_testcase extends advanced_testcase {

    /**
     * @var totara_cohort_generator The cohort data generator.
     */
    protected $cohort_generator = null;
    protected $cohort = null;
    protected $ruleset = 0;
    /**
     * @var int The ID of the developer profile field.
     */
    protected $profiledeveloperid;
    const TEST_USER_COUNT_MEMBERS = 53;

    protected function tearDown() {
        $this->cohort_generator = null;
        $this->cohort = null;
        $this->ruleset = null;
        $this->profiledeveloperid = null;
        parent::tearDown();
    }

    protected function setUp() {
        global $DB;

        parent::setup();
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $generator = $this->getDataGenerator();

        $this->profiledeveloperid = $this->add_user_profile_checkbox_field('developer', 0);

        // Set totara_cohort generator.
        $this->cohort_generator = $generator->get_plugin_generator('totara_cohort');

        // Create users.
        $users = array();
        for ($i = 1; $i <= self::TEST_USER_COUNT_MEMBERS; $i++) {
            $userdata = array(
                'username' => 'user' . $i,
                'idnumber' => 'USER00' . $i,
                'email' => 'user' . $i . '@123.com',
                'city' => 'Valencia',
                'country' => 'ES',
                'lang' => 'es',
                'institution' => 'UV',
                'department' => 'system',
            );

            if ($i <= 10) {
                $userdata['idnumber'] = 'USERX0' . $i;
            }

            if ($i%2 == 0) {
                $userdata['firstname'] = 'nz_' . $i . '_testuser';
                $userdata['lastname'] = 'NZ FAMILY NAME';
                $userdata['city'] = 'wellington';
                $userdata['country'] = 'NZ';
                $userdata['email'] = 'user' . $i . '@nz.com';
                $userdata['lang'] = 'en';
                $userdata['institution'] = 'Totara';
            }

            $user = $generator->create_user($userdata);
            $users[$user->id] = $user;
        }
        $this->assertSame(self::TEST_USER_COUNT_MEMBERS + 2, $DB->count_records('user'));

        // Here we are generating 53 users with a custom profile field called "developer" with a default of "0".
        // There is also one 'admin' account with default "parsnip".
        // Guest account should never be assigned to cohort, it is completely ignored here.
        //
        // The following is what we expect:
        //     10 users with '1'
        //      5 users with '' (weirdly this is supposed to mean '0')
        //      7 users with '0' set explicitly
        //   31+1 users with '0' from default (the 1 is admin)
        // --------------------------------------------------------------
        //     54 total of users that may be assigned to cohort
        //     10 total of users that have '1' set explicitly
        //     54 total of users that have '0' via default or set value

        // Set custom field values for some of them.
        reset($users);
        for ($i = 0; $i < 10; $i++) {
            next($users);
            $user = new stdClass;
            $user->id = key($users);
            $user->profile_field_developer = 1;
            profile_save_data($user);
            $user = current($users);
            profile_load_custom_fields($user);
            $this->assertSame('1', $user->profile['developer']);
        }
        for ($i = 0; $i < 5; $i++) {
            next($users);
            $user = new stdClass;
            $user->id = key($users);
            $user->profile_field_developer = '';
            profile_save_data($user);
            $user = current($users);
            profile_load_custom_fields($user);
            $this->assertSame('', $user->profile['developer']);
        }
        for ($i = 0; $i < 7; $i++) {
            next($users);
            $user = new stdClass;
            $user->id = key($users);
            $user->profile_field_developer = 0;
            profile_save_data($user);
            $user = current($users);
            profile_load_custom_fields($user);
            $this->assertSame('0', $user->profile['developer']);
        }

        // Creating an empty dynamic cohort.
        $this->cohort = $this->cohort_generator->create_cohort(array('cohorttype' => cohort::TYPE_DYNAMIC));
        $this->assertTrue($DB->record_exists('cohort', array('id' => $this->cohort->id)));
        $this->assertEquals(0, $DB->count_records('cohort_members', array('cohortid' => $this->cohort->id)));

        // Creating a ruleset.
        $this->ruleset = cohort_rule_create_ruleset($this->cohort->draftcollectionid);
    }

    /**
     * Adds a "checkbox" custom user profile field.
     *
     * @param string $name
     * @param string $default
     * @return int
     */
    protected function add_user_profile_checkbox_field($name, $default) {
        global $DB;
        $formfield = new profile_define_checkbox();

        if (!$DB->record_exists('user_info_category', array())) {
            // Copied from user/profile/index.php.
            $defaultcategory = new stdClass();
            $defaultcategory->name = 'Default category';
            $defaultcategory->sortorder = 1;

            $DB->insert_record('user_info_category', $defaultcategory);
        }

        $data = new stdClass;
        $data->name = $name;
        $data->shortname = $name;
        $data->descriptionformat = FORMAT_HTML;
        $data->description = 'This custom user profile field was added by unit tests.';
        $data->defaultdataformat = FORMAT_PLAIN;
        $data->defaultdata = $default;
        $data->categoryid = $DB->get_field('user_info_category', 'id', array(), IGNORE_MULTIPLE);
        $data->datatype = 'checkbox';

        $formfield->define_save($data);
        profile_reorder_fields();
        profile_reorder_categories();

        // We need to reset the rules after adding the custom user profile fields.
        cohort_rules_list(true);

        return $DB->get_field('user_info_field', 'id', array('shortname' => $name), IGNORE_MULTIPLE);
    }

    /**
     * Data provider for the checkbox profile field rule.
     */
    public function data_checkbox_isequalto() {
        $data = array(
            array(array(1), 10),
            array(array(''), 5),
            array(array(0), 39),
            array(array(1, 0), 39),
            array(array('', 0), 5),
            array(array('', 1, 0), 5),
        );
        return $data;
    }

    /**
     * Tests the checkbox profile field and multiple values.
     * @dataProvider data_checkbox_isequalto
     */
    public function test_checkbox_isequalto($values, $usercount) {
        global $DB;

        $this->cohort_generator->create_cohort_rule_params(
            $this->ruleset,
            'usercustomfields',
            'customfield'.$this->profiledeveloperid.'_0',
            array('equal' => COHORT_RULES_OP_IN_ISEQUALTO),
            $values
        );
        cohort_rules_approve_changes($this->cohort);
        $this->assertEquals($usercount, $DB->count_records('cohort_members', array('cohortid' => $this->cohort->id)));
    }

    /**
     * Data provider for the checkbox profile field rule.
     */
    public function data_checkbox_notequalto() {
        $data = array(
            array(array(1), 10),
            array(array(''), 5),
            array(array(0), 39),
            array(array(1, 0), 39),
            array(array('', 0), 5),
            array(array('', 1, 0), 5),
        );
        return $data;
    }

    /**
     * Tests the checkbox profile field and multiple values.
     * @dataProvider data_checkbox_notequalto
     */
    public function test_checkbox_notequalto($listofvalues, $usercount) {
        global $DB;

        $this->cohort_generator->create_cohort_rule_params(
            $this->ruleset,
            'usercustomfields',
            'customfield'.$this->profiledeveloperid.'_0',
            array('equal' => COHORT_RULES_OP_IN_NOTEQUALTO),
            $listofvalues
        );
        cohort_rules_approve_changes($this->cohort);
        $this->assertEquals($usercount, $DB->count_records('cohort_members', array('cohortid' => $this->cohort->id)));
    }

    /**
     * Data provider for the checkbox profile field rule.
     */
    public function data_checkbox_startswith() {
        $data = array(
            array(array(1), 10),
            array(array(''), 5),
            array(array(0), 39),
            array(array(1, 0), 39),
            array(array('', 0), 5),
            array(array('', 1, 0), 5),
        );
        return $data;
    }

    /**
     * Tests the checkbox profile field and multiple values.
     * @dataProvider data_checkbox_startswith
     */
    public function test_checkbox_startswith($listofvalues, $usercount) {
        global $DB;

        $this->cohort_generator->create_cohort_rule_params(
            $this->ruleset,
            'usercustomfields',
            'customfield'.$this->profiledeveloperid.'_0',
            array('equal' => COHORT_RULES_OP_IN_STARTSWITH),
            $listofvalues
        );
        cohort_rules_approve_changes($this->cohort);
        $this->assertEquals($usercount, $DB->count_records('cohort_members', array('cohortid' => $this->cohort->id)));
    }

    /**
     * Data provider for the checkbox profile field rule.
     */
    public function data_checkbox_endswith() {
        $data = array(
            array(array(1), 10),
            array(array(''), 5),
            array(array(0), 39),
            array(array(1, 0), 39),
            array(array('', 0), 5),
            array(array('', 1, 0), 5),
        );
        return $data;
    }

    /**
     * Tests the checkbox profile field and multiple values.
     * @dataProvider data_checkbox_endswith
     */
    public function test_checkbox_endswith($listofvalues, $usercount) {
        global $DB;

        $this->cohort_generator->create_cohort_rule_params(
            $this->ruleset,
            'usercustomfields',
            'customfield'.$this->profiledeveloperid.'_0',
            array('equal' => COHORT_RULES_OP_IN_ENDSWITH),
            $listofvalues
        );
        cohort_rules_approve_changes($this->cohort);
        $this->assertEquals($usercount, $DB->count_records('cohort_members', array('cohortid' => $this->cohort->id)));
    }

    /**
     * Data provider for the checkbox profile field rule.
     */
    public function data_checkbox_contains() {
        $data = array(
            array(array(1), 10),
            array(array(''), 5),
            array(array(0), 39),
            array(array(1, 0), 39),
            array(array('', 0), 5),
            array(array('', 1, 0), 5),
        );
        return $data;
    }

    /**
     * Tests the checkbox profile field and multiple values.
     * @dataProvider data_checkbox_contains
     */
    public function test_checkbox_contains($listofvalues, $usercount) {
        global $DB;

        $this->cohort_generator->create_cohort_rule_params(
            $this->ruleset,
            'usercustomfields',
            'customfield'.$this->profiledeveloperid.'_0',
            array('equal' => COHORT_RULES_OP_IN_CONTAINS),
            $listofvalues
        );
        cohort_rules_approve_changes($this->cohort);
        $this->assertEquals($usercount, $DB->count_records('cohort_members', array('cohortid' => $this->cohort->id)));
    }

    /**
     * Data provider for the checkbox profile field rule.
     */
    public function data_checkbox_notcontains() {
        $data = array(
            array(array(1), 10),
            array(array(''), 5),
            array(array(0), 39),
            array(array(1, 0), 39),
            array(array('', 0), 5),
            array(array('', 1, 0), 5),
        );
        return $data;
    }

    /**
     * Tests the checkbox profile field and multiple values.
     * @dataProvider data_checkbox_notcontains
     */
    public function test_checkbox_notcontains($listofvalues, $usercount) {
        global $DB;

        $this->cohort_generator->create_cohort_rule_params(
            $this->ruleset,
            'usercustomfields',
            'customfield'.$this->profiledeveloperid.'_0',
            array('equal' => COHORT_RULES_OP_IN_NOTCONTAIN),
            $listofvalues
        );
        cohort_rules_approve_changes($this->cohort);
        $this->assertEquals($usercount, $DB->count_records('cohort_members', array('cohortid' => $this->cohort->id)));
    }
}
