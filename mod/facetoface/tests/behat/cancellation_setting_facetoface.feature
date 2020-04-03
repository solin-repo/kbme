@mod @mod_facetoface @totara
Feature: Cancellation for session
  In order to allow or not cancellations in Face to face sessions
  As a teacher
  I need to create Face to face sessions with different settings (always/never/cut-off period)

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username     | firstname | lastname  | email                 |
      | teacher1     | Terry3    | Teacher   | teacher@example.com   |
      | student1     | Sam1      | Student1  | student1@example.com  |
      | student2     | Sam2      | Student2  | student2@example.com  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test facetoface name        |
      | Description                             | Test facetoface description |
      | Allow multiple sessions signup per user | 1                           |
    And I log out

  @javascript
  Scenario: User can cancel their booking at any time until session starts
    Given I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][day]     | +1               |
      | timestart[0][month]   | 0                |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][day]    | +1               |
      | timefinish[0][month]  | 0                |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
      | capacity              | 3                |
    And I click on "Allow at any time" "radio"
    And I press "Save changes"
    And I log out

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Sign-up" in row 1
    And I press "Sign-up"
    Then I should see "Your booking has been completed."
    And I should see "Cancel booking"
    When I click on the link "Cancel booking" in row 1
    And I press "Yes"
    And I should not see "Cancel booking"
    And I log out

  @javascript
  Scenario: User cannot cancel their booking (Never)
    Given I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][day]     | +2               |
      | timestart[0][month]   | 0                |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][day]    | +2               |
      | timefinish[0][month]  | 0                |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
      | capacity              | 3                |
    And I click on "Never allow" "radio"
    And I press "Save changes"
    And I log out

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Sign-up" in row 1
    And I press "Sign-up"
    Then I should see "Your booking has been completed."
    And I should not see "Cancel booking"
    And I log out

  @javascript
  Scenario: User can cancel their booking if cut-off period is not reached
    Given I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][day]     | +3               |
      | timestart[0][month]   | 0                |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][day]    | +3               |
      | timefinish[0][month]  | 0                |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
      | capacity              | 3                |
    And I click on "Allow until cut-off reached" "radio"
    And I press "Save changes"
    And I log out

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Sign-up" in row 1
    And I press "Sign-up"
    Then I should see "Your booking has been completed."
    And I should see "Cancel booking"
    And I log out

    And I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on "Edit session" "link"
    And I fill facetoface session with relative date in form data:
      | datetimeknown                | Yes              |
      | sessiontimezone[0]           | Pacific/Auckland |
      | timestart[0][day]            | +1               |
      | timestart[0][month]          | 0                |
      | timestart[0][year]           | 0                |
      | timestart[0][hour]           | -1               |
      | timestart[0][minute]         | 0                |
      | timefinish[0][day]           | +1               |
      | timefinish[0][month]         | 0                |
      | timefinish[0][year]          | 0                |
      | timefinish[0][hour]          | -1               |
      | timefinish[0][minute]        | 0                |
      | cancellationcutoff[number]   | 2                |
      | cancellationcutoff[timeunit] | days             |
    And I press "Save changes"
    And I log out

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    Then I should not see "Cancel booking"
    And I log out

  @javascript
  Scenario: User can cancel their booking at any time until session starts even when cancellation note field is deleted
    Given I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][day]     | +1               |
      | timestart[0][month]   | 0                |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][day]    | +1               |
      | timefinish[0][month]  | 0                |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
      | capacity              | 3                |
    And I click on "Allow at any time" "radio"
    And I press "Save changes"

    And I click on "Home" in the totara menu
    And I navigate to "Custom Fields" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I click on "Cancellation note" "link"
    And I click on "Delete" "link" in the "Cancellation note" "table_row"
    And I press "Yes"
    And I log out

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Sign-up" in row 1
    And I press "Sign-up"
    Then I should see "Your booking has been completed."
    And I should see "Cancel booking"
    When I click on the link "Cancel booking" in row 1
    And I press "Yes"
    And I should not see "Cancel booking"
    And I log out