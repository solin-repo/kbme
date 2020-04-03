@mod @mod_facetoface @totara @javascript
Feature: Facetoface session custom fields
  After facetoface sessions have been created
  As an admin
  I need to be able to test custom fields validation

  Background:
    Given I am on a totara site
    And the following "courses" exist:
      | fullname     | shortname | category |
      | Course 13901 | C13901    | 0        |
    And the following "activities" exist:
      | activity   | name                | course | idnumber |
      | facetoface | Facetoface TL-13901 | C13901 | F13901   |

  Scenario: Test uniques for text custom field
    Given I log in as "admin"
    And I navigate to "Custom Fields" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I set the field "datatype" to "Text input"
    And I set the following fields to these values:
      | fullname           | Unique identifier |
      | shortname          | UID               |
      | forceunique        | 1                 |
    And I press "Save changes"

    And I click on "Find Learning" in the totara menu
    And I follow "Course 13901"
    And I follow "View all sessions"

    And I follow "Add a new session"
    And I click on "#id_customfields" "css_element"
    And I set the field "customfield_UID" to "20172017"
    When I press "Save changes"
    Then I should see "Unique identifier"
    And I should see "20172017"

    And I follow "Add a new session"
    And I click on "#id_customfields" "css_element"
    And I set the field "customfield_UID" to "20172017"
    When I press "Save changes"
    And I click on "#id_customfields" "css_element"
    Then I should see "This value has already been used."

    When I set the field "customfield_UID" to "20172018"
    And I press "Save changes"
    Then I should see "20172018"

