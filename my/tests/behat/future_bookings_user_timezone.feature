@javascript @mod @mod_facetoface @totara @totara_reportbuilder
Feature: My Future Bookings seminar sessions report overview
  In order to see all student future bookings
  As an admin
  I need to create an user with different timezone and see user future bookings

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                   | timezone         |
      | alice    | Alice     | Smith    | alice.smith@example.com | America/New_York |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | completionstartonenrol |
      | Course 1 | C1        | 0        | 1                | 1                      |
    And the following "course enrolments" exist:
      | user  | course | role    |
      | alice | C1     | student |
    And the following "activities" exist:
      | activity   | name            | course | idnumber | multiplesessions |
      | facetoface | Seminar TL-9395 | C1     | S9395    | 1                |

    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Seminar TL-9395"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown          | Yes  |
      | sessiontimezone[0]     | Europe/Prague |
      | timestart[0][day]      | 1    |
      | timestart[0][month]    | 1    |
      | timestart[0][year]     | 2030 |
      | timestart[0][hour]     | 11   |
      | timestart[0][minute]   | 00   |
      | timestart[0][timezone] | Europe/Prague |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
      | timefinish[0][timezone] | Europe/Prague |
    And I press "Save changes"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 2    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 2    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
    And I press "Save changes"

    And I click on "Attendees" "link" in the "Australia/Perth" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Alice Smith, alice.smith@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    And I click on "Go back" "link"

    And I click on "Attendees" "link" in the "Europe/Prague" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Alice Smith, alice.smith@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"

    And I log out

  @javascript
  Scenario: Login as a student and check My future bookings event timezones
    And I log in as "alice"
    And I click on "My Bookings" in the totara menu
    And I should see "America/New_York"
    And I should see "Europe/Prague"

