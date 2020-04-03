@mod @mod_facetoface @totara
Feature: Test room conflicts through backup/restore
  In order to test Face to face room conflicts
  As a site manager
  I need to create facetoface, add sessions, add room to each session with different room conflict settings

  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name               | course | idnumber |
      | facetoface | Facetoface TL12734 | C1     | TL12734  |

    And I log in as "admin"
    And I navigate to "Rooms" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I press "Add a room"
    And I set the following fields to these values:
      | Room name | Room 1                 |
      | Building  | Building 123           |
      | Address   | 123 Tory street        |
      | Capacity  | 10                     |
      | Type      | Prevent room conflicts |
    And I press "Add a room"
    And I press "Add a room"
    And I set the following fields to these values:
      | Room name | Room 2               |
      | Building  | Building 234         |
      | Address   | 234 Tory street      |
      | Capacity  | 10                   |
      | Type      | Allow room conflicts |
    And I press "Add a room"

  @javascript
  Scenario: Add sessions with different rooms and duplicate facetoface
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Facetoface TL12734"

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

    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I open "Facetoface TL12734" actions menu

    When I click on "Duplicate" "link" in the "Facetoface TL12734" activity
    And I turn editing mode off
    Then "//li[@id='section-0']/div[@class='content']/ul/li[1]/div/div/div[2]/div[2]/div/div/div[1]/div[1]/span[contains(text(), 'Room 1')]" "xpath_element" should exist
    And "//li[@id='section-0']/div[@class='content']/ul/li[1]/div/div/div[2]/div[2]/div/div/div[2]/div[1]/span[contains(text(), 'Room 2')]" "xpath_element" should exist
    # The room with prevent conflict should not appear.
    And "//li[@id='section-0']/div[@class='content']/ul/li[2]/div/div/div[2]/div[2]/div/div/div[1]/div[1]/span[contains(text(), 'Room 1')]" "xpath_element" should not exist
    And "//li[@id='section-0']/div[@class='content']/ul/li[2]/div/div/div[2]/div[2]/div/div/div[2]/div[1]/span[contains(text(), 'Room 2')]" "xpath_element" should exist

