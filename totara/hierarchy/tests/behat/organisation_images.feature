@totara @totara_hierarchy @totara_hierarchy_organisation @_file_upload
Feature: Test use of images in organisations and organisation custom fields
  I should be able to use and view images in organisation descriptions
  and custom text area fields

  @javascript
  Scenario: Images in organisation and custom organisation fields and descriptions
    Given I am on a totara site
    And the following "organisation" frameworks exist:
      | fullname           | idnumber |
      | Test Org Framework | tstorgfw   |
    And I log in as "admin"

    # Add images to the private files block to use later
    And I follow "My learning"
    And I follow "Manage private files..."
    And I upload "totara/hierarchy/tests/behat/fixtures/learninglogo1.jpg" file to "Files" filemanager
    And I upload "totara/hierarchy/tests/behat/fixtures/learninglogo2.jpg" file to "Files" filemanager
    And I upload "totara/hierarchy/tests/behat/fixtures/learninglogo3.jpg" file to "Files" filemanager
    Then I should see "learninglogo1.jpg"
    And I should see "learninglogo2.jpg"
    And I should see "learninglogo3.jpg"

    # Create text area custom field for Organisation type
    When I navigate to "Manage types" node in "Site administration > Hierarchies > Organisations"
    And I press "Add a new type"
    And I set the following fields to these values:
    | Type full name | Organisation type 1 |
    # Add the image to the description field.
    And I click on "//button[@class='atto_image_button']" "xpath_element"
    And I click on "Browse repositories..." "button"
    And I click on "learninglogo1.jpg" "link"
    And I click on "Select this file" "button"
    And I set the field "Describe this image for someone who cannot see it" to "logo1 on organisation type description"
    And I click on "Save image" "button"
    And I press "Save changes"
    Then "Organisation type 1" "link" should exist
    And I follow "Organisation type 1"
    And I set the field "Create a new custom field" to "Text area"
    And I set the following fields to these values:
      | Full name                   | Custom text area 1  |
      | Short name (must be unique) | CTA1                |
    And I press "Save changes"
    Then I should see "Custom text area 1"

    # Create organisation using the organisation type
    When I navigate to "Manage organisations" node in "Site administration > Hierarchies > Organisations"
    And I follow "Test Org Framework"
    And I press "Add new organisation"
    And I set the following fields to these values:
      | Name | My organisation 1    |
      | Type | Organisation type 1  |

    # Image in organisation description
    And I click on "//button[@class='atto_image_button']" "xpath_element"
    And I click on "Browse repositories..." "button"
    And I click on "learninglogo2.jpg" "link"
    And I click on "Select this file" "button"
    And I set the field "Describe this image for someone who cannot see it" to "logo2 in organisation description"
    And I click on "Save image" "button"
    And I press "Save changes"
    Then I should see the "logo2 in organisation description" image in the "//dd[preceding-sibling::dt[1][. = 'Description']]" "xpath_element"

    # Image in the custom field
    When I click on "Edit" "link"
    And I click on "//button[@class='atto_image_button']" "xpath_element" in the "//div[@id='fitem_id_customfield_CTA1_editor']" "xpath_element"
    And I click on "Browse repositories..." "button"
    And I click on "learninglogo3.jpg" "link"
    And I click on "Select this file" "button"
    And I set the field "Describe this image for someone who cannot see it" to "logo3 on customfield text area"
    And I click on "Save image" "button"
    And I press "Save changes"
    Then I should see the "logo2 in organisation description" image in the "//dd[preceding-sibling::dt[1][. = 'Description']]" "xpath_element"
    And I should see the "logo3 on customfield text area" image in the "//dd[preceding-sibling::dt[1][. = 'Custom text area 1']]" "xpath_element"

    When I press "Return to organisation framework"
    Then I should see the "logo2 in organisation description" image in the "My organisation 1" "table_row"
    And I should see the "logo3 on customfield text area" image in the "Custom text area 1" "table_row"

    # Also check reports
    When I am on site homepage
    And I navigate to "Manage reports" node in "Site administration > Reports > Report builder"
    And I set the following fields to these values:
      | Name   | Test Organisations |
      | Source | Organisations      |
    And I press "Create report"
    Then I should see "Edit Report 'Test Organisations'"

    When I switch to "Columns" tab
    Then "//select[@id='id_newcolumns']/optgroup[@label='Organisation custom fields']/option[.='Custom text area 1']" "xpath_element" should exist
    And the "newcolumns" select box should contain "Custom text area 1"

    When I select "Custom text area 1" from the "newcolumns" singleselect
    And I press "Add"
    And I press "Save changes"
    And I follow "View This Report"
    Then I should see the "logo3 on customfield text area" image in the "My organisation 1" "table_row"

    When I follow "My organisation 1"
    Then I should see the "logo2 in organisation description" image in the "//dd[preceding-sibling::dt[1][. = 'Description']]" "xpath_element"
    And I should see the "logo3 on customfield text area" image in the "//dd[preceding-sibling::dt[1][. = 'Custom text area 1']]" "xpath_element"
