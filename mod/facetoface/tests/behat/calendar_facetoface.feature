@mod @mod_facetoface @totara @core_calendar @javascript
Feature: Seminar calendar publishing to course, site, and user calendars

  Background:
    Given the following "categories" exist:
      | name             | category | idnumber | visible |
      | Open Top Level   | 0        | CAT1     | 1       |
      | Hidden Top Level | 0        | CAT2     | 0       |
      | Open Nested      | CAT1     | CAT3     | 1       |
      | Hidden Nested    | CAT1     | CAT4     | 0       |
    Given the following "courses" exist:
      | fullname           | shortname | category | visible | audiencevisible |
      | OTL Course         | course1   | CAT1     | 1       | 2               |
      | OTL Hidden Course  | course2   | CAT1     | 0       | 1               |
      | HTL Course         | course3   | CAT2     | 0       | 0               |
      | ON Course          | course4   | CAT3     | 1       | 2               |
      | ON Hidden Course   | course5   | CAT3     | 0       | 1               |
      | HN Course          | course6   | CAT4     | 0       | 3               |
    And the following "activities" exist:
      | activity   | name                     | shortname                | course  | idnumber  |
      | quiz       | OTL Quiz                 | OTL Quiz                 | course1 | quiz1     |
      | facetoface | OTL Seminar              | OTL Seminar              | course1 | seminar1  |
      | facetoface | OTL Hidden Seminar       | OTL Hidden Seminar       | course2 | seminar2  |
      | facetoface | HTL Seminar              | HTL Seminar              | course3 | seminar3  |
      | facetoface | ON Seminar               | ON Seminar               | course4 | seminar4  |
      | facetoface | ON Hidden Seminar        | ON Hidden Seminar        | course5 | seminar5  |
      | facetoface | HN Seminar               | HN Seminar               | course6 | seminar6  |
    And the following "users" exist:
      | username  | firstname | lastname | email         | idnumber |
      | kbomba    | kian      | bomba    | k@example.com | 101      |
      | tedison   | thomas    | edison   | t@example.com | 102      |
    And the following "cohorts" exist:
      | name      | idnumber | contextlevel | reference |
      | Inventors | aud1     | System       |           |
    And the following "cohort members" exist:
      | user     | cohort |
      | tedison  | aud1   |
    And the following "course enrolments" exist:
      | user      | course  | role    |
      | kbomba    | course1 | student |
      | kbomba    | course3 | student |
      | kbomba    | course4 | student |
      | kbomba    | course6 | student |
    And I log in as "admin"
    And I am on "OTL Course" course homepage with editing mode on
    And I delete "OTL Quiz" activity
    And I turn editing mode off

    And I am on "OTL Course" course homepage
    And I follow "OTL Seminar"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Calendar display settings     | 2           |
      | Show entry on user's calendar | 0           |
    And I click on "Save and display" "button"
    And I follow "Add a new event"
    And I press "Save changes"

    And I click on "Attendees" "link"
    And I set the field "Attendee actions" to "Add users"
    And I set the field "addselect" to "kian bomba, k@example.com"
    And I press "Add"
    And I set the following fields to these values:
      | Allow scheduling conflicts | 1 |
    And I press "Continue"
    And I press "Confirm"
    Then I should see "kian bomba"

    And I am on "OTL Hidden Course" course homepage
    And I follow "OTL Hidden Seminar"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Calendar display settings     | 2                  |
      | Show entry on user's calendar | 0                  |
    And I click on "Save and display" "button"
    And I follow "Add a new event"
    And I press "Save changes"

    And I am on "HTL Course" course homepage
    And I follow "HTL Seminar"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Calendar display settings     | 2           |
      | Show entry on user's calendar | 0           |
    And I click on "Save and display" "button"
    And I follow "Add a new event"
    And I press "Save changes"
    And I click on "Attendees" "link"
    And I set the field "Attendee actions" to "Add users"
    And I set the field "addselect" to "kian bomba, k@example.com"
    And I press "Add"
    And I set the following fields to these values:
      | Allow scheduling conflicts | 1 |
    And I press "Continue"
    And I press "Confirm"
    Then I should see "kian bomba"

    And I am on "ON Course" course homepage
    And I follow "ON Seminar"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Calendar display settings     | 2          |
      | Show entry on user's calendar | 0          |
    And I click on "Save and display" "button"
    And I follow "Add a new event"
    And I press "Save changes"
    And I click on "Attendees" "link"
    And I set the field "Attendee actions" to "Add users"
    And I set the field "addselect" to "kian bomba, k@example.com"
    And I press "Add"
    And I set the following fields to these values:
      | Allow scheduling conflicts | 1 |
    And I press "Continue"
    And I press "Confirm"
    Then I should see "kian bomba"

    And I am on "ON Hidden Course" course homepage
    And I follow "ON Hidden Seminar"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Calendar display settings     | 2                 |
      | Show entry on user's calendar | 0                 |
    And I click on "Save and display" "button"
    And I follow "Add a new event"
    And I press "Save changes"

    And I am on "HN Course" course homepage
    And I follow "HN Seminar"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Calendar display settings     | 2          |
      | Show entry on user's calendar | 0          |
    And I click on "Save and display" "button"
    And I follow "Add a new event"
    And I press "Save changes"
    And I click on "Attendees" "link"
    And I set the field "Attendee actions" to "Add users"
    And I set the field "addselect" to "kian bomba, k@example.com"
    And I press "Add"
    And I set the following fields to these values:
      | Allow scheduling conflicts | 1 |
    And I press "Continue"
    And I press "Confirm"
    Then I should see "kian bomba"

  Scenario: Normal visibility, check calendar event view
    Given I click on "Dashboard" in the totara menu
    When I follow "Go to calendar"
    Then I should see "OTL Seminar" exactly "1" times
    And I should see "OTL Hidden Seminar"
    And I should see "HTL Seminar"
    And I should see "ON Seminar"
    And I should see "ON Hidden Seminar"
    And I should see "HN Seminar"
    And I log out

    When I log in as "kbomba"
    And I follow "Go to calendar"
    Then I should see "OTL Seminar" exactly "1" times
    And I should not see "OTL Hidden Seminar"
    And I should not see "HTL Seminar"
    And I should see "ON Seminar"
    And I should not see "ON Hidden Seminar"
    And I should not see "HN Seminar"
    And I log out

    When I log in as "tedison"
    And I follow "Go to calendar"
    Then I should see "OTL Seminar" exactly "1" times
    And I should not see "OTL Hidden Seminar"
    And I should not see "HTL Seminar"
    And I should see "ON Seminar"
    And I should not see "ON Hidden Seminar"
    And I should not see "HN Seminar"

  Scenario: Audience based visibility, check calendar event view
    And I set the following administration settings values:
      | audiencevisibility | 1 |
    And I am on "OTL Hidden Course" course homepage
    And I follow "Edit settings"
    And I click on "Add visible audiences" "button"
    And I click on "Inventors" "link" in the "course-cohorts-visible-dialog" "totaradialogue"
    And I click on "OK" "button" in the "course-cohorts-visible-dialog" "totaradialogue"
    And I wait "1" seconds
    And I press "Save and display"
    And I am on "ON Hidden Course" course homepage
    And I follow "Edit settings"
    And I click on "Add visible audiences" "button"
    And I click on "Inventors" "link" in the "course-cohorts-visible-dialog" "totaradialogue"
    And I click on "OK" "button" in the "course-cohorts-visible-dialog" "totaradialogue"
    And I wait "1" seconds
    And I press "Save and display"
    And I log out

    When I log in as "kbomba"
    And I follow "Go to calendar"
    Then I should see "OTL Seminar" exactly "1" times
    And I should not see "OTL Hidden Seminar"
    And I should see "HTL Seminar"
    And I should see "ON Seminar"
    And I should not see "ON Hidden Seminar"
    And I should not see "HN Seminar"
    And I log out

    When I log in as "tedison"
    And I follow "Go to calendar"
    Then I should see "OTL Seminar" exactly "1" times
    And I should see "OTL Hidden Seminar"
    And I should not see "HTL Seminar"
    And I should see "ON Seminar"
    And I should see "ON Hidden Seminar"
    And I should not see "HN Seminar"
