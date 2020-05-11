<?php
/**
 * This file is part of Totara LMS
 *
 * Copyright (C) 2017 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package auth_approved
 */

class auth_approved_auth_testcase extends advanced_testcase {

    public function test_basic_auth_structure() {
        global $CFG;
        require_once($CFG->dirroot . '/auth/approved/auth.php');

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user([
            'username' => 'test',
            'password' => 'test'
        ]);

        $auth = new auth_plugin_approved();

        $this->assertFalse($auth->user_login('false', 'false'));
        $this->assertTrue($auth->user_login('test', 'test'));
        $this->assertTrue($auth->user_update_password($user, 'newtest'));
        $this->assertTrue($auth->can_signup());
        $this->assertInstanceOf('\auth_approved\form\signup', $auth->signup_form());
        $this->assertFalse($auth->can_confirm());
        $this->assertFalse($auth->prevent_local_passwords());
        $this->assertTrue($auth->is_internal());
        $this->assertTrue($auth->can_change_password());
        $this->assertSame(null, $auth->change_password_url());
        $this->assertTrue($auth->can_reset_password());
        $this->assertTrue($auth->can_be_manually_set());

    }

}