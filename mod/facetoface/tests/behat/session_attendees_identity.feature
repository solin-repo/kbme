@mod @mod_facetoface @totara
Feature: Change identity settings to ensure that the attendance selector
    shows the selected options.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | learner1 | Learner   | One      | learner1@example.com | L1       |
      | learner2 | Learner   | Two      | learner2@example.com | L2       |
      | learner3 | Learner   | Three    | learner3@example.com | L3       |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |

  @javascript
  Scenario: I can change identity settings to better identify users
    Given I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface             |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
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
      | timestart[0][timezone]  | Pacific/Auckland |
      | timefinish[0][day]      | 2                |
      | timefinish[0][month]    | 1                |
      | timefinish[0][year]     | 2030             |
      | timefinish[0][hour]     | 4                |
      | timefinish[0][minute]   | 00               |
      | timefinish[0][timezone] | Pacific/Auckland |
    And I press "Save changes"
    And I click on "Attendees" "link"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I should see "learner1@example.com"
    And I should not see "L1"
    And I press "Cancel"
    And I navigate to "User policies" node in "Site administration > Users > Permissions"
    And I click on "User policies" "link" in the "Administration" "block"
    And I click on "s__showuseridentity[idnumber]" "checkbox"
    And I press "Save changes"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test facetoface"
    And I click on "Attendees" "link"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I should see "learner1@example.com"
    And I should see "L1"

