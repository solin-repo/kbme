@totara @totara_appraisal
Feature: Add appraisal messages
  In order to ensure the appraisal messages works as expected
  As an admin
  I need to be able to add appraisal messages

  @javascript
  Scenario: Show and Hide Different Message Titles and Bodies
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Manage appraisals" node in "Site administration > Appraisals"
    And I press "Create appraisal"
    And I set the following fields to these values:
      | Name | Behat Test Appraisal                   |
      | Description | This is a behat test appraisal  |
    And I press "Create appraisal"
    And I click on "Messages" "link" in the ".tabrow0" "css_element"

    When I press "Create Message"
    Then the "messagetitle[0]" "field" should be enabled
    And the "messagebody[0]" "field" should be enabled
    And "messagetitle[1]" "field" should not be visible
    And "messagebody[1]" "field" should not be visible
    And "messagetitle[2]" "field" should not be visible
    And "messagebody[2]" "field" should not be visible
    And "messagetitle[4]" "field" should not be visible
    And "messagebody[4]" "field" should not be visible
    And "messagetitle[8]" "field" should not be visible
    And "messagebody[8]" "field" should not be visible

    When I set the field "messagetoall" to "Send different message for each role"
    Then "messagetitle[0]" "field" should not be visible
    And "messagebody[0]" "field" should not be visible
    And "messagetitle[1]" "field" should not be visible
    And "messagebody[1]" "field" should not be visible
    And "messagetitle[2]" "field" should not be visible
    And "messagebody[2]" "field" should not be visible
    And "messagetitle[4]" "field" should not be visible
    And "messagebody[4]" "field" should not be visible
    And "messagetitle[8]" "field" should not be visible
    And "messagebody[8]" "field" should not be visible

    When I click on "Learner" "checkbox"
    Then the "messagetitle[1]" "field" should be enabled
    And the "messagebody[1]" "field" should be enabled

    When I click on "Manager" "checkbox"
    Then the "messagetitle[2]" "field" should be enabled
    And the "messagebody[2]" "field" should be enabled

    When I click on "Manager's Manager" "checkbox"
    Then the "messagetitle[4]" "field" should be enabled
    And the "messagebody[4]" "field" should be enabled

    When I click on "Appraiser" "checkbox"
    Then the "messagetitle[8]" "field" should be enabled
    And the "messagebody[8]" "field" should be enabled
