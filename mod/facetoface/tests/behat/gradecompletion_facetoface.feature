@mod @mod_facetoface @totara
Feature: Give a grade to a student for a face to face
    In order to check that they are completed

    Background:
        Given I am on a totara site
        And the following "users" exist:
            | username | firstname | lastname | email               |
            | teacher1 | Terry1    | Teacher1 | teacher1@moodle.com |
            | student1 | Sam1      | Student1 | student1@moodle.com |
        And the following "courses" exist:
            | fullname | shortname | category | enablecompletion |
            | Course 1 | C1        | 0        | 1                |
        And the following "course enrolments" exist:
            | user     | course | role           |
            | teacher1 | C1     | editingteacher |
            | student1 | C1     | student        |
        And I log in as "admin"
        And I click on "Find Learning" in the totara menu
        And I follow "Course 1"
        And I turn editing mode on
        And I add a "Face-to-face" to section "1" and I fill the form with:
            | Name                | Test facetoface name        |
            | Description         | Test facetoface description |
            | Completion tracking | Show activity as complete when conditions are met |
            | Require grade       | 1 |
        And I follow "Course completion"
        And I click on "Condition: Activity completion" "link"
        And I click on "Face-to-face - Test facetoface name" "checkbox"
        And I press "Save changes"
        And I log out

    @javascript
    Scenario: Set grade for student to complete face to face
        When I log in as "teacher1"
        And I click on "Find Learning" in the totara menu
        And I follow "Course 1"
        And I navigate to "Grades" node in "Course administration"
        And I turn editing mode on
        And I set the field "grade_4_2" to "100"

        And I press "Save changes"
        And I navigate to "Course completion" node in "Course administration > Reports"
        And I should see "Sam1 Student1"
        And "//tr[@id='user-4']/td[2]/img[@alt='Completed']" "xpath_element" should exist
        And "//tr[@id='user-4']/td[3]/img[@alt='Completed']" "xpath_element" should exist

