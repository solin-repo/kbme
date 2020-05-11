@javascript @mod @mod_facetoface @totara
Feature: Reserve spaces in waitlist in Seminar
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
      | student3 | Sam3      | Student3 | student3@example.com |
      | manager  | Max       | Manager  | manager@example.com  |
      | teamlead | Torry     | Teamlead | teamlead@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | summary |
      | Course 1 | C1        | 0        |         |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student        |
      | student2 | C1 | student        |
      | manager  | C1 | editingteacher |
    And the following "role assigns" exist:
      | user    | role    | contextlevel | reference |
      | manager | manager | System       |           |
    And the following "position" frameworks exist:
      | fullname      | idnumber |
      | PosHierarchy1 | FW001    |
    And the following "position" hierarchy exists:
      | framework | idnumber | fullname   |
      | FW001     | POS001   | Position1  |
    And the following job assignments exist:
      | user     | position | manager  |
      | student1 | POS001   | manager  |
      | student2 | POS001   | manager  |
      | student3 | POS001   | teamlead |
    And the following "activities" exist:
      | activity   | name              | intro                           | course | idnumber | managerreserve | maxmanagerreserves |
      | facetoface | Test Seminar name | <p>Test Seminar description</p> | C1     | seminar  | 1              | 2                  |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I click on "Test Seminar name" "link"
    And I click on "Add a new event" "link"
    And I click on "Edit session" "link"
    And I set the following fields to these values:
      | timestart[day]     | 1    |
      | timestart[month]   | 2    |
      | timestart[year]    | ## next year ## Y ## |
      | timestart[hour]    | 11   |
      | timestart[minute]  | 00   |
    And I press "OK"
    And I set the following fields to these values:
      | capacity           | 1    |
      | allowoverbook      | 1    |
    And I press "Save changes"
    And I log out

  Scenario: Confirm manager reservations are on waitlist when overbooked
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I click on "Test Seminar name" "link"
    And I click on "Attendees" "link" in the "1 February" "table_row"
    And I set the field "Attendee actions" to "Add users"
    And I set the field "potential users" to "Sam3 Student3, student3@example.com"
    And I press "Add"
    And I press "Continue"
    And I press "Confirm"
    Then I should see "Bulk add attendees success"
    And I should see "Booked" in the "Sam3 Student3" "table_row"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Test Seminar name" "link"
    And I click on "Join waitlist" "link" in the "1 February" "table_row"
    And I press "Join waitlist"
    Then I should see "You have been placed on the waitlist"
    And I log out
    And I log in as "manager"
    And I am on "Course 1" course homepage
    And I click on "Test Seminar name" "link"
    And I click on "Reserve spaces for team" "link" in the "1 February" "table_row"
    And I set the field "Reserve spaces for team" to "1"
    And I press "Update"
    Then I should see "Reserve spaces for team (1/2)"

    And the "generaltable" table should contain the following:
      | Session times | Capacity | Capacity      |
      | 1 February    | 1 / 1    | 2 Wait-listed |
    But the "generaltable" table should not contain the following:
      | Session times | Capacity   |
      | 1 February    | Overbooked |
    And I click on "Attendees" "link" in the "1 February" "table_row"
    Then "table#facetoface_sessions > tbody > tr:nth-child(2)" "css_element" should not exist
    When I switch to "Wait-list" tab
    Then I should see "Wait-listed" in the "Sam1 Student1" "table_row"
    # The following steps must be fixed in TL-23420 as such:
    # And I should see "Wait-listed" in the "Reserved (Max Manager)" "table_row"
    # And "Cancel reservation" "link" should exist in the "Reserved (Max Manager)" "table_row"
    And I should see "Wait-listed" in the "table#facetoface_waitlist > tbody > tr:nth-child(2)" "css_element"

    And I switch to "Attendees" tab
    When I set the field "Attendee actions" to "Remove users"
    And I set the field "Current attendees" to "Sam3 Student3, student3@example.com"
    And I press "Remove"
    And I press "Continue"
    And I press "Confirm"
    Then I should see "Bulk remove users success"
    And I should not see "Sam3 Student3"
    But I should see "Booked" in the "Sam1 Student1" "table_row"

    When I switch to "Wait-list" tab
    And I should see "Wait-listed" in the "table#facetoface_waitlist > tbody > tr:first-child" "css_element"
    When I switch to "Cancellations" tab
    Then I should see "User cancellation" in the "Sam3 Student3" "table_row"

    When I follow "Go back"
    Then the "generaltable" table should contain the following:
      | Session times | Capacity | Capacity      |
      | 1 February    | 1 / 1    | 1 Wait-listed |
    But the "generaltable" table should not contain the following:
      | Session times | Capacity   |
      | 1 February    | Overbooked |
    And I click on "Attendees" "link" in the "1 February" "table_row"

    When I set the field "Attendee actions" to "Remove users"
    And I set the field "Current attendees" to "Sam1 Student1, student1@example.com"
    And I press "Remove"
    And I press "Continue"
    And I press "Confirm"
    Then I should see "Bulk remove users success"
    And I should not see "Sam1 Student1"
    # The following step must be fixed in TL-23420
    But I should see "Booked" in the "table#facetoface_sessions > tbody > tr:first-child" "css_element"

    When I follow "Go back"
    Then the "generaltable" table should contain the following:
      | Session times | Capacity |
      | 1 February    | 1 / 1    |
    But the "generaltable" table should not contain the following:
      | Session times | Capacity   | Capacity    |
      | 1 February    | Overbooked | Wait-listed |
    And I click on "Attendees" "link" in the "1 February" "table_row"

    When I switch to "Cancellations" tab
    Then I should see "User cancellation" in the "Sam1 Student1" "table_row"
    And I should see "User cancellation" in the "Sam3 Student3" "table_row"
