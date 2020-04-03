@totara @totara_reportbuilder @javascript
Feature: Test expand course details in Reportbuilder
  As a admin
  I need to be able to expand course details in reports regardless whether the
  report has enabled filters or not

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username     | firstname | lastname  | email                 |
      | student1     | Sam1      | Student1  | student1@example.com  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
      | Course 3 | C3        | 0        |

    And I log in as "admin"
    And I navigate to "Manage enrol plugins" node in "Site administration > Plugins > Enrolments"
    And I click on "Enable" "link" in the "Face-to-face direct enrolment" "table_row"
    And I am on homepage

    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test facetoface 1             |
      | Description                             | Test facetoface 1 description |
    And I navigate to "Enrolment methods" node in "Course administration > Users"
    And I click on "Disable" "link" in the "Manual enrolments" "table_row"
    And I click on "Disable" "link" in the "Program" "table_row"
    And I set the field "Add method" to "Face-to-face direct enrolment"
    And I press "Add method"

    And I click on "Find Learning" in the totara menu
    And I follow "Course 2"
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | Test facetoface 2             |
      | Description                             | Test facetoface 2 description |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][month]   | 0                |
      | timestart[0][day]     | +1               |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | 0                |
      | timefinish[0][month]  | 0                |
      | timefinish[0][day]    | +1               |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | +1               |
      | timefinish[0][minute] | 0                |
    And I press "Save changes"
    And I navigate to "Enrolment methods" node in "Course administration > Users"
    And I click on "Disable" "link" in the "Manual enrolments" "table_row"
    And I click on "Disable" "link" in the "Program" "table_row"
    And I set the field "Add method" to "Face-to-face direct enrolment"
    And I press "Add method"

    And I click on "Find Learning" in the totara menu
    And I follow "Course 3"
    And I add a "Page" to section "1" and I fill the form with:
      | Name                | Page 1 |
      | Description         | Test   |
      | Page content        | Test   |
    And I log out

  Scenario: Expand course detail in coursecatalog with filters
    Given I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 1')]" "xpath_element"
    Then I should see "Face-to-face direct enrolment"
    And I should see "Cannot enrol (no face-to-face sessions in this course)"
    And I should not see "To enrol in the session and course, choose a session below"
    And I should not see "Manual enrolments, Program"

    When I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 1')]" "xpath_element"
    And I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 2')]" "xpath_element"
    Then I should see "Face-to-face direct enrolment"
    And I should not see "Cannot enrol (no face-to-face sessions in this course)"
    And I should see "To enrol in the session and course, choose a session below"
    And I should not see "Manual enrolments, Program"

    When I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 1')]" "xpath_element"
    And I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 3')]" "xpath_element"
    Then I should see "Manual enrolments, Program"
    And I log out

@_alert
  Scenario: Expand course detail in coursecatalog with all filters disabled
    Given I log in as "admin"
    And I click on "Find Learning" in the totara menu
    And I press "Edit this report"
    And I switch to "Filters" tab
    # Deleting all filters
    And I click on "Delete" "link" confirming the dialogue
    And I click on "Delete" "link" confirming the dialogue
    And I press "Save changes"
    And I log out

    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 1')]" "xpath_element"
    Then I should see "Face-to-face direct enrolment"
    And I should see "Cannot enrol (no face-to-face sessions in this course)"
    And I should not see "To enrol in the session and course, choose a session below"
    And I should not see "Manual enrolments, Program"

    When I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 1')]" "xpath_element"
    And I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 2')]" "xpath_element"
    Then I should see "Face-to-face direct enrolment"
    And I should not see "Cannot enrol (no face-to-face sessions in this course)"
    And I should see "To enrol in the session and course, choose a session below"
    And I should not see "Manual enrolments, Program"

    When I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 1')]" "xpath_element"
    And I click on "//div[contains(@class, 'rb-display-expand') and contains (., 'Course 3')]" "xpath_element"
    Then I should see "Manual enrolments, Program"
    And I log out
