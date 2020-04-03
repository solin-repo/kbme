@mod @mod_facetoface @totara @javascript
Feature: Manage pre-defined rooms
  In order to test Face to face rooms
  As a site manager
  I need to create and allocate rooms

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                    |
      | teacher1 | Teacher   | One      | teacher1@example.invalid |
      | user1    | User      | One      | user1@example.invalid    |
      | user2    | User      | Two      | user2@example.invalid    |
      | user3    | User      | Three    | user3@example.invalid    |
      | user4    | User      | Four     | user4@example.invalid    |
      | user5    | User      | Five     | user5@example.invalid    |
      | user6    | User      | Six      | user6@example.invalid    |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | user1    | C1     | student        |
      | user2    | C1     | student        |
      | user3    | C1     | student        |
      | user4    | C1     | student        |
      | user5    | C1     | student        |
      | user6    | C1     | student        |
    And I log in as "admin"
    And I navigate to "Rooms" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I press "Add a room"
    And I set the following fields to these values:
      | Room name | Room 1          |
      | Building  | That house      |
      | Address   | 123 here street |
      | Capacity  | 5               |
    And I press "Add a room"

    And I press "Add a room"
    And I set the following fields to these values:
      | Room name | Room 2          |
      | Building  | Your house      |
      | Address   | 123 near street |
      | Capacity  | 6               |
    And I press "Add a room"

  Scenario: See that the rooms were created correctly
    Given I navigate to "Rooms" node in "Site administration > Plugins > Activity modules > Face-to-face"
    Then I should see "That house" in the "Room 1" "table_row"
    And I should see "123 here street" in the "Room 1" "table_row"
    And I should see "5" in the "Room 1" "table_row"

    Then I should see "Your house" in the "Room 2" "table_row"
    And I should see "123 near street" in the "Room 2" "table_row"
    And I should see "6" in the "Room 2" "table_row"

    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    When I press "Choose a pre-defined room"
    Then I should see "Room 1, That house, 123 here street,  (Capacity: 5)" in the "Choose a room" "totaradialogue"
    And I should see "Room 2, Your house, 123 near street,  (Capacity: 6)" in the "Choose a room" "totaradialogue"

  Scenario: Fill a room
    Given I click on "Find Learning" in the totara menu
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
      | capacity              | 7    |
    When I press "Choose a pre-defined room"
    And I wait "1" seconds
    And I click on "Room 1, That house, 123 here street,  (Capacity: 5)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
    And I wait "1" seconds
    And I press "Save changes"

    When I click on "Attendees" "link"
    And I set the field "menuf2f-actions" to "Add/remove attendees"
    And I wait "1" seconds
    And I click on "User One, user1@example.invalid" "option"
    And I click on "User Two, user2@example.invalid" "option"
    And I click on "User Three, user3@example.invalid" "option"
    And I click on "User Four, user4@example.invalid" "option"
    And I click on "User Five, user5@example.invalid" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    Then I should see "User One"
    And I should see "User Two"
    And I should see "User Three"
    And I should see "User Four"
    And I should see "User Five"
    And I should see "Add/edit attendees success"
    And I should not see "This session is overbooked"

    And I set the field "menuf2f-actions" to "Add/remove attendees"
    And I wait "1" seconds
    And I click on "User Six, user6@example.invalid" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    Then I should see "User Six"
    And I should see "This session is overbooked"

  Scenario: Try and clash a room
    Given I click on "Find Learning" in the totara menu
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
      | capacity              | 7    |
    When I press "Choose a pre-defined room"
    And I wait "1" seconds
    And I click on "Room 1, That house, 123 here street,  (Capacity: 5)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
    And I wait "1" seconds
    And I press "Save changes"

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
    When I press "Choose a pre-defined room"
    And I wait "1" seconds
    Then I should see "(room unavailable on selected dates)" in the "Choose a room" "totaradialogue"
    And I click on "Cancel" "button" in the "Choose a room" "totaradialogue"
    And I wait "1" seconds
    And I set the following fields to these values:
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 14   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 15   |
      | timefinish[0][minute] | 00   |
    When I press "Choose a pre-defined room"
    And I click on "Room 1, That house, 123 here street,  (Capacity: 5)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
    And I wait "1" seconds
    And I set the following fields to these values:
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
    And I should see "There is a room conflict - another session is using the room at the same time"

    When I set the following fields to these values:
      | datetimeknown         | No |
    And I press "Save changes"
    Then I should see "Room 1" in the "1 January 2030" "table_row"
    And I should see "Room 1" in the "Wait-listed" "table_row"

  Scenario: Clash a room with different timezones
    Given I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes              |
      | timestart[0][day]       | 1                |
      | timestart[0][month]     | 1                |
      | timestart[0][year]      | 2030             |
      | timestart[0][hour]      | 19               |
      | timestart[0][minute]    | 00               |
      | timestart[0][timezone]  | Pacific/Auckland |
      | timefinish[0][day]      | 1                |
      | timefinish[0][month]    | 1                |
      | timefinish[0][year]     | 2030             |
      | timefinish[0][hour]     | 20               |
      | timefinish[0][minute]   | 00               |
      | timefinish[0][timezone] | Pacific/Auckland |
      | capacity                | 7                |
    When I press "Choose a pre-defined room"
    And I wait "1" seconds
    And I click on "Room 1, That house, 123 here street,  (Capacity: 5)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
    And I wait "1" seconds
    And I press "Save changes"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes           |
      | timestart[0][day]       | 1             |
      | timestart[0][month]     | 1             |
      | timestart[0][year]      | 2030          |
      | timestart[0][hour]      | 6             |
      | timestart[0][minute]    | 00            |
      | timestart[0][timezone]  | Europe/London |
      | timefinish[0][day]      | 1             |
      | timefinish[0][month]    | 1             |
      | timefinish[0][year]     | 2030          |
      | timefinish[0][hour]     | 7             |
      | timefinish[0][minute]   | 00            |
      | timefinish[0][timezone] | Europe/London |
      | capacity                | 7             |
    When I press "Choose a pre-defined room"
    And I wait "1" seconds
    Then I should see "(room unavailable on selected dates)" in the "Choose a room" "totaradialogue"
    And I click on "Cancel" "button" in the "Choose a room" "totaradialogue"
    And I wait "1" seconds
    And I set the following fields to these values:
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 14   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 15   |
      | timefinish[0][minute] | 00   |
      | capacity              | 7    |
    When I press "Choose a pre-defined room"
    And I click on "Room 1, That house, 123 here street,  (Capacity: 5)" "text" in the "Choose a room" "totaradialogue"
    And I click on "OK" "button" in the "Choose a room" "totaradialogue"
    And I wait "1" seconds
    And I set the following fields to these values:
      | timestart[0][day]       | 1             |
      | timestart[0][month]     | 1             |
      | timestart[0][year]      | 2030 |
      | timestart[0][hour]      | 6             |
      | timestart[0][minute]    | 00            |
      | timestart[0][timezone]  | Europe/London |
      | timefinish[0][day]      | 1             |
      | timefinish[0][month]    | 1             |
      | timefinish[0][year]     | 2030 |
      | timefinish[0][hour]     | 7             |
      | timefinish[0][minute]   | 00            |
      | timefinish[0][timezone] | Europe/London |
      | capacity                | 7             |
    And I press "Save changes"
    And I wait "1" seconds
    And I press "Save changes"
    And I should see "There is a room conflict - another session is using the room at the same time"

    When I set the following fields to these values:
      | datetimeknown         | No |
    And I press "Save changes"
    Then I should see "Room 1" in the "1 January 2030" "table_row"
    And I should see "Room 1" in the "Wait-listed" "table_row"
