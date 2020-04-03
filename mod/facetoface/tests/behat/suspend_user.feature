@mod @mod_facetoface @totara
Feature: Suspend user in different session times
  In order to test the suspended user in Face to face
  As admin
  I need to keep or remove the suspend user in/from session

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |

  @javascript
  Scenario: Create sessions with diffrent dates and add users to a face to face sessions
    Given I log in as "admin"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
      | Allow multiple sessions signup per user | 1 |
    And I follow "View all sessions"

    # Session in the fututre
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2031 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2031 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
      | capacity              | 1    |
      | Allow overbooking     | 1    |
    And I press "Save changes"

    When I click on "Attendees" "link"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Sam1 Student1, student1@example.com" "option"
    And I press "Add"
    And I click on "Sam2 Student2, student2@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    Then I wait until "Sam1 Student1" "text" exists
    And I wait until the page is ready
    And I click on "Go back" "link"

    # Session is wait-listed
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | No   |
      | capacity              | 2    |
    And I press "Save changes"

    When I click on "Attendees" "link" in the "Wait-listed" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Sam1 Student1, student1@example.com" "option"
    And I press "Add"
    And I click on "Sam2 Student2, student2@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    Then I wait until "Sam1 Student1" "text" exists
    And I wait until the page is ready
    And I click on "Go back" "link"

    # Session in the past
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2015 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2015 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
      | capacity              | 2    |
    And I press "Save changes"

    When I click on "Attendees" "link" in the "Session over" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Sam1 Student1, student1@example.com" "option"
    And I press "Add"
    And I click on "Sam2 Student2, student2@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    Then I wait until "Sam1 Student1" "text" exists
    And I wait until the page is ready
    And I click on "Go back" "link"

    # Session in progress
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown         | Yes  |
      | timestart[0][day]     | 1    |
      | timestart[0][month]   | 1    |
      | timestart[0][year]    | 2017 |
      | timestart[0][hour]    | 11   |
      | timestart[0][minute]  | 00   |
      | timefinish[0][day]    | 1    |
      | timefinish[0][month]  | 1    |
      | timefinish[0][year]   | 2030 |
      | timefinish[0][hour]   | 12   |
      | timefinish[0][minute] | 00   |
      | capacity              | 2    |
    And I press "Save changes"

    When I click on "Attendees" "link" in the "Session in progress" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Sam1 Student1, student1@example.com" "option"
    And I press "Add"
    And I click on "Sam2 Student2, student2@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    Then I wait until "Sam1 Student1" "text" exists
    And I wait until the page is ready
    And I click on "Go back" "link"

    # Suspend Sam1 Student1 user
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "Suspend user account" "link" in the "Sam1 Student1" "table_row"
    And I wait until the page is ready

    And I click on "Find Learning" in the totara menu
    And I click on "Course 1" "link"
    And I follow "Test facetoface name"

    # Check the result
    When I click on "Attendees" "link" in the "Booking full" "table_row"
    Then I should not see "Sam1 Student1"
    And I should see "Sam2 Student2"

    And I click on "Go back" "link"

    When I click on "Attendees" "link" in the "Wait-listed" "table_row"
    Then I should not see "Sam1 Student1"
    And I should see "Sam2 Student2"

    And I click on "Go back" "link"

    When I click on "Attendees" "link" in the "Session over" "table_row"
    Then I should see "Sam1 Student1"
    And I should see "Sam2 Student2"

    And I click on "Go back" "link"

    When I click on "Attendees" "link" in the "Session in progress" "table_row"
    Then I should see "Sam1 Student1"
    And I should see "Sam2 Student2"
