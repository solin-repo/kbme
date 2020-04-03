@mod @mod_facetoface @totara @core_calendar
Feature: Face to face calendar
  In order to verify Face to Face events in the calendar
  As a teacher
  I need to create and assign Face to face activities

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                | city     | country | calendartype | timezone           |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com | Auckland | NZ      | gregorian    | 'Pacific/Auckland' |
      | student1 | Sam1      | Student1 | student1@example.com | Chicago  | US      | gregorian    | 'America/Chicago'  |
      | student2 | Sam2      | Student2 | student2@example.com | Madrid   | ES      | gregorian    | 'UTC'              |
      | student3 | Sam3      | Student3 | student3@example.com | Perth    | AU      | gregorian    | 'Australia/Perth'  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |

  @javascript
  Scenario: View main calendar
    Given I log in as "teacher1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test facetoface name        |
      | Description                             | Test facetoface description |
      | Allow multiple sessions signup per user | 1                           |
      | Show entry on user's calendar           | 1                           |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][day]     | +1               |
      | timestart[0][month]   | 0                |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][day]    | +1               |
      | timefinish[0][month]  | 0                |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
    And I press "Save changes"
    And I log out

    When I log in as "student1"
    And I click on "Calendar" "link"
#    Make step to see the date.
#    see calendar_format_event_time function to get the expected result.
    And I should see "(time zone: Pacific/Auckland)"
    And I log out

