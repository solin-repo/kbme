@mod @mod_facetoface @totara
Feature: Delete a course with a facetoface
  In order to delete a course
  As a teacher
  I need the facetoface to not do silly things with completion during purging of course.

  @javascript
  Scenario: Delete a course with one facetoface activity
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | completionstartonenrol |
      | Course 1 | C1        | 0        | 1                | 1                      |
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
      | Name                | Test facetoface name                              |
      | Description         | Test facetoface description                       |
      | Completion tracking | Show activity as complete when conditions are met |
      | Require grade       | 1                                                 |
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
    And I press "Save changes"
    When I click on "Attendees" "link"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Sam1 Student1, student1@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I click on "Sam2 Student2, student2@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    Then I wait until "Sam1 Student1" "text" exists
    And I log out

    And I log in as "admin"
    And I navigate to "Manage courses and categories" node in "Site administration > Courses"
    And I should see "Course 1" in the "#course-listing" "css_element"
    And I click on "delete" action for "Course 1" in management course listing
    And I should see "Delete C1"
    And I should see "Course 1 (C1)"

    When I press "Delete"
    Then I should see "Deleting C1"
    And I should see "C1 has been completely deleted"
    And I press "Continue"
    And I navigate to "Manage courses and categories" node in "Site administration > Courses"
    And I should not see "Course 1" in the "#course-listing" "css_element"

