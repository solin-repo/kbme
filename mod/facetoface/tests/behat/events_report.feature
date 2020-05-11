@mod @mod_facetoface @totara @totara_reportbuilder @javascript
Feature: Check the seminar events and sessions reports display correctly

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | Terry1    | Teacher1 | teacher1@moodle.com |
      | student1 | Sam1      | Student1 | student1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    When I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Seminar" to section "1" and I fill the form with:
      | Name        | Test seminar name        |
      | Description | Test seminar description |
    And I follow "View all events"
    And I follow "Add a new event"
    And I click on "Edit session" "link"
    And I set the following fields to these values:
      | timestart[day]     | 1    |
      | timestart[month]   | 1    |
      | timestart[year]    | 2030 |
      | timestart[hour]    | 11   |
      | timestart[minute]  | 00   |
      | timefinish[day]    | 1    |
      | timefinish[month]  | 1    |
      | timefinish[year]   | 2030 |
      | timefinish[hour]   | 12   |
      | timefinish[minute] | 00   |
    And I press "OK"
    And I press "Save changes"
    Then I should see "1 January 2030"

  Scenario: Seminar events report should only display one row per event with the sessions report showing one row per session
    #
    # Events with a single session display correctly.
    #
    When I navigate to "Events report" node in "Site administration > Seminars"
    Then I should see "Test seminar name" in the "Course 1" "table_row"
    When I follow "Sessions view"
    Then I should see "1 January 2030" in the "Course 1" "table_row"

    #
    # Events with multiple sessions display correctly.
    # Lets add another session.
    # There should only be one row per event in the events report.
    #
    When I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "View all events"
    And I click on "Edit event" "link"
    And I click on "Add a new session" "button"
    And I click to edit the seminar event date at position 2
    And I set the following fields to these values:
      | timestart[day]     | 2    |
      | timestart[month]   | 1    |
      | timestart[year]    | 2030 |
      | timestart[hour]    | 11   |
      | timestart[minute]  | 00   |
      | timefinish[day]    | 2    |
      | timefinish[month]  | 1    |
      | timefinish[year]   | 2030 |
      | timefinish[hour]   | 12   |
      | timefinish[minute] | 00   |
    And I click on "OK" "button" in the "Select date" "totaradialogue"
    And I press "Save changes"
    Then I should see "1 January 2030"
    And I should see "2 January 2030"

    # Check reports.
    When I navigate to "Events report" node in "Site administration > Seminars"
    Then I should see "Test seminar name" in the "Course 1" "table_row"
    And "//table[@id='facetoface_events']/tbody/tr[2]" "xpath_element" should not exist
    When I follow "Sessions view"
    Then the following should exist in the "facetoface_summary" table:
      | Seminar Name      | Course Name | Session Start Date/Time |
      | Test seminar name | Course 1    | 1 January 2030          |
      | Test seminar name | Course 1    | 2 January 2030          |