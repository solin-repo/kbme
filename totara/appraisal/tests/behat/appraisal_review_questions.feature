@totara @totara_appraisal @javascript
Feature: Complete review questions in appraisals
  In order to complete the review questions in appraisals
  As admin I can add them
  As learner or manager I can complete them

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | learner1 | Learner   | One      | learner1@example.com |
      | manager1 | Manager   | One      | manager1@example.com |
    And the following "position" frameworks exist:
      | fullname           | idnumber |
      | Position Framework | posfw    |
    And the following "position" hierarchy exists:
      | fullname     | idnumber | framework |
      | Position One | pos1     | posfw     |
    And the following position assignments exist:
      | user     | position | manager  |
      | learner1 | pos1     | manager1 |
    And the following "goal" frameworks exist:
      | fullname       | idnumber |
      | Goal Framework | goalfw   |
    And the following "goal" hierarchy exists:
      | fullname | idnumber | framework |
      | Company Goal One | goal1    | goalfw    |
      | Company Goal Two | goal2    | goalfw    |
    And I log in as "admin"
    And I navigate to "Manage appraisals" node in "Site administration > Appraisals"
    And I press "Create appraisal"
    And I set the following fields to these values:
      | Name        | Appraisal review questions test |
      | Description | This is the behat description   |
    And I press "Create appraisal"
    And I press "Add stage"
    And I set the following fields to these values:
      | Name                  | Behat Appraisal stage   |
      | Description           | Behat stage description |
      | timedue[enabled]      | 1                       |
      | timedue[day]          | 31                      |
      | timedue[month]        | 12                      |
      | timedue[year]         | 2037                    |
      | Page names (optional) | Page1                   |
    And I click on "Add stage" "button" in the ".fitem_actionbuttons" "css_element"
    And I should see "Behat Appraisal stage" in the ".appraisal-stages" "css_element"
    And I click on "Behat Appraisal stage" "link" in the ".appraisal-stages" "css_element"
    And I click on "Page1" "link" in the ".appraisal-page-list" "css_element"

    And I set the field "id_datatype" to "Goals"
    And I click on "Add" "button" in the "#fgroup_id_addquestgroup" "css_element"
    And I set the following fields to these values:
      | Question     | Goals question   |
      | id_roles_1_2 | 1                |
      | id_roles_1_1 | 1                |
      | id_roles_2_2 | 1                |
      | id_roles_2_1 | 1                |
    And I press "Save changes"
    And I wait "1" seconds
    And I switch to "Assignments" tab
    And I select "Position" from the "groupselector" singleselect
    And I click on "Position One" "link" in the "Assign Learner Group To Appraisal" "totaradialogue"
    And I click on "Save" "button" in the "Assign Learner Group To Appraisal" "totaradialogue"
    And I wait "1" seconds
    And I should see "Learner One" in the "#assignedusers" "css_element"
    And I click on "Activate now" "link"
    And I press "Activate"
    Then I should see "Appraisal review questions test activated"
    When I log out
    And I log in as "learner1"
    And I click on "Goals" in the totara menu
    And I press "Add company goal"
    And I click on "Company Goal One" "link" in the "Assign goals" "totaradialogue"
    And I click on "Company Goal Two" "link" in the "Assign goals" "totaradialogue"
    And I click on "Save" "button" in the "Assign goals" "totaradialogue"
    And I wait "1" seconds

  Scenario: Both learner and manager can complete an appraisal review question
    Given I click on "Latest Appraisal" in the totara menu
    And I press "Start"
    And I press "Choose goals to review"
    And I click on "Company Goal One" "link" in the "Choose goals to review" "totaradialogue"
    And I click on "Save" "button" in the "Choose goals to review" "totaradialogue"
    And I wait "1" seconds
    Then I should see "Company Goal One"
    When I press "Complete Stage"
    And I press "View"
    Then I should see "Company Goal One"
    When I log out
    And I log in as "manager1"
    And I click on "All Appraisals" in the totara menu
    And I click on "Appraisal review questions test" "link"
    And I press "Start"
    Then I should see "Company Goal One"
    When I press "Choose goals to review"
    And I click on "Company Goal Two" "link" in the "Choose goals to review" "totaradialogue"
    And I click on "Save" "button" in the "Choose goals to review" "totaradialogue"
    And I wait "1" seconds
    Then I should see "Company Goal Two"
    When I press "Complete Stage"
    And I press "View"
    Then I should see "Company Goal Two"
