@mod @mod_facetoface @totara @javascript
Feature: Confirm overlapping sessions can be removed
  In order to remove additional dates
  As a user
  I need to be able to remove overlapping times

  Scenario Outline:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And I log in as "admin"
    Given I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I press "Add a new date"
    And I set the following fields to these values:
      | datetimeknown           | Yes              |
      | timestart[0][day]       | 15               |
      | timestart[0][month]     | 7                |
      | timestart[0][year]      | 2030             |
      | timestart[0][hour]      | 15               |
      | timestart[0][minute]    | 00               |
      | timestart[0][timezone]  | Pacific/Auckland |
      | timefinish[0][day]      | 15               |
      | timefinish[0][month]    | 7                |
      | timefinish[0][year]     | 2030             |
      | timefinish[0][hour]     | 16               |
      | timefinish[0][minute]   | 00               |
      | timefinish[0][timezone] | Pacific/Auckland |
      | timestart[1][day]       | 15               |
      | timestart[1][month]     | 7                |
      | timestart[1][year]      | 2030             |
      | timestart[1][hour]      | <starthour>      |
      | timestart[1][minute]    | <startminute>    |
      | timestart[1][timezone]  | <timezone>       |
      | timefinish[1][day]      | 15               |
      | timefinish[1][month]    | 7                |
      | timefinish[1][year]     | 2030             |
      | timefinish[1][hour]     | <finishhour>     |
      | timefinish[1][minute]   | <finishminute>   |
      | timefinish[1][timezone] | <timezone>       |
      | datedelete[1]           | 1                |
    And I press "Save changes"
    Then I should not see "This date conflicts with an earlier date in this session"
    And I should see "Upcoming sessions"

  Examples:
    | starthour | startminute | finishhour | finishminute | timezone         |
    | 12        | 00          | 13         | 00           | Pacific/Auckland |
    | 15        | 00          | 16         | 00           | Pacific/Auckland |
    | 15        | 30          | 16         | 30           | Pacific/Auckland |
    | 14        | 30          | 15         | 30           | Pacific/Auckland |
    | 14        | 30          | 16         | 30           | Pacific/Auckland |
    | 15        | 05          | 15         | 55           | Pacific/Auckland |
    | 03        | 00          | 04         | 00           | UTC              |