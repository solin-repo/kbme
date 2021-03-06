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
 * @author David Curry <david.curry@totaralearning.com>
 * @package totara_appraisal
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/totara/appraisal/db/upgradelib.php');

/**
 * Appraisal module PHPUnit archive test class.
 *
 * To test, run this from the command line from the $CFG->dirroot.
 * vendor/bin/phpunit --verbose totara_appraisal_upgradelib_testcase totara/appraisal/tests/upgradelib_test.php
 */
class totara_appraisal_upgradelib_testcase extends advanced_testcase {

    public $users = array();
    public $appgenerator = null;
    public $appraisal = null;
    public $stage = null;
    public $page = null;

    /**
     * Set up the users, certifications and completions.
     */
    public function setup() {
        // Create users.
        for ($i = 1; $i <= 10; $i++) {
            $this->users[$i] = $this->getDataGenerator()->create_user();
        }

        $this->appgenerator = $this->getDataGenerator()->get_plugin_generator('totara_appraisal');
        $this->appraisal = $this->appgenerator->create_appraisal();
        $this->stage = $this->appgenerator->create_stage($this->appraisal->id);
        $this->page = $this->appgenerator->create_page($this->stage->id);
    }

    /**
     * Check that $param1 is json encoded for all aggregate questions.
     */
    public function test_appraisals_aggregate_questions_encoding() {
        global $DB;

        $this->resetAfterTest();

        $ratingspage = $this->page;
        $aggregatepage = $this->appgenerator->create_page($this->stage->id);

        // Setup a couple of ratings to be aggregated.
        $ratingdata = array(
            'datatype' => 'ratingnumeric',
            'rangefrom' => 1,
            'rangeto' => 10,
            'list' => 1,
            'setdefault' => false,
        );

        $ratingdata['name'] = 'Rating#1';
        $rating1 = $this->appgenerator->create_complex_question($ratingspage->id, $ratingdata);
        $ratingdata['name'] = 'Rating#2';
        $rating2 = $this->appgenerator->create_complex_question($ratingspage->id, $ratingdata);
        $ratingdata['name'] = 'Rating#3';
        $rating3 = $this->appgenerator->create_complex_question($ratingspage->id, $ratingdata);

        $aggregates = array();
        $expectedparam = array();
        $aggregatedata = array(
            'datatype' => 'aggregate',
            'aggregateaverage' => 1,
            'aggregatemedian' => 1,
            'aggregateincludezero' => 0,
            'aggregateincludeunanswered' => 0,
        );

        $aggregatedata['name'] = 'Aggregate#1';
        $aggregatedata['multiselectfield'] = array((string)$rating1->id, (string)$rating2->id);
        $aggregate1 = $this->appgenerator->create_complex_question($aggregatepage->id, $aggregatedata);
        $aggregates[$aggregate1->id] = $aggregate1;

        $aggregatedata['name'] = 'Aggregate#2';
        $aggregatedata['multiselectfield'] = array((string)$rating2->id, (string)$rating3->id);
        $aggregate2 = $this->appgenerator->create_complex_question($aggregatepage->id, $aggregatedata);
        $aggregates[$aggregate2->id] = $aggregate2;

        $aggregatedata['name'] = 'Aggregate#3';
        $aggregatedata['multiselectfield'] = array((string)$rating1->id, (string)$rating2->id, (string)$rating3->id);
        $aggregate3 = $this->appgenerator->create_complex_question($aggregatepage->id, $aggregatedata);
        $aggregates[$aggregate3->id] = $aggregate2;

        // Now we need to mangle the data a little so we can test the fixes, leave aggregate 1 as a control.

        // Aggregate 2 should become "2,3" a comma seperated list in quotes.
        $update2 = $DB->get_record('appraisal_quest_field', array('id' => $aggregate2->id));
        $update2->param1 = '"' . $rating2->id . ',' . $rating3->id . '"';
        $DB->update_record('appraisal_quest_field', $update2);

        // Aggregate 3 should become 1,2,3 a comma seperated list without quotes.
        $update3 = $DB->get_record('appraisal_quest_field', array('id' => $aggregate3->id));
        $update3->param1 = $rating1->id . ',' . $rating2->id . ',' . $rating3->id;
        $DB->update_record('appraisal_quest_field', $update3);

        // Check the mangled parameters after updates.
        $this->assertEquals(json_encode($aggregate1->param1), $DB->get_field('appraisal_quest_field', 'param1', array('id' => $aggregate1->id)));
        $this->assertNotEquals(json_encode($aggregate2->param1), $DB->get_field('appraisal_quest_field', 'param1', array('id' => $aggregate2->id)));
        $this->assertNotEquals(json_encode($aggregate3->param1), $DB->get_field('appraisal_quest_field', 'param1', array('id' => $aggregate3->id)));

        // Now clean up the data.
        appraisals_upgrade_clean_aggregate_params();

        // Check the clean up has fixed the parameters.
        $this->assertEquals(json_encode($aggregate1->param1), $DB->get_field('appraisal_quest_field', 'param1', array('id' => $aggregate1->id)));
        $this->assertEquals(json_encode($aggregate2->param1), $DB->get_field('appraisal_quest_field', 'param1', array('id' => $aggregate2->id)));
        $this->assertEquals(json_encode($aggregate3->param1), $DB->get_field('appraisal_quest_field', 'param1', array('id' => $aggregate3->id)));

        // Check some of the other fields to make sure they are unaffected.
        $checkfields = $DB->get_record('appraisal_quest_field', array('id' => $aggregate2->id));
        $this->assertEquals($aggregate2->name, $checkfields->name);
        $this->assertEquals($aggregate2->param2, $checkfields->param2);
        $this->assertEquals($aggregate2->param3, $checkfields->param3);

        // Check the other non-aggregate question types are unaffected.
        $checkrating = $DB->get_record('appraisal_quest_field', array('id' => $rating2->id));
        $this->assertEquals($rating2->name, $checkrating->name);
        $this->assertEquals(json_encode($rating2->param1), $checkrating->param1);
        $this->assertEquals($rating2->param2, $checkrating->param2);
    }

