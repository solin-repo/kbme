@totara @totara_reportbuilder @javascript
Feature: Caching works as expected when adding search columns
  In order to check cache report builder is working when adding search columns
  As a admin
  I need to be able set up caching and add search columns as filters

  Background:
    Given I log in as "admin"
    And I set the following administration settings values:
      | Enable report caching | 1 |

  Scenario:
    Given I navigate to "Manage reports" node in "Site administration > Reports > Report builder"
    And I set the field "Report Name" to "Custom Course Report"
    And I set the field "Source" to "Courses"
    And I press "Create report"
    And I click on "Filters" "link" in the ".tabtree" "css_element"
    And I set the field "newsearchcolumn" to "Tags"
    And I press "Add"
    And I click on "Performance" "link" in the ".tabtree" "css_element"
    And I click on "Enable Report Caching" "text"
    And I click on "Generate Now" "text"
    And I click on "Save changes" "button"
    And I should see "Last cached"
    And I should not see "Not cached yet"

  Scenario:
    Given I navigate to "Manage reports" node in "Site administration > Reports > Report builder"
    And I set the field "Report Name" to "Custom Face-to-face Sessions Report"
    And I set the field "Source" to "Face-to-face sessions"
    And I press "Create report"
    And I click on "Filters" "link" in the ".tabtree" "css_element"
    And I set the field "newsearchcolumn" to "Building"
    And I press "Add"
    And I click on "Performance" "link" in the ".tabtree" "css_element"
    And I click on "Enable Report Caching" "text"
    And I click on "Generate Now" "text"
    And I click on "Save changes" "button"
    And I should see "Last cached"
    And I should not see "Not cached yet"

  Scenario:
    Given I navigate to "Manage reports" node in "Site administration > Reports > Report builder"
    And I set the field "Report Name" to "Custom Audience Report"
    And I set the field "Source" to "Audiences"
    And I press "Create report"
    And I click on "Filters" "link" in the ".tabtree" "css_element"
    And I set the field "newsearchcolumn" to "User's Organisation Name"
    And I press "Add"
    And I click on "Performance" "link" in the ".tabtree" "css_element"
    And I click on "Enable Report Caching" "text"
    And I click on "Generate Now" "text"
    And I click on "Save changes" "button"
    And I should see "Last cached"
    And I should not see "Not cached yet"
