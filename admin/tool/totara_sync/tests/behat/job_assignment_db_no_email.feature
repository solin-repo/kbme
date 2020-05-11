@javascript @tool @tool_totara_sync @totara @totara_job
Feature: Configure HR Import to sync user job assignment database data without an email address.

  Background:
    Given I log in as "admin"
    And the following "user" HR Import database source exists:
      | idnumber | timemodified | deleted | username | firstname | lastname | jobassignmentidnumber | jobassignmentfullname | manageridnumber | managerjobassignmentidnumber |
      | 1        | 0            | 0       | learner1 | Bob1      | Learner1 | LearnerJobId1         | Learner Job 1         | 3               | ManagerJobId1                |
      | 1        | 0            | 0       | learner1 | Bob1      | Learner1 | LearnerJobId2         | Learner Job 2         | 4               | ManagerJobId2                |
      | 1        | 0            | 0       | learner1 | Bob1      | Learner1 | LearnerJobId3         | Learner Job 3         | 4               | ManagerJobId2                |
      | 2        | 0            | 0       | learner2 | Bob2      | Learner2 | LearnerJobId4         | Learner Job 4         | 3               | ManagerJobId1                |
      | 3        | 0            | 0       | manager1 | Dave1     | Manager1 | ManagerJobId1         | Manager Job 1         |                 |                              |
      | 4        | 0            | 0       | manager2 | Dave2     | Manager2 | ManagerJobId2         | Manager Job 2         |                 |                              |
    And I navigate to "General settings" node in "Site administration > HR Import"
    And I set the following fields to these values:
      | File Access | Upload Files |
    And I press "Save changes"
    And I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "User" HR Import element
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Source                 | External Database                        |
      | Allow duplicate emails | Yes                                       |
      | Link job assignments   | using the user's job assignment ID number |
    And I press "Save changes"
    And I navigate to "External Database" node in "Site administration > HR Import > Sources > User"
    And I set the following fields to these values:
      | id_import_jobassignmentidnumber | 1 |
      | id_import_jobassignmentfullname | 1 |
      | id_import_manageridnumber       | 1 |
    And I press "Save changes"
    Then I should see "Settings saved"
    And I should see "idnumber, timemodified, username, deleted, firstname, lastname,"
    And I should see "jobassignmentidnumber, jobassignmentfullname, manageridnumber, managerjobassignmentidnumber"

    When I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should see "Running HR Import cron...Done!"
    And I should not see "However, there have been some problems"

  Scenario: Verify HR Import can add via database User source job assignment data without an email address.

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

  Scenario: Verify HR Import can update via database User source job assignment data without an email address.

    Given the following "user" HR Import database source exists:
      | idnumber | timemodified | deleted | username | firstname | lastname | jobassignmentidnumber | jobassignmentfullname | manageridnumber | managerjobassignmentidnumber |
      | 1        | 0            | 0       | learner1 | Bob1      | Learner1 | LearnerJobId1         | Learner Job 1         | 3               | ManagerJobId1                |
      | 1        | 0            | 0       | learner1 | Bob1      | Learner1 | LearnerJobId2         | Learner Job 2         | 4               | ManagerJobId2B               |
      | 1        | 0            | 0       | learner1 | Bob1      | Learner1 | LearnerJobId3         | Learner Job 3         |                 |                              |
      | 2        | 0            | 0       | learner2 | Bob2      | Learner2 | LearnerJobId4         | Learner Job 4         | 4               | ManagerJobId2B               |
      | 3        | 0            | 0       | manager1 | Dave1     | Manager1 | ManagerJobId1         | Manager Job 1.1       |                 |                              |
      | 4        | 0            | 0       | manager2 | Dave2     | Manager2 | ManagerJobId2B        | Manager Job 2B        |                 |                              |
    When I navigate to "Run HR Import" node in "Site administration > HR Import"
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
    # TL-15954. The behaviour here is inconsistent with CSV import.
    # Then I should see "Dave2 Manager2 - Manager Job 2"
    Then I should not see "Dave2 Manager2 - Manager Job 2"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "Bob2 Learner2"
    And I follow "Learner Job 4"
    Then I should see "Dave2 Manager2 - Manager Job 2B"
