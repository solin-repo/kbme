@mod @mod_facetoface @totara @javascript
Feature: Confirm end date is adjusted when start date is altered
  In order to test that when the end date and time is adjusted when the start time changes
  As a site manager
  I need to create and edit a face to face session

  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test facetoface name        |
      | Description                             | Test facetoface description |
      | Allow multiple sessions signup per user | 1                           |
      | Allow manager reservations              | Yes                         |
      | Maximum reservations                    | 10                          |
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

  Scenario Outline: Alter time by dropdown
    Given I set the following fields to these values:
      | <field> | <start_value> |
    Then I should see "<end_value>" in the "#<end_field>" "css_element"

  Examples:
    | field                | start_value | end_value | end_field              |
    | timestart[0][day]    | 2           | 2         | id_timefinish_0_day    |
    | timestart[0][month]  | 2           | February  | id_timefinish_0_month  |
    | timestart[0][year]   | 2037        | 2037      | id_timefinish_0_year   |
    | timestart[0][hour]   | 12          | 13        | id_timefinish_0_hour   |
    | timestart[0][minute] | 30          | 30        | id_timefinish_0_minute |

  Scenario: Alter date by calendar
    Given I click on "Calendar" "link" in the "#fitem_id_timestart_0" "css_element"
    And I click on "22" "text" in the "#dateselector-calendar-panel" "css_element"
    Then I should see "22" in the "#id_timefinish_0_day" "css_element"

    Given I click on "Calendar" "link" in the "#fitem_id_timestart_0" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "22" "text" in the "#dateselector-calendar-panel" "css_element"
    Then I should see "February" in the "#id_timefinish_0_month" "css_element"

    Given I click on "Calendar" "link" in the "#fitem_id_timestart_0" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "#dateselector-calendar-panel .yui3-calendarnav-nextmonth" "css_element"
    And I click on "22" "text" in the "#dateselector-calendar-panel" "css_element"
    Then I should see "2037" in the "#id_timefinish_0_year" "css_element"

