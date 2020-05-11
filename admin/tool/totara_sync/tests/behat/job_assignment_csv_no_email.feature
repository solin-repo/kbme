@javascript @tool @tool_totara_sync @totara @totara_job
Feature: Configure HR Import to sync user job assignment CSV data without an email address.

  Background:
    Given I log in as "admin"
    And I navigate to "General settings" node in "Site administration > HR Import"
    And I set the following fields to these values:
      | File Access | Upload Files |
    And I press "Save changes"
    And I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "User" HR Import element
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Source                 | CSV                                       |
      | Allow duplicate emails | Yes                                       |
      | Link job assignments   | using the user's job assignment ID number |
    And I press "Save changes"
    And I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    And I set the following fields to these values:
      | id_import_jobassignmentidnumber | 1 |
      | id_import_jobassignmentfullname | 1 |
      | id_import_manageridnumber       | 1 |
    And I press "Save changes"
    Then I should see "Settings saved"
    And I should see "\"idnumber\",\"timemodified\",\"username\",\"deleted\",\"firstname\",\"lastname\""
    And I should see "\"jobassignmentidnumber\",\"jobassignmentfullname\",\"manageridnumber\",\"managerjobassignmentidnumber\""

    When I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/users_ja_with_managerjaid_no_email1.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should see "Running HR Import cron...Done!"
    And I should not see "However, there have been some problems"

  Scenario: Verify HR Import can add via CSV User source job assignment data without an email address.

    Given I navigate to "HR Import Log" node in "Site administration > HR Import"
    Then I should see "created user 1"
    And I should see "created user 2"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Dave1 Manager1"
    Then I should see "Manager Job 1"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Dave2 Manager2"
    Then I should see "Manager Job 2"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob1 Learner1"
    And I follow "Learner Job 1"
    Then I should see "Dave1 Manager1 - Manager Job 1"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob1 Learner1"
    And I follow "Learner Job 2"
    Then I should see "Dave2 Manager2 - Manager Job 2"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob1 Learner1"
    And I follow "Learner Job 3"
    Then I should see "Dave2 Manager2 - Manager Job 2"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob2 Learner2"
    And I follow "Learner Job 4"
    Then I should see "Dave1 Manager1 - Manager Job 1"

  Scenario: Verify HR Import can update via CSV User source job assignment data without an email address.

    Given I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    When I upload "admin/tool/totara_sync/tests/fixtures/users_ja_with_managerjaid_no_email2.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should see "Running HR Import cron...Done!"
    And I should not see "However, there have been some problems"

    When I navigate to "HR Import Log" node in "Site administration > HR Import"
    Then I should see "updated user 1"
    And I should see "updated user 2"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Dave1 Manager1"
    Then I should see "Manager Job 1.1"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Dave2 Manager2"
    Then I should see "Manager Job 2"
    And I should see "Manager Job 2B"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob1 Learner1"
    And I follow "Learner Job 1"
    Then I should see "Dave1 Manager1 - Manager Job 1.1"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob1 Learner1"
    And I follow "Learner Job 2"
    Then I should see "Dave2 Manager2 - Manager Job 2B"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob1 Learner1"
    And I follow "Learner Job 3"
    Then I should see "Dave2 Manager2 - Manager Job 2"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob2 Learner2"
    And I follow "Learner Job 4"
    Then I should see "Dave2 Manager2 - Manager Job 2B"
