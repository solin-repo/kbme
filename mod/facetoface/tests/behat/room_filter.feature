@mod @mod_facetoface @totara
Feature: Filter session by pre-defined rooms
  In order to test Face to face rooms
  As a site manager
  I need to create rooms

Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
  And the following "activities" exist:
    | activity   | name                 | course | idnumber |
    | facetoface | Test facetoface name | C1     | S9103    |

    And I log in as "admin"
    And I navigate to "Rooms" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I press "Add a room"
    And I set the following fields to these values:
      | Room name | Room 1          |
      | Building  | Building 123    |
      | Address   | 123 Tory street |
      | Capacity  | 10              |
    And I press "Add a room"
    And I press "Add a room"
    And I set the following fields to these values:
      | Room name | Room 2          |
      | Building  | Building 234    |
      | Address   | 234 Tory street |
      | Capacity  | 10              |
    And I press "Add a room"
    And I press "Add a room"
    And I set the following fields to these values:
      | Room name | Room 3          |
      | Building  | Building 345    |
      | Address   | 345 Tory street |
      | Capacity  | 10              |
    And I press "Add a room"
    And I press "Add a room"
    And I set the following fields to these values:
      | Room name | Room 4          |
      | Building  | Building 456    |
      | Address   | 456 Tory street |
      | Capacity  | 10              |
    And I press "Add a room"

  @javascript
  Scenario: Add sessions with different rooms and filter sessions by rooms
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test facetoface name"

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
    And I press "Choose a pre-defined room"
    And I click on "Room 1, Building 123, 123 Tory street,  (Capacity: 10)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
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
    And I press "Choose a pre-defined room"
    And I click on "Room 2, Building 234, 234 Tory street,  (Capacity: 10)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
    And I press "Save changes"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 3    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 3    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
    And I press "Choose a pre-defined room"
    And I click on "Room 3, Building 345, 345 Tory street,  (Capacity: 10)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
    And I press "Save changes"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 4    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 4    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
    And I press "Choose a pre-defined room"
    And I click on "Room 4, Building 456, 456 Tory street,  (Capacity: 10)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
    And I press "Save changes"

    When I click on "Room: Room 1" "option"
    Then I should see "Room 1" in the "1 January 2030" "table_row"
    And I should not see "Room 2" in the ".generaltable" "css_element"
    And I should not see "Room 3" in the ".generaltable" "css_element"
    And I should not see "Room 4" in the ".generaltable" "css_element"

    When I click on "Room: Room 2" "option"
    Then I should see "Room 2" in the "2 January 2030" "table_row"
    And I should not see "Room 1" in the ".generaltable" "css_element"
    And I should not see "Room 3" in the ".generaltable" "css_element"
    And I should not see "Room 4" in the ".generaltable" "css_element"

    When I click on "Room: Room 3" "option"
    Then I should see "Room 3" in the "3 January 2030" "table_row"
    And I should not see "Room 2" in the ".generaltable" "css_element"
    And I should not see "Room 1" in the ".generaltable" "css_element"
    And I should not see "Room 4" in the ".generaltable" "css_element"

    When I click on "Room: Room 4" "option"
    Then I should see "Room 4" in the "4 January 2030" "table_row"
    And I should not see "Room 2" in the ".generaltable" "css_element"
    And I should not see "Room 3" in the ".generaltable" "css_element"
    And I should not see "Room 1" in the ".generaltable" "css_element"

    When I click on "All rooms" "option"
    Then I should see "Room 1" in the "1 January 2030" "table_row"
    And I should see "Room 2" in the "2 January 2030" "table_row"
    And I should see "Room 3" in the "3 January 2030" "table_row"
    And I should see "Room 4" in the "4 January 2030" "table_row"
