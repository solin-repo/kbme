@mod @mod_facetoface @totara
Feature: Facetoface session date management
  In order to set up a session
  As an administrator
  I need to be able to use timezones

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                | timezone        |
      | teacher1 | Terry     | Teacher  | teacher1@example.com | Australia/Perth |
      | teacher2 | Herry     | Tutor    | teacher2@example.com | Europe/Prague   |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | editingteacher |

  @javascript
  Scenario:
    Given I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And the field "sessiontimezone[0]" matches value "User timezone"
    And I set the following fields to these values:
      | Other room              | 1                |
      | Room name               | Room 1           |
      | datetimeknown           | Yes              |
      | sessiontimezone[0]      | Pacific/Auckland |
      | timestart[0][day]       | 2                |
      | timestart[0][month]     | 1                |
      | timestart[0][year]      | 2030             |
      | timestart[0][hour]      | 3                |
      | timestart[0][minute]    | 00               |
      | timestart[0][timezone]  | Europe/Prague    |
      | timefinish[0][day]      | 2                |
      | timefinish[0][month]    | 1                |
      | timefinish[0][year]     | 2030             |
      | timefinish[0][hour]     | 4                |
      | timefinish[0][minute]   | 00               |
      | timefinish[0][timezone] | Europe/Prague    |
    And I press "Add a new date"
    And I set the following fields to these values:
      | sessiontimezone[1]      | User timezone |
      | timestart[1][day]       | 3             |
      | timestart[1][month]     | 2             |
      | timestart[1][year]      | 2031          |
      | timestart[1][hour]      | 9             |
      | timestart[1][minute]    | 00            |
      | timestart[1][timezone]  | Europe/London |
      | timefinish[1][day]      | 3             |
      | timefinish[1][month]    | 2             |
      | timefinish[1][year]     | 2031          |
      | timefinish[1][hour]     | 11            |
      | timefinish[1][minute]   | 00            |
      | timefinish[1][timezone] | Europe/Prague |

    When I press "Save changes"
    Then I should see "3:00 PM - 4:00 PM Pacific/Auckland" in the "Room 1" "table_row"
    And I should see "5:00 PM - 6:00 PM Australia/Perth" in the "Room 1" "table_row"

    When I click on "Edit" "link" in the "Room 1" "table_row"
    Then the following fields match these values:
      | sessiontimezone[0]      | Pacific/Auckland |
      | timestart[0][day]       | 2                |
      | timestart[0][month]     | January          |
      | timestart[0][year]      | 2030             |
      | timestart[0][hour]      | 15               |
      | timestart[0][minute]    | 00               |
      | timestart[0][timezone]  | Pacific/Auckland |
      | timefinish[0][day]      | 2                |
      | timefinish[0][month]    | January          |
      | timefinish[0][year]     | 2030             |
      | timefinish[0][hour]     | 16               |
      | timefinish[0][minute]   | 00               |
      | timefinish[0][timezone] | Pacific/Auckland |
      | sessiontimezone[1]      | User timezone    |
      | timestart[1][day]       | 3                |
      | timestart[1][month]     | February         |
      | timestart[1][year]      | 2031             |
      | timestart[1][hour]      | 17               |
      | timestart[1][minute]    | 00               |
      | timestart[1][timezone]  | Australia/Perth  |
      | timefinish[1][day]      | 3                |
      | timefinish[1][month]    | February         |
      | timefinish[1][year]     | 2031             |
      | timefinish[1][hour]     | 18               |
      | timefinish[1][minute]   | 00               |
      | timefinish[1][timezone] | Australia/Perth  |

    When I press "Add a new date"
    Then the following fields match these values:
      | sessiontimezone[2]      | Pacific/Auckland |
      | timestart[2][timezone]  | Pacific/Auckland |
      | timefinish[2][timezone] | Pacific/Auckland |

    And I set the following fields to these values:
      | timestart[2][day]       | 4             |
      | timestart[2][month]     | 3             |
      | timestart[2][year]      | 2032          |
      | timestart[2][hour]      | 1             |
      | timestart[2][minute]    | 00            |
      | timefinish[2][day]      | 4             |
      | timefinish[2][month]    | 3             |
      | timefinish[2][year]     | 2032          |
      | timefinish[2][hour]     | 2             |
      | timefinish[2][minute]   | 00            |
      | sessiontimezone[0]      | Europe/Prague |

    When I press "Save changes"
    Then I should see "3:00 AM - 4:00 AM Europe/Prague" in the "Room 1" "table_row"
    And I should see "5:00 PM - 6:00 PM Australia/Perth" in the "Room 1" "table_row"
    And I should see "1:00 AM - 2:00 AM Pacific/Auckland" in the "Room 1" "table_row"

    When I log out
    And I log in as "teacher2"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test facetoface name"
    Then I should see "3:00 AM - 4:00 AM Europe/Prague" in the "Room 1" "table_row"
    And I should see "10:00 AM - 11:00 AM Europe/Prague" in the "Room 1" "table_row"
    And I should see "1:00 AM - 2:00 AM Pacific/Auckland" in the "Room 1" "table_row"

    When I log out
    And I log in as "admin"
    And I set the following administration settings values:
      | facetoface_displaysessiontimezones | 0 |
    And I log out
    And I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test facetoface name"
    Then I should see "10:00 AM - 11:00 AM " in the "Room 1" "table_row"
    And I should see "5:00 PM - 6:00 PM " in the "Room 1" "table_row"
    And I should see "8:00 PM - 9:00 PM" in the "Room 1" "table_row"
