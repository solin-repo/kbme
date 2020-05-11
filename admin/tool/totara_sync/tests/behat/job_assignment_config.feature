@javascript @tool @tool_totara_sync @totara @totara_job
Feature: Configure user source to import job assignment data in HR sync
  In order to test HR import of users with job assignments
  I must log in as an admin and configure the user source

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
      | Source | CSV |
    And I press "Save changes"
    And I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    And I should see "\"firstname\""
    And I should see "\"lastname\""
    And I should see "\"email\""
    And I should not see "\"jobassignmentidnumber\""
    And I should not see "\"managerjobassignmentidnumber\""
    And I press "Save changes"

  Scenario: Configure HR import source link jaidnumber off
    Given I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Link job assignments | 0 |
    And I press "Save changes"
    And I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    Then I should not see "\"jobassignmentidnumber\""
    And I should not see "\"jobassignmentfullname\""
    And I should not see "\"jobassignmentstartdate\""
    And I should not see "\"jobassignmentenddate\""
    And I should not see "\"orgidnumber\""
    And I should not see "\"posidnumber\""
    And I should not see "\"manageridnumber\""
    And I should not see "\"managerjobassignmentidnumber\""
    And I should not see "\"appraiseridnumber\""
    When I set the following fields to these values:
      | Job assignment full name  | 1 |
      | Job assignment start date | 1 |
      | Job assignment end date   | 1 |
      | Organisation              | 1 |
      | Position                  | 1 |
      | Manager                   | 1 |
      | Appraiser                 | 1 |
    And I press "Save changes"
    Then I should not see "\"jobassignmentidnumber\""
    And I should see "\"jobassignmentfullname\""
    And I should see "\"jobassignmentstartdate\""
    And I should see "\"jobassignmentenddate\""
    And I should see "\"orgidnumber\""
    And I should see "\"posidnumber\""
    And I should see "\"manageridnumber\""
    And I should not see "\"managerjobassignmentidnumber\""
    And I should see "\"appraiseridnumber\""
    When I set the following fields to these values:
      | Job assignment ID number | 0 |
    And I press "Save changes"
    Then I should not see "\"jobassignmentidnumber\""
    And I should see "\"jobassignmentfullname\""
    And I should see "\"jobassignmentstartdate\""
    And I should see "\"jobassignmentenddate\""
    And I should see "\"orgidnumber\""
    And I should see "\"posidnumber\""
    And I should see "\"manageridnumber\""
    And I should not see "\"managerjobassignmentidnumber\""
    And I should see "\"appraiseridnumber\""
    And "#id_import_managerjobassignmentidnumber[type=checkbox][disabled=disabled]" "css_element" should not exist

  Scenario: Configure HR import source link jaidnumber on
    Given I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Link job assignments | 1 |
    And I press "Save changes"
    And I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    Then I should not see "\"jobassignmentidnumber\""
    And I should not see "\"jobassignmentfullname\""
    And I should not see "\"jobassignmentstartdate\""
    And I should not see "\"jobassignmentenddate\""
    And I should not see "\"orgidnumber\""
    And I should not see "\"posidnumber\""
    And I should not see "\"manageridnumber\""
    And I should not see "\"managerjobassignmentidnumber\""
    And I should not see "\"appraiseridnumber\""
    And "#id_import_jobassignmentfullname[type=checkbox][disabled=disabled]" "css_element" should exist
    And "#id_import_jobassignmentstartdate[type=checkbox][disabled=disabled]" "css_element" should exist
    And "#id_import_jobassignmentenddate[type=checkbox][disabled=disabled]" "css_element" should exist
    And "#id_import_orgidnumber[type=checkbox][disabled=disabled]" "css_element" should exist
    And "#id_import_posidnumber[type=checkbox][disabled=disabled]" "css_element" should exist
    And "#id_import_manageridnumber[type=checkbox][disabled=disabled]" "css_element" should exist
    And "#id_import_managerjobassignmentidnumber[type=checkbox][disabled=disabled]" "css_element" should exist
    When I set the following fields to these values:
      | Job assignment ID number  | 1 |
      | Job assignment full name  | 1 |
      | Job assignment start date | 1 |
      | Job assignment end date   | 1 |
      | Organisation              | 1 |
      | Position                  | 1 |
      | Manager                   | 1 |
      | Appraiser                 | 1 |
    And I press "Save changes"
    Then I should see "\"jobassignmentidnumber\""
    And I should see "\"jobassignmentfullname\""
    And I should see "\"jobassignmentstartdate\""
    And I should see "\"jobassignmentenddate\""
    And I should see "\"orgidnumber\""
    And I should see "\"posidnumber\""
    And I should see "\"manageridnumber\""
    And I should see "\"managerjobassignmentidnumber\""
    And I should see "\"appraiseridnumber\""
    When I set the following fields to these values:
      | Manager | 0 |
    And I press "Save changes"
    Then I should not see "\"manageridnumber\""
    And I should not see "\"managerjobassignmentidnumber\""
    When I set the following fields to these values:
      | Manager | 1 |
    And I press "Save changes"
    Then I should see "\"manageridnumber\""
    And I should see "\"managerjobassignmentidnumber\""
    When I set the following fields to these values:
      | Job assignment ID number | 0 |
    And I press "Save changes"
    Then I should not see "\"jobassignmentidnumber\""
    And I should not see "\"jobassignmentfullname\""
    And I should not see "\"jobassignmentstartdate\""
    And I should not see "\"jobassignmentenddate\""
    And I should not see "\"orgidnumber\""
    And I should not see "\"posidnumber\""
    And I should not see "\"manageridnumber\""
    And I should not see "\"managerjobassignmentidnumber\""
    And I should not see "\"appraiseridnumber\""

  Scenario: Configure HR import source link jaidnumber cannot be turned off after run with setting on
    Given I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Link job assignments | using the user's job assignment ID number |
    And I press "Save changes"
    When I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    Then I should see "Manager's job assignment"
    When I set the following fields to these values:
      | Job assignment ID number  | 1 |
      | Job assignment full name  | 1 |
      | Manager                   | 1 |
    And I press "Save changes"
    Then the following fields match these values:
      | Job assignment ID number  | 1 |
      | Job assignment full name  | 1 |
      | Manager                   | 1 |
      | Manager's job assignment  | 1 |

    # Setting can still be changed before first run with setting on.
    When I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Link job assignments | to the user's first job assignment |
    And I press "Save changes"
    Then the following fields match these values:
      | Link job assignments | to the user's first job assignment |
    When I set the following fields to these values:
      | Link job assignments | using the user's job assignment ID number |
    And I press "Save changes"
    Then the following fields match these values:
      | Link job assignments | using the user's job assignment ID number |

    # Run HR Import now.
    When I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/users_ja_with_managerjaid_only_1.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should see "Running HR Import cron...Done!"

    # Make sure data was imported correctly.
    When I navigate to "HR Import Log" node in "Site administration > HR Import"
    Then I should see "created user learner1"
    And I should see "created user manager1"
    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "Learner One" "link"
    And I click on "Learner1 JA1" "link"
    Then I should not see "Manager One (manager1@example.com) - Manager1 JA2"
    When I press "Cancel"
    And I click on "Learner1 JA2" "link"
    Then I should see "Manager One (manager1@example.com) - Manager1 JA2"
    When I press "Cancel"
    And I click on "Learner1 JA3" "link"
    Then I should not see "Manager One (manager1@example.com) - Manager1 JA2"

    # Change an irrelevant setting in the user element config and save.
    # This is checking for bug TL-12312 where the link to job assigment setting is updated unintentionally.
    When I navigate to "User" node in "Site administration > HR Import > Elements"
    Then I should not see "Link job assignments"
    When I set the following fields to these values:
      | Force password change for new users | Yes |
    And I press "Save changes"
    Then I should not see "Link job assignments"

    # Now check that the manager job assignment id number setting is still in the source config.
    # This should be the case if the Link job assignments setting is still what it was when we set it.
    When I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    Then I should see "Manager's job assignment"
    And the following fields match these values:
      | Job assignment ID number  | 1 |
      | Job assignment full name  | 1 |
      | Manager                   | 1 |
      | Manager's job assignment  | 1 |

    # Now run HR Import again just to make sure the correct setting is still applied there.
    When I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/users_ja_with_managerjaid_only_2.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    Then I should see "Running HR Import cron...Done!"

    # Manager Two should have been created and added to the learner's 3rd job assignment.
    When I navigate to "HR Import Log" node in "Site administration > HR Import"
    Then I should see "updated user learner1"
    And I should see "updated user manager1"
    And I should see "created user manager2"
    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "Learner One" "link"
    And I click on "Learner1 JA1" "link"
    Then I should not see "Manager One (manager1@example.com) - Manager1 JA2"
    And I should not see "Manager Two (manager2@example.com) - Manager2 JA2"
    When I press "Cancel"
    And I click on "Learner1 JA2" "link"
    Then I should see "Manager One (manager1@example.com) - Manager1 JA2"
    When I press "Cancel"
    And I click on "Learner1 JA3" "link"
    Then I should see "Manager Two (manager2@example.com) - Manager2 JA2"
