@enrol @totara @enrol_totara_facetoface @javascript
Feature: Test add/update/delete actions for Seminar direct enrolment method
  In order to manage Seminar direct enrolment method
  I use Enrolments plugins to enable Seminar direct enrolment
  As an admin
  I need to add/update/delete Seminar direct enrolment

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | alice    | Alice     | Smith    | alice@example.com |
    And the following "courses" exist:
      | fullname     | shortname |
      | Course 10782 | C10782    |
    And the following "activities" exist:
      | activity   | name          | course | idnumber |
      | facetoface | Seminar 10782 | C10782 | S10782   |
    And I log in as "admin"
    And I navigate to "Manage enrol plugins" node in "Site administration > Plugins > Enrolments"
    And I click on "Enable" "link" in the "Face-to-face direct enrolment" "table_row"
    And I click on "Home" in the totara menu
    And I follow "Course 10782"
    And I add "Face-to-face direct enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |

  Scenario: Check Seminar direct enrolment when no users enrolled
    Given I click on "Home" in the totara menu
    And I follow "Course 10782"
    When I navigate to "Enrolment methods" node in "Course administration > Users"
    Then I should see "Test student enrolment"
    And "//img[contains(@alt,'Delete')]" "xpath_element" should exist in the "Test student enrolment" "table_row"
    And "//img[contains(@alt,'Disable')]" "xpath_element" should exist in the "Test student enrolment" "table_row"
    And "//img[contains(@alt,'Edit')]" "xpath_element" should exist in the "Test student enrolment" "table_row"

    When I click on "Edit" "link" in the "Test student enrolment" "table_row"
    And I set the following fields to these values:
      | Custom instance name | Seminar enrolment 10782 |
    And I press "Save changes"
    Then I should see "Seminar enrolment 10782"
    And I should not see "Test student enrolment"
    And "//img[contains(@alt,'Delete')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Disable')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Edit')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"

    When I click on "Disable" "link" in the "Seminar enrolment 10782" "table_row"
    Then I should see "Seminar enrolment 10782"
    And "//img[contains(@alt,'Delete')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Enable')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Disable')]" "xpath_element" should not exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Edit')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"

    When I click on "Delete" "link" in the "Seminar enrolment 10782" "table_row"
    And I should see "You are about to delete the enrolment method \"Seminar enrolment 10782\". Are you sure you want to continue?"
    And I press "Cancel"
    Then I should see "Seminar enrolment 10782"

    When I click on "Delete" "link" in the "Seminar enrolment 10782" "table_row"
    And I should see "You are about to delete the enrolment method \"Seminar enrolment 10782\". Are you sure you want to continue?"
    And I press "Continue"
    Then I should not see "Seminar enrolment 10782"

  Scenario: Check Seminar direct enrolment with users enrolled
    Given I click on "Home" in the totara menu
    And I follow "Course 10782"
    And I follow "Seminar 10782"
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
    And I press "Save changes"
    And I log out
    And I log in as "alice"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 10782"
    And I click on "[name^='sid']" "css_element" in the "1 January 2030" "table_row"
    When I press "Sign-up"
    Then I should see "Your booking has been completed."
    And I log out

    And I log in as "admin"
    And I follow "Course 10782"
    When I navigate to "Enrolled users" node in "Course administration > Users"
    Then I should see "Alice Smith" in the "Learner" "table_row"
    When I navigate to "Enrolment methods" node in "Course administration > Users"
    Then I should see "Test student enrolment"
    And I should see "1" in the "Test student enrolment" "table_row"
    And "//img[contains(@alt,'Delete')]" "xpath_element" should not exist in the "Test student enrolment" "table_row"
    And "//img[contains(@alt,'Disable')]" "xpath_element" should exist in the "Test student enrolment" "table_row"
    And "//img[contains(@alt,'Edit')]" "xpath_element" should exist in the "Test student enrolment" "table_row"

    When I click on "Edit" "link" in the "Test student enrolment" "table_row"
    And I set the following fields to these values:
      | Custom instance name | Seminar enrolment 10782 |
    And I press "Save changes"
    Then I should see "Seminar enrolment 10782"
    And I should not see "Test student enrolment"
    And "//img[contains(@alt,'Delete')]" "xpath_element" should not exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Disable')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Edit')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"

    When I click on "Disable" "link" in the "Seminar enrolment 10782" "table_row"
    Then I should see "Seminar enrolment 10782"
    And "//img[contains(@alt,'Delete')]" "xpath_element" should not exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Enable')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Disable')]" "xpath_element" should not exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Edit')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"

    When I click on "Enable" "link" in the "Seminar enrolment 10782" "table_row"
    Then I should see "Seminar enrolment 10782"
    And "//img[contains(@alt,'Delete')]" "xpath_element" should not exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Enable')]" "xpath_element" should not exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Disable')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"
    And "//img[contains(@alt,'Edit')]" "xpath_element" should exist in the "Seminar enrolment 10782" "table_row"
