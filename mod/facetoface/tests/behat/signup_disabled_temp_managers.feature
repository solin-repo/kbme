@javascript @mod @mod_facetoface @totara
Feature: Test suitable job assignment for session sign-up
  In order to sign up for seminar session
  As learner
  I need to have suitable job assignment when manager approval is required.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | idnumber | email                |
      | student1 | Sam1      | Student1 | sid#1    | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name              | course | idnumber | forceselectjobassignment |
      | facetoface | Test seminar name | C1     | seminar  | 1                        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |

  Scenario: Test learner job assignment for session sign-up when manager approval required
    Given I log in as "admin"
    And I navigate to "User policies" node in "Site administration > Permissions"
    And I set the following fields to these values:
      | s__enabletempmanagers | 0 |
    And I press "Save changes"
    And I navigate to "Global settings" node in "Site administration > Seminars"
    And I click on "id_s__facetoface_selectjobassignmentonsignupglobal" "checkbox"
    And I press "Save changes"
    And I click on "Find Learning" in the totara menu
    And I click on "Course 1" "text"
    And I follow "Go to course"
    And I follow "Test seminar name"
    And I follow "Add a new event"
    And I set the following fields to these values:
      | capacity           | 10   |
    And I press "Save changes"
    And I navigate to "Edit settings" node in "Seminar administration"
    And I click on "Sign-up Workflow" "link"
    And I click on "#id_approvaloptions_approval_manager" "css_element"
    And I press "Save and display"
    And I log out
    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I click on "Course 1" "text"
    And I follow "Go to course"
    And I follow "Test seminar name"
    And I click on the link "More info" in row 1
    Then I should see "You must have a suitable job assignment to sign up for this seminar activity."
