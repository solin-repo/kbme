@javascript @mod @mod_facetoface @totara @takeattendance
Feature: Check attendees actions are performed by users with the right permissions
  In order to check users with the right permission could perform action on the attendees page
  As Admin
  I need to set users with different capabilities and perform actions as the users

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username  | firstname | lastname | email                |
      | trainer1  | Trainer   | One      | trainer1@example.com |
      | student1  | Sam1      | Student1 | student1@example.com |
      | student2  | Sam2      | Student2 | student2@example.com |
      | student3  | Sam3      | Student3 | student3@example.com |
      | manager1  | Manager   | One      | student4@example.com |
    And the following "manager assignments" exist in "totara_hierarchy" plugin:
      | user     | manager   |
      | student1 | manager1  |
      | student2 | manager1  |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | completionstartonenrol |
      | Course 1 | C1        | 0        | 1                | 1                      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | trainer1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test seminar name        |
      | Description | Test seminar description |
      | Completion tracking           | Show activity as complete when conditions are met |
      | completionstatusrequired[100] | 1                                                 |
    And I navigate to "Course completion" node in "Course administration"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Face-to-face - Test seminar name | 1 |
    And I press "Save changes"
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][month]   | 0                |
      | timestart[0][day]     | -1               |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][month]  | 0                |
      | timefinish[0][day]    | -1               |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | 0                |
      | timefinish[0][minute] | 0                |
    And I press "Save changes"
    And I click on "Attendees" "link"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Sam1 Student1, student1@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I click on "Sam2 Student2, student2@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I click on "Sam3 Student3, student3@example.com" "option"
    And I press "Add"
    # We must wait here, because the refresh may not happen before the save button is clicked otherwise.
    And I wait "1" seconds
    And I press "Save"
    Then I wait until "Sam1 Student1" "text" exists
    And I should see "Sam2 Student2"
    And I should see "Sam3 Student3"
    And I reload the page
    And I log out

  Scenario: Check trainer actions on attendees page
    Given I log in as "trainer1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I click on "View all sessions" "link"
    When I click on "Attendees" "link"
    Then I should see "Attendees" in the "div.tabtree" "css_element"
    And I should see "Wait-list" in the "div.tabtree" "css_element"
    And I should see "Cancellations" in the "div.tabtree" "css_element"
    And I should see "Take attendance" in the "div.tabtree" "css_element"
    And I should see "Message users" in the "div.tabtree" "css_element"
    And I log out

  Scenario: Check trainer actions on attendees page after removing take attendance capability
    Given the following "permission overrides" exist:
      | capability                       | permission | role           | contextlevel | reference |
      | mod/facetoface:takeattendance    | Prohibit   | editingteacher | Course       |        C1 |
    When I log in as "trainer1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link"
    Then I should see "Attendees" in the "div.tabtree" "css_element"
    And I should see "Wait-list" in the "div.tabtree" "css_element"
    And I should see "Cancellations" in the "div.tabtree" "css_element"
    And I should not see "Take attendance" in the "div.tabtree" "css_element"
    And I should not see "Message users" in the "div.tabtree" "css_element"
    When I visit the attendees page for session "1" with action "takeattendance"
    Then I should not see "Sam1 Student1"
    And I should not see "Sam2 Student2"
    And I should not see "Sam3 Student3"
    And I should not see "Mark all selected as:"
    And "Save attendance" "button" should not exist

  Scenario: Check trainer actions on attendees page after removing view cancellations capability
    Given the following "permission overrides" exist:
      | capability                       | permission | role           | contextlevel | reference |
      | mod/facetoface:viewcancellations | Prohibit   | editingteacher | Course       |        C1 |
    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Sam1 Student1, , student1@example.com" "option"
    And I press "Remove"
    And I wait "1" seconds
    And I press "Save"
    And I reload the page
    And I log out
    When I log in as "trainer1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link"
    Then I should see "Attendees" in the "div.tabtree" "css_element"
    And I should see "Wait-list" in the "div.tabtree" "css_element"
    And I should see "Take attendance" in the "div.tabtree" "css_element"
    And I should see "Message users" in the "div.tabtree" "css_element"
    And I should not see "Cancellations" in the "div.tabtree" "css_element"
    When I visit the attendees page for session "1" with action "cancellations"
    Then I should not see "Sam1 Student1"
    And I should not see "Cancellations" in the "div.f2f-attendees-table" "css_element"

  Scenario: Check trainer actions on attendees page after removing view attendees capability
    Given the following "permission overrides" exist:
      | capability                    | permission | role           | contextlevel | reference |
      | mod/facetoface:viewattendees  | Prohibit   | editingteacher | Course       |        C1 |
    When I log in as "trainer1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I click on "View all sessions" "link"
    Then "Attendees" "link" should not exist
    When I visit the attendees page for session "1" with action "takeattendance"
    And I should see "Cancellations" in the "div.tabtree" "css_element"
    And I should see "Take attendance" in the "div.tabtree" "css_element"
    And I should see "Message users" in the "div.tabtree" "css_element"
    And I should not see "Attendees" in the "div.tabtree" "css_element"
    And I should not see "Wait-list" in the "div.tabtree" "css_element"
#    I cannot visit attendees page with action=attendees because an exception is thrown a Behat doesn't like it

  Scenario: Check managers can view attendees page
    Given I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test seminar2 name        |
      | Description | Test seminar2 description |
      | Completion tracking           | Show activity as complete when conditions are met |
      | completionstatusrequired[100] | 1                                                 |
      | Approval required              | 1                                                 |
    And I navigate to "Course completion" node in "Course administration"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Face-to-face - Test seminar2 name | 1 |
    And I press "Save changes"
    And I follow "Test seminar2 name"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][month]   | 0                |
      | timestart[0][day]     | +8               |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][month]  | 0                |
      | timefinish[0][day]    | +8               |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | 0                |
      | timefinish[0][minute] | +30              |
    And I press "Save changes"
    And I log out

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Sign-up"
    And I press "Sign-up"
    Then I should see "Your booking has been completed but requires approval from your manager."
    And I log out

    When I log in as "manager1"
    And I click on "My Learning" in the totara menu
    And I click on "View all tasks" "link"
#    And I should see "Participant: Sam1 Student1"
    And I click on "Attendees" "link"
    Then I should see "Sam1 Student1"
    And I should not see "Cancellations" in the "div.f2f-attendees-table" "css_element"
    And I should not see "Take attendance" in the "div.tabtree" "css_element"
    And I should not see "Message users" in the "div.tabtree" "css_element"
    And I should not see "Attendees" in the "div.tabtree" "css_element"
    And I should not see "Wait-list" in the "div.tabtree" "css_element"
    And I select to approve "Sam1 Student1"
    And I press "Update requests"
    And I log out

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    Then I should see "Cancel booking"
