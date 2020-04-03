@totara @totara_program @javascript
Feature: Course enrolment through programs
  Verify that user enrolment / unenrolment in courses associated with a program
  is handled correctly for all Unenrol program plugin settings

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher   | First    | teacher1@example.com |
      | learner1 | Learner   | One      | learner1@example.com |
      | learner2 | Learner   | Two      | learner2@example.com |
      | manager1 | Manager   | One      | manager1@example.com |

    Given the following "organisation frameworks" exist in "totara_hierarchy" plugin:
      | fullname        | idnumber |
      | Organisation FW | OFW001   |
    And the following "organisations" exist in "totara_hierarchy" plugin:
      | org_framework | fullname      | idnumber |
      | OFW001        | Organisation1 | org1     |
    And the following "position frameworks" exist in "totara_hierarchy" plugin:
      | fullname    | idnumber |
      | Position FW | PFW001   |
    And the following "positions" exist in "totara_hierarchy" plugin:
      | pos_framework | fullname  | idnumber |
      | PFW001        | Manager   | manager  |
      | PFW001        | Learner   | learner  |
    And the following position assignments exist:
      | user       | organisation | position | manager  |
      | teacher1   |              |          |          |
      | manager1   |              | manager  | teacher1 |
      | learner1   | org1         | learner  | manager1 |
      | learner2   | org1         | learner  | manager1 |

    # Create two programs with one course each
    And the following "programs" exist in "totara_program" plugin:
      | fullname                | shortname |
      | Test Program 1          | program1  |
      | Test Program 2          | program2  |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | course1   | 1                |
      | Course 2 | course2   | 1                |
    And I add a courseset with courses "course1" to "program1":
      | Set name              | set1        |
      | Learner must complete | All courses |
      | Minimum time required | 1           |
    And I add a courseset with courses "course2" to "program2":
      | Set name              | set1        |
      | Learner must complete | All courses |
      | Minimum time required | 1           |

    # Assign the position Learner program1
    And I log in as "admin"
    And I click on "Programs" in the totara menu
    And I follow "Test Program 1"
    And I press "Edit program details"
    And I switch to "Assignments" tab
    And I select "Positions" from the "Add a new" singleselect
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I press "Add position to program"
    And I click on "Learner" "link" in the "Add position to program" "totaradialogue"
    And I click on "Ok" "button" in the "Add position to program" "totaradialogue"
    And I press "Save changes"
    And I press "Save all changes"
    Then I should see "Learner"

    # Assign the organisation to the program2
    When I click on "Programs" in the totara menu
    And I follow "Test Program 2"
    And I press "Edit program details"
    And I switch to "Assignments" tab
    And I select "Organisations" from the "Add a new" singleselect
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I press "Add organisations to program"
    And I click on "Organisation1" "link" in the "Add organisations to program" "totaradialogue"
    And I click on "Ok" "button" in the "Add organisations to program" "totaradialogue"
    And I press "Save changes"
    And I press "Save all changes"
    Then I should see "Organisation1"
    And I log out

    # Enrol learner1 in both courses
    When I log in as "learner1"
    And I click on "Record of Learning" in the totara menu
    Then I should see "Test Program 1"
    And I should see "Test Program 2"
    When I follow "Test Program 1"
    Then I should see "Course 1"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 1 via required learning program Test Program 1."
    And I click on "Record of Learning" in the totara menu
    And I follow "Test Program 2"
    Then I should see "Course 2"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 2 via required learning program Test Program 2."
    And I log out

  Scenario: Enrolled user removed from program with Unenrol program plugin setting
    Given I log in as "admin"
    # Set the program plugin unenrolment action
    When I navigate to "Program" node in "Site administration > Plugins > Enrolments"
    And I select "Unenrol user from course" from the "External unenrol action" singleselect
    And I press "Save changes"

    # Remove learner1's position
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Learner One"
    And I follow "Primary position"
    And I click on "Delete" "link" in the "#positiontitle" "css_element"
    And I click on "Update position" "button"
    And I log out

    # Run the cron tasks
    When I run the scheduled task "\totara_program\task\user_assignments_task"

    # learner1 should no longer be enrolled in the program and should not be able to access the course
    And I log in as "learner1"
    And I click on "Record of Learning" in the totara menu
    Then I should not see "Test Program 1"
    And I should see "Test Program 2"

    When I click on "Courses" in the totara menu
    Then I should see "Course 1"
    And I should see "Course 2"

    When I follow "Course 1"
    Then I should see "You can not enrol yourself in this course"
    When I click on "Courses" in the totara menu
    And I follow "Course 2"
    Then I should see "Topic 1"
    And I log out

    # learner2 can still enrol
    When I log in as "learner2"
    And I click on "Record of Learning" in the totara menu
    Then I should see "Test Program 1"
    And I should see "Test Program 2"
    When I follow "Test Program 1"
    Then I should see "Course 1"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 1 via required learning program Test Program 1."
    When I click on "Record of Learning" in the totara menu
    And I follow "Test Program 2"
    Then I should see "Course 2"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 2 via required learning program Test Program 2."
    And I log out

  Scenario: Enrolled user removed from program with Disable course enrolment program plugin setting
    Given I log in as "admin"
    # Set the program plugin unenrolment action
    When I navigate to "Program" node in "Site administration > Plugins > Enrolments"
    And I select "Disable course enrolment" from the "External unenrol action" singleselect
    And I press "Save changes"

    # Remove learner1's position
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Learner One"
    And I follow "Primary position"
    And I click on "Delete" "link" in the "#positiontitle" "css_element"
    And I click on "Update position" "button"
    And I log out

    # Run the cron tasks
    When I run the scheduled task "\totara_program\task\user_assignments_task"

    # learner1 should no longer be enrolled in the program and should not be able to access the course
    And I log in as "learner1"
    And I click on "Record of Learning" in the totara menu
    Then I should not see "Test Program 1"
    And I should see "Test Program 2"

    When I click on "Courses" in the totara menu
    Then I should see "Course 1"
    And I should see "Course 2"

    When I follow "Course 1"
    Then I should see "You can not enrol yourself in this course"

    When I click on "Courses" in the totara menu
    And I follow "Course 2"
    Then I should see "Topic 1"
    And I log out

    # learner2 can still enrol
    When I log in as "learner2"
    And I click on "Record of Learning" in the totara menu
    Then I should see "Test Program 1"
    And I should see "Test Program 2"
    When I follow "Test Program 1"
    Then I should see "Course 1"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 1 via required learning program Test Program 1."
    When I click on "Record of Learning" in the totara menu
    And I follow "Test Program 2"
    Then I should see "Course 2"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 2 via required learning program Test Program 2."
    And I log out

  Scenario: Enrolled user removed from program with Disable course enrolment and remove roles program plugin setting
    Given I log in as "admin"
    # Set the program plugin unenrolment action
    When I navigate to "Program" node in "Site administration > Plugins > Enrolments"
    And I select "Disable course enrolment and remove roles" from the "External unenrol action" singleselect
    And I press "Save changes"

    # Remove learner1's position
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Learner One"
    And I follow "Primary position"
    And I click on "Delete" "link" in the "#positiontitle" "css_element"
    And I click on "Update position" "button"
    And I log out

    # Run the cron tasks
    When I run the scheduled task "\totara_program\task\user_assignments_task"

    # User1 should no longer be enrolled in the program and should not be able to access the course
    And I log in as "learner1"
    And I click on "Record of Learning" in the totara menu
    Then I should not see "Test Program 1"
    And I should see "Test Program 2"

    When I click on "Courses" in the totara menu
    Then I should see "Course 1"
    And I should see "Course 2"

    When I follow "Course 1"
    Then I should see "You can not enrol yourself in this course"

    When I click on "Courses" in the totara menu
    And I follow "Course 2"
    Then I should see "Topic 1"
    And I log out

    # learner2 can still enrol
    When I log in as "learner2"
    And I click on "Record of Learning" in the totara menu
    Then I should see "Test Program 1"
    And I should see "Test Program 2"
    When I follow "Test Program 1"
    Then I should see "Course 1"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 1 via required learning program Test Program 1."
    When I click on "Record of Learning" in the totara menu
    And I follow "Test Program 2"
    Then I should see "Course 2"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 2 via required learning program Test Program 2."
    And I log out

  Scenario: User added to program
    # teacher1 not in any program. Should not be allowed to enrol
    Given I log in as "teacher1"
    And I click on "Record of Learning" in the totara menu
    Then I should not see "Test Program 1"
    And I should not see "Test Program 2"

    When I click on "Courses" in the totara menu
    Then I should see "Course 1"
    And I should see "Course 2"

    When I follow "Course 1"
    Then I should see "You can not enrol yourself in this course"
    When I click on "Courses" in the totara menu
    And I follow "Course 2"
    Then I should see "You can not enrol yourself in this course"
    And I log out

    # Now add teacher to both programs
    Given I log in as "admin"

    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Teacher First"
    And I follow "Primary position"
    And I press "Choose position"
    And I click on "Learner" "link" in the "Choose position" "totaradialogue"
    And I click on "OK" "button" in the "Choose position" "totaradialogue"
    And I press "Choose organisation"
    And I click on "Organisation1" "link" in the "Choose organisation" "totaradialogue"
    And I click on "OK" "button" in the "Choose organisation" "totaradialogue"
    And I click on "Update position" "button"
    And I log out

    # Run the cron tasks
    When I run the scheduled task "\totara_program\task\user_assignments_task"

    # teacher1 should now be able to enrol in the courses
    And I log in as "teacher1"
    And I click on "Record of Learning" in the totara menu
    Then I should see "Test Program 1"
    And I should see "Test Program 2"

    When I follow "Test Program 1"
    Then I should see "Course 1"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 1 via required learning program Test Program 1."
    When I click on "Record of Learning" in the totara menu
    And I follow "Test Program 2"
    Then I should see "Course 2"
    When I press "Launch course"
    Then I should see "You have been enrolled in course Course 2 via required learning program Test Program 2."
    And I log out