    public function test_totara_appraisal_upgrade_fix_inconsistent_multichoice_param1() {
        $this->resetAfterTest();

        global $DB;

        // Insert some fake data.
        $question = new stdClass();
        $question->appraisalstagepageid = 123;
        $question->name = '1 fix me 1';
        $question->sortorder = 234;
        $question->datatype = 'multichoicemulti';
        $question->requried = 0;
        $question->param1 = '"345"';
        $question->param2 = '"456"';
        $question->param3 = '"567"';
        $question->param4 = '"678"';
        $question->param5 = '"789"';
        $DB->insert_record('appraisal_quest_field', $question);

        $question->name = '2 fix me 2';
        $question->datatype = 'multichoicesingle';
        $question->param1 = '"346"';
        $question->param2 = '456';
        $question->param3 = '{"1":"2","3":"4"}';
        $question->param4 = '[]';
        $question->param5 = null;
        $DB->insert_record('appraisal_quest_field', $question);

        $question->name = '3 leave me type';
        $question->datatype = 'someothertype';
        $question->param1 = '"347"';
        $DB->insert_record('appraisal_quest_field', $question);

        $question->name = '4 leave me int';
        $question->datatype = 'multichoicesingle';
        $question->param1 = '348';
        $DB->insert_record('appraisal_quest_field', $question);

        $question->name = '5 leave me null';
        $question->datatype = 'multichoicemulti';
        $question->param1 = null;
        $DB->insert_record('appraisal_quest_field', $question);

        $question->name = '6 leave me empty array';
        $question->datatype = 'multichoicemulti';
        $question->param1 = '[]';
        $DB->insert_record('appraisal_quest_field', $question);

        $question->name = '7 leave me array';
        $question->datatype = 'multichoicemulti';
        $question->param1 = '{"1":"2","3":"4"}';
        $DB->insert_record('appraisal_quest_field', $question);

        // Construct the expected results.
        $expectedresults = $DB->get_records('appraisal_quest_field', array(), 'name');
        $expectedfixme1 = reset($expectedresults);
        $expectedfixme1->param1 = '345';
        $expectedfixme2 = next($expectedresults);
        $expectedfixme2->param1 = '346';

        // Run the function.
        totara_appraisal_upgrade_fix_inconsistent_multichoice_param1();

        // Check the results.
        $actualresults = $DB->get_records('appraisal_quest_field', array(), 'name');
        $this->assertEquals($expectedresults, $actualresults);
    }
}
