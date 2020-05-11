<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @package mod_facetoface
 */

use mod_facetoface\{grade_helper, seminar, seminar_event, signup, signup_helper};
use mod_facetoface\signup\state\{booked, fully_attended, partially_attended, no_show};

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/facetoface/lib.php');
require_once($CFG->dirroot . '/lib/gradelib.php');

class mod_facetoface_grade_helper_testcase extends advanced_testcase {
    /** @var testing_data_generator */
    private $gen;

    /** @var mod_facetoface_generator */
    private $f2fgen;

    /** @var integer */
    private $course = 0;

    /** @var seminar */
    private $seminar;

    /** @var grade_item */
    private $gradeitem;

    public function setUp() {
        parent::setUp();
        $this->gen = $this->getDataGenerator();
        $this->f2fgen = $this->gen->get_plugin_generator('mod_facetoface');
        $this->course = $this->gen->create_course()->id;
        $this->seminar = new seminar($this->f2fgen->create_instance([
            'name' => 'my seminar',
            'course' => $this->course
        ])->id);
        $this->seminar
            ->set_multiplesessions((int)true)
            ->set_multisignupmaximum(99)
            ->set_multisignupfully(true)
            ->set_multisignuppartly(true)
            ->set_multisignupnoshow(true)
            ->save();
        $this->gradeitem = new grade_item(['itemtype' => 'mod', 'itemmodule' => 'facetoface', 'iteminstance' => $this->seminar->get_id(), 'courseid' => $this->course]);
    }

    public function tearDown() {
        $this->gradeitem = null;
        $this->seminar = null;
        $this->course = 0;
        $this->f2fgen = null;
        $this->gen = null;

        parent::tearDown();
    }

    /**
     * @return integer[]
     */
    private function create_and_enrol_students(int $num_students): array {
        global $DB;
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $students = [];
        while ($num_students--) {
            $student = $this->gen->create_user()->id;
            $this->gen->enrol_user($student, $this->course, $studentrole->id);
            $students[] = $student;
        }
        return $students;
    }

    /**
     * Akin to the behat step "I use magic to adjust the seminar event"
     */
    private function magic_adjust_event(seminar_event $seminarevent, int $timestart, int $timefinish) {
        global $DB;
        $DB->execute('UPDATE {facetoface_sessions_dates} SET timestart = ?, timefinish = ? WHERE sessionid = ?', [
            $timestart,
            $timefinish,
            $seminarevent->get_id()
        ]);
        return [$timestart, $timefinish];
    }

    public function prepare_yet_another_seminar_with_attendees(array $attendees): seminar {
        $seminar = new seminar($this->f2fgen->create_instance([
            'name' => 'test seminar',
            'course' => $this->course,
            'attendancetime' => seminar::EVENT_ATTENDANCE_UNRESTRICTED
        ])->id);

        $seminarevent = new seminar_event($this->f2fgen->add_session(['facetoface' => $seminar->get_id(), 'sessiondates' => [time() + YEARSECS]]));
        $attendance = [];
        foreach ($attendees as $student => $state) {
            $signup = signup_helper::signup(signup::create($student, $seminarevent));
            $attendance[$signup->get_id()] = $state;
        }
        $processed = signup_helper::process_attendance($seminarevent, $attendance);
        $this->assertTrue($processed);

        return $seminar;
    }

    /**
     * Ensure grade_helper::get_final_grades() returns false if no one sign ups a seminar.
     */
    public function test_get_final_grades_with_no_signups() {
        $this->create_and_enrol_students(5);
        $grades = grade_helper::get_final_grades($this->seminar, 0);
        $this->assertFalse($grades);
    }

    /**
     * Ensure grade_helper::get_final_grades() returns false if no event grades are set.
     */
    public function test_get_final_grades_with_no_grades() {
        $students = $this->create_and_enrol_students(5);
        $seminarevent = new seminar_event($this->f2fgen->add_session(['facetoface' => $this->seminar->get_id(), 'sessiondates' => [time() + DAYSECS]]));
        $attendance = [];
        foreach ($students as $student) {
            $signup = signup_helper::signup(signup::create($student, $seminarevent));
            $attendance[$signup->get_id()] = fully_attended::get_code();
        }
        $this->magic_adjust_event($seminarevent, time() - DAYSECS * 2, time() - DAYSECS);
        // First take attendance as fully attended, then set it to not set.
        $processed = signup_helper::process_attendance($seminarevent, $attendance);
        $this->assertTrue($processed);
        foreach ($attendance as $signupid => $state) {
            $attendance[$signupid] = booked::get_code();
        }
        $processed = signup_helper::process_attendance($seminarevent, $attendance);
        $this->assertTrue($processed);
        $grades = grade_helper::get_final_grades($this->seminar, 0);
        $this->assertFalse($grades);
    }

