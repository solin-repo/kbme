@enrol @totara @enrol_totara_facetoface
Feature: Users can auto-enrol themself in courses where face to face direct enrolment is allowed
  In order to participate in courses
  As a user
  I need to auto enrol me in courses

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |

    And I log in as "admin"
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Enrolments" node
    And I follow "Manage enrol plugins"
    And I click on "Enable" "link" in the "Face-to-face direct enrolment" "table_row"
    And I log out
    And I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
      | Approval required | 0                     |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown | Yes |
      | timestart[0][day] | 1 |
      | timestart[0][month] | 1 |
      | timestart[0][year] | 2030 |
      | timestart[0][hour] | 11 |
      | timestart[0][minute] | 00 |
      | timefinish[0][day] | 1 |
      | timefinish[0][month] | 1 |
      | timefinish[0][year] | 2030 |
      | timefinish[0][hour] | 12 |
      | timefinish[0][minute] | 00 |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Enrol using face to face direct with signup note
    Given I log in as "teacher1"
    And I follow "Course 1"
    When I add "Face-to-face direct enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
    And I log out
    And I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I click on "[name^='sid']" "css_element" in the "1 January 2030" "table_row"
    And I set the following fields to these values:
      | Signup note | Lorem ipsum dolor sit amet |
    And I press "Sign-up"
    Then I should see "You are booked for the following session"
    And I log out
    # Check signup note
    And I log in as "admin"
    And I follow "Course 1"
    And I follow "Test facetoface name"
    When I click on "Attendees" "link"
    Then I should see "Lorem ipsum dolor sit amet" in the "Student 1" "table_row"

  @javascript
  Scenario: Face to face direct enrolment disabled
    Given I log in as "student1"
    And I click on "Find Learning" in the totara menu
    When I follow "Course 1"
    Then I should see "You can not enrol yourself in this course"

  @javascript
  Scenario: Enrol through course catalogue
    Given I log in as "admin"
    And I set the following administration settings values:
      | Enhanced catalog | 1 |
    And I press "Save changes"
    And I log out
    Given I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    When I add "Face-to-face direct enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
    And I log out
    And I log in as "student1"
    And I should see "Courses" in the "Navigation" "block"
    And I click on "Courses" "link_or_button" in the "Navigation" "block"
    And I click on "Course 1" "link"
    And I click on "[name^='sid']" "css_element" in the "1 January 2030" "table_row"
    And I press "Sign-up"
    Then I should see "Topic 1"
