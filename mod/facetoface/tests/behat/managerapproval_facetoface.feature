@mod @mod_facetoface @totara
Feature: Manager approval
  In order to control seminar attendance
  As a manager
  I need to authorise seminar signups

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
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
      | Name              | Test facetoface name        |
      | Description       | Test facetoface description |
      | Approval required | 1                           |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
      | capacity              | 1    |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Student signs up with no manager assigned
    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I should see "Sign-up"
    And I follow "Sign-up"
    And I should see "You are currently not assigned to a manager in the system. Please contact the site administrator."

  @javascript
  Scenario: Student signs up a with a manager assigned
    Given the following "position" frameworks exist:
      | fullname      | idnumber |
      | PosHierarchy1 | FW001    |
    And the following "position" hierarchy exists:
      | framework | idnumber | fullname   |
      | FW001     | POS001   | Position1  |
    And the following position assignments exist:
      | user     | position | manager  |
      | student1 | POS001   | teacher1 |

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I should see "Sign-up"
    And I follow "Sign-up"
    And I should see "This session requires manager approval to book."
    And I press "Sign-up"
    And I should see "Your booking has been completed but requires approval from your manager."
    And I log out
    And I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test facetoface name"
    And I follow "Attendees"
    And I follow "Approval required"
    And I click on "input[value='2']" "css_element" in the "Sam1 Student1" "table_row"
    And I press "Update requests"

  @javascript
  Scenario: Approved learner cancels attendance
    Given the following "position" frameworks exist:
      | fullname      | idnumber |
      | PosHierarchy1 | FW001    |
    And the following "position" hierarchy exists:
      | framework | idnumber | fullname   |
      | FW001     | POS001   | Position1  |
    And the following position assignments exist:
      | user     | position | manager  |
      | student1 | POS001   | teacher1 |
      | student2 | POS001   | teacher1 |

    When I log in as "student1"
    # Sign up student 1.
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Sign-up"
    And I should see "This session requires manager approval to book."
    And I press "Sign-up"
    And I should see "Your booking has been completed but requires approval from your manager."
    And I log out
    # Sign up student 2
    And I log in as "student2"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Sign-up"
    And I should see "This session requires manager approval to book."
    And I press "Sign-up"
    And I should see "Your booking has been completed but requires approval from your manager."
    And I log out
    # Approve both users
    And I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test facetoface name"
    And I follow "Attendees"
    And I follow "Approval required"
    And I select to approve "Sam1 Student1"
    And I select to approve "Sam2 Student2"
    Then I press "Update requests"
    And I follow "Attendees"
    And I should see "This session is overbooked (2 / 1)"
    And I should see "Sam1 Student1" in the "table.mod-facetoface-attendees" "css_element"
    And I should see "Sam2 Student2" in the "table.mod-facetoface-attendees" "css_element"
    And I log out
    # Student 1 cancels their attendance.
    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Cancel booking"
    And I should see "Cancel booking for Test facetoface name"
    And I press "Yes"
    Then I should see "Your booking has been cancelled"
    And I log out
    # Teacher confirms that student 1 is not longer an attendee
    When I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test facetoface name"
    And I follow "Attendees"
    Then I should not see "This session is overbooked (2 / 1)"
    And I should not see "Sam1 Student1" in the "table.mod-facetoface-attendees" "css_element"
    And I should see "Sam2 Student2" in the "table.mod-facetoface-attendees" "css_element"
    # Confirm the cancellation page shows the correct result
    When I follow "Cancellations"
    And I should see "Sam1 Student1" in the "table.mod-facetoface-attendees" "css_element"
    And I should not see "Sam2 Student2" in the "table.mod-facetoface-attendees" "css_element"

