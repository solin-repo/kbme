@totara @block_facetoface
Feature: Confirm Sessions show up in my face to face sessions
  In order for the my sessions page is correct
  As an admin
  I need to be able to see and create sessions

  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name           | course | idnumber |
      | facetoface | Test session   | C1     | session1 |
      | facetoface | Test session 2 | C1     | session2 |
    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test session"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes          |
      | timestart[0][day]       | 1            |
      | timestart[0][month]     | 1            |
      | timestart[0][year]      | 2030         |
      | timestart[0][hour]      | 11           |
      | timestart[0][minute]    | 00           |
      | timefinish[0][day]      | 1            |
      | timefinish[0][month]    | 1            |
      | timefinish[0][year]     | 2030         |
      | timefinish[0][hour]     | 12           |
      | timefinish[0][minute]   | 00           |
      | capacity                | 20           |
      | Details                 | some details |
    And I click on "Save changes" "button"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes               |
      | timestart[0][day]       | 2                 |
      | timestart[0][month]     | 1                 |
      | timestart[0][year]      | 2030              |
      | timestart[0][hour]      | 11                |
      | timestart[0][minute]    | 00                |
      | timefinish[0][day]      | 2                 |
      | timefinish[0][month]    | 1                 |
      | timefinish[0][year]     | 2030              |
      | timefinish[0][hour]     | 12                |
      | timefinish[0][minute]   | 00                |
      | capacity                | 20                |
      | Details                 | some more details |
    And I click on "Save changes" "button"
    And I follow "C1"
    And I follow "Test session 2"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes            |
      | timestart[0][day]       | 1              |
      | timestart[0][month]     | 2              |
      | timestart[0][year]      | 2030           |
      | timestart[0][hour]      | 11             |
      | timestart[0][minute]    | 00             |
      | timefinish[0][day]      | 1              |
      | timefinish[0][month]    | 2              |
      | timefinish[0][year]     | 2030           |
      | timefinish[0][hour]     | 12             |
      | timefinish[0][minute]   | 00             |
      | capacity                | 30             |
      | Details                 | 1 some details |
    And I click on "Save changes" "button"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown           | Yes                 |
      | timestart[0][day]       | 2                   |
      | timestart[0][month]     | 2                   |
      | timestart[0][year]      | 2030                |
      | timestart[0][hour]      | 11                  |
      | timestart[0][minute]    | 00                  |
      | timefinish[0][day]      | 2                   |
      | timefinish[0][month]    | 2                   |
      | timefinish[0][year]     | 2030                |
      | timefinish[0][hour]     | 12                  |
      | timefinish[0][minute]   | 00                  |
      | capacity                | 30                  |
      | Details                 | 2 some more details |
    And I click on "Save changes" "button"

  @javascript
  Scenario: Test filters
    Given I click on "My Learning" in the totara menu
    And I click on "Customise this page" "button"
    And I add the "Face-to-face" block
    And I follow "Upcoming sessions"
    When I set the following fields to these values:
      | from[day]   | 1    |
      | from[month] | 1    |
      | from[year]  | 2019 |
      | to[enabled] | 0    |
    And I click on "Apply" "button"
    Then I should see "Test session" in the "1 January 2030" "table_row"
    And I should see "Test session" in the "2 January 2030" "table_row"
    And I should see "Test session 2" in the "1 February 2030" "table_row"
    And I should see "Test session 2" in the "2 February 2030" "table_row"

    When I set the following fields to these values:
      | to[enabled] | 1    |
      | to[day]     | 1    |
      | to[month]   | 2    |
      | to[year]    | 2030 |
    And I click on "Apply" "button"
    Then I should see "Test session" in the "1 January 2030" "table_row"
    And I should see "Test session" in the "2 January 2030" "table_row"
    And I should not see "Test session 2"
