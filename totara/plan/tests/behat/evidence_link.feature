@totara @totara_plan
Feature: Verify evidence link.

  @javascript
  Scenario: Test the evidence link with http and https address.

    Given I am on a totara site
    And I log in as "admin"
    And I click on "Record of Learning" in the totara menu
    And I press "Add evidence"
    And I set the following fields to these values:
      | Evidence name        | Website 1                  |
      | Evidence Link        | http://www.website1.com    |
    And I press "Add evidence"
    And I should see "Evidence created"
    And I click on "Edit" "link"
    And I press "Update evidence"
    Then I should see "Evidence Link : http://www.website1.com"

    And I press "Edit details"
    And I set the field "Evidence Link" to "https://www.website2.com"
    And I press "Update evidence"
    Then I should see "Evidence Link : https://www.website2.com"

    And I press "Edit details"
    And I set the field "Evidence Link" to "index.php"
    And I press "Update evidence"
    Then I should see "Evidence Link : http://index.php"
