@_file_upload @javascript @tool @totara @totara_hierarchy @tool_totara_sync
Feature: Verify that parentid is set correctly for organisation CSV uploads.

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And the following "organisation" frameworks exist:
      | fullname                 | idnumber |
      | Organisation Framework 1 | OF1      |

    When I navigate to "General settings" node in "Site administration > HR Import"
    And I set the following fields to these values:
      | File Access | Upload Files |
    And I press "Save changes"
    Then I should see "Settings saved"

    When I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "Organisation" HR Import element
    Then I should see "Element enabled"

    When I navigate to "Organisation" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Source | CSV |
    And I press "Save changes"
    Then I should see "Settings saved"

    When I navigate to "CSV" node in "Site administration > HR Import > Sources > Organisation"
    And I set the following fields to these values:
      | Parent | 1 |
    And I press "Save changes"
    Then I should see "Settings saved"

  Scenario: Verify organisations CSV upload with a parent organisation id of 0.

    Given I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/organisations_parent_zero_1.csv" file to "CSV" filemanager
    When I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"

    When I navigate to "Manage organisations" node in "Site administration > Hierarchies > Organisations"
    And I follow "Organisation Framework 1"
    Then I should see these hierarchy items at the following depths:
      | Head Office       | 1 |
      | Development Team  | 2 |
      | NZ Developers     | 3 |
      | UK Developers     | 3 |
      | Support Team      | 2 |
      | NZ Support        | 3 |
      | UK Support        | 3 |

  Scenario: Verify organisations CSV upload deletes a record and updates the parentid appropriately.

    Given I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/organisations_parent_zero_1.csv" file to "CSV" filemanager
    When I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"

    Given I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/organisations_parent_zero_2.csv" file to "CSV" filemanager
    When I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"

    When I navigate to "Manage organisations" node in "Site administration > Hierarchies > Organisations"
    And I follow "Organisation Framework 1"

    # Marketing is in the wrong place. It should be under UK Office. See TL-12671.
    Then I should see these hierarchy items at the following depths:
      | Head Office           | 1 |
      | UK Office             | 2 |
      | Development & Support | 3 |
      | Development           | 2 |
      | Support               | 2 |
      | Marketing             | 1 |
    And I should not see "Development Team"

  Scenario: Verify organisations CSV upload deletes a record and parentid appropriately.

    Given I navigate to "Organisation" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Empty string behaviour in CSV  | Empty strings erase existing data |
    When I press "Save changes"
    Then I should see "Settings saved"

    When I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/organisations_parent_zero_1.csv" file to "CSV" filemanager
    And I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"

    Given I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/organisations_parent_zero_2.csv" file to "CSV" filemanager
    When I press "Upload"
    Then I should see "HR Import files uploaded successfully"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should not see "Error"
    And I should see "Running HR Import cron...Done!"

    When I navigate to "Manage organisations" node in "Site administration > Hierarchies > Organisations"
    And I follow "Organisation Framework 1"

    # The parentid for Marketing has been deleted so it's at the top level.
    Then I should see these hierarchy items at the following depths:
      | Head Office           | 1 |
      | UK Office             | 2 |
      | Development & Support | 3 |
      | Development           | 2 |
      | Support               | 2 |
      | Marketing             | 1 |
    And I should not see "Development Team"