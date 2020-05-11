@enrol @javascript @totara @enrol_totara_facetoface @mod_facetoface
Feature: Users can enrol on courses when two steps approval is on
  In order to check enrolments work for seminars with two steps approval
  As a user
  I need to request approval from the manager and an admin

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username    | firstname | lastname | email              |
      | sysapprover | Terry     | Ter      | terry@example.com  |
      | actapprover | Larry     | Lar      | larry@example.com  |
      | manager     | Cassy     | Cas      | cassy@example.com  |
      | jimmy       | Jimmy     | Jim      | jimmy@example.com  |
      | timmy       | Timmy     | Tim      | timmy@example.com  |
      | sammy       | Sammy     | Sam      | sammy@example.com  |
      | mickymau    | Micky     | Mau      | micky@example.com  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course1  | CCC       | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | manager | CCC    | teacher        |
    And the following job assignments exist:
      | user  | manager |
      | jimmy | manager |
      | timmy | manager |
      | sammy | manager |
    And I log in as "admin"
    And I navigate to "Manage enrol plugins" node in "Site administration > Plugins > Enrolments"
    And I click on "Enable" "link" in the "Seminar direct enrolment" "table_row"
    And I navigate to "Global settings" node in "Site administration > Seminars"
    And I click on "s__facetoface_approvaloptions[approval_none]" "checkbox"
    And I click on "s__facetoface_approvaloptions[approval_self]" "checkbox"
    And I click on "s__facetoface_approvaloptions[approval_manager]" "checkbox"
    And I click on "s__facetoface_approvaloptions[approval_admin]" "checkbox"
    And I press "Save changes"
    And I am on "Course1" course homepage
    And I add "Seminar direct enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
    And I am on "Course1" course homepage with editing mode on
    And I add a "Seminar" to section "1" and I fill the form with:
      | Name              | Course1 Seminar1              |
      | Description       | Course1 Seminar1 description  |
    And I follow "View all events"
    And I navigate to "Edit settings" node in "Seminar administration"
    And I expand all fieldsets
    And I click on "#id_approvaloptions_approval_admin" "css_element"
    And I click on "addapprovaladmins" "button"
    And I click on "Larry Lar" "link" in the "Select activity level approvers" "totaradialogue"
    And I click on "Search" "link" in the "Select activity level approvers" "totaradialogue"
    And I search for "Mick" in the "Select activity level approvers" totara dialogue
    And I click on "Micky Mau" from the search results in the "Select activity level approvers" totara dialogue
    And I click on "Save" "button" in the "Select activity level approvers" "totaradialogue"
    And I press "Save and display"
    And I follow "Add a new event"
    And I set the following fields to these values:
      | capacity           | 10   |
    And I press "Save changes"
    And I log out

  Scenario: Student gets approved and enrolled through both steps of the 2 stage approval
    Given I log in as "jimmy"
    And I am on "Course1" course homepage
    And I should see "Request approval"
    And I follow "Request approval"
    And I should see "Manager and Administrative approval"
    And I press "Request approval"
    And I run all adhoc tasks
    And I log out

    And I log in as "manager"
    And I click on "Dashboard" in the totara menu
    Then I should see "Seminar booking admin request"
    And I click on "View all tasks" "link"
    And I should see "This is to advise that Jimmy Jim has requested to be booked into the following course" in the "td.message_values_statement" "css_element"
    And I click on "Attendees" "link" in the "Follow the link" "table_row"
    Then I should see "Jimmy Jim" in the ".lastrow" "css_element"

    When I click on "requests[6]" "radio" in the ".lastrow .lastcol" "css_element"
    And I click on "Update requests" "button"
    Then I should not see "Jimmy Jim"
    And I switch to "Attendees" tab
    Then I should not see "Jimmy Jim"

    When I log out
    And I log in as "actapprover"
    And I click on "Dashboard" in the totara menu
    Then I should see "Seminar booking admin request"
    And I click on "View all tasks" "link"
    And I should see "This is to advise that Jimmy Jim has requested to be booked into the following course" in the "td.message_values_statement" "css_element"
    And I click on "Attendees" "link" in the "Follow the link" "table_row"
    Then I should see "Jimmy Jim"

    When I click on "requests[6]" "radio" in the ".lastrow .lastcol" "css_element"
    And I click on "Update requests" "button"
    Then I should not see "Jimmy Jim"
    And I run all adhoc tasks

    When I log out
    And I log in as "jimmy"
    And I click on "Dashboard" in the totara menu
    Then I should see "Seminar booking confirmation"

    When I am on "Course1" course homepage
    And I follow "View all events"
    Then I should see "Booked" in the "More info" "table_row"
    And I log out

    When I log in as "admin"
    And I am on "Course1" course homepage
    And I navigate to "Enrolled users" node in "Course administration > Users"
    And I should see "Jimmy Jim"
    And I should see "Test student enrolment" in the "Jimmy Jim" "table_row"