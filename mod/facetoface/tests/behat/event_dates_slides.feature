@mod @mod_facetoface @totara @javascript
Feature: I can slide seminar session dates
  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | facetoface | Seminar 1 | C1     | S1       |
    And the following "custom rooms" exist in "mod_facetoface" plugin:
      |  name  | capacity | description |
      | Room 1 | 10       |             |
      | Room 2 | 20       |             |
      | Room 3 | 30       |             |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Seminar 1"
    And I follow "Add a new event"
    And I press "Add a new session"
    And I press "Add a new session"
    And I click to edit the seminar event date at position 1
    And I set the following fields to these values:
      | timestart[day]       | 1                    |
      | timestart[month]     | 12                   |
      | timestart[year]      | ## next year ## Y ## |
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate0-dialog']" "xpath_element"
    And I click to edit the seminar event date at position 2
    And I set the following fields to these values:
      | timestart[day]       | 7                    |
      | timestart[month]     | 12                   |
      | timestart[year]      | ## next year ## Y ## |
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate1-dialog']" "xpath_element"
    And I click to edit the seminar event date at position 3
    And I set the following fields to these values:
      | timestart[day]       | 14                   |
      | timestart[month]     | 12                   |
      | timestart[year]      | ## next year ## Y ## |
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate2-dialog']" "xpath_element"
    And I click on the link "Select room" in row 1
    And I click on "Room 1" "text" in the "//div[@aria-describedby='selectroom0-dialog']" "xpath_element"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectroom0-dialog']" "xpath_element"
    And I click on the link "Select room" in row 2
    And I click on "Room 2" "text" in the "//div[@aria-describedby='selectroom1-dialog']" "xpath_element"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectroom1-dialog']" "xpath_element"
    And I click on the link "Select room" in row 3
    And I click on "Room 3" "text" in the "//div[@aria-describedby='selectroom2-dialog']" "xpath_element"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectroom2-dialog']" "xpath_element"
    And I press "Save changes"
    And I should see "Room 1" in the "1 December" "table_row"
    And I should see "Room 2" in the "7 December" "table_row"
    And I should see "Room 3" in the "14 December" "table_row"
    And I click to edit the seminar session in row 1

  Scenario: I can shift seminar session dates
    # 01/12 -> 07/12
    And I click to edit the seminar event date at position 1
    And I set the field "timestart[day]" to "7"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate0-dialog']" "xpath_element"
    # 07/12 -> 14/12
    And I click to edit the seminar event date at position 2
    And I set the field "timestart[day]" to "14"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate1-dialog']" "xpath_element"
    # 14/12 -> 21/12
    And I click to edit the seminar event date at position 3
    And I set the field "timestart[day]" to "21"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate2-dialog']" "xpath_element"
    And I press "Save changes"
    And I should see "Room 1" in the "7 December" "table_row"
    And I should see "Room 2" in the "14 December" "table_row"
    And I should see "Room 3" in the "21 December" "table_row"

  Scenario: I can rotate seminar session dates
    # 01/12 -> 07/12
    And I click to edit the seminar event date at position 1
    And I set the field "timestart[day]" to "7"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate0-dialog']" "xpath_element"
    # 07/12 -> 14/12
    And I click to edit the seminar event date at position 2
    And I set the field "timestart[day]" to "14"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate1-dialog']" "xpath_element"
    # 14/12 -> 01/12
    And I click to edit the seminar event date at position 3
    And I set the field "timestart[day]" to "1"
    And I click on "OK" "button" in the "//div[@aria-describedby='selectdate2-dialog']" "xpath_element"
    And I press "Save changes"
    And I should see "Room 1" in the "7 December" "table_row"
    And I should see "Room 2" in the "14 December" "table_row"
    And I should see "Room 3" in the "1 December" "table_row"
