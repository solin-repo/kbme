@totara @totara_completioneditor @core_grades @mod @javascript
Feature: Activity completed via RPL
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | student  | Stu       | Dent     | student@example.com |
      | teacher  | Tea       | Cher     | teacher@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | student | C1     | student        |
      | teacher | C1     | editingteacher |

  @mod_assign @_file_upload
  Scenario: Ensure assignments completed via RPL are excluded from cascade update
    # Assignment  Criteria  RPL     Cascade
    # ----------  --------  ------  -------
    # 1           Yes       Yes/CC  No
    # 2           Yes       Yes/CE  No
    # 3           Yes       No      Yes
    # 4           No        -       Yes
    # 5           Yes       No      -

    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name     | Ass 1                                             |
      | Completion tracking | Show activity as complete when conditions are met |
      | completionusegrade  | 1                                                 |
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name     | Ass 2                                             |
      | Completion tracking | Show activity as complete when conditions are met |
      | completionusegrade  | 1                                                 |
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name     | Ass 3                                             |
      | Completion tracking | Show activity as complete when conditions are met |
      | completionusegrade  | 1                                                 |
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name     | Ass 4                                             |
      | Completion tracking | Show activity as complete when conditions are met |
      | completionusegrade  | 1                                                 |
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name     | Ass 5                                             |
      | Completion tracking | Show activity as complete when conditions are met |
      | completionusegrade  | 1                                                 |

    When I navigate to "Course completion" node in "Course administration"
    And I expand all fieldsets
    And I set the field "Completion requirements" to "Course is complete when ALL conditions are met"
    And I set the field "Ass 1" to "1"
    And I set the field "Ass 2" to "1"
    And I set the field "Ass 3" to "1"
    And I set the field "Ass 5" to "1"
    And I press "Save changes"
    Then I should see "Course completion criteria changes have been saved"
    And I log out

    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    And I navigate to "Course completion" node in "Course administration > Reports"

    # Complete via RPL with course completion report
    And I click on "//tr[contains(.,'Stu Dent')]/td[2]//a[@class='rpledit' and contains(.,'Not completed')]" "xpath_element"
    And I set the field "rplinput" to "lorem"
    And I press key "13" in the field "rplinput"

    And I log out
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Course completion" node in "Course administration > Reports"

    # Complete via RPL with completion editor
    And I click on "Edit" "link" in the "Stu Dent" "table_row"
    And I click on "Edit" "link" in the "Ass 2" "table_row"
    And I set the field "Activity status" to "Completed"
    And I wait "2" seconds
    And I set the field "RPL" to "ipsum"
    And I set the field "Activity time completed" to "2019-08-01 00:00"
    And I click on "Done" "button" in the ".ui-datepicker" "css_element"
    And I wait "1" seconds
    And I click on "Save changes" "button"
    And I click on "Yes" "button"

    # Complete with completion editor
    And I click on "Edit" "link" in the "Ass 3" "table_row"
    And I set the field "Activity status" to "Completed"
    And I wait "2" seconds
    And I set the field "Activity time completed" to "2019-08-02 00:00"
    And I click on "Done" "button" in the ".ui-datepicker" "css_element"
    And I wait "1" seconds
    And I click on "Save changes" "button"
    And I click on "Yes" "button"

    # Complete with completion editor
    And I click on "Edit" "link" in the "Ass 4" "table_row"
    And I set the field "Activity status" to "Completed"
    And I wait "2" seconds
    And I set the field "Activity time completed" to "2019-08-03 00:00"
    And I click on "Done" "button" in the ".ui-datepicker" "css_element"
    And I wait "1" seconds
    And I click on "Save changes" "button"
    And I click on "Yes" "button"

    When I navigate to "Course completion" node in "Course administration > Reports"
    Then I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[2]/span[@class='sr-only'][1]" "xpath_element"
    And I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[2]/span[@class='sr-only'][2]" "xpath_element"
    And I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[3]/span[@class='sr-only'][1]" "xpath_element"
    And I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[3]/span[@class='sr-only'][2]" "xpath_element"
    And I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[4]/span[@class='sr-only'][1]" "xpath_element"
    But I should see "Not completed" in the "//tr[contains(.,'Stu Dent')]/td[5]/span[@class='sr-only'][1]" "xpath_element"
    But I should see "Not completed" in the "//tr[contains(.,'Stu Dent')]/td[5]//a[@class='rpledit']" "xpath_element"
    When I click on "//tr[contains(.,'Stu Dent')]/td[2]//a[@title='Show RPL']" "xpath_element"
    Then I should see "lorem" in the "//tr[contains(.,'Stu Dent')]/td[2]" "xpath_element"
    When I click on "//tr[contains(.,'Stu Dent')]/td[3]//a[@title='Show RPL']" "xpath_element"
    Then I should see "ipsum" in the "//tr[contains(.,'Stu Dent')]/td[3]" "xpath_element"
    But "//tr[contains(.,'Stu Dent')]/td[4]//a[@title='Show RPL']" "xpath_element" should not exist
    But "//tr[contains(.,'Stu Dent')]/td[5]//a[@title='Show RPL']" "xpath_element" should not exist
    And I log out

    And I log in as "student"
    And I am on "Course 1" course homepage
    And I should see "Completed: Ass 1"
    And I should see "Completed: Ass 2"
    And I should see "Completed: Ass 3"
    And I should see "Completed: Ass 4"
    But I should see "Not completed: Ass 5"
    And I follow "Ass 5"
    When I press "Add submission"
    And I upload "lib/tests/fixtures/empty.txt" file to "File submissions" filemanager
    And I press "Save changes"
    Then I should see "Submitted"
    And I am on "Course 1" course homepage
    And I should see "Completed: Ass 1"
    And I should see "Completed: Ass 2"
    But I should see "Not completed: Ass 3"
    But I should see "Not completed: Ass 4"
    But I should see "Not completed: Ass 5"
    And I log out

    And I log in as "teacher"
    And I am on "Course 1" course homepage
    And I navigate to "Course completion" node in "Course administration > Reports"
    Then I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[2]/span[@class='sr-only'][1]" "xpath_element"
    And I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[2]/span[@class='sr-only'][2]" "xpath_element"
    And I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[3]/span[@class='sr-only'][1]" "xpath_element"
    And I should see "Completed" in the "//tr[contains(.,'Stu Dent')]/td[3]/span[@class='sr-only'][2]" "xpath_element"
    But I should see "Not completed" in the "//tr[contains(.,'Stu Dent')]/td[4]/span[@class='sr-only'][1]" "xpath_element"
    But I should see "Not completed" in the "//tr[contains(.,'Stu Dent')]/td[5]/span[@class='sr-only'][1]" "xpath_element"
    But I should see "Not completed" in the "//tr[contains(.,'Stu Dent')]/td[5]//a[@class='rpledit']" "xpath_element"
    When I click on "//tr[contains(.,'Stu Dent')]/td[2]//a[@title='Show RPL']" "xpath_element"
    Then I should see "lorem" in the "//tr[contains(.,'Stu Dent')]/td[2]" "xpath_element"
    When I click on "//tr[contains(.,'Stu Dent')]/td[3]//a[@title='Show RPL']" "xpath_element"
    Then I should see "ipsum" in the "//tr[contains(.,'Stu Dent')]/td[3]" "xpath_element"
    But "//tr[contains(.,'Stu Dent')]/td[4]//a[@title='Show RPL']" "xpath_element" should not exist
    But "//tr[contains(.,'Stu Dent')]/td[5]//a[@title='Show RPL']" "xpath_element" should not exist
    And I log out
