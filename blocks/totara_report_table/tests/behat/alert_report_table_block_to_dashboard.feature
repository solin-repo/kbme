@block @block_totara_report_table @javascript @totara @totara_reportbuilder @dashboard
Feature: Only Alerts Report table block on dashboard
  In order to test the Alerts report table block functions on its own on the dashboard
  As a user
  I need to use a dashboard containing only the Alerts report table block
  and ensure that my messages are shown and everything functions properly

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Bob2      | Student2 | student2@example.com |
    And the following "cohorts" exist:
      | name       | idnumber | description            | contextlevel | reference |
      | Audience 1 | A1       | Audience 1 description | System       | 0         |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name              | course | idnumber |
      | facetoface | Test seminar name | C1     | seminar  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student2 | C1     | student        |

    # Create a Seminar.
    And I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Test seminar name"
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
      | capacity              | 1    |
    And I press "Save changes"

    When I click on "Attendees" "link"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "Sam1 Student1, student1@example.com" "option"
    And I press "Add"
    And I wait "1" seconds
    And I press "Save"
    Then I should see "Sam1 Student1"

    # Set up the dashboard.
    And I navigate to "Dashboards" node in "Site administration > Appearance"
    And I press "Create dashboard"
    And I set the following fields to these values:
     | Name | My Dashboard |
     | Published | 1             |
    And I press "Assign new audiences"
    And I follow "Audience 1"
    And I press "OK"
    And I press "Create dashboard"
    Then I should see "My Dashboard"

    # Create an audience that we can allocate to the dashboard.
    When I navigate to "Audiences" node in "Site administration > Users > Accounts"
    And I follow "Audience 1"
    And I follow "Edit members"
    And I set the field "Potential users" to "Admin User (moodle@example.com)"
    And I press "Add"
    And I set the field "Potential users" to "Sam1 Student1 (student1@example.com)"
    And I press "Add"
    And I set the field "Potential users" to "Bob2 Student2 (student2@example.com)"
    And I press "Add"
    And I follow "Members"
    Then I should see "Admin User"
    Then I should see "Sam1 Student1"
    Then I should see "Bob2 Student2"
    And I log out

  Scenario: Add only the Alerts report table block to the dashboard
    When I log in as "student1"
    And I click on "Dashboard" in the totara menu
    When I press "Customize dashboard"
    And I add the "Report table" block
    And I configure the "Report table" block
    And I set the following fields to these values:
      | Block title | MyAlerts block |
      | Report | Alerts |
    And I press "Save changes"
    And I press "Stop customizing this dashboard"
    Then I should see "Sam1 Student1" in the "MyAlerts block" "block"
    And I should see "Test seminar name" in the "MyAlerts block" "block"
    And I log out

    # Check that other users don't see your messages
    When I log in as "student2"
    And I click on "Dashboard" in the totara menu
    When I press "Customize dashboard"
    And I add the "Report table" block
    And I configure the "Report table" block
    And I set the following fields to these values:
      | Block title | MyAlerts block |
      | Report | Alerts |
    And I press "Save changes"
    And I press "Stop customizing this dashboard"
    Then I should not see "Sam1 Student1" in the "MyAlerts block" "block"
    And I should see "There are no records in this report"
    And I log out

    # Check that the dismiss dialog box is shown correctly
    When I log in as "student1"
    And I click on "Dashboard" in the totara menu
    And I click on "#dismissmsg1-dialog" "css_element"
    Then I should see "Review Item(s)"
    When I click on "//div[contains(@class, 'ui-dialog-buttonpane')]//button[contains(.,'Dismiss')]" "xpath_element"
    Then I should not see "Test seminar name" in the "MyAlerts block" "block"
    And I should see "There are no records in this report"
