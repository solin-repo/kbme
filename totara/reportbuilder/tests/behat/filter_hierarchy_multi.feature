@totara @totara_reportbuilder
Feature: Use the multi-item hierarchy filter
  To filter the courses in a report
  by several positions and/or organisations at a time
  I need to use the multi-item hierarchy filter

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | First1    | Last1    | user1@example.com |
      | user2    | First2    | Last2    | user2@example.com |
      | user3    | First3    | Last3    | user3@example.com |
      | user4    | First4    | Last4    | user4@example.com |
      | user5    | First5    | Last5    | user5@example.com |
    And the following "organisation frameworks" exist in "totara_hierarchy" plugin:
      | fullname               | idnumber |
      | Organisation Framework | orgfw    |
    And the following "organisations" exist in "totara_hierarchy" plugin:
      | fullname         | idnumber | org_framework |
      | Organisation One | org1     | orgfw         |
      | Organisation Two | org2     | orgfw         |
    And the following "organisation assignments" exist in "totara_hierarchy" plugin:
      | user  | organisation |
      | user1 | org1         |
      | user2 | org2         |
      | user3 | org1         |
      | user4 | org2         |
    And the following "position frameworks" exist in "totara_hierarchy" plugin:
      | fullname           | idnumber |
      | Position Framework | posfw    |
    And the following "positions" exist in "totara_hierarchy" plugin:
      | fullname     | idnumber | pos_framework |
      | Position One | pos1     | posfw         |
      | Position Two | pos2     | posfw         |
    And the following "position assignments" exist in "totara_hierarchy" plugin:
      | user    | position |
      | user1 | pos1     |
      | user2 | pos1     |
      | user3 | pos2     |
      | user4 | pos2     |
    And I log in as "admin"
    And I navigate to "Manage reports" node in "Site administration > Reports > Report builder"

  @javascript
  Scenario: Use position filter with User report source
    Given I set the field "Report Name" to "Users"
    And I set the field "Source" to "user"
    And I press "Create report"
    And I click on "Filters" "link" in the ".tabtree" "css_element"
    And I select "User's Position (multi-item)" from the "newstandardfilter" singleselect
    And I press "Add"
    And I select "User's Position Framework ID" from the "newstandardfilter" singleselect
    And I press "Save changes"
    And I follow "View This Report"
    Then I should see "user1" in the ".reportbuilder-table" "css_element"
    And I should see "user2" in the ".reportbuilder-table" "css_element"
    And I should see "user3" in the ".reportbuilder-table" "css_element"
    And I should see "user4" in the ".reportbuilder-table" "css_element"
    And I should see "user5" in the ".reportbuilder-table" "css_element"
    When I select "is greater than or equal to" from the "user-positionframeworkid_op" singleselect
    And I set the field "user-positionframeworkid" to "1"
    And I click on "Search" "button" in the "#fgroup_id_submitgroupstandard" "css_element"
    Then I should see "user1"
    And I should see "user2" in the ".reportbuilder-table" "css_element"
    And I should see "user3" in the ".reportbuilder-table" "css_element"
    And I should see "user4" in the ".reportbuilder-table" "css_element"
    And I should not see "user5" in the ".reportbuilder-table" "css_element"
    And I click on "Clear" "button" in the "#fgroup_id_submitgroupstandard" "css_element"
    When I click on "Choose Positions" "link" in the "Search by" "fieldset"
    And I click on "Position One" "link" in the "Choose Positions" "totaradialogue"
    And I click on "Save" "button" in the "Choose Positions" "totaradialogue"
    And I wait "1" seconds
    And I click on "Search" "button" in the "#fgroup_id_submitgroupstandard" "css_element"
    Then I should see "user1" in the ".reportbuilder-table" "css_element"
    And I should see "user2" in the ".reportbuilder-table" "css_element"
    And I should not see "user3" in the ".reportbuilder-table" "css_element"
    And I should not see "user4" in the ".reportbuilder-table" "css_element"
    And I should not see "user5" in the ".reportbuilder-table" "css_element"
    When I click on "Choose Positions" "link" in the "Search by" "fieldset"
    And I click on "Position Two" "link" in the "Choose Positions" "totaradialogue"
    And I click on "Save" "button" in the "Choose Positions" "totaradialogue"
    And I wait "1" seconds
    And I click on "Search" "button" in the "#fgroup_id_submitgroupstandard" "css_element"
    Then I should see "user1" in the ".reportbuilder-table" "css_element"
    And I should see "user2" in the ".reportbuilder-table" "css_element"
    And I should see "user3" in the ".reportbuilder-table" "css_element"
    And I should see "user4" in the ".reportbuilder-table" "css_element"
    And I should not see "user5" in the ".reportbuilder-table" "css_element"

  @javascript
  Scenario: Use organisation filter with User report source
    Given I set the field "Report Name" to "Users"
    And I set the field "Source" to "User"
    And I press "Create report"
    And I click on "Filters" "link" in the ".tabtree" "css_element"
    And I select "User's Organisation (multi-item)" from the "newstandardfilter" singleselect
    And I press "Save changes"
    And I follow "View This Report"
    Then I should see "user1" in the ".reportbuilder-table" "css_element"
    And I should see "user2" in the ".reportbuilder-table" "css_element"
    And I should see "user3" in the ".reportbuilder-table" "css_element"
    And I should see "user4" in the ".reportbuilder-table" "css_element"
    And I should see "user5" in the ".reportbuilder-table" "css_element"
    When I click on "Choose Organisations" "link" in the "Search by" "fieldset"
    And I click on "Organisation One" "link" in the "Choose Organisations" "totaradialogue"
    And I click on "Save" "button" in the "Choose Organisations" "totaradialogue"
    And I wait "1" seconds
    And I click on "Search" "button" in the "#fgroup_id_submitgroupstandard" "css_element"
    Then I should see "user1" in the ".reportbuilder-table" "css_element"
    And I should not see "user2" in the ".reportbuilder-table" "css_element"
    And I should see "user3" in the ".reportbuilder-table" "css_element"
    And I should not see "user4" in the ".reportbuilder-table" "css_element"
    And I should not see "user5" in the ".reportbuilder-table" "css_element"
    When I click on "Choose Organisations" "link" in the "Search by" "fieldset"
    And I click on "Organisation Two" "link" in the "Choose Organisations" "totaradialogue"
    And I click on "Save" "button" in the "Choose Organisations" "totaradialogue"
    And I wait "1" seconds
    And I click on "Search" "button" in the "#fgroup_id_submitgroupstandard" "css_element"
    Then I should see "user1" in the ".reportbuilder-table" "css_element"
    And I should see "user2" in the ".reportbuilder-table" "css_element"
    And I should see "user3" in the ".reportbuilder-table" "css_element"
    And I should see "user4" in the ".reportbuilder-table" "css_element"
    And I should not see "user5" in the ".reportbuilder-table" "css_element"
