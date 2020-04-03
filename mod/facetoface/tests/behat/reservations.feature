@javascript @mod @mod_facetoface @totara
Feature: Add - Remove manager reservations in Face-to-face
  In order to test the add/remove Face to face manager reservations
  As manager
  I need to add and remove attendees to/from a face to face session using reservations

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
      | manager  | Max       | Manager  | manager@example.com  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
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
    And the following position assignments exist:
      | user     | position | type      | manager |
      | student1 | POS001   | primary   | manager |
      | student2 | POS001   | primary   | manager |

    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                       | Test facetoface name        |
      | Description                | Test facetoface description |
      | Allow manager reservations | Yes                         |
      | Maximum reservations       | 2                           |
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
      | capacity              | 2    |
    And I press "Save changes"
    And I log out

  Scenario: Add and then remove users from Face-to-face using manager reservations
    Given I log in as "manager"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I click on "Test facetoface name" "link"
    And I should see "Allocate spaces for team (0/2)"
    And I should see "Reserve spaces for team (0/2)"
    And I click on "Allocate spaces for team" "link"
    And I click on "Sam1 Student1" "option"
    And I click on "Sam2 Student2" "option"
    And I press "Add"
    And I click on "Test facetoface name" "link"
    And I should see "Allocate spaces for team (2/2)"
    And I click on "Allocate spaces for team" "link"
    And I click on "Sam2 Student2" "option"
    And I press "Remove"
    And I click on "Test facetoface name" "link"
    And I should see "Allocate spaces for team (1/2)"
    And I should see "Reserve spaces for team (1/1)"
