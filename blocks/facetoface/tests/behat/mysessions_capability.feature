@totara @block_facetoface
Feature: Confirm session capability check works in my face to face sessions
  In order to check the capability
  As an admin
  I need to be able to see and create sessions

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | learner1 | Learner   | 1        | learner@example.com  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
    And the following "activities" exist:
      | activity   | name           | course | idnumber |
      | facetoface | Test session   | C1     | session1 |
      | facetoface | Test session 2 | C2     | session2 |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C1     | teacher |
      | teacher1 | C2     | teacher |
      | learner1 | C1     | student |
    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test session"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes  |
      | timestart[0][day]       | 1    |
      | timestart[0][month]     | 1    |
      | timestart[0][year]      | 2030 |
      | timestart[0][hour]      | 11   |
      | timestart[0][minute]    | 00   |
      | timefinish[0][day]      | 1    |
      | timefinish[0][month]    | 1    |
      | timefinish[0][year]     | 2030 |
      | timefinish[0][hour]     | 12   |
      | timefinish[0][minute]   | 00   |
      | capacity                | 20   |
    And I click on "Save changes" "button"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes  |
      | timestart[0][day]       | 2    |
      | timestart[0][month]     | 1    |
      | timestart[0][year]      | 2030 |
      | timestart[0][hour]      | 11   |
      | timestart[0][minute]    | 00   |
      | timefinish[0][day]      | 2    |
      | timefinish[0][month]    | 1    |
      | timefinish[0][year]     | 2030 |
      | timefinish[0][hour]     | 12   |
      | timefinish[0][minute]   | 00   |
      | capacity                | 20   |
    And I click on "Save changes" "button"

    And I click on "Attendees" "link"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Learner 1, learner@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"

    And I click on "Find Learning" in the totara menu
    And I follow "Course 2"
    And I follow "Test session 2"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes  |
      | timestart[0][day]       | 1    |
      | timestart[0][month]     | 2    |
      | timestart[0][year]      | 2030 |
      | timestart[0][hour]      | 11   |
      | timestart[0][minute]    | 00   |
      | timefinish[0][day]      | 1    |
      | timefinish[0][month]    | 2    |
      | timefinish[0][year]     | 2030 |
      | timefinish[0][hour]     | 12   |
      | timefinish[0][minute]   | 00   |
      | capacity                | 20   |
    And I click on "Save changes" "button"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes  |
      | timestart[0][day]       | 2    |
      | timestart[0][month]     | 2    |
      | timestart[0][year]      | 2030 |
      | timestart[0][hour]      | 11   |
      | timestart[0][minute]    | 00   |
      | timefinish[0][day]      | 2    |
      | timefinish[0][month]    | 2    |
      | timefinish[0][year]     | 2030 |
      | timefinish[0][hour]     | 12   |
      | timefinish[0][minute]   | 00   |
      | capacity                | 20   |
    And I click on "Save changes" "button"
    And I log out

  @javascript
  Scenario: Test teacher capability
    Given I log in as "teacher1"
    And I click on "My Learning" in the totara menu
    And I click on "Customise this page" "button"
    And I add the "Face-to-face" block
    And I follow "Upcoming sessions"
    And I set the following fields to these values:
      | from[day]   | 1    |
      | from[month] | 1    |
      | from[year]  | 2016 |
      | to[enabled] | 0    |
    When I click on "Apply" "button"
    Then I should see "Test session" in the "1 January 2030" "table_row"
    And I should see "Test session" in the "2 January 2030" "table_row"
    And I should see "Test session 2" in the "1 February 2030" "table_row"
    And I should see "Test session 2" in the "2 February 2030" "table_row"
    And I log out

  @javascript
  Scenario: Test student capability
    Given I log in as "learner1"
    And I click on "My Learning" in the totara menu
    And I click on "Customise this page" "button"
    And I add the "Face-to-face" block
    And I follow "Upcoming sessions"
    And I set the following fields to these values:
      | from[day]   | 1    |
      | from[month] | 1    |
      | from[year]  | 2016 |
      | to[enabled] | 0    |
    When I click on "Apply" "button"
    Then I should see "Test session" in the "1 January 2030" "table_row"
    And I should see "Test session" in the "2 January 2030" "table_row"
    And I should not see "Test session 2"

