@mod @mod_feedback @feedback_export
Feature: Export feedback analysis to excel
  As admin
  I must be able to export feedback analysis to excel

  Background:
    Given I log in as "admin"
    And I navigate to "Manage activities" node in "Site administration > Plugins > Activity modules"
    And I click on "//a[@title=\"Show Feedback\"]" "xpath_element" in the "Feedback" "table_row"

    And the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log out

  @javascript
  Scenario: Export feedback analysis to Excel
    # Set up a feedback.
    When I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Feedback" to section "1" and I fill the form with:
      | Name                | Frogs                                             |
      | Description         | x                                                 |
      | Record user names   | User's name will be logged and shown with answers |
    And I follow "Frogs"
    And I follow "Analysis"
    And I follow "Edit questions"
    And I set the field "id_typ" to "Longer text answer"
    And I set the following fields to these values:
      | Question | Frog Information |
      | Label    | Frog information |
    And I press "Save question"
    And I log out

    # Go in as student 1 and do the feedback.
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Frogs"
    And I follow "Answer the questions"
    And I set the field "textarea_1" to multiline
      """
      Some
      information
      about frogs
      """
    And I press "Submit your answers"
    And I press "Continue"
    And I log out

    # Go in as teacher and check the users who haven't completed it.
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Frogs"
    And I follow "Analysis"

    # Should only show student 2; not student 1 (they did it) or 3 (not in grouping).
    Then I should see "(Frog information) Frog Information"
    And I should see "Some"
    And I should see "information"
    And I should see "about frogs"

    And I click on "//form[@action='analysis_to_excel.php']//input[@value='Export to Excel']" "xpath_element"
    # For now basically just testing that the export operation didn't throw an exception
    Then I should not see "More information about this error"
