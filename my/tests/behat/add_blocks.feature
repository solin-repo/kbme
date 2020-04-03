@core @core_my
Feature: Add blocks to My learning page
  In order to add more functionality to My learning page
  As a user
  I need to add blocks to My learning page

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And I log in as "student1"
    And I follow "My learning"

  Scenario: Add blocks to page
    When I press "Customise this page"
    And I add the "Latest news" block
    And I add the "Latest badges" block
    Then I should see "Latest news"
    And I should see "Latest badges"
    And I should see "Calendar" in the "Calendar" "block"
    And I should see "Upcoming events" in the "Upcoming events" "block"

  Scenario: Reset page for all users on My learning page
    # User need to visit page, so copy will be created for them.
    Given I log out
    And I log in as "admin"
    And I navigate to "Default My Learning page" node in "Site administration > Appearance"
    And I press "Blocks editing on"
    And I add the "Latest news" block
    And I log out
    And I log in as "student1"
    And I follow "My learning"
    And I should not see "Latest news"
    # Reset
    And I log out
    And I log in as "admin"
    And I navigate to "Default My Learning page" node in "Site administration > Appearance"
    And I press "Reset My Learning page for all users"
    And I follow "Continue"
    And I log out
    #Check
    And I log in as "student1"
    And I follow "My learning"
    And I should see "Latest news"