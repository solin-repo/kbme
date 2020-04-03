@totara @totara_core
Feature: Test temporary managers
    In order to restrict to temporary managers
    As a user
    I need to be able to assign a temporary manager

    Background:
        Given I am on a totara site
        And the following "users" exist:
            | username | firstname | lastname | email | role | context |
            | user1 | user | 1 | user1@example.com | learner | system |
            | user2 | user | 2 | user2@example.com | learner | system |
            | tempmanager | temp | manager | manager@example.com | staffmanager | system |
        And I log in as "admin"
        And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
        And I follow "user 2"
        And I click on "Primary position" "link"
        And I click on "Choose manager" "button"
        And I click on "temp manager" "link"
        And I click on "OK" "button" in the ".totara-dialog[aria-describedby=manager]" "css_element"
        And I click on "Update position" "button"

    @javascript
    Scenario: Temporary manager can be anyone
        And I set the following administration settings values:
            | tempmanagerrestrictselection | 0 |
        And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
        And I follow "user 1"
        And I click on "Primary position" "link"
        And I click on "Choose temporary manager" "button"
        And I should see "user 2"
        And I should see "temp manager"

    @javascript
    Scenario: Only assign temporary manager
        And I set the following administration settings values:
            | tempmanagerrestrictselection | 1 |
        And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
        And I follow "user 1"
        And I click on "Primary position" "link"
        And I click on "Choose temporary manager" "button"
        And I should not see "user 2"
        And I should see "temp manager"
