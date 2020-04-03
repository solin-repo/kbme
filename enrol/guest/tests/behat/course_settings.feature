@enrol @enrol_guest @totara @javascript
Feature: Guest access can be configure from course page
  In order to setu guest access
  As a admin
  I need to be able to st settins on course form

  Scenario: Configure guest access in course
    Given I am on a totara site
    And I log in as "admin"
    And I press "Add a new course"
    And I set the following fields to these values:
    | Course full name   | My course 1 |
    | Course short name  | MC1         |
    | Allow guest access | Yes         |
    | Password           | heslo       |
    And I press "Save and display"

    When I navigate to "Edit settings" node in "Course administration"
    And I expand all fieldsets
    Then the field "Allow guest access" matches value "Yes"
    And the field "Password" matches value "heslo"

    When I set the following fields to these values:
      | Course full name   | My course 1 |
      | Course short name  | MC1         |
      | Password           | veslo       |
    And I press "Save and display"
    And I navigate to "Edit settings" node in "Course administration"
    And I expand all fieldsets
    Then the field "Allow guest access" matches value "Yes"
    And the field "Password" matches value "veslo"

    When I set the following fields to these values:
      | Course full name   | My course 1 |
      | Course short name  | MC1         |
      | Allow guest access | No         |
    And I press "Save and display"
    And I navigate to "Edit settings" node in "Course administration"
    And I expand all fieldsets
    Then the field "Allow guest access" matches value "No"

  Scenario: Configure required password guest access in course
    Given I am on a totara site
    And I log in as "admin"
    And I set the following administration settings values:
    | Require guest access password | 1 |
    And I am on site homepage
    And I press "Add a new course"
    And I set the following fields to these values:
      | Course full name   | My course 1 |
      | Course short name  | MC1         |
      | Allow guest access | Yes         |
      | Password           | heslo       |
    And I press "Save and display"

    When I navigate to "Edit settings" node in "Course administration"
    And I expand all fieldsets
    Then the field "Allow guest access" matches value "Yes"
    And the field "Password" matches value "heslo"

    When I set the following fields to these values:
      | Allow guest access | Yes         |
      | Password           |             |
    And I press "Save and display"
    And I should see "Allow guest access"

    When I set the following fields to these values:
      | Password           | kreslo |
    And I press "Save and display"
    Then I should not see "Allow guest access"

  Scenario: Do not configure required password guest access in course
    Given I am on a totara site
    And I log in as "admin"
    And I set the following administration settings values:
      | Require guest access password | 0 |
    And I am on site homepage
    And I press "Add a new course"
    When I set the following fields to these values:
      | Course full name   | My course 1 |
      | Course short name  | MC1         |
      | Allow guest access | No         |
    And I press "Save and display"
    Then I should not see "Allow guest access"
