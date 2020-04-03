@mod @mod_facetoface @totara
Feature: I can add and edit facetoface session dates
  In order to test the add/remove Face to face attendees
  As admin
  I need to add and remove attendees to/from a face to face session

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
  Scenario: I can edit a past facetoface session
    Given I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I press "Add a new date"
    And I set the following fields to these values:
      | datetimeknown             | Yes              |
      | timestart[0][day]         | 1                |
      | timestart[0][month]       | 1                |
      | timestart[0][year]        | 2030             |
      | timestart[0][hour]        | 11               |
      | timestart[0][minute]      | 00               |
      | timestart[0][timezone]    | Pacific/Auckland |
      | timefinish[0][day]        | 1                |
      | timefinish[0][month]      | 1                |
      | timefinish[0][year]       | 2030             |
      | timefinish[0][hour]       | 12               |
      | timefinish[0][minute]     | 00               |
      | timefinish[0][timezone]   | Pacific/Auckland |
      | timestart[1][day]         | 3                |
      | timestart[1][month]       | 1                |
      | timestart[1][year]        | 2030             |
      | timestart[1][hour]        | 11               |
      | timestart[1][minute]      | 00               |
      | timestart[1][timezone]    | Pacific/Auckland |
      | timefinish[1][day]        | 3                |
      | timefinish[1][month]      | 1                |
      | timefinish[1][year]       | 2030             |
      | timefinish[1][hour]       | 12               |
      | timefinish[1][minute]     | 00               |
      | timefinish[1][timezone]   | Pacific/Auckland |
      | capacity                  | 10               |
    And I press "Save changes"
    And I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    And I should see "Upcoming sessions"
    And I should see "1 January 2030"
    And I should see "3 January 2030"
    And I use magic to adjust the facetoface session "start" from "01/01/2030 11:00" "Pacific/Auckland" to "23/10/2016 11:00"
    And I use magic to adjust the facetoface session "end" from "01/01/2030 12:00" "Pacific/Auckland" to "23/10/2016 12:00"
    And I use magic to adjust the facetoface session "start" from "03/01/2030 11:00" "Pacific/Auckland" to "26/10/2016 11:00"
    And I use magic to adjust the facetoface session "end" from "03/01/2030 12:00" "Pacific/Auckland" to "26/10/2016 12:00"

    When I follow "Test facetoface name"
    Then I should see "Upcoming sessions"
    And I should see "23 October 2016"
    And I should see "26 October 2016"

    When I click to edit the facetoface session in row 1
    Then I should see "Editing session in Test facetoface name"

    When I set the following fields to these values:
      | Details | This session was run in the past |
    And I press "Save changes"
    And I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    Then I should see "Upcoming sessions"

    When I click to edit the facetoface session in row 1
    Then I should see "This session was run in the past"

    When I set the following fields to these values:
      | timestart[1][day]         | 3                |
      | timestart[1][month]       | 1                |
      | timestart[1][year]        | 2016             |
      | timestart[1][hour]        | 11               |
      | timestart[1][minute]      | 00               |
      | timefinish[1][day]        | 3                |
      | timefinish[1][month]      | 1                |
      | timefinish[1][year]       | 2016             |
      | timefinish[1][hour]       | 12               |
      | timefinish[1][minute]     | 00               |
    And I press "Save changes"
    Then I should see "Upcoming sessions"
    And I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."

  @javascript
  Scenario: I can edit a future facetoface session
    Given I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I press "Add a new date"
    And I set the following fields to these values:
      | datetimeknown             | Yes              |
      | timestart[0][day]         | 1                |
      | timestart[0][month]       | 1                |
      | timestart[0][year]        | 2030             |
      | timestart[0][hour]        | 11               |
      | timestart[0][minute]      | 00               |
      | timestart[0][timezone]    | Pacific/Auckland |
      | timefinish[0][day]        | 1                |
      | timefinish[0][month]      | 1                |
      | timefinish[0][year]       | 2030             |
      | timefinish[0][hour]       | 12               |
      | timefinish[0][minute]     | 00               |
      | timefinish[0][timezone]   | Pacific/Auckland |
      | timestart[1][day]         | 3                |
      | timestart[1][month]       | 1                |
      | timestart[1][year]        | 2030             |
      | timestart[1][hour]        | 11               |
      | timestart[1][minute]      | 00               |
      | timestart[1][timezone]    | Pacific/Auckland |
      | timefinish[1][day]        | 3                |
      | timefinish[1][month]      | 1                |
      | timefinish[1][year]       | 2030             |
      | timefinish[1][hour]       | 12               |
      | timefinish[1][minute]     | 00               |
      | timefinish[1][timezone]   | Pacific/Auckland |
      | capacity                  | 10               |
    When I press "Save changes"
    Then I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    And I should see "Upcoming sessions"
    And I should see "1 January 2030"
    And I should see "3 January 2030"

    When I click to edit the facetoface session in row 1
    Then I should see "Editing session in Test facetoface name"

    When I set the following fields to these values:
      | Details | This session was run in the past |
    And I press "Save changes"
    And I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    Then I should see "Upcoming sessions"

    When I click to edit the facetoface session in row 1
    Then I should see "This session was run in the past"

    When I set the following fields to these values:
      | timestart[1][day]         | 3    |
      | timestart[1][month]       | 2    |
      | timestart[1][year]        | 2030 |
      | timestart[1][hour]        | 11   |
      | timestart[1][minute]      | 00   |
      | timefinish[1][day]        | 3    |
      | timefinish[1][month]      | 2    |
      | timefinish[1][year]       | 2030 |
      | timefinish[1][hour]       | 12   |
      | timefinish[1][minute]     | 00   |
    And I press "Save changes"
    Then I should see "Upcoming sessions"
    And I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."

  @javascript
  Scenario: I can edit a past facetoface session with a minimum capacity and cutoff
    Given I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I press "Add a new date"
    And I set the following fields to these values:
      | datetimeknown             | Yes              |
      | timestart[0][day]         | 1                |
      | timestart[0][month]       | 1                |
      | timestart[0][year]        | 2030             |
      | timestart[0][hour]        | 11               |
      | timestart[0][minute]      | 00               |
      | timestart[0][timezone]    | Pacific/Auckland |
      | timefinish[0][day]        | 1                |
      | timefinish[0][month]      | 1                |
      | timefinish[0][year]       | 2030             |
      | timefinish[0][hour]       | 12               |
      | timefinish[0][minute]     | 00               |
      | timefinish[0][timezone]   | Pacific/Auckland |
      | timestart[1][day]         | 3                |
      | timestart[1][month]       | 1                |
      | timestart[1][year]        | 2030             |
      | timestart[1][hour]        | 11               |
      | timestart[1][minute]      | 00               |
      | timestart[1][timezone]    | Pacific/Auckland |
      | timefinish[1][day]        | 3                |
      | timefinish[1][month]      | 1                |
      | timefinish[1][year]       | 2030             |
      | timefinish[1][hour]       | 12               |
      | timefinish[1][minute]     | 00               |
      | timefinish[1][timezone]   | Pacific/Auckland |
      | capacity                  | 10               |
      | id_allowcancellations_2   | 1                |
      | Enable minimum capacity   | 1                |
      | Minimum capacity          | 5                |
      | cutoff[number]            | 24               |
    And I press "Save changes"
    And I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    And I should see "Upcoming sessions"
    And I should see "1 January 2030"
    And I should see "3 January 2030"
    And I use magic to adjust the facetoface session "start" from "01/01/2030 11:00" "Pacific/Auckland" to "23/10/2016 11:00"
    And I use magic to adjust the facetoface session "end" from "01/01/2030 12:00" "Pacific/Auckland" to "23/10/2016 12:00"
    And I use magic to adjust the facetoface session "start" from "03/01/2030 11:00" "Pacific/Auckland" to "26/10/2016 11:00"
    And I use magic to adjust the facetoface session "end" from "03/01/2030 12:00" "Pacific/Auckland" to "26/10/2016 12:00"

    When I follow "Test facetoface name"
    Then I should see "Upcoming sessions"
    And I should see "23 October 2016"
    And I should see "26 October 2016"

    When I click to edit the facetoface session in row 1
    Then I should see "Editing session in Test facetoface name"

    When I set the following fields to these values:
      | Details | This session was run in the past |
    And I press "Save changes"
    And I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    Then I should see "Upcoming sessions"

    When I click to edit the facetoface session in row 1
    Then I should see "This session was run in the past"

    When I set the following fields to these values:
      | timestart[1][day]         | 3    |
      | timestart[1][month]       | 1    |
      | timestart[1][year]        | 2016 |
      | timestart[1][hour]        | 11   |
      | timestart[1][minute]      | 00   |
      | timefinish[1][day]        | 3    |
      | timefinish[1][month]      | 1    |
      | timefinish[1][year]       | 2016 |
      | timefinish[1][hour]       | 12   |
      | timefinish[1][minute]     | 00   |
    And I press "Save changes"
    Then I should see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    And I should not see "Upcoming sessions"

  @javascript
  Scenario: I can edit a future facetoface session with a minimum capacity and cutoff
    Given I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I press "Add a new date"
    And I set the following fields to these values:
      | datetimeknown             | Yes              |
      | timestart[0][day]         | 1                |
      | timestart[0][month]       | 1                |
      | timestart[0][year]        | 2030             |
      | timestart[0][hour]        | 11               |
      | timestart[0][minute]      | 00               |
      | timestart[0][timezone]    | Pacific/Auckland |
      | timefinish[0][day]        | 1                |
      | timefinish[0][month]      | 1                |
      | timefinish[0][year]       | 2030             |
      | timefinish[0][hour]       | 12               |
      | timefinish[0][minute]     | 00               |
      | timefinish[0][timezone]   | Pacific/Auckland |
      | timestart[1][day]         | 3                |
      | timestart[1][month]       | 1                |
      | timestart[1][year]        | 2030             |
      | timestart[1][hour]        | 11               |
      | timestart[1][minute]      | 00               |
      | timestart[1][timezone]    | Pacific/Auckland |
      | timefinish[1][day]        | 3                |
      | timefinish[1][month]      | 1                |
      | timefinish[1][year]       | 2030             |
      | timefinish[1][hour]       | 12               |
      | timefinish[1][minute]     | 00               |
      | timefinish[1][timezone]   | Pacific/Auckland |
      | capacity                  | 10               |
      | id_allowcancellations_2   | 1                |
      | Enable minimum capacity   | 1                |
      | Minimum capacity          | 5                |
      | cutoff[number]            | 24               |
    When I press "Save changes"
    Then I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    And I should see "Upcoming sessions"
    And I should see "1 January 2030"
    And I should see "3 January 2030"

    When I click to edit the facetoface session in row 1
    Then I should see "Editing session in Test facetoface name"

    When I set the following fields to these values:
      | Details | This session was run in the past |
    And I press "Save changes"
    And I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    Then I should see "Upcoming sessions"

    When I click to edit the facetoface session in row 1
    Then I should see "This session was run in the past"

    When I set the following fields to these values:
      | timestart[1][day]         | 3    |
      | timestart[1][month]       | 2    |
      | timestart[1][year]        | 2030 |
      | timestart[1][hour]        | 11   |
      | timestart[1][minute]      | 00   |
      | timefinish[1][day]        | 3    |
      | timefinish[1][month]      | 2    |
      | timefinish[1][year]       | 2030 |
      | timefinish[1][hour]       | 12   |
      | timefinish[1][minute]     | 00   |
    And I press "Save changes"
    Then I should not see "The cut-off for minimum capacity is after the sessions earliest start date, it must be before to have any effect."
    And I should see "Upcoming sessions"