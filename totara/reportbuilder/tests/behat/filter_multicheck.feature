@totara @totara_reportbuilder @javascript
Feature: Filter multicheck works as expected

  Background: Set up a user report
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Manage user reports" node in "Site administration > Reports"
    And I press "Create report"
    And I set the field "Report Name" to "Report 1"
    And I set the field "Source" to "Users"
    And I press "Create report"
    And I switch to "Performance" tab
    And I click on "overrideexportoptions" "checkbox"
    And I set the following fields to these values:
      | exportoptions[csv]           | 0 |
      | exportoptions[excel]         | 0 |
      | exportoptions[ods]           | 0 |
      | exportoptions[pdflandscape]  | 0 |
      | exportoptions[pdfportrait]   | 0 |
    And I press "Save changes"
    Then I should see "Report Updated"

    When I navigate to "Manage user reports" node in "Site administration > Reports"
    And I press "Create report"
    And I set the field "Report Name" to "Report 2"
    And I set the field "Source" to "Users"
    And I press "Create report"
    And I switch to "Performance" tab
    And I click on "overrideexportoptions" "checkbox"
    And I set the following fields to these values:
      | exportoptions[csv]           | 1 |
      | exportoptions[excel]         | 1 |
      | exportoptions[ods]           | 1 |
      | exportoptions[pdflandscape]  | 1 |
      | exportoptions[pdfportrait]   | 1 |
    And I press "Save changes"
    Then I should see "Report Updated"

    When I navigate to "Manage user reports" node in "Site administration > Reports"
    And I press "Create report"
    And I set the field "Report Name" to "Report 3"
    And I set the field "Source" to "Users"
    And I press "Create report"
    And I switch to "Performance" tab
    And I click on "overrideexportoptions" "checkbox"
    And I set the following fields to these values:
      | exportoptions[csv]           | 1 |
      | exportoptions[excel]         | 1 |
      | exportoptions[ods]           | 1 |
      | exportoptions[pdflandscape]  | 0 |
      | exportoptions[pdfportrait]   | 0 |
    And I press "Save changes"
    Then I should see "Report Updated"

    When I navigate to "Manage user reports" node in "Site administration > Reports"
    And I press "Create report"
    And I set the field "Report Name" to "All report"
    And I set the field "Source" to "Reports"
    And I press "Create report"
    And I switch to "Filters" tab
    And I select "Export formats" from the "newstandardfilter" singleselect
    And I press "Save changes"
    Then I should see "Filters updated"

  Scenario: Test the multicheck filter
    Given I click on "Reports" in the totara menu
    When I follow "All report"
    Then I should see "Export formats"
    And I should see "Report 1" in the ".reportbuilder-table" "css_element"
    And I should see "Report 2" in the ".reportbuilder-table" "css_element"
    And I should see "Report 3" in the ".reportbuilder-table" "css_element"

    # Any value.
    When I set the field "Export formats" to "Any value"
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "Report 1" in the ".reportbuilder-table" "css_element"
    And I should see "Report 2" in the ".reportbuilder-table" "css_element"
    And I should see "Report 3" in the ".reportbuilder-table" "css_element"

    # Any of the selected.
    When I set the field "Export formats" to "Any of the selected"
    And I set the following fields to these values:
      | report-exportoptions[csv]           | 1 |
      | report-exportoptions[excel]         | 0 |
      | report-exportoptions[ods]           | 0 |
      | report-exportoptions[pdflandscape]  | 0 |
      | report-exportoptions[pdfportrait]   | 0 |
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should not see "Report 1" in the ".reportbuilder-table" "css_element"
    And I should see "Report 2" in the ".reportbuilder-table" "css_element"
    And I should see "Report 3" in the ".reportbuilder-table" "css_element"

    # All of the selected.
    When I set the field "Export formats" to "Any of the selected"
    And I set the following fields to these values:
      | report-exportoptions[csv]           | 1 |
      | report-exportoptions[excel]         | 0 |
      | report-exportoptions[ods]           | 0 |
      | report-exportoptions[pdflandscape]  | 0 |
      | report-exportoptions[pdfportrait]   | 0 |
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should not see "Report 1" in the ".reportbuilder-table" "css_element"
    And I should see "Report 2" in the ".reportbuilder-table" "css_element"

    # Not any of the selected.
    When I set the field "Export formats" to "Not any of the selected"
    And I set the following fields to these values:
      | report-exportoptions[csv]           | 1 |
      | report-exportoptions[excel]         | 0 |
      | report-exportoptions[ods]           | 0 |
      | report-exportoptions[pdflandscape]  | 0 |
      | report-exportoptions[pdfportrait]   | 0 |
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "Report 1" in the ".reportbuilder-table" "css_element"
    And I should not see "Report 2" in the ".reportbuilder-table" "css_element"
    And I should not see "Report 3" in the ".reportbuilder-table" "css_element"

    # Not all of the selected.
    When I set the field "Export formats" to "Not all of the selected"
    And I set the following fields to these values:
      | report-exportoptions[csv]           | 1 |
      | report-exportoptions[excel]         | 0 |
      | report-exportoptions[ods]           | 0 |
      | report-exportoptions[pdflandscape]  | 0 |
      | report-exportoptions[pdfportrait]   | 0 |
    And I click on "Search" "button" in the ".fitem_actionbuttons" "css_element"
    Then I should see "Report 1" in the ".reportbuilder-table" "css_element"
    And I should not see "Report 2" in the ".reportbuilder-table" "css_element"
