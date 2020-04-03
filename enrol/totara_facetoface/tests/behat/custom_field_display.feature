@enrol @totara @enrol_totara_facetoface @javascript
Feature: Direct Seminar enrolment plugin displays custom fields
  Before I sign up via the direct enrolment plugin
  As a learner
  I should see all details pertaining to a session in which I am interested.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | student1 | Student   | 1        | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name                    | course | idnumber |
      | facetoface | Direct Enrolled Seminar | C1     | 12300    |

    And I log in as "admin"
    And I navigate to "Custom Fields" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I set the field "datatype" to "Checkbox"
    And I set the following fields to these values:
      | fullname  | signupcheckbox |
      | shortname | signupcheckbox |
    And I press "Save changes"

    And I set the field "datatype" to "Date/time"
    And I set the following fields to these values:
      | fullname  | signupdatetime |
      | shortname | signupdatetime |
    And I press "Save changes"

    And I set the field "datatype" to "File"
    And I set the following fields to these values:
      | fullname  | signupfile |
      | shortname | signupfile |
    And I press "Save changes"

    And I set the field "datatype" to "Menu of choices"
    And I set the following fields to these values:
      | fullname    | signupmenu |
      | shortname   | signupmenu |
      | defaultdata | Ja         |
    And I set the field "Menu options (one per line)" to multiline
      """
      Ja
      Nein
      """
    And I press "Save changes"

    And I set the field "datatype" to "Multi-select"
    And I set the following fields to these values:
      | fullname                   | signupmulti |
      | shortname                  | signupmulti |
      | multiselectitem[0][option] | Aye   |
      | multiselectitem[1][option] | Nay   |
    And I press "Save changes"

    And I set the field "datatype" to "Text area"
    And I set the following fields to these values:
      | fullname           | signuptextarea |
      | shortname          | signuptextarea |
    And I press "Save changes"

    And I set the field "datatype" to "Text input"
    And I set the following fields to these values:
      | fullname           | signuptextinput |
      | shortname          | signuptextinput |
    And I press "Save changes"

    And I navigate to "Manage enrol plugins" node in "Site administration > Plugins > Enrolments"
    And I click on "Enable" "link" in the "Face-to-face direct enrolment" "table_row"

    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I add "Face-to-face direct enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |

    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    And I follow "Direct Enrolled Seminar"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown                       | Yes      |
      | timestart[0][day]                   | 1        |
      | timestart[0][month]                 | 1        |
      | timestart[0][year]                  | 2030     |
      | timestart[0][hour]                  | 9        |
      | timestart[0][minute]                | 00       |
      | timefinish[0][day]                  | 1        |
      | timefinish[0][month]                | 1        |
      | timefinish[0][year]                 | 2030     |
      | timefinish[0][hour]                 | 16       |
      | timefinish[0][minute]               | 00       |
      | customfield_signupcheckbox          | 1        |
      | customfield_signupdatetime[enabled] | 1        |
      | customfield_signupdatetime[day]     | 1        |
      | customfield_signupdatetime[month]   | December |
      | customfield_signupdatetime[year]    | 2030     |
      | customfield_signupmenu              | Nein     |
      | customfield_signupmulti[0]          | 1        |
      | customfield_signupmulti[1]          | 1        |
      | customfield_signuptextinput         | hi again |
    And I press "Save changes"
    And I log out

  Scenario: See if custom field values show up in sign up page
    When I log in as "student1"
    And I click on "Find Learning" in the totara menu
    And I follow "Course 1"
    Then I should see "signupcheckbox"
    And I should see "Yes"
    And I should see "signupdate"
    And I should see "1 Dec 2030"
    And I should see "signupfile"
    And I should see "No file selected"
    And I should see "signupmenu"
    And I should see "Nein"
    And I should see "signupmulti"
    And I should see "Aye, Nay"
    And I should see "signuptextarea"
    And I should see "signuptextinput"
    And I should see "hi again"

