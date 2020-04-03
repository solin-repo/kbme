@core @core_calendar @mod @mod_facetoface @javascript
Feature: Config setting calendar_adminseesall allows admin to view all events on calendar
  In order to view all events as an admin
  I need the correct capability
  As well as the adminseesall setting turned on

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | caladmin | Calendar  | Admin    | caladmin@example.com |
      | learner1 | Learner   | One      | learner1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
      | Course 3 | C3        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | caladmin | C2     | student |
      | learner1 | C2     | student |
    And the following "roles" exist:
      | shortname |
      | seeall    |
    And the following "system role assigns" exist:
      | user     | role   |
      | caladmin | seeall |
    And the following "permission overrides" exist:
      | capability                    | permission | role   | contextlevel | reference |
      | moodle/calendar:manageentries | Allow      | seeall | System       |           |
    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test Face-to-face One         |
      | Description                             | Test Face-to-face description |
      | Show entry on user's calendar           | 1                        |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][month]   | 0                |
      | timestart[0][day]     | +1               |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][month]  | 0                |
      | timefinish[0][day]    | +1               |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
    And I press "Save changes"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 2"
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test Face-to-face Two         |
      | Description                             | Test Face-to-face description |
      | Show entry on user's calendar           | 1                        |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][month]   | 0                |
      | timestart[0][day]     | +1               |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][month]  | 0                |
      | timefinish[0][day]    | +1               |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
    And I press "Save changes"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 3"
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test Face-to-face Three       |
      | Description                             | Test Face-to-face description |
      | Show entry on user's calendar           | 1                        |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][month]   | 0                |
      | timestart[0][day]     | +1               |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][month]  | 0                |
      | timefinish[0][day]    | +1               |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
    And I press "Save changes"
    And I log out

  Scenario: Without the setting or moodle/calendar:manageentries capability, a user will only see events from enrolled courses
    Given I log in as "learner1"
    And I click on "Go to calendar" "link"
    Then I should not see "Test Face-to-face One"
    And I should see "Test Face-to-face Two"
    And I should not see "Test Face-to-face Three"

  Scenario: Combination of moodle/calendar:manageentries capability and calendar_adminseesall setting allows a user to see all events
    Given I log in as "caladmin"
    And I click on "Go to calendar" "link"
    Then I should not see "Test Face-to-face One"
    And I should see "Test Face-to-face Two"
    And I should not see "Test Face-to-face Three"
    When I log out
    And I log in as "admin"
    And I set the following administration settings values:
     | calendar_adminseesall | 1 |
    And I log out
    And I log in as "caladmin"
    And I click on "Go to calendar" "link"
    Then I should see "Test Face-to-face One"
    And I should see "Test Face-to-face Two"
    And I should see "Test Face-to-face Three"
