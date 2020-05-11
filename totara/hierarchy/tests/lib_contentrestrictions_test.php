<?php // $Id$
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Riana Rossouw <riana.rossouw@totaralearning.com>
 * @package totara_hierarchy
 */

/*
 * PhpUnit tests for hierarchy/lib.php with content restrictions
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
global $CFG;
require_once($CFG->dirroot . '/totara/hierarchy/lib.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/position/lib.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/organisation/lib.php');


class hierarchylib_contentrestrictions_test extends advanced_testcase {
    use totara_reportbuilder\phpunit\report_testing;

    /** @var array Test users */
    protected $users;
    /** @var array Test position frameworks */
    protected $posfw;
    /** @var array Test positions */
    protected $pos;
    /** @var array Test organisation frameworks */
    protected $orgfw;
    /** @var array Test organisations */
    protected $org;
    /** @var position Position hierarchy to use for tests  */
    protected $position;
    /** @var organisation Organisation hierarchy to use for tests  */
    protected $organisation;
    /** @var array Test hierarchy structure */
    protected $hierarchy;
    /** @var int id of test report */
    protected $reportid;
    /** @var reportbuilder Report instance */
    protected $report;

    /**
     * Cleanup data
     */
    protected function tearDown() {
        $this->users = null;
        $this->posfw = null;
        $this->pos = null;
        $this->orgfw = null;
        $this->org = null;
        $this->position = null;
        $this->organisation = null;
        $this->hierarchy = null;
        $this->reportid = null;
        $this->report = null;
        parent::tearDown();
    }

    /**
     * Set up data
     */
    protected function setUp() {
        global $DB;

        $this->setAdminUser();
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $hierarchy_generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');

        for ($index = 1; $index <= 6; $index++) {
            $this->users[$index] = $generator->create_user();
        }

        $this->position = new position();
        $this->organisation = new organisation();

        // Positions
        $this->posfw['pframe'] = $hierarchy_generator->create_framework('position', ['fullname' => 'pframe', 'idnumber' => 'pframe']);
        $this->pos['pos100'] = $hierarchy_generator->create_hierarchy($this->posfw['pframe']->id, 'position', ['fullname' => 'pos100', 'idnumber' => 'pos100',
            'depthlevel' => 1, 'sortthread' => '01']);
        $this->pos['pos200'] = $hierarchy_generator->create_hierarchy($this->posfw['pframe']->id, 'position', ['fullname' => 'pos200', 'idnumber' => 'pos200',
            'depthlevel' => 1, 'sortthread' => '02']);
        $this->pos['pos110'] = $hierarchy_generator->create_hierarchy($this->posfw['pframe']->id, 'position', ['fullname' => 'pos110', 'idnumber' => 'pos110',
            'parentid' => $this->pos['pos100']->id, 'depthlevel' => 2, 'sortthread' => '01']);
        $this->pos['pos120'] = $hierarchy_generator->create_hierarchy($this->posfw['pframe']->id, 'position', ['fullname' => 'pos120', 'idnumber' => 'pos120',
            'parentid' => $this->pos['pos100']->id, 'depthlevel' => 2, 'sortthread' => '02']);
        $this->pos['pos111'] = $hierarchy_generator->create_hierarchy($this->posfw['pframe']->id, 'position', ['fullname' => 'pos111', 'idnumber' => 'pos111',
            'parentid' => $this->pos['pos110']->id, 'depthlevel' => 3, 'sortthread' => '01']);
        $this->pos['pos112'] = $hierarchy_generator->create_hierarchy($this->posfw['pframe']->id, 'position', ['fullname' => 'pos112', 'idnumber' => 'pos112',
            'parentid' => $this->pos['pos110']->id, 'depthlevel' => 2, 'sortthread' => '02']);

        $this->posfw['pframe2'] = $hierarchy_generator->create_framework('position', ['fullname' => 'pframe2', 'idnumber' => 'pframe2']);
        $this->pos['f2pos100'] = $hierarchy_generator->create_hierarchy($this->posfw['pframe2']->id, 'position', ['fullname' => 'f2pos100', 'idnumber' => 'f2pos100']);

        // Organisations
        $this->orgfw['oframe'] = $hierarchy_generator->create_framework('organisation', ['fullname' => 'oframe', 'idnumber' => 'oframe']);
        $this->org['org100'] = $hierarchy_generator->create_hierarchy($this->orgfw['oframe']->id, 'organisation', ['fullname' => 'org100', 'idnumber' => 'org100',
            'depthlevel' => 2, 'sortthread' => '01']);
        $this->org['org200'] = $hierarchy_generator->create_hierarchy($this->orgfw['oframe']->id, 'organisation', ['fullname' => 'org200', 'idnumber' => 'org200',
            'depthlevel' => 2, 'sortthread' => '01']);
        $this->org['org110'] = $hierarchy_generator->create_hierarchy($this->orgfw['oframe']->id, 'organisation', ['fullname' => 'org110', 'idnumber' => 'org110',
            'parentid' => $this->org['org100']->id, 'depthlevel' => 2, 'sortthread' => '01']);
        $this->org['org120'] = $hierarchy_generator->create_hierarchy($this->orgfw['oframe']->id, 'organisation', ['fullname' => 'org120', 'idnumber' => 'org120',
            'parentid' => $this->org['org100']->id, 'depthlevel' => 2, 'sortthread' => '02']);
        $this->org['org111'] = $hierarchy_generator->create_hierarchy($this->orgfw['oframe']->id, 'organisation', ['fullname' => 'org111', 'idnumber' => 'org111',
            'parentid' => $this->org['org110']->id, 'depthlevel' => 3, 'sortthread' => '01']);
        $this->org['org112'] = $hierarchy_generator->create_hierarchy($this->orgfw['oframe']->id, 'organisation', ['fullname' => 'org112', 'idnumber' => 'org112',
            'parentid' => $this->org['org110']->id, 'depthlevel' => 3, 'sortthread' => '02']);

        $this->orgfw['oframe2'] = $hierarchy_generator->create_framework('organisation', ['fullname' => 'oframe2', 'idnumber' => 'oframe2']);
        $this->org['f2org100'] = $hierarchy_generator->create_hierarchy($this->orgfw['oframe2']->id, 'organisation', ['fullname' => 'f2org100', 'idnumber' => 'f2org100']);

        // The Report for content restriction definition
        $this->reportid = $this->create_report('user', 'Test User Report');
        $this->report = new reportbuilder($this->reportid, null, false, null, null, true);

        $update = $DB->get_record('report_builder', ['id' => $this->reportid]);
        $update->accessmode = REPORT_BUILDER_ACCESS_MODE_NONE;
        $update->contentmode = REPORT_BUILDER_CONTENT_MODE_ALL;
        $DB->update_record('report_builder', $update);

        // User job assignments
        $tocreate = [
            1 => [
                ['pos' => $this->pos['pos100'], 'org' => $this->org['org100'], 'parent' => ''],
                ['pos' => $this->pos['f2pos100'], 'org' => $this->org['f2org100'], 'parent' => ''],
            ],
            2 => [
                ['pos' => $this->pos['pos110'], 'org' => $this->org['org100'], 'parent' => '1:0'],
            ],
            3 => [
                ['pos' => $this->pos['pos111'], 'org' => $this->org['org100'], 'parent' => '2:0'],
            ],
            4 => [
                ['pos' => $this->pos['pos100'], 'org' => $this->org['org100'], 'parent' => ''],
                ['pos' => $this->pos['pos200'], 'org' => $this->org['org200'], 'parent' => ''],
            ],
            5 => [
                ['pos' => $this->pos['pos110'], 'org' => $this->org['org110'], 'parent' => '4:0'],
            ],
        ];

        // Now do the actual job assignments
        foreach ($tocreate as $idx => $assignments) {
            $userid = $this->users[$idx]->id;
            $this->hierarchy[$idx] = [
                'ja' => [],
                'posfw' => [],
                'pos' => [],
                'orgfw' => [],
                'org' => [],
            ];
            $userhierarchy = &$this->hierarchy[$idx];

            foreach ($assignments as $tstja) {
                $pos = $tstja['pos'];
                $org = $tstja['org'];
                $parent = $tstja['parent'];

                $jadata = [
                    'userid' => $userid,
                    'fullname' => "User-{$userid} Assignment-1",
                    'idnumber' => $pos->idnumber . $org->idnumber,
                    'positionid' => $pos->id,
                    'organisationid' => $org->id,
                ];

                $parentidx = 0;
                if (!empty($parent)) {
                    $tstparent = explode(':', $parent);
                    $parentidx = (int)$tstparent[0];
                    $parentjaidx = (int)$tstparent[1];

                    $parenthierarchy = &$this->hierarchy[$parentidx];
                    $jadata['managerjaid'] = $parenthierarchy['ja'][$parentjaidx]->id;
                }

                $ja = \totara_job\job_assignment::create($jadata);
                $userhierarchy['ja'][] = $ja;

                if (!in_array($pos->frameworkid, $userhierarchy['posfw'])) {
                    $userhierarchy['posfw'][] = $pos->frameworkid;
                }
                if (!in_array($pos->id, $userhierarchy['pos'])) {
                    $userhierarchy['pos'][] = $pos->id;
                }
                if (!in_array($org->frameworkid, $userhierarchy['orgfw'])) {
                    $userhierarchy['orgfw'][] = $org->frameworkid;
                }
                if (!in_array($org->id, $userhierarchy['org'])) {
                    $userhierarchy['org'][] = $org->id;
                }
            }
        }
    }


    /**
     * Test get_framework for positions with hierarchy content restriction
     */
    function test_hierarchy_get_framework_pos_restriction() {
        // Without contentrestiction - should get the framework
        $this->assertEquals($this->posfw['pframe2'], $this->position->get_framework($this->posfw['pframe2']->id));

        // With contentrestictions
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'recursive', 0); //CONTENT_POS_EQUAL

        // user1 should get pframe2
        $userid = $this->users[1]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $this->assertEquals($this->posfw['pframe2'], $this->position->get_framework($this->posfw['pframe2']->id));

        // User2 should get a result if we search for first framework (id == 0)
        $userid = $this->users[2]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $this->assertEquals($this->posfw['pframe'], $this->position->get_framework(0));

        // user2 should get an error if we search for pframe2
        $this->setExpectedException('moodle_exception', get_string('frameworkdoesntexist', 'totara_hierarchy', 'position'));
        $this->position->get_framework($this->posfw['pframe2']->id);
    }

    /**
     * Test get_framework for organisations with hierarchy content restriction
     */
    function test_hierarchy_get_framework_org_restriction() {
        // Without contentrestiction - should get the framework
        $this->assertEquals($this->orgfw['oframe2'], $this->organisation->get_framework($this->orgfw['oframe2']->id));

        // With contentrestictions
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'recursive', 0); //CONTENT_ORG_EQUAL

        // user1 should get oframe2
        $userid = $this->users[1]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $this->assertEquals($this->orgfw['oframe2'], $this->organisation->get_framework($this->orgfw['oframe2']->id));

        // user2 should get a result if we search for a the first framework (id == 0)
        $userid = $this->users[2]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $this->assertEquals($this->orgfw['oframe'], $this->organisation->get_framework(0));

        // User2 should get an error when searching for oframe2
        $this->setExpectedException('moodle_exception', get_string('frameworkdoesntexist', 'totara_hierarchy', 'organisation'));

        $userid = $this->users[2]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $this->organisation->get_framework($this->orgfw['oframe2']->id);
    }

    /**
     * Test get_frameworks for positions with hierarchy content restriction
     */
    function test_hierarchy_get_frameworks_pos_restriction() {
        $fws = $this->position->get_frameworks();

        // should return an array of frameworks
        $this->assertTrue((bool)is_array($fws));
        // the array should include all frameworks
        $this->assertEquals(count($this->posfw), count($fws));

        foreach ($this->posfw as $fw) {
            $this->assertTrue(in_array($fw->id, array_keys($fws)));
        }

        // With contentrestictions
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'recursive', 0); //CONTENT_POS_EQUAL

        // user1 should get both frameworks
        $userid = $this->users[1]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);

        $fws = $this->position->get_frameworks();

        // should return an array of frameworks
        $this->assertTrue((bool)is_array($fws));
        // the array should include all frameworks
        $this->assertEquals(count($this->posfw), count($fws));

        foreach ($this->posfw as $fw) {
            $this->assertTrue(in_array($fw->id, array_keys($fws)));
        }

        // user2 should get only pframe
        $userid = $this->users[2]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);

        $fws = $this->position->get_frameworks();

        // should return an array of frameworks
        $this->assertTrue((bool)is_array($fws));
        // the array should include only pframe
        $this->assertEquals(1, count($fws));
        $this->assertTrue(in_array($this->posfw['pframe']->id, array_keys($fws)));

        // user4 should get only pframe
        $userid = $this->users[4]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);

        $fws = $this->position->get_frameworks();

        // should return an array of frameworks
        $this->assertTrue((bool)is_array($fws));
        // the array should include only pframe
        $this->assertEquals(1, count($fws));
        $this->assertTrue(in_array($this->posfw['pframe']->id, array_keys($fws)));

        // user6 should get none
        $userid = $this->users[6]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);

        $fws = $this->position->get_frameworks();
        $this->assertTrue((bool)empty($fws));
    }

    /**
     * Test get_frameworks for organisations with hierarchy content restriction
     */
    function test_hierarchy_get_frameworks_org_restriction() {
        $fws = $this->organisation->get_frameworks();

        // should return an array of frameworks
        $this->assertTrue((bool)is_array($fws));
        // the array should include all frameworks
        $this->assertEquals(count($this->orgfw), count($fws));

        foreach ($this->orgfw as $fw) {
            $this->assertTrue(in_array($fw->id, array_keys($fws)));
        }

        // With contentrestictions
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'recursive', 0); //CONTENT_ORG_EQUAL

        // user1 should get both frameworks
        $userid = $this->users[1]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);

        $fws = $this->organisation->get_frameworks();

        // should return an array of frameworks
        $this->assertTrue((bool)is_array($fws));
        // the array should include all frameworks
        $this->assertEquals(count($this->orgfw), count($fws));

        foreach ($this->orgfw as $fw) {
            $this->assertTrue(in_array($fw->id, array_keys($fws)));
        }

        // user2 should only get pframe
        $userid = $this->users[2]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);

        $fws = $this->organisation->get_frameworks();

        // should return an array of frameworks
        $this->assertTrue((bool)is_array($fws));
        // the array should include only pframe
        $this->assertEquals(1, count($fws));
        $this->assertTrue(in_array($this->orgfw['oframe']->id, array_keys($fws)));

        // user4 should only get pframe
        $userid = $this->users[4]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);

        $fws = $this->organisation->get_frameworks();

        // should return an array of frameworks
        $this->assertTrue((bool)is_array($fws));
        // the array should include only pframe
        $this->assertEquals(1, count($fws));
        $this->assertTrue(in_array($this->orgfw['oframe']->id, array_keys($fws)));

        // use6 should get none
        $userid = $this->users[6]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);

        $fws = $this->organisation->get_frameworks();
        $this->assertTrue((bool)empty($fws));
    }

    /**
     * Test get_item for positions with hierarchy content restriction
     */
    function test_hierarchy_get_item_pos_restricted() {
        // without content restrictions
        $this->assertEquals($this->pos['f2pos100'], $this->position->get_item($this->pos['f2pos100']->id));

        // add content restrictions
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'recursive', 0); //CONTENT_POS_EQUAL

        // user1 should get f2pos100
        $userid = $this->users[1]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $this->assertEquals($this->pos['f2pos100'], $this->position->get_item($this->pos['f2pos100']->id));

        // user2 should not get f2pos100
        $userid = $this->users[2]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $out = $this->position->get_item($this->pos['f2pos100']->id);
        $this->assertTrue(empty($this->position->get_item($this->pos['f2pos100']->id)));
    }

    /**
     * Test get_item for organisations with hierarchy content restriction
     */
    function test_hierarchy_get_item_org_restricted() {
        // without content restrictions
        $this->assertEquals($this->org['f2org100'], $this->organisation->get_item($this->org['f2org100']->id));

        // add content restrictions
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'recursive', 0); //CONTENT_ORG_EQUAL

        // user1 should get f2org100
        $userid = $this->users[1]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $this->assertEquals($this->org['f2org100'], $this->organisation->get_item($this->org['f2org100']->id));

        // user2 should not get f2org100
        $userid = $this->users[2]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $this->assertTrue(empty($this->organisation->get_item($this->org['f2org100']->id)));
    }

    /**
     * Test get_items for positions with hierarchy content restriction
     */
    function test_hierarchy_get_items_pos_restricted() {
        // without content restriction should return an array of items
        $this->position->frameworkid = $this->posfw['pframe']->id;
        $items = $this->position->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(6, count($items));
        $this->position->frameworkid = $this->posfw['pframe2']->id;
        $items = $this->position->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));

        // with content restrictions
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'recursive', 0); //CONTENT_POS_EQUAL

        // user1 has 1 position in pframe and 1 in pframe2
        $userid = $this->users[1]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);

        $this->position->frameworkid = $this->posfw['pframe']->id;
        $items = $this->position->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertEquals($this->pos['pos100'], current($items));

        $this->position->frameworkid = $this->posfw['pframe2']->id;
        $items = $this->position->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertEquals($this->pos['f2pos100'], current($items));

        // user2 has 1 item in pframe, but none pframe2
        $userid = $this->users[2]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);

        $this->position->frameworkid = $this->posfw['pframe']->id;
        $items = $this->position->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertEquals($this->pos['pos100'], current($items)); // includes parent to allow hierarchy tree visualisation
        $this->assertEquals($this->pos['pos110'], next($items));

        $this->position->frameworkid = $this->posfw['pframe2']->id;
        $items = $this->position->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(0, count($items));
    }

    /**
     * Test get_items for organisations with hierarchy content restriction
     */
    function test_hierarchy_get_items_org_restricted() {
        // without content restriction should return an array of items
        $this->organisation->frameworkid = $this->orgfw['oframe']->id;
        $items = $this->organisation->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(6, count($items));
        $this->organisation->frameworkid = $this->orgfw['oframe2']->id;
        $items = $this->organisation->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));

        // with content restrictions
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'recursive', 0); //CONTENT_ORG_EQUAL

        // user1 has 1 organisation in oframe and 1 in oframe2
        $userid = $this->users[1]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);

        $this->organisation->frameworkid = $this->orgfw['oframe']->id;
        $items = $this->organisation->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertEquals($this->org['org100'], current($items));

        $this->organisation->frameworkid = $this->orgfw['oframe2']->id;
        $items = $this->organisation->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertEquals($this->org['f2org100'], current($items));

        // user2 has 1 item in oframe, but none oframe2
        $userid = $this->users[2]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);

        $this->organisation->frameworkid = $this->orgfw['oframe']->id;
        $items = $this->organisation->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertEquals($this->org['org100'], current($items));

        $this->organisation->frameworkid = $this->orgfw['oframe2']->id;
        $items = $this->organisation->get_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(0, count($items));
    }

    /**
     * Test get_items_by_parent for positions with hierarchy content restriction
     */
    function test_hierarchy_get_items_by_parent_pos_restricted() {
        // Without content restrictions
        // should return an array of items belonging to specified parent
        $items = $this->position->get_items_by_parent($this->pos['pos100']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos110']->id, $items));
        $this->assertTrue(array_key_exists($this->pos['pos120']->id, $items));

        // if no parent specified should return root level items
        $items = $this->position->get_items_by_parent();
        $this->assertEquals(3, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos100']->id, $items));
        $this->assertTrue(array_key_exists($this->pos['pos200']->id, $items));
        $this->assertTrue(array_key_exists($this->pos['f2pos100']->id, $items));

        // With content restrictions
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'recursive', 0); //CONTENT_POS_EQUAL

        // user1 - no children of pos100
        $userid = $this->users[1]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->position->get_items_by_parent($this->pos['pos100']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(0, count($items));

        // user1 - 2 root items
        $items = $this->position->get_items_by_parent();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos100']->id, $items));
        $this->assertTrue(array_key_exists($this->pos['f2pos100']->id, $items));

        // user2 - 1 child of pos100
        $userid = $this->users[2]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->position->get_items_by_parent($this->pos['pos100']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos110']->id, $items));

        // user2 - root item not allowed, but include it anyway so that we print hierarchy tree
        $items = $this->position->get_items_by_parent();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos100']->id, $items));
    }

    /**
     * Test get_items_by_parent for organisations with hierarchy content restriction
     */
    function test_hierarchy_get_items_by_parent_org_restricted() {
        // Without content restrictions
        // should return an array of items belonging to specified parent
        $items = $this->organisation->get_items_by_parent($this->org['org100']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->org['org110']->id, $items));
        $this->assertTrue(array_key_exists($this->org['org120']->id, $items));

        // if no parent specified should return root level items
        $items = $this->organisation->get_items_by_parent();
        $this->assertEquals(3, count($items));
        $this->assertTrue(array_key_exists($this->org['org100']->id, $items));
        $this->assertTrue(array_key_exists($this->org['org200']->id, $items));
        $this->assertTrue(array_key_exists($this->org['f2org100']->id, $items));

        // With content restrictions
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'recursive', 0); //CONTENT_ORG_EQUAL

        // user1 - no children of org100
        $userid = $this->users[1]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->organisation->get_items_by_parent($this->org['org100']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(0, count($items));

        // user1 - 2 root items
        $items = $this->organisation->get_items_by_parent();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->org['org100']->id, $items));
        $this->assertTrue(array_key_exists($this->org['f2org100']->id, $items));

        // user2 - no children of org100
        $userid = $this->users[2]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->organisation->get_items_by_parent($this->org['org100']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(0, count($items));

        // user2 - 1 root items
        $items = $this->organisation->get_items_by_parent();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertTrue(array_key_exists($this->org['org100']->id, $items));

    }

    /**
     * Test get_all_root_items for positions with hierarchy content restriction
     */
    function test_hierarchy_get_all_root_items_pos_restricted() {
        // Without content restriction
        // should return root items for framework
        $this->position->frameworkid = $this->posfw['pframe']->id;
        $items = $this->position->get_all_root_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos100']->id, $items));
        $this->assertTrue(array_key_exists($this->pos['pos200']->id, $items));

        // With content restrictions
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'recursive', 0); //CONTENT_POS_EQUAL

        // user1 - pos100 only
        $userid = $this->users[1]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->position->get_all_root_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos100']->id, $items));

        // Return all root items of user1
        $items = $this->position->get_all_root_items(true);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos100']->id, $items));
        $this->assertTrue(array_key_exists($this->pos['f2pos100']->id, $items));
    }

    /**
     * Test get_all_root_items for organisations with hierarchy content restriction
     */
    function test_hierarchy_get_all_root_items_org_restricted() {
        // Without content restriction
        // should return root items for framework
        $this->organisation->frameworkid = $this->orgfw['oframe']->id;
        $items = $this->organisation->get_all_root_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->org['org100']->id, $items));
        $this->assertTrue(array_key_exists($this->org['org200']->id, $items));

        // With content restrictions
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'recursive', 0); //CONTENT_ORG_EQUAL

        // user1 - org100 only
        $userid = $this->users[1]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->organisation->get_all_root_items();
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertTrue(array_key_exists($this->org['org100']->id, $items));

        // Return all root items of user1
        $items = $this->organisation->get_all_root_items(true);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->org['org100']->id, $items));
        $this->assertTrue(array_key_exists($this->org['f2org100']->id, $items));
    }

    /**
     * Test get_item_descendants for positions with hierarchy content restriction
     */
    function test_hierarchy_get_item_descendants_pos_restricted() {
        // Without content restriction
        // should return an array of items
        $items = $this->position->get_item_descendants($this->pos['pos110']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(3, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos110']->id, $items));
        $this->assertTrue(array_key_exists($this->pos['pos111']->id, $items));
        $this->assertTrue(array_key_exists($this->pos['pos112']->id, $items));

        // With content restrictions
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'recursive', 0); //CONTENT_POS_EQUAL

        // user1 - None
        $userid = $this->users[1]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->position->get_item_descendants($this->pos['pos110']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(0, count($items));

        // user2 - pos110 only
        $userid = $this->users[2]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->position->get_item_descendants($this->pos['pos110']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos110']->id, $items));

        // user3 - pos111 only
        $userid = $this->users[3]->id;
        $this->position->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->position->get_item_descendants($this->pos['pos110']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(2, count($items));
        $this->assertTrue(array_key_exists($this->pos['pos110']->id, $items)); // includes parent so that we can construct hierarchy tree
        $this->assertTrue(array_key_exists($this->pos['pos111']->id, $items));
    }

    /**
     * Test get_item_descendants for organisations with hierarchy content restriction
     */
    function test_hierarchy_get_item_descendants_org_restricted() {
        // Without content restriction
        // should return an array of items
        $items = $this->organisation->get_item_descendants($this->org['org110']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(3, count($items));
        $this->assertTrue(array_key_exists($this->org['org110']->id, $items));
        $this->assertTrue(array_key_exists($this->org['org111']->id, $items));
        $this->assertTrue(array_key_exists($this->org['org112']->id, $items));

        // With content restrictions
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'recursive', 0); //CONTENT_ORG_EQUAL

        // user1 - None
        $userid = $this->users[1]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->organisation->get_item_descendants($this->org['org110']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(0, count($items));

        // user2 - None
        $userid = $this->users[2]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->organisation->get_item_descendants($this->org['org110']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(0, count($items));

        // user3 - org110 only
        $userid = $this->users[5]->id;
        $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
        $items = $this->organisation->get_item_descendants($this->org['org110']->id);
        $this->assertTrue((bool)is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertTrue(array_key_exists($this->org['org110']->id, $items));
    }

    /**
     * Test get_hierarchy_item_adjacent_peer for positions with hierarchy content restriction
     */
    function test_hierarchy_get_hierarchy_item_adjacent_peer_pos_restricted() {
        // Without content restriction
        // if an adjacent peer exists, should return its id
        $item = $this->position->get_hierarchy_item_adjacent_peer($this->pos['pos110'], HIERARCHY_ITEM_BELOW);
        $this->assertEquals($this->pos['pos120']->id, $item);
        // should return false if no adjacent peer exists in the direction specified
        $item = $this->position->get_hierarchy_item_adjacent_peer($this->pos['pos110'], HIERARCHY_ITEM_ABOVE);
        $this->assertFalse($item);

        // With content restrictions
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_pos_content', 'recursive', 0); //CONTENT_POS_EQUAL

        // No user in pos120
        for ($idx = 1; $idx <= 6; $idx++) {
            $userid = $this->users[$idx]->id;
            $this->position->set_content_restriction_from_report($this->reportid, $userid);
            $item = $this->position->get_hierarchy_item_adjacent_peer($this->pos['pos110'], HIERARCHY_ITEM_BELOW);
            $this->assertFalse($item);
        }
    }

    /**
     * Test get_hierarchy_item_adjacent_peer for organisations with hierarchy content restriction
     */
    function test_hierarchy_get_hierarchy_item_adjacent_peer_org_restricted() {
        // Without content restriction
        // if an adjacent peer exists, should return its id
        $item = $this->organisation->get_hierarchy_item_adjacent_peer($this->org['org110'], HIERARCHY_ITEM_BELOW);
        $this->assertEquals($this->org['org120']->id, $item);
        // should return false if no adjacent peer exists in the direction specified
        $item = $this->organisation->get_hierarchy_item_adjacent_peer($this->org['org110'], HIERARCHY_ITEM_ABOVE);
        $this->assertFalse($item);

        // With content restrictions
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'enable', 1);
        reportbuilder::update_setting($this->reportid, 'current_org_content', 'recursive', 0); //CONTENT_ORG_EQUAL

        // No user in org120
        for ($idx = 1; $idx <= 6; $idx++) {
            $userid = $this->users[$idx]->id;
            $this->organisation->set_content_restriction_from_report($this->reportid, $userid);
            $item = $this->organisation->get_hierarchy_item_adjacent_peer($this->org['org110'], HIERARCHY_ITEM_BELOW);
            $this->assertFalse($item);
        }
    }
}
