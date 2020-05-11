@totara @totara_plan @totara_rol @totara_core_menu
Feature: Check Record of Learning feature visibility
  In order to control access to RoL
  As an admin
  I need to be able to enable and disable it

  Scenario: Verify Record of Learning appears in the Totara menu if enabled
    Given I am on a totara site
    And I log in as "admin"

    When I navigate to "Main menu" node in "Site administration > Navigation"
    Then I should see "Record of Learning" in the "#totaramenutable" "css_element"
    And I should see "Record of Learning" in the totara menu

  Scenario: Verify Record of Learning does not appear in the Totara menu if disabled
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Advanced features" node in "Site administration > System information"
    And I set the field "Enable Record of Learning" to "Disable"
    And I press "Save changes"

    When I navigate to "Main menu" node in "Site administration > Navigation"
    Then I should see "Record of Learning" in the "#totaramenutable" "css_element"
    And I should see "Feature disabled" in the "Record of Learning" "table_row"
    And I should not see "Record of Learning" in the totara menu

  Scenario: Verify Record of Learning can still be loaded if teams are disabled
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email          |
      | user     | test      | user     | test@test.test |
    When I log in as "admin"

    # '3' = 'Disable'
    And I set the following administration settings values:
      | Enable Team | 3 |

    And I navigate to "Browse list of users" node in "Site administration > Users"
    And I click on "test user" "link"
    And I click on "Record of Learning" "link" in the ".userprofile" "css_element"
    Then I should see "0 records shown"
