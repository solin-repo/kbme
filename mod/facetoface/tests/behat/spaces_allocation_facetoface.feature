@mod @mod_facetoface @totara
Feature: Allocate spaces for team
  In order to test Face to faces allocations
  As a site manager
  I need to allocate spaces for my team

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username     | firstname | lastname     | email                    | role     | context|
      | sitemanager1 | Terry1    | Sitemanager1 | sitemanager1@example.com | manager  | system |
      | sitemanager2 | Terry2    | Sitemanager2 | sitemanager2@example.com | manager  | system |
      | teacher1     | Terry3    | Teacher      | teacher@example.com      | learner  | system |
      | student1     | Sam1      | Student1     | student1@example.com     | learner  | system |
      | student2     | Sam2      | Student2     | student2@example.com     | learner  | system |
      | student3     | Sam3      | Student3     | student3@example.com     | learner  | system |
      | student4     | Sam4      | Student4     | student2@example.com     | learner  | system |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
    And the following "system role assigns" exist:
      | user         | role         | contextlevel | reference |
      | sitemanager1 | manager      | System       |           |
      | sitemanager2 | manager      | System       | System    |
    And the following "position" frameworks exist:
      | fullname      | idnumber |
      | PosHierarchy1 | FW001    |
    And the following "position" hierarchy exists:
      | framework | idnumber | fullname   |
      | FW001     | POS001   | Position1  |
    And the following position assignments exist:
      | user     | position | manager      |
      | student1 | POS001   | sitemanager1 |
      | student2 | POS001   | sitemanager1 |
      | student3 | POS001   | sitemanager2 |
      | student4 | POS001   | sitemanager2 |
    And I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test facetoface name        |
      | Description                             | Test facetoface description |
      | Allow multiple sessions signup per user | 1                           |
      | Allow manager reservations              | Yes                         |
      | Maximum reservations                    | 10                          |
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
      | capacity              | 3    |
    And I press "Save changes"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 2    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 2    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
      | capacity              | 3    |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Manager can deallocate users that he has allocated in the current session
    Given I log in as "sitemanager1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    And I press "Add"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"
    When I click on "Sam1 Student1" "option"
    And I press "Remove"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Potential allocations" select box should contain "Sam1 Student1"
    And I log out

  @javascript
  Scenario: Capacity should be unaffected if removing allocation and create reservations
  when removing allocations is set to Yes
    Given I log in as "sitemanager1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    When I press "Add"
    Then I should see "1 / 3"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"
    When I set the following fields to these values:
      | replaceallocations         | Yes  |
    And I click on "Sam1 Student1" "option"
    And I press "Remove"
    Then I should see "1 / 3"
    But I click on the link "Allocate spaces for team" in row 1
    And the "Current allocations" select box should not contain "Sam1 Student1"
    And the "Potential allocations" select box should contain "Sam1 Student1"
    And I log out

  @javascript
  Scenario: Capacity should be affected if removing allocation and create reservations when removing allocations is set to No
    Given I log in as "sitemanager1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    When I press "Add"
    Then I should see "1 / 3"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"
    When I set the following fields to these values:
      | replaceallocations         | No  |
    And I click on "Sam1 Student1" "option"
    And I press "Remove"
    Then I should see "0 / 3"
    And I click on the link "Allocate spaces for team" in row 1
    And the "Potential allocations" select box should contain "Sam1 Student1"
    And I log out

  @javascript
  Scenario: Manager cannot see users allocated from another managers
    Given I log in as "sitemanager1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    And I press "Add"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"
    And I log out

    When I log in as "sitemanager2"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should not contain "Sam1 Student1"
    And I log out

  @javascript
  Scenario: Manager cannot deallocate self booked users even if he is their manager
    Given I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Sign-up" in row 1
    And I press "Sign-up"
    And I should see "Your booking has been completed."
    And I log out

    When I log in as "sitemanager1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1 (Self booked)"
    When I click on "Sam1 Student1" "option"
    And I press "Remove"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1 (Self booked)"
    And I log out

  @javascript
  Scenario: Manager cannot deallocate users in another activity even if he is their manager and he allocated the user
    Given I log in as "sitemanager1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    And I press "Add"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"

    When I click on "Course 1" "link"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 2
    Then I should see "Sam1 Student1" in the "Other session(s) in this activity" "optgroup"
    When I click on "Sam1 Student1" "option" in the "#deallocation" "css_element"
    And I press "Remove"
    And I click on the link "Allocate spaces for team" in row 2
    But I should see "Sam1 Student1" in the "Other session(s) in this activity" "optgroup"
    And I log out

  @javascript
  Scenario: Allocate spaces for students in different sessions should be allowed if multiple sessions per signup is On
    Given I log in as "sitemanager1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    When I click on "Sam1 Student1" "option"
    And I press "Add"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"

    When I click on "Course 1" "link"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 2
    When I click on "Sam1 Student1" "option"
    And I press "Add"
    And I click on the link "Allocate spaces for team" in row 2
    Then the "Current allocations" select box should contain "Sam1 Student1"
    And I log out

  @javascript
  Scenario: Allocate and remove spaces for students when student has self-booked
    Given I log in as "student1"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Sign-up" in row 1
    And I press "Sign-up"
    And I should see "Your booking has been completed."
    And I log out

    When I log in as "sitemanager1"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1 (Self booked)"

    When I click on "Course 1" "link"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 2
    And I click on "Sam1 Student1" "option" in the "#allocation" "css_element"
    And I press "Add"
    And I click on the link "Allocate spaces for team" in row 2
    Then I should see "Sam1 Student1" in the "This session" "optgroup"
    And I should see "Sam1 Student1 (Self booked)" in the "Other session(s) in this activity" "optgroup"

    When I click on "Course 1" "link"
    And I follow "View all sessions"
    And I click on the link "Allocate spaces for team" in row 2
    And I click on "Sam1 Student1" "option"
    And I press "Remove"
    And I click on the link "Allocate spaces for team" in row 2
    Then I should not see "Sam1 Student1" in the "This session" "optgroup"
    And I should see "Sam1 Student1 (Self booked)" in the "Other session(s) in this activity" "optgroup"
