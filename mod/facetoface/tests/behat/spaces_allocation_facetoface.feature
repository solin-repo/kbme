@javascript @mod @mod_facetoface @totara
Feature: Allocate spaces for team in seminar
  In order to test seminar allocations
  As a site manager
  I need to allocate spaces for my team

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username     | firstname | lastname     | email                    | role     | context|
      | sitemanager1 | Terry1    | Sitemanager1 | sitemanager1@example.com | manager  | system |
      | sitemanager2 | Terry2    | Sitemanager2 | sitemanager2@example.com | manager  | system |
      | teacher1     | Terry3    | Teacher      | teacher@example.com      | learner  | system |
      | staff1       | Terry4    | Staff        | staff@example.com    | staffmanager | system |
      | student1     | Sam1      | Student1     | student1@example.com     | learner  | system |
      | student2     | Sam2      | Student2     | student2@example.com     | learner  | system |
      | student3     | Sam3      | Student3     | student3@example.com     | learner  | system |
      | student4     | Sam4      | Student4     | student4@example.com     | learner  | system |
      | student5     | Sam5      | Student5     | student5@example.com     | learner  | system |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | staff1   | C1     | staffmanager   |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
      | student5 | C1     | student        |
    And the following "system role assigns" exist:
      | user         | role         | contextlevel | reference |
      | sitemanager1 | manager      | System       |           |
      | sitemanager2 | manager      | System       | System    |
      | staff1       | staffmanager | System       |           |
    And the following "position" frameworks exist:
      | fullname      | idnumber |
      | PosHierarchy1 | FW001    |
    And the following "position" hierarchy exists:
      | framework | idnumber | fullname   |
      | FW001     | POS001   | Position1  |
    And the following job assignments exist:
      | user     | position | manager      |
      | student1 | POS001   | sitemanager1 |
      | student2 | POS001   | sitemanager1 |
      | student3 | POS001   | sitemanager2 |
      | student4 | POS001   | sitemanager2 |
      | student5 | POS001   | staff1       |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Seminar" to section "1" and I fill the form with:
      | Name                                    | Test seminar name        |
      | Description                             | Test seminar description |
      | How many times the user can sign-up?    | Unlimited                |
      | Fully attended                          | 0                        |
      | Partially attended                      | 0                        |
      | No show                                 | 0                        |
      | Allow manager reservations              | Yes                      |
      | Maximum reservations                    | 10                       |
    And I follow "View all events"
    And I follow "Add a new event"
    And I click on "Edit session" "link"
    And I set the following fields to these values:
      | timestart[day]     | 1    |
      | timestart[month]   | 2    |
      | timestart[year]    | ## next year ## Y ## |
      | timestart[hour]    | 11   |
      | timestart[minute]  | 0    |
      | timefinish[day]    | 1    |
      | timefinish[month]  | 2    |
      | timefinish[year]   | ## next year ## Y ## |
      | timefinish[hour]   | 12   |
      | timefinish[minute] | 0    |
    And I press "OK"
    And I set the following fields to these values:
      | capacity           | 3    |
    And I press "Save changes"
    And I follow "Add a new event"
    And I click on "Edit session" "link"
    And I set the following fields to these values:
      | timestart[day]     | 2    |
      | timestart[month]   | 2    |
      | timestart[year]    | ## next year ## Y ## |
      | timestart[hour]    | 11   |
      | timestart[minute]  | 0    |
      | timefinish[day]    | 2    |
      | timefinish[month]  | 2    |
      | timefinish[year]   | ## next year ## Y ## |
      | timefinish[hour]   | 12   |
      | timefinish[minute] | 0    |
    And I press "OK"
    And I set the following fields to these values:
      | capacity           | 3    |
    And I press "Save changes"
    And I log out

  Scenario: Manager can deallocate users that he has allocated in the current session
    Given I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    And I press "Add"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"
    When I click on "Sam1 Student1" "option"
    And I press "Remove"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Potential allocations" select box should contain "Sam1 Student1"
    And I log out

  Scenario: Capacity should be unaffected if removing allocation and create reservations when removing allocations is set to Yes
    Given I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    When I press "Add"
    Then I should see "1 / 3"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"
    When I set the following fields to these values:
      | replaceallocations         | Yes  |
    And I click on "Sam1 Student1" "option"
    And I press "Remove"
    Then I should see "1 / 3"
    But I click on the link "Allocate spaces for team" in row 1
    And the "Current allocations" select box should not contain "Sam1 Student1"
    And the "Potential allocations" select box should contain "Sam1 Student1"
    And I log out

  Scenario: Capacity should be affected if removing allocation and create reservations when removing allocations is set to No
    Given I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    When I press "Add"
    Then I should see "1 / 3"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"
    When I set the following fields to these values:
      | replaceallocations         | No  |
    And I click on "Sam1 Student1" "option"
    And I press "Remove"
    Then I should see "0 / 3"
    And I click on the link "Allocate spaces for team" in row 1
    And the "Potential allocations" select box should contain "Sam1 Student1"
    And I log out

  Scenario: Manager cannot see users allocated from another managers
    Given I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    And I press "Add"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"
    And I log out

    When I log in as "sitemanager2"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should not contain "Sam1 Student1"
    And I log out

  Scenario: Manager cannot deallocate self booked users even if he is their manager
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Sign-up" in row 1
    And I press "Sign-up"
    And I should see "Your request was accepted"
    And I log out

    When I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1 (Self booked)"
    When I click on "Sam1 Student1" "option"
    And I press "Remove"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1 (Self booked)"
    And I log out

  Scenario: Manager cannot deallocate users in another activity even if he is their manager and he allocated the user
    Given I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    And I click on "Sam1 Student1" "option"
    And I press "Add"
    When I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"

    When I click on "Course 1" "link"
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 2
    Then I should see "Sam1 Student1" in the "Other event(s) in this activity" "optgroup"
    When I click on "Sam1 Student1" "option" in the "#deallocation" "css_element"
    And I press "Remove"
    And I click on the link "Allocate spaces for team" in row 2
    But I should see "Sam1 Student1" in the "Other event(s) in this activity" "optgroup"
    And I log out

  Scenario: Allocate spaces for students in different sessions should be allowed if multiple sessions per signup is On
    Given I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    When I click on "Sam1 Student1" "option"
    And I press "Add"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1"

    When I click on "Course 1" "link"
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 2
    When I click on "Sam1 Student1" "option"
    And I press "Add"
    And I click on the link "Allocate spaces for team" in row 2
    Then the "Current allocations" select box should contain "Sam1 Student1"
    And I log out

  Scenario: Allocate and remove spaces for students when student has self-booked
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Sign-up" in row 1
    And I press "Sign-up"
    And I should see "Your request was accepted"
    And I log out

    When I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 1
    Then the "Current allocations" select box should contain "Sam1 Student1 (Self booked)"

    When I click on "Course 1" "link"
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 2
    And I click on "Sam1 Student1" "option" in the "#allocation" "css_element"
    And I press "Add"
    And I click on the link "Allocate spaces for team" in row 2
    Then I should see "Sam1 Student1" in the "This event" "optgroup"
    And I should see "Sam1 Student1 (Self booked)" in the "Other event(s) in this activity" "optgroup"

    When I click on "Course 1" "link"
    And I follow "View all events"
    And I click on the link "Allocate spaces for team" in row 2
    And I click on "Sam1 Student1" "option"
    And I press "Remove"
    And I click on the link "Allocate spaces for team" in row 2
    Then I should not see "Sam1 Student1" in the "This event" "optgroup"
    And I should see "Sam1 Student1 (Self booked)" in the "Other event(s) in this activity" "optgroup"

  Scenario: Cannot allocate learners in already started event.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I follow "Add a new event"
    And I follow "show-selectdate0-dialog"
    And I set the following fields to these values:
      | timestart[day]     | 3    |
      | timestart[month]   | 3    |
      | timestart[year]    | ## last year ## Y ## |
      | timefinish[day]    | 4    |
      | timefinish[month]  | 4    |
      | timefinish[year]   | ## next year ## Y ## |
    And I press "OK"
    And I press "Save changes"
    And I log out

    When I log in as "sitemanager1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    Then I should see "Event in progress" in the "3 March" "table_row"
    And I should not see "Allocate spaces for team" in the "3 March" "table_row"
    And I should not see "Reserve spaces for team" in the "3 March" "table_row"
    And I should not see "Manage reservations" in the "3 March" "table_row"
    And I should see "Allocate spaces for team" in the "1 February" "table_row"
    And I should see "Reserve spaces for team" in the "1 February" "table_row"
    And I should see "Manage reservations" in the "1 February" "table_row"
    And I should see "Allocate spaces for team" in the "2 February" "table_row"
    And I should see "Reserve spaces for team" in the "2 February" "table_row"
    And I should see "Manage reservations" in the "2 February" "table_row"

  Scenario: Cannot allocate learners when sign-up is closed
    When I log in as "staff1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    Then I should not see "Sign-up unavailable" in the "1 February" "table_row"
    And I should see "Booking open" in the "1 February" "table_row"
    And I should see "Allocate spaces for team" in the "1 February" "table_row"
    And I should see "Reserve spaces for team" in the "1 February" "table_row"
    And I log out

    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on "Edit event" "link" in the "1 February" "table_row"
    When I set the following fields to these values:
      | registrationtimestart[enabled] | 1 |
      | registrationtimestart[day]     | 18 |
      | registrationtimestart[month]   | 1 |
      | registrationtimestart[year]    | ## next year ## Y ## |
    And I press "Save changes"
    And I log out

    When I log in as "staff1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    Then I should see "Sign-up unavailable" in the "1 February" "table_row"
    And I should not see "Reserve for another manager" in the "1 February" "table_row"
    And I should not see "Manage reservations" in the "1 February" "table_row"
    And I should not see "Allocate spaces for team" in the "1 February" "table_row"
    And I should not see "Reserve spaces for team" in the "1 February" "table_row"
    And I log out

    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on "Edit event" "link" in the "1 February" "table_row"
    When I set the following fields to these values:
      | registrationtimefinish[enabled] | 1 |
      | registrationtimefinish[day]     | 24 |
      | registrationtimefinish[month]   | 1 |
      | registrationtimefinish[year]    | ## next year ## Y ## |
    And I press "Save changes"
    And I log out

    When I log in as "staff1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    Then I should see "Sign-up unavailable" in the "1 February" "table_row"
    And I should not see "Reserve for another manager" in the "1 February" "table_row"
    And I should not see "Manage reservations" in the "1 February" "table_row"
    And I should not see "Allocate spaces for team" in the "1 February" "table_row"
    And I should not see "Reserve spaces for team" in the "1 February" "table_row"
    And I log out

    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    And I click on "Edit event" "link" in the "1 February" "table_row"
    When I set the following fields to these values:
      | registrationtimestart[enabled]  | 0 |
      | registrationtimefinish[year]    | ## last year ## Y ## |
    And I press "Save changes"
    And I log out

    When I log in as "staff1"
    And I am on "Course 1" course homepage
    And I follow "View all events"
    Then I should see "Sign-up unavailable" in the "1 February" "table_row"
    And I should not see "Reserve for another manager" in the "1 February" "table_row"
    And I should not see "Manage reservations" in the "1 February" "table_row"
    And I should not see "Allocate spaces for team" in the "1 February" "table_row"
    And I should not see "Reserve spaces for team" in the "1 February" "table_row"
    And I log out
