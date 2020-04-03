@mod @mod_facetoface @totara @javascript @totara_certification
Feature: Take attendance for a Face to face with multiple sessions
  Ensure that the correct session is being used to mark activity completion when a face to face has multiple sessions
  To test whether the old or new session date is the current completion date, we run cron, and the older date will expire,
  whereas the newer date will merely open the recert window.

  Background:
    Given I am on a totara site

    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | first1    | last1    | user1@example.com |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | course1  | course1   | 1                |
    And the following "course enrolments" exist:
      | user  | course  | role           |
      | user1 | course1 | student        |

    # Create the face to face.
    And I log in as "admin"
    And I follow "course1"
    And I turn editing mode on
    And I add the "Course completion status" block
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name                                    | facetoface1                                       |
      | Description                             | Test facetoface description                       |
      | Allow multiple sessions signup per user | 1                                                 |
      | Completion tracking                     | Show activity as complete when conditions are met |
      | completionstatusrequired[100]           | 1                                                 |

    # Set course completion to f2f completion.
    And I navigate to "Course completion" node in "Course administration"
    And I set the following fields to these values:
      | Face-to-face - facetoface1 | 1 |
    And I press "Save changes"

    # Add sessions to f2f.
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][day]     | -10              |
      | timestart[0][month]   | 0                |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | -25              |
      | timefinish[0][day]    | -10              |
      | timefinish[0][month]  | 0                |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | 0                |
      | timefinish[0][minute] | +5               |
      | customroom            | 1                |
      | croomname             | later session    |
    And I press "Save changes"
    And I follow "Add a new session"
    And I fill facetoface session with relative date in form data:
      | datetimeknown         | Yes              |
      | sessiontimezone[0]    | Pacific/Auckland |
      | timestart[0][day]     | -40              |
      | timestart[0][month]   | 0                |
      | timestart[0][year]    | 0                |
      | timestart[0][hour]    | 0                |
      | timestart[0][minute]  | -25              |
      | timefinish[0][day]    | -40              |
      | timefinish[0][month]  | 0                |
      | timefinish[0][year]   | 0                |
      | timefinish[0][hour]   | 0                |
      | timefinish[0][minute] | +5               |
      | customroom            | 1                |
      | croomname             | earlier session  |
    And I press "Save changes"

    # Create the certification and add the course.
    And I click on "Certifications" in the totara menu
    And I press "Create Certification"
    And I press "Save changes"
    And I click on "Certification" "link" in the ".tabtree" "css_element"
    And I set the following fields to these values:
      | recertifydatetype | 1   |
      | activenum         | 15  |
      | activeperiod      | day |
      | windownum         | 10  |
      | windowperiod      | day |
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    And I click on "Content" "link" in the ".tabtree" "css_element"
    And I click on "addcontent_ce" "button" in the "#programcontent_ce" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "course1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait until the page is ready
    And I click on "addcontent_rc" "button" in the "#programcontent_rc" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "course1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait until the page is ready
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"

    # Assign the user to the cert.
    And I click on "Assignments" "link" in the ".tabtree" "css_element"
    And I click on "Individuals" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add individuals to program" "button"
    And I click on "first1 last1 (user1@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I wait until the page is ready
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    Then I should see "1 learner(s) assigned: 1 active, 0 exception(s)."

  Scenario: Complete older session, archive cert, complete newer session, see newer completion date on cert
    # Complete older session.
    Then I click on "Home" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "earlier session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I click on "Take attendance" "link" in the ".tabtree" "css_element"
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"

    # Run cron to open window and expire the older completion.
    And I run the "\totara_certification\task\update_certification_task" task

    # Verify user is expired.
    And I log out
    And I log in as "user1"
    And I click on "Record of Learning" in the totara menu
    And I click on "Certifications" "link" in the ".tabtree" "css_element"
    # Due date (when it expired, -40 + 15).
    And I should see date "-25 day" formatted "%d %B %Y"
    And I should see "Overdue!" in the "Certification program fullname 101" "table_row"
    And I should see "Expired" in the "Certification program fullname 101" "table_row"

    # Complete newer session.
    And I log out
    And I log in as "admin"
    And I click on "Home" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "later session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I click on "Take attendance" "link" in the ".tabtree" "css_element"
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"

    # Run cron to open window for the newer completion.
    And I run the "\totara_certification\task\update_certification_task" task

    # Verify user has window open.
    And I log out
    And I log in as "user1"
    And I click on "Record of Learning" in the totara menu
    And I click on "Certifications" "link" in the ".tabtree" "css_element"
    # Completion, window open and expiry.
    And I should see date "-10 day" formatted "%d %b %Y"
    And I should see date "-5 day" formatted "%d %b %Y"
    And I should see date "5 day" formatted "%d %b %Y"
    And I should see "Certified" in the "Certification program fullname 101" "table_row"
    And I should see "Due for renewal" in the "Certification program fullname 101" "table_row"
    And I should see "Open" in the "Certification program fullname 101" "table_row"
    And I should see "5 days remaining" in the "Certification program fullname 101" "table_row"

  Scenario: Complete newer session, archive cert, complete older session, still see newer completion date on cert
    # Complete newer session.
    Then I click on "Home" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "later session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I click on "Take attendance" "link" in the ".tabtree" "css_element"
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"

    # Run cron to open window for the newer completion.
    And I run the "\totara_certification\task\update_certification_task" task

    # Verify user has window open.
    And I log out
    And I log in as "user1"
    And I click on "Record of Learning" in the totara menu
    And I click on "Certifications" "link" in the ".tabtree" "css_element"
    # Completion, window open and expiry.
    And I should see date "-10 day" formatted "%d %b %Y"
    And I should see date "-5 day" formatted "%d %b %Y"
    And I should see date "5 day" formatted "%d %b %Y"
    And I should see "Certified" in the "Certification program fullname 101" "table_row"
    And I should see "Due for renewal" in the "Certification program fullname 101" "table_row"
    And I should see "Open" in the "Certification program fullname 101" "table_row"
    And I should see "5 days remaining" in the "Certification program fullname 101" "table_row"

    # Complete older session.
    And I log out
    And I log in as "admin"
    And I click on "Home" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "earlier session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I click on "Take attendance" "link" in the ".tabtree" "css_element"
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"

    # Run cron which should do nothing.
    And I run the "\totara_certification\task\update_certification_task" task

    # Verify user has window open (so older session completion didn't trigger course and cert completion).
    And I log out
    And I log in as "user1"
    And I click on "Record of Learning" in the totara menu
    And I click on "Certifications" "link" in the ".tabtree" "css_element"
    # Completion, window open and expiry.
    And I should see date "-10 day" formatted "%d %b %Y"
    And I should see date "-5 day" formatted "%d %b %Y"
    And I should see date "5 day" formatted "%d %b %Y"
    And I should see "Due for renewal" in the "Certification program fullname 101" "table_row"
    And I should see "Open" in the "Certification program fullname 101" "table_row"
    And I should see "5 days remaining" in the "Certification program fullname 101" "table_row"

  Scenario: Complete newer, complete older, see newer completion date, reset activity completion, see newer completion date
    # Complete newer session.
    Then I click on "Home" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "later session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I click on "Take attendance" "link" in the ".tabtree" "css_element"
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"

    # Verify course completed with newer session date.
    And I log out
    And I log in as "user1"
    And I follow "course1"
    And I click on "More details" "link"
    And I should see date "-10 day" formatted "%d %B %Y"

    # Complete older session.
    And I log out
    And I log in as "admin"
    And I click on "Home" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "earlier session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I click on "Take attendance" "link" in the ".tabtree" "css_element"
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"

    # Verify course still completed with newer session date.
    And I log out
    And I log in as "user1"
    And I follow "course1"
    And I click on "More details" "link"
    And I should see date "-10 day" formatted "%d %B %Y"

    # Reset activity completion.
    And I log out
    And I log in as "admin"
    And I follow "course1"
    And I click on "facetoface1" "link"
    And I navigate to "Edit settings" node in "Facetoface administration"
    And I click on "Activity completion" "link"
    And I press "Unlock completion and delete completion data"
    And I press "Save and return to course"

    # Verify course completed with newer session date.
    And I log out
    And I log in as "user1"
    And I follow "course1"
    And I click on "More details" "link"
    And I should see date "-10 day" formatted "%d %B %Y"

  Scenario: Complete older, complete newer, see older completion date, reset activity completion, see newer completion date
    # Complete older session.
    Then I click on "Home" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "earlier session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I click on "Take attendance" "link" in the ".tabtree" "css_element"
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"

    # Verify course completed with older session date.
    And I log out
    And I log in as "user1"
    And I follow "course1"
    And I click on "More details" "link"
    And I should see date "-40 day" formatted "%d %B %Y"

    # Complete newer session.
    And I log out
    And I log in as "admin"
    And I click on "Home" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "later session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I click on "Take attendance" "link" in the ".tabtree" "css_element"
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"

    # Verify course still completed with older session date.
    And I log out
    And I log in as "user1"
    And I follow "course1"
    And I click on "More details" "link"
    And I should see date "-40 day" formatted "%d %B %Y"

    # Reset activity completion.
    And I log out
    And I log in as "admin"
    And I follow "course1"
    And I click on "facetoface1" "link"
    And I navigate to "Edit settings" node in "Facetoface administration"
    And I click on "Activity completion" "link"
    And I press "Unlock completion and delete completion data"
    And I press "Save and return to course"

    # Verify course completed with newer session date.
    And I log out
    And I log in as "user1"
    And I follow "course1"
    And I click on "More details" "link"
    And I should see date "-10 day" formatted "%d %B %Y"

  Scenario: Take attendance with minimum permissions
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | taker    | taker     | taker    | taker@example.com |
    And the following "permission overrides" exist:
      | capability                     | permission | role       | contextlevel | reference |
      | mod/facetoface:addattendees    | Prohibit   | teacher    | Course       | course1   |
      | mod/facetoface:addinstance     | Prohibit   | teacher    | Course       | course1   |
      | mod/facetoface:editsessions    | Prohibit   | teacher    | Course       | course1   |
      | mod/facetoface:removeattendees | Prohibit   | teacher    | Course       | course1   |
      | mod/facetoface:takeattendance  | Allow      | teacher    | Course       | course1   |
    And the following "course enrolments" exist:
      | user  | course  | role    |
      | taker | course1 | teacher |
    And I click on "Find Learning" in the totara menu
    And I follow "course1"
    And I click on "View all sessions" "link"
    And I click on "Attendees" "link" in the "earlier session" "table_row"
    And I click on "Add/remove attendees" "option" in the "#menuf2f-actions" "css_element"
    And I click on "first1 last1, user1@example.com" "option"
    And I press "Add"
    And I wait until the page is ready
    And I press "Save"
    And I log out
    When I log in as "taker"
    And I click on "Find Learning" in the totara menu
    And I follow "course1"
    And I click on "facetoface1" "link"
    And I click on "Attendees" "link" in the "earlier session" "table_row"
    And I switch to "Take attendance" tab
    And I click on "Fully attended" "option" in the "first1 last1" "table_row"
    And I press "Save attendance"
    Then I should see "Successfully updated attendance"
