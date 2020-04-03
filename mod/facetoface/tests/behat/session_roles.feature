@mod @mod_facetoface @totara
Feature: Use facetoface session roles
  In order to use session roles
  As a teacher
  I need to be able to setup session roles and see them in report

  @javascript @_alert
  Scenario: Seup and report facetoface session roles
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | middlename | email                |
      | teacher1 | Terry1    | Teacher1 | Midter1    | teacher1@example.com |
      | student1 | Sam1      | Student1 | Midsam1    | student1@example.com |
      | student2 | Sam2      | Student2 |            | student2@example.com |
      | student3 | Sam3      | Student3 |            | student3@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
    And I log in as "admin"
    And I set the following administration settings values:
      | fullnamedisplay           | lastname middlename firstname |
      | alternativefullnameformat | lastname middlename firstname |
      | enablereportcaching       | 1                         |
    And I navigate to "General Settings" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I set the field "id_s__facetoface_session_roles_5" to "1"
    And I press "Save changes"

    And I navigate to "Manage reports" node in "Site administration > Reports > Report builder"
    And I set the field "Report Name" to "F2F sessions"
    And I set the field "Source" to "Face-to-face sessions"
    And I press "Create report"
    And I click on "Columns" "link" in the ".tabtree" "css_element"
    And I add the "Session Learner" column to the report
    And I add the "Face to Face Name" column to the report
    And I click on "Delete" "link" confirming the dialogue
    And I press "Save changes"
    And I click on "Filters" "link" in the ".tabtree" "css_element"
    And I add the "Session Learner" filter to the report
    And I press "Save changes"
    And I click on "Access" "link" in the ".tabtree" "css_element"
    And I set the field "All users can view this report" to "1"
    And I press "Save changes"
    And I log out

    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
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
      | capacity              | 10   |
    And I set the field "Student1 Midsam1 Sam1" to "1"
    And I set the field "Student3 Sam3" to "1"
    And I press "Save changes"
    And I click on "Attendees" "link" in the "Booking open" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Student2 Sam2, student2@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    And I wait until "Student2 Sam2" "text" exists

    # Standard report
    When I follow "My Reports"
    And I follow "F2F sessions"
    Then I should see "Student3  Sam3" in the "Test facetoface name" "table_row"
    And I should see "Student1 Midsam1 Sam1" in the "Test facetoface name" "table_row"
    And I should not see "Student2" in the "Test facetoface name" "table_row"
    And I log out

    # Cached report
    When I log in as "admin"
    And I click on "My Reports" in the totara menu
    And I follow "F2F sessions"
    And I press "Edit this report"
    And I click on "Performance" "link" in the ".tabtree" "css_element"
    And I set the following fields to these values:
      | Enable Report Caching | 1 |
      | Generate Now          | 1 |
    And I press "Save changes"
    Then I should see "Last cached at"
    And I follow "View This Report"
    And I should see "Report data last updated"

