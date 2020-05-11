@totara @block @block_current_learning
Feature: Test Current Learning block

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname  | lastname  | email                |
      | learner1 | firstname1 | lastname1 | learner1@example.com |

  Scenario: Learner has Current Learning block on Dashboard by default
    Given I log in as "learner1"
    And I click on "Dashboard" in the totara menu
    Then I should see "You do not have any current learning. For previously completed learning see your Record of Learning"

  @javascript
  Scenario: Learner can view their program in the Current Learning block

    # Setup the program.
    Given the following "programs" exist in "totara_program" plugin:
      | fullname                | shortname |
      | Test Program 1          | program1  |
    And the following "program assignments" exist in "totara_program" plugin:
      | user      | program  |
      | learner1  | program1 |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | course1   | 1                |
      | Course 2 | course2   | 1                |
      | Course 3 | course3   | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | learner1 | course1| student        |
    And I log in as "admin"

    # Add an image to the private files block to use later in the program.
    And I click on "Dashboard" in the totara menu
    And I press "Customise this page"
    And I select "Private files" from the "Add a block" singleselect
    And I follow "Manage private files..."
    And I upload "mod/workshop/tests/fixtures/moodlelogo.png" file to "Files" filemanager
    And I click on "Save changes" "button"

    # Edit the program.
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Test Program 1" "link"
    And I click on "Edit program details" "button"

    # Add the image to the summary field.
    And I click on "Details" "link"
    And I set the field "Summary" to "<p>Image test</p>"
    And I select the text in the "id_summary_editor" Atto editor
    And I click on "Image" "button" in the "#fitem_id_summary_editor" "css_element"
    And I click on "Browse repositories..." "button"
    And I click on "moodlelogo.png" "link"
    And I click on "Select this file" "button"
    And I set the field "Describe this image for someone who cannot see it" to "It's a picture"
    And I click on "Save image" "button"
    And I press "Save changes"
    And I should see image with alt text "It's a picture"

    # Add the program content.
    And I click on "Content" "link"
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 3" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait "1" seconds
    And I press "Save changes"
    And I click on "Save all changes" "button"
    And I log out

    # As the learner check the block and program is displayed correctly.
    And I log in as "learner1"
    And I click on "Dashboard" in the totara menu
    And I press "Customise this page"
    When I click on "Actions" "link" in the "Current Learning" "block"
    And I follow "Delete Current Learning block"
    When I press "Yes"
    And I add the "Current Learning" block
    And I configure the "Current Learning" block
    And I expand all fieldsets
    And I set the following fields to these values:
      | Default region | Main |
      | Default weight | -10  |
    And I press "Save changes"
    Then I should see "Course 1" in the "Current Learning" "block"
    And I should see "Test Program 1" in the "Current Learning" "block"

  @javascript
  Scenario: Learner can remove and re-add Current Learning block on Dashboard
    Given the following "programs" exist in "totara_program" plugin:
      | fullname                | shortname |
      | Test Program 1          | program1  |
    And the following "program assignments" exist in "totara_program" plugin:
      | user      | program  |
      | learner1  | program1 |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | course1   | 1                |
      | Course 2 | course2   | 1                |
      | Course 3 | course3   | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | learner1 | course1| student        |
    And I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Test Program 1" "link"
    And I click on "Edit program details" "button"
    And I click on "Content" "link"
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 3" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait "1" seconds
    And I press "Save changes"
    And I click on "Save all changes" "button"
    And I log out

    And I log in as "learner1"
    And I click on "Dashboard" in the totara menu
    And I press "Customise this page"
    When I click on "Actions" "link" in the "Current Learning" "block"
    And I follow "Delete Current Learning block"
    When I press "Yes"
    And I add the "Current Learning" block
    And I configure the "Current Learning" block
    And I expand all fieldsets
    And I set the following fields to these values:
      | Default region | Main |
      | Default weight | -10  |
    And I press "Save changes"
    Then I should see "Course 1" in the "Current Learning" "block"
    And I should see "Test Program 1" in the "Current Learning" "block"

  @javascript
  Scenario: Learner expands accordian for a program within the Current Learning block
    Given the following "programs" exist in "totara_program" plugin:
      | fullname                | shortname |
      | Test Program 1          | program1  |
    And the following "program assignments" exist in "totara_program" plugin:
      | user     | program  |
      | learner1 | program1 |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | course1   | 1                |
      | Course 2 | course2   | 1                |
      | Course 3 | course3   | 1                |
      | Course 4 | course4   | 1                |
    And I add a courseset with courses "course1,course2,course3" to "program1":
      | Set name              | set1        |
      | Learner must complete | All courses |
      | Minimum time required | 1           |
    And I add a courseset with courses "course4" to "program1":
      | Set name              | set1          |
      | Learner must complete | Some courses  |
      | Minimum time required | 1             |
    And I log in as "learner1"
    And I click on "Dashboard" in the totara menu
    And I should not see "Course 3"
    And I toggle "Test Program 1" in the current learning block
    And I should see "Course 3"
    And I wait "1" seconds
    And I toggle "Test Program 1" in the current learning block
    And I should not see "Course 3"

  @javascript
  Scenario: Learner can change pages in the Current Learning block
   Given the following "courses" exist:
    | fullname  | shortname | category |
    | Course 1  | C1        | 0        |
    | Course 2  | C2        | 0        |
    | Course 3  | C3        | 0        |
    | Course 4  | C4        | 0        |
    | Course 5  | C5        | 0        |
    | Course 6  | C6        | 0        |
    | Course 7  | C7        | 0        |
    | Course 8  | C8        | 0        |
    | Course 9  | C9        | 0        |
    | Course 10 | C10       | 0        |
    | Course 11 | C11       | 0        |
    | Course 12 | C12       | 0        |
    | Course 13 | C13       | 0        |
    | Course 14 | C14       | 0        |
    | Course 15 | C15       | 0        |
  And the following "course enrolments" exist:
    | user     | course | role    |
    | learner1 | C1     | student |
    | learner1 | C2     | student |
    | learner1 | C3     | student |
    | learner1 | C4     | student |
    | learner1 | C5     | student |
    | learner1 | C6     | student |
    | learner1 | C7     | student |
    | learner1 | C8     | student |
    | learner1 | C9     | student |
    | learner1 | C10    | student |
    | learner1 | C11    | student |
    | learner1 | C12    | student |
    | learner1 | C13    | student |
    | learner1 | C14    | student |
    | learner1 | C15    | student |
  And I log in as "learner1"
  And I click on "Dashboard" in the totara menu
  And I should see "Course 10"
  And I click on ".block_current_learning .pagination [data-page=2]" "css_element"
  And I should see "Course 5"
  And I should not see "Course 10"
  And I click on ".block_current_learning .pagination [data-page=1]" "css_element"
  And I should see "Course 10"
  And I should not see "Course 5"
  And I click on ".block_current_learning .pagination [data-page=next]" "css_element"
  And I should see "Course 5"
  And I should not see "Course 10"
  And I click on ".block_current_learning .pagination [data-page=prev]" "css_element"
  And I should see "Course 10"
  And I should not see "Course 5"
