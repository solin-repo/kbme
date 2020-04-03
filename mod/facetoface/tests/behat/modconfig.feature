@mod @totara @mod_facetoface @javascript
Feature: Configure face to face settings
  In order to use face to face
  As a configurator
  I need to access all settings

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username     | firstname    | lastname | email         |
      | configurator | Configurator | User     | c@example.com |

    And I log in as "admin"
    And I navigate to "Define roles" node in "Site administration > Users > Permissions"
    And I click on "Add a new role" "button"
    And I click on "Continue" "button"
    And I set the following fields to these values:
      | Short name            | configurator          |
      | Custom full name      | Activity configurator |
      | contextlevel10        | 1                     |
      | totara/core:modconfig | 1                     |
    And I click on "Create this role" "button"
    And the following "role assigns" exist:
      | user         | role         | contextlevel | reference |
      | configurator | configurator | System       |           |
    And I log out

  Scenario: Access all face to face activity settings with modconfig capability
    Given I log in as "configurator"

    When I navigate to "General Settings" node in "Site administration > Plugins > Activity modules > Face-to-face"
    Then I should see "facetoface_fromaddress"

    When I navigate to "Session Defaults" node in "Site administration > Plugins > Activity modules > Face-to-face"
    Then I should see "defaultdaystosession"

    When I navigate to "Rooms" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I click on "Add a room" "button"
    Then I should see "Room name"

    When I navigate to "Notification templates" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I click on "Add" "button"
    Then I should see "Manager copy prefix"

    When I navigate to "Site Notices" node in "Site administration > Plugins > Activity modules > Face-to-face"
    And I click on "Create a new site notice" "button"
    Then I should see "Notice text"