    /**
     * Ensure grade_helper::get_final_grades() excludes cancellations.
     */
    public function test_get_final_grades_with_grades_but_cancelled() {
        $students = $this->create_and_enrol_students(2);
        $seminarevent = new seminar_event($this->f2fgen->add_session(['facetoface' => $this->seminar->get_id(), 'sessiondates' => [time() + DAYSECS]]));
        $signups = [];
        $attendance = [];
        foreach ($students as $student) {
            $signup = signup_helper::signup(signup::create($student, $seminarevent));
            $signups[] = $signup;
            $attendance[$signup->get_id()] = fully_attended::get_code();
        }
        $this->magic_adjust_event($seminarevent, time() - DAYSECS * 2, time() - DAYSECS);
        // Take attendance.
        $processed = signup_helper::process_attendance($seminarevent, $attendance);
        $this->assertTrue($processed);
        $grades = grade_helper::get_final_grades($this->seminar, 0);
        $this->assertNotFalse($grades);
        $this->assertSame(100., $grades[$students[0]]->rawgrade);
        $this->assertSame(100., $grades[$students[1]]->rawgrade);

        // To cancel a user, first set his (her?) attendance state back to 'not set'.
        $attendance = [$signups[0]->get_id() => booked::get_code()];
        $processed = signup_helper::process_attendance($seminarevent, $attendance);
        $this->assertTrue($processed);
        $grades = grade_helper::get_final_grades($this->seminar, 0);
        // Make sure student #0 is now gone.
        $this->assertNotFalse($grades);
        $this->assertArrayNotHasKey($students[0], $grades);
        // And adjust time.
        $this->magic_adjust_event($seminarevent, time() + DAYSECS, time() + DAYSECS * 2);
        // Now ready to cancel.
        signup_helper::user_cancel($signups[0], 'Duh');

        // Make sure student #0 is already gone, but student #1 is still there.
        $grades = grade_helper::get_final_grades($this->seminar, 0);
        $this->assertNotFalse($grades);
        $this->assertArrayNotHasKey($students[0], $grades);
        $this->assertSame(100., $grades[$students[1]]->rawgrade);

        // Cancel event.
        $this->assertTrue($seminarevent->cancel());
        // Make sure both students are gone.
        $grades = grade_helper::get_final_grades($this->seminar, 0);
        $this->assertFalse($grades);
    }

    /**
     * Test grade_helper::get_final_grades() with grade_helper::FORMAT_xxx.
     */
    public function test_get_final_grades_with_format() {
        $students = $this->create_and_enrol_students(2);

        $sessiondates = [
            (object)[
                'timestart' => time()  + 11112,
                'timefinish' => time() + 22224,
                'sessiontimezone' => 'Pacific/Auckland'
            ],
            (object)[
                'timestart' => time()  + 271828,
                'timefinish' => time() + 314159,
                'sessiontimezone' => 'Pacific/Auckland'
            ]
        ];
        $seminarevents = [
            new seminar_event($this->f2fgen->add_session(['facetoface' => $this->seminar->get_id(), 'sessiondates' => [$sessiondates[0]]])),
            new seminar_event($this->f2fgen->add_session(['facetoface' => $this->seminar->get_id(), 'sessiondates' => [$sessiondates[1]]]))
        ];
        $signups = [
            signup_helper::signup(signup::create($students[0], $seminarevents[0])),
            signup_helper::signup(signup::create($students[1], $seminarevents[0]))
        ];
        $attendance = [
            $signups[0]->get_id() => fully_attended::get_code(),
            $signups[1]->get_id() => no_show::get_code()
        ];
        // Move event #0 to the past before taking attendance.
        [$sessiondates[0]->timestart, $sessiondates[0]->timefinish] = $this->magic_adjust_event($seminarevents[0], time() - 11112, time() - 22224);
        $processed = signup_helper::process_attendance($seminarevents[0], $attendance);
        $this->assertTrue($processed);
        $signups = [
            signup_helper::signup(signup::create($students[0], $seminarevents[1])),
            signup_helper::signup(signup::create($students[1], $seminarevents[1]))
        ];
        $attendance = [
            $signups[0]->get_id() => no_show::get_code(),
            $signups[1]->get_id() => partially_attended::get_code()
        ];
        // Move event #1 to the past before taking attendance.
        [$sessiondates[1]->timestart, $sessiondates[1]->timefinish] = $this->magic_adjust_event($seminarevents[1], time() - 271828, time() - 314159);
        $processed = signup_helper::process_attendance($seminarevents[1], $attendance);
        $this->assertTrue($processed);

        // Totara 12 always uses the last saved grade as final grade.
        $grades = grade_helper::get_final_grades($this->seminar, 0, grade_helper::FORMAT_GRADELIB);
        $this->assertNotFalse($grades);
        $expected = (object)['id' => $students[0], 'userid' => $students[0], 'rawgrade' => 0.00];
        $this->assertEquals($expected, $grades[$students[0]]);
        $expected = (object)['id' => $students[1], 'userid' => $students[1], 'rawgrade' => 50.0];
        $this->assertEquals($expected, $grades[$students[1]]);

        $grades = grade_helper::get_final_grades($this->seminar, 0, grade_helper::FORMAT_FACETOFACE);
        $this->assertNotFalse($grades);
        $expected = (object)['userid' => $students[0], 'rawgrade' => 0.00, 'timecompleted' => $sessiondates[1]->timefinish];
        $this->assertEquals($expected, $grades[$students[0]]);
        $expected = (object)['userid' => $students[1], 'rawgrade' => 50.0, 'timecompleted' => $sessiondates[1]->timefinish];
        $this->assertEquals($expected, $grades[$students[1]]);
    }
}
