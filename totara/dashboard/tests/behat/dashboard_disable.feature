@javascript @totara @totara_dashboard
  Feature: test enable/disable dashboardsfeature
    In order to test the correct behaviour related to the visibility settings for the dashboard feature
    As a admin
    I need to choose among the three different settings (show/hide/disabled) and check the GUI

    Background:
      Given I am on a totara site
      And the following "users" exist:
        | username | firstname | lastname | email                   |
        | student1 | Student   | One      | student.one@example.com |
      And the following "cohorts" exist:
        | name | idnumber |
        | Cohort 1 | CH1 |
      And I log in as "admin"
      And I navigate to "Front page settings" node in "Site administration > Front page"
      # Behat does not recognize field name in this case "Front page summary"
      And I set the following fields to these values:
        | summary | I'm a label on the frontpage |
      And I press "Save changes"

      # Totara 2.9 sets "My learning" as home page while upgrade from Totara 2.7 sets site front page.
      # For testing make site front page as a home page.
      And I set the following administration settings values:
        | defaulthomepage | Site |

      And the following "cohort members" exist:
        | user     | cohort |
        | student1 | CH1    |
      And I navigate to "Dashboards" node in "Site administration > Appearance"
      # Add a dashboard.
      And I press "Create dashboard"
      And I set the following fields to these values:
        | Name | My first dashboard |
        | Published | 1             |
      And I press "Assign new audiences"
      And I click on "Cohort 1" "link"
      And I press "OK"
      And I wait "1" seconds
      And I press "Create dashboard"
      # Add a second dashboard.
      And I press "Create dashboard"
      And I set the following fields to these values:
        | Name | My second dashboard |
        | Published | 1             |
      And I press "Assign new audiences"
      And I click on "Cohort 1" "link"
      And I press "OK"
      And I wait "1" seconds
      And I press "Create dashboard"
      # Add content to the first dashboard.
      And I click on "My first dashboard" "link"
      And I add the "HTML" block
      And I configure the "(new HTML block)" block
      And I set the field "Block title" to "First dashboard block header"
      And I set the field "Content" to "First dashboard block content"
      And I press "Save changes"
      # Add content to the second dashboard.
      And I navigate to "Dashboards" node in "Site administration > Appearance"
      And I click on "My second dashboard" "link"
      And I add the "HTML" block
      And I configure the "(new HTML block)" block
      And I set the field "Block title" to "Second dashboard block header"
      And I set the field "Content" to "Second dashboard block content"
      And I press "Save changes"
      And I set the following administration settings values:
        | defaulthomepage | Totara dashboard |

    Scenario: Enable Totara dashboard feature
      Given I set the following administration settings values:
        | enabletotaradashboard | Show |
      And I log out
      When I log in as "student1"
      Then I should see "My first dashboard" in the "#page-header" "css_element"
      And I should see "First dashboard block header"
      And I click on "My second dashboard" "link"
      And I should see "Second dashboard block header"
      And I click on "Customize dashboard" "button"
      And I add the "My Learning Nav" block
      And I click on "My learning" "link"
      And I should see "Course overview" in the "#region-main" "css_element"
      And I click on "Site home" "link"
      And I should see "I'm a label on the frontpage"

    Scenario: Disable Totara dashboard feature
      Given I set the following administration settings values:
        | enabletotaradashboard | Disable |
      # As admin I shouldn't see any links or any reference to Totara dashboard, not even the one in the left menu.
      And I click on "Home" in the totara menu
      And I should not see "Dashboard" in the "Navigation" "block"
      When I expand "Site administration" node
      And I expand "Appearance" node
      Then I should not see "Dashboards" in the "#settingsnav" "css_element"
      And I log out
      # As a student I shouldn't see any references to Totara dashboard.
      When I log in as "student1"
      Then I should not see "My first dashboard" in the "#page-header" "css_element"
      And I should not see "My second dashboard" in the "#page-header" "css_element"
      And I click on "Site home" "link"
      And I should see "I'm a label on the frontpage"

    Scenario: Check Dashboard submenu in the home
      Given I set the following administration settings values:
        | defaulthomepage | Site |

      # Dasboard shows in the Totara menu
      And I set the following administration settings values:
        | enabletotaradashboard | Show |
      And I log out

      When I log in as "student1"
      Then I should see "Dashboard" in the totara menu
      And I log out

      # Dasboard does not show in the Totara menu
      And I log in as "admin"
      And I set the following administration settings values:
        | enabletotaradashboard | Disable |
      And I log out

      When I log in as "student1"
      Then I should not see "Dashboard" in the totara menu
      And I log out
