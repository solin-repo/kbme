@mod @mod_facetoface @totara
Feature: Filter session by locations
  In order to test session locations
  As a site manager
  I need to create textarea customfield and named it as "location"

  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name            | course | idnumber |
      | facetoface | Seminar TL-9103 | C1     | S9103    |

  @javascript
  Scenario: Create location customfield, create sessions and filter sessions by location
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Custom Fields" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I set the field "datatype" to "textarea"
    And I set the following fields to these values:
      | Full name                   | Session Location |
      | Short name (must be unique) | location         |
    And I press "Save changes"

    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Seminar TL-9103"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 9    |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 16   |
      | timefinish[0][minute] | 00   |
      | customfield_location_editor[text] | <p>Building 123<br />123 Tory street<br />Wellington</p> |
    When I press "Save changes"
    Then I should see "Building 123" in the "1 January 2030" "table_row"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 2    |
      | timestart[0][month]   | 2    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 9    |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 2    |
      | timefinish[0][month]  | 2    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 16   |
      | timefinish[0][minute] | 00   |
      | customfield_location_editor[text] | <p>Building 234<br />234 Willis street<br />Wellington</p> |
    When I press "Save changes"
    Then I should see "Building 234" in the "2 February 2030" "table_row"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 3    |
      | timestart[0][month]   | 3    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 9    |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 3    |
      | timefinish[0][month]  | 3    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 16   |
      | timefinish[0][minute] | 00   |
      | customfield_location_editor[text] | <p>Building 345<br />345 Dixon street<br />Wellington</p> |
    When I press "Save changes"
    Then I should see "Building 345" in the "3 March 2030" "table_row"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 4    |
      | timestart[0][month]   | 4    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 9    |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 4    |
      | timefinish[0][month]  | 4    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 16   |
      | timefinish[0][minute] | 00   |
      | customfield_location_editor[text] | <p>Building 123<br />123 Tory street<br />Wellington</p> |
    When I press "Save changes"
    Then I should see "Building 123" in the "4 April 2030" "table_row"

    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 5    |
      | timestart[0][month]   | 5    |
      | timestart[0][year]    | 2030 |
      | timestart[0][hour]    | 9    |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 5    |
      | timefinish[0][month]  | 5    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 16   |
      | timefinish[0][minute] | 00   |
      | customfield_location_editor[text] | <p>Building 234<br />234 Willis street<br />Wellington</p> |
    When I press "Save changes"
    Then I should see "Building 234" in the "5 May 2030" "table_row"

    When I set the field "menulocation" to "<p>Building 123<br />123 Tory street<br />Wellington</p>"
    And I press "Show by location"
    Then I should see "Building 123" in the "1 January 2030" "table_row"
    And I should see "Building 123" in the "4 April 2030" "table_row"
    And I should not see "Building 234" in the ".generaltable" "css_element"
    And I should not see "Building 345" in the ".generaltable" "css_element"

    When I set the field "menulocation" to "<p>Building 234<br />234 Willis street<br />Wellington</p>"
    And I press "Show by location"
    Then I should see "Building 234" in the "2 February 2030" "table_row"
    And I should see "Building 234" in the "5 May 2030" "table_row"
    And I should not see "Building 123" in the ".generaltable" "css_element"
    And I should not see "Building 345" in the ".generaltable" "css_element"

    When I set the field "menulocation" to "<p>Building 345<br />345 Dixon street<br />Wellington</p>"
    And I press "Show by location"
    Then I should see "Building 345" in the "3 March 2030" "table_row"
    And I should not see "Building 123" in the ".generaltable" "css_element"
    And I should not see "Building 234" in the ".generaltable" "css_element"

    When I set the field "menulocation" to "All locations"
    And I press "Show by location"
    Then I should see "Building 123" in the "1 January 2030" "table_row"
    And I should see "Building 234" in the "2 February 2030" "table_row"
    And I should see "Building 345" in the "3 March 2030" "table_row"
    And I should see "Building 123" in the "4 April 2030" "table_row"
    And I should see "Building 234" in the "5 May 2030" "table_row"
