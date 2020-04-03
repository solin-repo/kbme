@totara_hierarchy @totara
Feature: The generators create the expected position assignments
  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username   | firstname   | lastname   | email                |
      | user1      | User        | 1          | user1@example.com    |
      | manager1   | Manager     | 1          | manager1@example.com |
      | manager2   | Manager     | 2          | manager2@example.com |
    And the following "position" frameworks exist:
      | fullname                | idnumber |
      | Test position framework | FW001    |
    And the following "position" hierarchy exists:
      | framework | fullname          | idnumber |
      | FW001     | First position    | POS001   |
    And the following "organisation" frameworks exist:
      | fullname                    | idnumber |
      | Test organisation framework | FW002    |
    And the following "organisation" hierarchy exists:
      | framework | fullname            | idnumber |
      | FW002     | First organisation  | ORG001   |

  Scenario: The primary position can be assigned to a user via a generator
    Given the following position assignments exist:
      | user  | manager  | position | organisation |
      | user1 | manager1 | POS001   | ORG001       |
    When I log in as "admin"
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "User 1"
    And I follow "Primary position"
    Then I should see "First position" in the "#positiontitle" "css_element"
    And I should see "First organisation" in the "#organisationtitle" "css_element"
    And I should see "Manager 1" in the "#managertitle" "css_element"

  Scenario: The secondary position can be assigned to a user via a generator
    Given the following position assignments exist:
      | user  | manager  | position | organisation | type      |
      | user1 | manager2 | POS001   | ORG001       | secondary |
    When I log in as "admin"
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I follow "User 1"
    And I follow "Secondary position"
    Then I should see "First position" in the "#positiontitle" "css_element"
    And I should see "First organisation" in the "#organisationtitle" "css_element"
    And I should see "Manager 2" in the "#managertitle" "css_element"
