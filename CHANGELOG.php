<?php
/*

Totara Learn Changelog

Release 2.9.50 (26th February 2020):
====================================


Important:

    TL-23764       Chrome 80: SameSite=None is now only set if you are using secure cookies and HTTPS

                   Prior to this change if you were not running your Totara site over HTTPS,
                   and upgraded to Chrome 80 then you not be able to log into your site.
                   This was because Chrome 80 was rejecting the cookie as it had the SameSite
                   attribute set to None and the Secure flag was not set (as you were not
                   running over HTTPS).

                   After upgrading SameSite will be left for Chrome to default a value for.
                   You will be able to log in, but may find that third party content on your
                   site does not work.
                   In order to ensure that your site performs correctly please upgrade your
                   site to use HTTPS and enable the Secure Cookies setting within Totara if it
                   is not already enabled.

Security issues:

    TL-24133       Ensured content was encoded before being used within aria-labels when viewing the users list


Release 2.9.49 (22nd January 2020):
===================================


API changes:

    TL-23511       The new minimum required Node.js version has changed to 12

                   It is recommended now to run at least Node.js 12 to run grunt builds.
                   Node.js 8 is almost out of support; we recommend to use the latest Node.js
                   12 to run grunt builds. However to avoid compatibility issues in stable
                   releases running Node 8 is still supported.


Release 2.9.48 (26th November 2019):
====================================


Improvements:

    TL-22122       Added on-screen notification to users trying to connect to the Mozilla Open Badges Backpack

                   Since Mozilla retired its Open Badges Backpack platform in August 2019,
                   users attempting a connection to the backpack from Totara experience a
                   connection time out.

                   This improvement notifies the user about the backpack's end-of-service and
                   no longer tries to connect to the backpack.

                   Also, on new installations, the 'Enable connection to external backpacks'
                   is now disabled by default, since no other external backpacks are currently
                   supported.


Release 2.9.47 (25th October 2019):
===================================


Important:

    TL-22311       The SameSite cookie attribute is now set to None in Chrome 78 and above

                   Chrome, in an upcoming release, will be introducing a default for the
                   SameSite cookie attribute of 'Lax'.

                   The current behaviour in all supported browsers is to leave the SameSite
                   cookie attribute unset, when not explicitly provided by the server at the
                   time the cookie is first set. When unset or set to 'None', HTTP requests
                   initiated by another site will often contain the Totara session cookie.
                   When set to 'Lax', requests initiated by another site will no longer
                   provide the Totara session cookie with the request.

                   Many commonly used features in Totara rely on third-party requests
                   including the user's session cookie. Furthermore, there are inconsistencies
                   between browsers in how the SameSite=Lax behaviour works. For this reason,
                   we will be setting the SameSite to 'None' for the session cookie when
                   Chrome 78 or later is in use. This will ensure that Totara continues to
                   operate as it has previously in this browser.

                   Due to the earlier mentioned inconsistencies in other browsers, we will not
                   set the SameSite attribute in any other browsers for the time being.
                   TL-22692 has been opened to watch the situation as it evolves and make
                   further improvements to our product when the time is right.

                   This change is currently planned to be made in Chrome 80, which we
                   anticipate will be released Q1 2020.

                   Chrome 80 is bringing another related change as well. Insecure cookies that
                   set SameSite to 'None' will be rejected. This will require that sites both
                   run over HTTPS and have the 'Secure cookies only' setting enabled within
                   Totara (leading to the secure cookie attribute being enabled).

                   The following actions will need to be taken by all sites where users will
                   be running Chrome:
                    * Upgrade to this release of Totara, or a later one.
                    * Configure the site to run over HTTPS if it is not already doing so.
                    * Enable the 'Secure cookies only' [cookiesecure] setting within Totara

                   For more information on the two changes being made in Chrome please see the
                   following:
                    * [https://www.chromestatus.com/feature/5088147346030592] Cookies default
                   to SameSite=Lax
                    * [https://www.chromestatus.com/feature/5633521622188032] Reject insecure
                   SameSite=None cookies

    TL-22621       SCORM no longer uses synchronous XHR requests for interaction

                   Chrome, in an upcoming release, will be removing the ability to make
                   synchronous XHR requests during page unload events, including beforeunload,
                   unload, pagehide and visibilitychanged.
                   If JavaScript code attempts to make such a request, the request will fail.

                   This functionality is often used by SCORM to perform a last-second save of
                   the user's progress at the time the user leaves the page. Totara sends this
                   request to the server using XHR. As a consequence of the change Chrome is
                   making, the user's progress would not be saved.

                   The fix introduced with this patch detects page unload events, and if the
                   SCORM package attempts to save state or communicate with the server during
                   unload, the navigation.sendBeacon API will be used (if available) instead
                   of a synchronous XHR request. The purpose of the navigation.sendBeacon API
                   is in line with this use, and it is one of two approaches recommended by
                   Chrome.

                   The original timeframe for this change in Chrome was with Chrome 78 due out
                   this month. However Chrome has pushed this back now to Chrome 80. More
                   information on this change in Chrome can be found at
                   [https://www.chromestatus.com/feature/4664843055398912]

                   We recommend all sites that make use of SCORM and who have users running
                   Chrome to update their Totara installations in advance of the Chrome 80
                   release.

Bug fixes:

    TL-22398       Fixed a potential problem in role_unassign_all_bulk() related to cascaded manual role unassignment

                   The problem may only affect third-party code because the problematic
                   parameter is not used in standard distribution.

    TL-22401       Removed unnecessary use of set context on report builder filters page


Release 2.9.46 (19th September 2019):
=====================================

Important:
    There are no issues included in this month's release – this release is to
    maintain a linear upgrade path from earlier versions.


Release 2.9.45 (22nd August 2019):
==================================


Security issues:

    TL-8385        Fixed users still having the ability to edit evidence despite lacking the capability

                   Previously when a user did not have the 'Edit one's own site-level
                   evidence' capability, they were still able to edit and delete their own
                   evidence.

                   With this patch, users without the capability are now prevented from
                   editing and deleting their own evidence.

    TL-21743       Prevented invalid email addresses in user upload

                   Prior to this fix validation of user emails uploaded by the site
                   administrator through the upload user administration tool was not
                   consistent with the rest of the platform. Email addresses were validated,
                   but if invalid they were not rejected or fixed, and the invalid email
                   address was saved for the user.

                   This fix ensures that user email address validation is consistent in all
                   parts of the code base.

Bug fixes:

    TL-21581       Added 'debugstringids' configuration setting support to core_string_manager

                   Fixed issue when "Show origin of languages strings" in Development >
                   Debugging is enabled, in some rare cases, not all strings origins were
                   displayed.

Contributions:

    * Jo Jones at Kineo UK - TL-21581


Release 2.9.44 (19th June 2019):
================================


Security issues:

    TL-21071       MDL-64708: Removed an open redirect within the audience upload form
    TL-21243       Added sesskey checks to prevent CSRF in several Learning Plan dialogs


Release 2.9.43 (22nd May 2019):
===============================


Security issues:

    TL-20730       Course grouping descriptions are now consistently cleaned

                   Prior to this fix grouping descriptions for the most part were consistently
                   cleaned.
                   There was however one use of the description field that was not cleaned in
                   the same way as all other uses.
                   This fix was to make that one use consistent with all other uses.

    TL-20803       Improved the sanitisation of user ID number field for display in various places

                   The user ID number field is treated as raw, unfiltered text, which means
                   that HTML tags are not removed when a user's profile is saved. While it is
                   desirable to treat it that way, for compatibility with systems that might
                   allow HTML entities to be part of user IDs, it is extremely important to
                   properly sanitise ID numbers whenever they are used in output.

                   This patch explicitly sanitises user ID numbers in all places where they
                   are known to be displayed.

                   Even with this patch, admins are strongly encouraged to set the 'Show user
                   identity' setting so that the display of ID number is disabled.

Bug fixes:

    TL-20767       Removed duplicate settings and unused headings from course default settings
    TL-20943       Fixed incorrect field reference set in the content options of the 'Badges issued' report

Contributions:

    * Stephen O'Hara, MediaCorp - TL-20943


Release 2.9.42 (29th April 2019):
=================================


Security issues:

    TL-20532       Fixed a file path serialisation issue in TCPDF library

                   Prior to this fix an attacker could trigger a deserialisation of arbitrary
                   data by targeting the phar:// stream wrapped in PHP.
                   In Totara 11, 12 and above The TCPDF library  has been upgraded to version
                   6.2.26.
                   In all older versions the fix from the TCPDF library for this issue has
                   been cherry-picked into Totara.

    TL-20614       Removed session key from page URL on seminar attendance and cancellation note editing screens
    TL-20615       Fixed external database credentials being passed as URL parameters in HR Import

                   When using the HR Import database sync, the external DB credentials were
                   passed to the server via query parameters in the URL. This meant that these
                   values could be unintentionally preserved in a user's browser history, or
                   network logs.

                   This doesn't pose any risk of compromise to the Totara database, but does
                   leave external databases vulnerable, and any other services that share its
                   credentials.

                   If you have used HR Import's external database import, it is recommended
                   that you update the external database credentials, as well as clear browser
                   histories and remove any network logs that might have captured the
                   parameters.

Bug fixes:

    TL-20488       Added batch processing of users when being unassigned from or reassigned to a program
    TL-20700       Fixed misleading count of users with role

                   A user can be assigned the same role from different contexts. The Users
                   With Role count was incorrectly double-counting such instances leading to
                   inaccurate totals being displayed. With this fix the system counts only the
                   distinct users per role, not the number of assignments per role.

    TL-20751       Fixed 'fullname' column option in user columns to return NULL when empty

                   Previously the column returned a space character when no value was
                   available which prevented users from applying "is empty" filter

Contributions:

    * Kineo UK - TL-20751


Release 2.9.41 (22nd March 2019):
=================================


Security issues:

    TL-20498       MDL-64651: Prevented links in comments from including the referring URL when followed


Release 2.9.40 (14th February 2019):
====================================


Bug fixes:

    TL-20109       Added a default value for $activeusers3mth when calling core_admin_renderer::admin_notifications_page()

                   TL-18789 introduced an additional parameter to
                   core_admin_renderer::admin_notifications_page() which was not indicated and
                   will cause issues with themes that override this function (which
                   bootstrapbase did in Totara 9). This issue adds a default value for this
                   function and also fixes the PHP error when using themes derived off
                   bootstrap base in Totara 9.


Release 2.9.39 (19th December 2018):
====================================


Security issues:

    TL-19593       Improved handling of face-to-face attendee export fields

                   Validation was improved for fields that are set by a site admin to be
                   included when exporting face-to-face attendance, making user information
                   that can be exported consistent with other parts of the application.

                   Permissions checks are now also made to ensure that the user exporting has
                   permission to access the information of each user in the report.

Bug fixes:

    TL-19692       Fixed a naming error for an undefined user profile datatype in the observer class unit tests

Contributions:

    * Ghada El-Zoghbi at Catalyst AU - TL-19692


Release 2.9.38 (4th December 2018):
===================================


Security issues:

    TL-19669       Backported MDL-64222 security fix for badges
    TL-19365       CSRF protection was added to the login page, and HTML blocks on user pages now prevent self-XSS

                   Cross-site request forgery is now prevented on the login page. This means
                   that alternate login pages cannot be supported anymore and as such this
                   feature was deprecated. The change may also interfere with incorrectly
                   designed custom authentication plugins.

                   Previously configured alternate login pages would not work after upgrade;
                   if attempting to log in on the alternate page, users would be directed to
                   the regular login page and presented with an error message asking them to
                   retry log in, where it will be successful. To keep using vulnerable
                   alternate login pages, the administrator would need to disable CSRF
                   protection on the login page in config.php.

Bug fixes:

    TL-18806       Prevented prog_write_completion from being used with certification data
    TL-17804       Fixed certification expiry date not being updated when a user is granted an extension

                   Additional changes include:
                    * new baseline expiry field in the completion editor which is used to calculate subsequent expiry dates
                    * preventing users from requesting extension after the certification expiry


Release 2.9.37 (25th October 2018):
===================================


Security issues:

    TL-18957       Fixed permission checks for learning plans

                   Prior to this patch all plan templates were being checked to see if a user
                   had a permission (e.g. update plan). Now only the template that the plan is
                   based off is checked for the permission.

Improvements:

    TL-18983       Added workaround for missing support for PDF embedding on iOS devices

                   Web browsers on iOS devices have very limited support for embedding PDF
                   files – for example, only the first page is displayed and users cannot
                   scroll to next page. A new workaround was added to PDF embedding in File
                   resource to allow iPhone and iPad users to open a PDF in full-screen mode
                   after clicking on an embedded PDF.

Bug fixes:

    TL-14204       Updated the inline helper text for course completion tracking

                   Prior to this patch, there was a misleading inline helper text on the
                   course view page next to 'Your progress'.
                   With this patch, the inline helper text is updated to reflect with the
                   change of the completion icon.


Release 2.9.36 (19th September 2018):
=====================================


Important:

    TL-14270       Added additional information about plugins usage to registration system
    TL-18788       Added data about installed language packs into registration system
    TL-18789       Added data about number of active users in last 3 months to registration system

Improvements:

    TL-18777       Allowed plugins to have custom plugininfo class instead of just type class

Bug fixes:

    TL-18571       Fixed access rights bug when viewing goal questions in completed appraisals

                   If an appraisal has a goal question and the appraisal was completed, then
                   it is the current learner's manager who can see the goal question. However,
                   there was an issue when a learner and their manager completed the appraisal
                   but then a new manager was assigned to the learner. In this case, only the
                   old manager could see the completed appraisal but they could not see the
                   goal question because they didn't have the correct access rights. The new
                   manager could not see the completed appraisal at all.

                   This applies to static and dynamic appraisals.


Release 2.9.35 (24th August 2018):
==================================


Security issues:

    TL-18491       Added upstream security hardening patch for Quickforms library

                   A remote code execution vulnerability was reported in the Quickforms
                   library. This applied to other software but no such vulnerability was found
                   in Totara. The changes made to fix this vulnerability have been taken to
                   reduce risks associated with this code.

Improvements:

    TL-13987       Improved approval request messages sent to managers for Learning Plans

                   Prior to this fix if a user requested approval for a learning plan then a
                   message was sent to the user's manager with a link to approve the request,
                   regardless of whether the manager actually had permission to view or
                   approve the request. This fix sends more appropriate messages depending on
                   the view and approve settings in the learning plan template.

Bug fixes:

    TL-17734       Fixed OpenSesame registration
    TL-17767       Fixed multiple blocks of the same type not being restored upon course restore
    TL-18488       Fixed a regression in DB->get_in_or_equal() when searching only integer values within a character field

                   This is a regression from TL-16700, introduced in 2.6.52, 2.7.35, 2.9.27,
                   9.15, 10.4, and 11.0. A fatal error would be encountered in PostgreSQL if
                   you attempted to call get_in_or_equal() with an array of integers, and then
                   used the output to search a character field.
                   The solution is ensure that all values are handled as strings.

    TL-18544       Fixed SQL error on reports using Toolbar Search when custom fields are deleted

                   If a custom field that is included as part of the Toolbar Search for a
                   Report Builder report (configured in the report settings) gets deleted then
                   an SQL error is generated. This only occurs after a search is done, viewing
                   the page normally will not display the error.

    TL-18562       Added HR Import check to ensure user's country code is two characters in length
    TL-18618       Restoring a course now correctly ignores links to external or deleted forum discussions
    TL-18649       Improved the Auto login guest setting description

                   The auto login guest setting incorrectly sets the expectation that
                   automatic login only happens when a non-logged in user attempts to access a
                   course. In fact it happens as soon as the user is required to login,
                   regardless of what they are trying to access. The description has been
                   improved to reflect the actual behaviour.


Release 2.9.34 (18th July 2018):
================================


Bug fixes:

    TL-16293       Fixed user profile custom fields "Dropdown Menu" to store non-formatted data

                   This fix has several consequences:
                   1) Whenever special characters (&, <, and >) were used in user custom
                      profile field, it was not found in dynamic audiences. It was fixed
                      by storing unfiltered values on save. Existing values will not be changed.
                   2) Improved multi language support of this custom field, which will display
                      item in user's preferred language (or default language if the user's
                      language is not given in the item).
                   3) Totara "Dropdown Menu" customfield also fixed on save.

                   Existing values that were stored previously, will not be automatically
                   fixed during upgrade. To fix them either:
                   1) Edit instance that holds value (e.g. user profile or seminar event),
                      re-select the value and save.
                   2) Use a special tool that we will provide upon request. This tool can work
                      in two modes: automatic or manual. In automatic mode it will attempt to
                      search filtered values and provide a confirmation form before fixing them.
                      In manual mode it will search for all inconsistent values (values that
                      don't have a relevant menu item in dropdown menu customfield settings)
                      across all supported components and allow you to choose to update them to
                      an existing menu item. To get this tool please request it on support board.

    TL-17324       Made completion imports trim leading and trailing spaces from the 'shortname' and 'idnumber' fields

                   Previously leading and trailing spaces on the 'shortname' or 'idnumber'
                   fields, were causing inconsistencies while matching upload data to existing
                   records during course and certification completion uploads. This patch now
                   trims any leading or trailing spaces from these fields while doing the
                   matching.

    TL-17657       Fixed an error causing a debugging message in the facetoface_get_users_by_status() function

                   Previously when the function was called with the include reservations
                   parameter while multiple reservations were available, there were some
                   fields added to the query that were causing a debugging message to be
                   displayed.

Contributions:

    *  Grace Ashton at Kineo.com - TL-17657


Release 2.9.33 (20th June 2018):
================================


Security issues:

    TL-10268       Prevented EXCEL/ODS Macro Injection

                   The Excel and Open Document Spreadsheet export functionality allowed the
                   exporting of formulas when they were detected, which could lead to
                   incorrect rendering and security issues on different reports throughout the
                   code base. To prevent exploitation of this functionality, formula detection
                   was removed and standard string type applied instead.

                   The formula type is still in the code base and can still be used, however
                   it now needs to be called directly using the "write_formula" method.

    TL-17424       Improved the validation of the form used to edit block configuration

                   Validation on the fields in the edit block configuration form has been
                   improved, and only fields that the user is permitted to change are passed
                   through this form.
                   The result of logical operators are no longer passed through or relied
                   upon.

    TL-17785       MDL-62275: Improved validation of calculated question formulae

Improvements:

    TL-17626       Prevented report managers from seeing performance data without specific capabilities

                   Site managers will no longer have access to the following report columns as
                   a default:

                   Appraisal Answers: Learner's Answers, Learner's Rating Answers, Learner's
                   Score, Manager's Answers, Manager's Rating Answers, Manager's
                   Score, Manager's Manager Answers, Manager's Manager Rating Answers,
                   Manager's Manager Score, Appraiser's Answers, Appraiser's Rating Answers,
                   Appraiser's Score, All Roles' Answers, All Roles' Rating Answers, All
                   Roles' Score.

                   Goals: Goal Name, Goal Description

                   This has been implemented to ensure site managers cannot access users'
                   performance-related personal data. To give site managers access to this
                   data the role must be updated with the following permissions:
                   * totara/appraisal:viewallappraisals
                   * totara/hierarchy:viewallgoals

Bug fixes:

    TL-17102       Fixed saved searches not being applied to report blocks
    TL-17289       Made message metadata usage consistent for alerts and blocks
    TL-17524       Fixed exporting reports as PDF during scheduled tasks when the PHP memory limit is exceeded

                   Generating PDF files as part of a scheduled report previously caused an
                   error and aborted the entire scheduled task if a report had a large data
                   set that exceeded the PDF memory limit. With this patch, the exception is
                   still raised, but the export completes with the exception message in the
                   PDF file notifying the user that they need to change their report. The
                   scheduled task then continues on to the next report to be exported.

    TL-17538       Fixed the room selection reset when the session form validation does not pass
    TL-17541       Fixed the help text for a setting in the course completion report

                   The help text for the 'Show only active enrolments' setting in the course
                   completion report was misleading, sounding like completion records for
                   users with removed enrolments were going to be shown on the report. This
                   has now been fixed to reflect the actual behaviour of the setting, which
                   excludes records from removed enrolments.

    TL-17610       Setup cron user and course before each scheduled or adhoc task

                   Before this patch we set the admin user and the course at the beginning of
                   the cron run. Any task could have overridden the user. But if the task did
                   not take care of resetting the user at the end it affected all following
                   tasks, potentially creating unwanted results. Same goes for the course. To
                   avoid any interference we now set the admin user and the default course
                   before each task to make sure all get the same environment.

    TL-17633       Removed misleading information in the program/certification extension help text

                   Previously the help text stated "This option will appear before the due
                   date (when it is close)" which was not accurate as the option always
                   appeared during the program/certification enrollment period. This statement
                   has now been removed.

Contributions:

    * Grace Ashton at Kineo UK - TL-17538
    * Jo Jones at Kineo UK - TL-17524


Release 2.9.32 (14th May 2018):
===============================


Security issues:

    TL-17436       Added additional validation on caller component when exporting to portfolio
    TL-17440       Added additional validation when exporting forum attachments using portfolio plugins
    TL-17445       Added additional validation when exporting assignments using portfolio plugins
    TL-17527       Seminar attendance can no longer be used to export sensitive user data

                   Previously it was possible for a site administrator to configure Seminar
                   attendance exports to contain sensitive user data, such as a user's hashed
                   password. User fields containing sensitive data can no longer be included
                   in Seminar attendance exports.

Improvements:

    TL-16958       Updated language strings to replace outdated references to system roles

                   This issue is a follow up to TL-16582 with further updates to language
                   strings to ensure any outdated references to systems roles are corrected
                   and consistent, in particular changing student to learner and teacher to
                   trainer.

Bug fixes:

    TL-6476        Removed the weekday-textual and month-textual options from the data source selector for report builder graphs

                   The is_graphable() method was changed to return false for the
                   weekday-textual and month-textual, stopping them from being selected in the
                   data source of a graph. This will not change existing graphs that contain
                   these fields, however if they are edited then a new data source will have
                   to be chosen. You can still display the weekday or month in a data source
                   by using the numeric form.

    TL-17387       Fixed managers not being able to allocate reserved spaces when an event was fully booked
    TL-17535       Fixed hard-coded links to the community site that were not being redirected properly

Contributions:

    * Marcin Czarnecki at Kineo UK - TL-17387


Release 2.9.31 (19th April 2018):
=================================


Improvements:

    TL-16582       Updated language contextual help strings to use terminology consistent with the rest of Totara

                   This change updates the contextual help information displayed against form
                   labels. For example this includes references to System roles, such as
                   student and teacher, have been replaced with learner and trainer.

                   In addition, HTML mark-up has been removed in the affected strings and
                   replaced with Markdown.

    TL-17170       Included hidden items while updating the sort order of Programs and Certifications
    TL-17268       Upgraded Node.js requirements to v8 LTS
    TL-17280       Improved compatibility for browsers with disabled HTTP referrers
    TL-17321       Added visibility checks to the Program deletion page

                   Previously the deletion of hidden programs was being stopped by an
                   exception in the deletion code, we've fixed the exception and added an
                   explicit check to only allow deletion of programs the user can see. If you
                   have users or roles with the totara/program:deleteprogram capability you
                   might want to consider allowing totara/program:viewhiddenprograms as well.

    TL-17352       PHPUnit and Behat do not show composer suggestions any more to minimise developer confusion
    TL-17384       composer.json now includes PHP version and extension requirements

Bug fixes:

    TL-14364       Disabled the option to issue a certificate based on the time spent on the course when tracking data is not available

                   The certificate activity has an option which requires a certain amount of
                   time to be spent on a course to receive a certificate. This time is
                   calculated on user actions recorded in the standard log. When the standard
                   log is disabled, the legacy log will be used instead. If both logs are
                   disabled, the option will also be disabled.

                   Please note, if the logs are disabled, and then re-enabled, user actions in
                   the time the logs were disabled will not be recorded. Consequently, actions
                   in this period will not be counted towards time spent on the course.

    TL-16724       Fixed an error while backing up a course containing a deleted glossary

                   This error occurred while attempting to backup a course that contained a
                   URL pointing to a glossary activity that had been deleted in the course
                   description. Deleted glossary items are now skipped during the backup
                   process.


Release 2.9.30 (23rd March 2018):
=================================


Important:

    TL-14114       Added support for Google ReCaptcha v2 (MDL-48501)

                   Google deprecated reCAPTCHA V1 in May 2016 and it will not work for newer
                   sites. reCAPTCHA v1 is no longer supported by Google and continued
                   functionality can not be guaranteed.

Security issues:

    TL-17225       Fixed security issues in course restore UI

Improvements:

    TL-16899       Backported TL-14432 which improved the performance of generating report caches for reports with text based columns

                   Previously all fields within a Report Builder cache had an index created
                   upon them.
                   This included both text and blob type fields and duly could lead to
                   degraded performance or even failure when trying to populate a Report
                   Builder cache.
                   As of this release indexes are no longer created for text or blob type
                   columns.
                   This may slow down the export of a full cached report on some databases if
                   the report contains many text or blob columns, but will greatly improve the
                   overall performance of the cache generation and help avoid memory
                   limitations in all databases.

    TL-16914       Added contextual details to the notification about broken audience rules

                   Additional information about broken rules and rule sets are added to email
                   notifications. This information is similar to what is displayed on
                   audiences "Overview" and "Rule Sets" tabs and contains the broken audience
                   name, the rule set with broken rule, and the internal name of the broken
                   rule.

                   This will be helpful to investigate the cause of the notifications if a
                   rule was fixed before administrator visited the audience pages.

Bug fixes:

    TL-16838       Stopped reaggregating competencies using the ANY aggregation rule when the user is already proficient
    TL-16865       Fixed the length of the uniquedelimiter string used as separator for the MS SQL GROUP_CONCAT_D aggregate function

                   MS SQL Server custom GROUP_CONCAT_* aggregate functions have issues when
                   the delimeter is more than 4 characters.

                   Some report builder sources used 5 character delimiter "\.|./" which caused
                   display issues in report. To fix it, delimeter was changed to 3 characters
                   sequence: "^|:"

    TL-17111       Renamed some incorrectly named unit test files

Contributions:

    * Jo Jones at Kineo UK - TL-16899


Release 2.9.29 (12th March 2018):
=================================


Important:

    TL-17166       Added support for March 1, 2018 PostgreSQL releases

                   PostgreSQL 10.3, 9.6.8, 9.5.12, 9.4.17 and 9.3.22 which were released 1st
                   March 2018 were found to not be compatible with Totara Learn due to the way
                   in which indexes were read by the PostgreSQL driver in Learn.
                   The method for reading indexes has been updated to ensure that Totara Learn
                   is compatible with PostgreSQL.

                   If you have upgraded PostgreSQL or are planning to you will need to upgrade
                   Totara Learn at the same time.


Release 2.9.28 (28th February 2018):
====================================


Security issues:

    TL-16789       Added output filtering for event names within the calendar popup

                   Previously event names when displayed within the calendar popup were not
                   being cleaned accurately.
                   They are now cleaned consistently and accurately before being output.

    TL-16841       Removed the ability to preview random group allocations within Courses

                   This functionality relied on setting the seed used by rand functions within
                   PHP.
                   A consequence of which was that for short periods of time the seed used by
                   PHP would not be randomly generated, but preset.
                   This could be used to make it easier to guess the result of randomised
                   operations within several PHP functions, including some functions used by
                   cryptographic routines within PHP and Totara.
                   The seed is no longer forced, and is now always randomly generated.

    TL-16844       Improved security and privacy of HTTP referrers

                   We have improved the existing "Secure referrers" setting to
                   be compatible with browsers implementing the latest referrer policy
                   recommendation from W3C. This setting improves user privacy by preventing
                   external sites from tracking users via referrers.

    TL-16859       Prevented sending emails to admin before IPN request is verified by Paypal

                   The IPN endpoint for the Paypal enrolment method was sending an email to
                   the site admin when the basic validation of the request parameters failed.
                   An attacker could have used this to send potential malicious emails to the
                   admin. With this patch an email is sent to the admin only after the
                   successful verification of the IPN request data with Paypal. Additionally
                   the script now validates if there's an active Paypal enrolment method for
                   the given course.

                   The check for a connection error of the verification request to Paypal has
                   been fixed. Now the CURL error of the last request stored in the CURL
                   object is used instead of the return value of the request method which
                   always returns either the response or an error.

    TL-16956       Added additional checks to CLI scripts to ensure that they can not be accessed via web requests

                   A small number of scripts designed to be run via CLI were found not to be
                   adequately checking that the script was truly being executed from the
                   command line.
                   All CLI scripts have been reviewed, and those found to be missing the
                   required checks have been updated.

Bug fixes:

    TL-16662       Cleaned up orphaned data left after deleting empty course sets from within a Program or Certification

                   The orphaned data happens when there are no orphaned program courses but
                   there are orphaned program course sets.
                   This is only known to affect sites running Totara Learn 2.7.3 or earlier.
                   An upgrade step has been added to remove any orphaned records from the
                   database.

    TL-16673       Fixed error being thrown in Moodle course catalog when clicking "Expand all" with multiple layers of categories
    TL-16749       Fixed a regression from TL-14803 to allow HTML in mod certificate custom text

                   This patch fixes a regression caused by TL-14803 which affected the display
                   of the custom text when used with multilang content in all versions back to
                   2.7.  Data has not been affected with the regression. The change updates
                   the use of format_string() function to format_text().

    TL-16759       Enabled answers in Appraisals to display for roles that have no user associated with them or the user has been deleted

                   In the populate_roles_element function in the appraisal_question class
                   empty question roles are no longer excluded from the appraisal question
                   role info.

    TL-16791       Fixed Certificate generation when using Traditional Chinese (zh_tw)
    TL-16955       Added a workaround for sqlsrv driver locking up during restore

                   In rare cases during the restoration of a large course MSSQL, would end up
                   in a locked state whilst waiting for two conflicting deadlocks.
                   This occurred due to a table being both read-from and written-to within a
                   single transaction.

Contributions:

    * Learning Pool - TL-16791


Release 2.9.27 (18th January 2018):
===================================


Important:

    TL-9352        New site registration form

                   In this release we have added a site registration page under Site
                   administration > Totara registration. Users with the 'site:config'
                   capability will be redirected to the page after upgrade until registration
                   has been completed.

                   Please ensure you have the registration code available for each site before
                   you upgrade. Partners can obtain the registration code for their customers'
                   sites via the Subscription Portal. Direct subscribers will receive their
                   registration code directly from Totara Learning.

                   For more information see the help documentation:

                   https://help.totaralearning.com/display/TLE/Totara+registration

Improvements:

    TL-7553        Improved Report Builder support of Microsoft Excel CSV import with Id columns

Bug fixes:

    TL-16536       Added missing string on the Feature overview page
    TL-16631       Fixed SCORM package display in simple popup window when package does not provide player API
    TL-16700       Added workaround in DML for fatal errors when get_in_or_equal() used with large number of items


Release 2.9.26 (21st December 2017):
====================================


Security issues:

    TL-16451       Fixed permissions not being checked when performing actions on the Face-to-face attendees page

Improvements:

    TL-9277        Added additional options when selecting the maximum Feedback activity reminder time

Bug fixes:

    TL-8062        Fixed Face-to-face notifications not being sent when the room has been changed
    TL-15804       Feedback Reminder periods help text has been clarified to explain it counts weekdays and not weekends

                   The Feedback Reminder period is calculated only using weekdays. All
                   weekends will be skipped and added to the period. To make this existing
                   behaviour clearer we modified the help text accordingly.

    TL-16218       Fixed a typo in the certification completion checker
    TL-16220       Fixed multisco SCORM completion with learning objects grading method (based on MDL-44712)

                   MDL-44712 introduced the "Require all scos to return 'completed'" setting.
                   This had been originally introduced into v10 and 11. Now it has been
                   backported to v9 and v2.9.

                   However note the following:
                   * A multisco SCORM might send back "cmi.core.lesson_status" (or equivalent)
                     values for every SCO. However, if there is a status condition completion
                     setting, then Totara (and Moodle) marks the whole SCORM activity as long as
                     any SCO has a "cmi.core.lesson_status" value of "completed".
                   * Things get especially confusing when a minimum score _condition_ is used
                     with a _grading_ method of "Learning Objects" (ie multisco).
                     * The minimum score condition uses the "cmi.score.raw" (or equivalent) to
                       compute whether the activity is complete.
                     * If the SCORM does not send back a "cmi.score.raw" attribute and the
                       minimum score completion value is set, then the activity *never completes,
                       even if the student goes through the entire SCORM*.
                     * In other words, _the minimum score completion setting has got nothing to
                       do with the "learning objects" grading method_. It is very
                       counter-intuitive but all along, there has been no code in SCORM module to
                       check the total no of "completed" learning objects against an expected
                       count. It is to address this problem that the new "Require all scos to
                       return "completed" status" setting is there.
                   * The TL patch also fixes a problem with MDL-4471 patch in which multiple,
                     simultaneous completion conditions were not evaluated properly. In this
                     case, if a multisco SCORM returned both "cmi.core.lesson_status" and
                     "cmi.score.raw" and the completion settings were for _both_ status and
                     minimum score, the activity would be marked as complete if the student
                     clicked through the entire SCORM but got less than the minimum score.

    TL-16458       Fixed Totara Connect SSO login process to update login dates and trigger login event
    TL-16483       Fixed Report Builder caching for reports that include Face-to-face session roles filter
    TL-16492       Allow less privileged reviewers and respondents to a 360° Feedback to access the files added to a response
    TL-16521       Fixed certification messages that were not reset before upgrading to TL-10979

                   When patch TL-10979 was included in Totara 2.9.13 and 9.1, it did not
                   include an upgrade to reset messages which were not reset when the
                   recertification window opened before the upgrade. This patch resets those
                   messages, where possible, allowing the messages to be sent again. Users
                   whose recertification windows have reopened since upgrading to the above
                   mentioned versions will not be affected because they should already be in
                   the correct state.

    TL-16522       Fixed the language used for the email from address for scheduled reports
    TL-16530       Fixed report builder cache generator

                   Previously the Report Builder source cache was removing the old cache table
                   before creating a new one, which was creating a problem whereby the user
                   couldn't use the old cache table and the new one wasn't ready.
                   The fix was to keep the old table until the new table was ready, at which
                   point the old table is removed.

    TL-16603       Ported MDL-55469 to allow learners to completely finish a final SCORM attempt

                   Important consideration: This fix relies on correct data submitted by the
                   SCORM package. If the SCORM reported that "cmi.core.lesson_status" is
                   either "completed", "failed", or "passed", then the attempt will be counted
                   as final even if user exited the activity without submitting/finalising the
                   attempt.

Contributions:

    * Barry Oosthuizen at Learning Pool - TL-9277
    * Jo Jones at Kineo UK - TL-16530
    * Simon Adams at Kineo UK - TL-16483


Release 2.9.25 (22nd November 2017):
====================================


Important:

    TL-16434       Updated email address validation to use the WHATWG recommendation.

Security issues:

    TL-16270       360° Feedback now correctly disposes of the user's access token when no longer needed

                   Previously if a user accessed a 360° Feedback instance using a token, that
                   token would be stored in the user's session and would allow them to access
                   the 360° Feedback as a user (not with a token).
                   The token used to access the first 360° Feedback instance is now disposed
                   of correctly.

Bug fixes:

    TL-15029       Fixed brief positioning issue when scrolling a 360° Feedback page
    TL-16287       Fixed renaming of user profile fields breaking HR Import user source settings

                   If the HR Import user source (CSV or Database) was configured to import a
                   custom profile field and the field short name was changed then HR Import
                   would no longer import data to it. In some situations it would then be
                   impossible to re-add the field. This has now been fixed.

    TL-16296       Fixed a bug leading to schedule changes for reports being forgotten
    TL-16312       Fixed formatting of text area fields in the Database course activity when exporting

                   When exporting text area field data from the Database activity the field
                   content included HTML tags. It now converts the HTML to standard text.

    TL-16376       Fixed LDAP sync for user profile custom menu field

                   TL-14170 fixed a problem where custom user profile fields were not being
                   synced with an LDAP backend. The fix worked for all user profile custom
                   fields except for menu dropdowns which required an extra processing step
                   during the LDAP syncing. This has now been fixed.

    TL-16429       Fixed session details missing from Trainer confirmation email
    TL-16435       Fixed missing "Notification does not exist" string
    TL-16443       Fixed an SQL error in the Appraisal details report due to multi-select questions

Contributions:

    * Richard Eastbury at Think Associates - TL-16376


Release 2.9.24 (27th October 2017):
===================================


Important:

    TL-16313       Release packages are now provided through https://subscriptions.totara.community/

                   Release packages are no longer being provided through FetchApp, and can now
                   be accessed through our new Subscription system at
                   https://subscriptions.totara.community/.

                   If you experience any problems accessing packages through this system
                   please open a support request and let us know.

                   Please note that SHA1 checksums for previous Evergreen releases will be
                   different from those provided in the changelog notes at the time of
                   release.
                   The reason for this is that we changed the name of the root directory
                   within the package archives to ensure it is consistent across all products.

Security issues:

    TL-12466       Corrected access restrictions on 360° Feedback files

                   Previously, users may have been able to access 360° Feedback files when
                   they did not have access to the corresponding 360° Feedback itself. This
                   will have included users who were not logged in. To do this, the user would
                   have needed to correctly guess the URL for the file. The access
                   restrictions on these files have now been fixed.

Improvements:

    TL-15835       Made some minor improvements to program and certification completion editors

                   Changes included:
                    * Improved formatting of date strings in the transaction interface and
                   logs.
                    * Fixed some inaccurate error messages when faults might occur.
                    * The program completion editor will now correctly default to the
                   "invalid" state when there is a problem with the record.

    TL-16381       The new release notification was updated to use new end point

Bug fixes:

    TL-15790       Fixed invalid URL error for the evidence field from being displayed in Other Evidence
    TL-15923       Fixed duplicate calendar records for Face-to-face wait-list user calendar
    TL-15980       Fixed bug within the course catalog when no filters are available
    TL-16215       Role assignments granted through the enrol_cohort plugin are now deleted if the plugin is disabled

                   Previously when the cohort enrolment plugin instance was disabled, the
                   roles for the affected users were not deleted from the {{role_assignments
                   table}} even though the log messages seemed to indicate this was the case.
                   This has been corrected with this patch.

                   Note the deletion behavior has always been correct in the original code
                   when the cohort enrolment plugin itself was disabled, However, it needs the
                   cohort enrolment task to be run first (every hour by default) to physically
                   delete the records from the table.

    TL-16223       Fixed a typo in the "cancellationcutoff" session variable
    TL-16224       Prevented orphaned program exceptions from occurring

                   It was possible for program and certification exceptions to become orphaned
                   - no exception showed in the "Exception report" tab, but users were
                   treated as having an exception and were being prevented from progressing.
                   The cause of this problem has now been fixed. After upgrade, use the
                   program and certification completion checkers to identify any records in
                   this state and fix them using one of the two available automated fixes
                   (which were added in TL-15891, in the previous release of Totara).

    TL-16254       Fixed automated course backup not taking audience-based visibility into account
    TL-16258       Fixed uniqueness checks for certification completion history

                   Certification completion history records should always be a unique
                   combination of user, certification, expiry date and completion date.

                   Completion import adhered to this rule, however the process of copying a
                   certification completion to history when the certification window opened
                   did not take the completion date into account. This led to overwriting of
                   the completion date if a history record had a matching expiry date but
                   different completion date. This could also lead to errors during the Update
                   certifications scheduled task.

                   The correct uniqueness rule has been applied consistently to prevent the
                   above behaviour.

    TL-16267       Fixed permissions error when accessing SCORM activities as a guest
    TL-16286       Fixed incorrect appraisal status on reassigned users

Contributions:

    * Oswaldo Rojas at Enovation - TL-15980
    * Richard Eastbury at Think Associates - TL-15790


Release 2.9.23 (22nd September 2017):
=====================================


Security issues:

    TL-12944       Updated Web Service tokens to use cryptographically secure generators

                   Previously, Web Service tokens were generated via a method which would
                   generate a random and hard-to-guess token that was not considered
                   cryptographically secure. New tokens will now be generated using
                   cryptographically secure methods, providing they are available in the
                   server's current version of PHP.

    TL-14325       Fixed an issue when users authenticating through external authentication systems experience password expiry
    TL-16116       Added a check for group permissions when viewing course user reports
    TL-16117       Events belonging to activity modules can no longer be manually deleted from the calendar
    TL-16119       Fixed incomplete escaping on the Feedback activity contact form
    TL-16120       Added warning to admins when a development libs directory exists.

Improvements:

    TL-14244       Updated default branding to Totara Learn

                   Changed language strings and logos to use the new product name "Totara
                   Learn" instead of "Totara LMS".

    TL-15056       Added warning notice to the top of delete category page

Bug fixes:

    TL-11012       Fixed formatting of grade percentage shown in quiz review

                   The configured 'decimal places in grades' value of a quiz is now also used
                   when formatting the grade percentage on the quiz review page. In earlier
                   releases the percentage has always been formatted with 0 decimal points
                   which resulted in confusing results.

                   Administrators and trainers are still responsible for ensuring that the
                   configured 'decimal places in grades' value will not result in confusion
                   for students due to the rounding up of the displayed values.

                   It is advised to use at least 2 decimal places if a student can score a
                   fraction of a point in any question in the quiz.

    TL-14676       Fixed error when deleting a closed 360 Feedback
    TL-14753       Fixed the display of grades within the course completion report sources
    TL-15875       Prevented temporary manager change from resulting in appraisal role change warning

                   Temporary managers do not take part in appraisals. However, when a
                   temporary manager was assigned to a user, it incorrectly resulted in a role
                   change warning being displayed. This warning is now only shown if the
                   manager changes.

    TL-15891       Added checks and fixes for orphaned program user assignment exceptions

                   Under certain exceptional circumstances, it is possible for a user assigned
                   to a program or certification to have an exception, but that exception does
                   not show up in the 'Exception Report' tab. In this state, the user is
                   unable to continue working on the program, and the exception cannot be
                   resolved. With this patch, the completion checker has been extended to
                   detect this problem, and two triggerable fixes have been provided.

                   To resolve the problem, run the program and certification completion
                   checkers to find all records affected, or edit a completion record, then
                   choose to either assign the users or have the exceptions recalculated. If
                   the 'recalculate exceptions' option is chosen and an exception still
                   applies to a user, then after fixing the problem you can resolve the
                   exceptions as normal in the 'Exception Report' tab.

    TL-15897       Fixed some typos in Certification language strings
    TL-15899       Corrected inconsistent validation of Face-to-face sender address setting
    TL-15919       Fixed missing delete assignment button for active appraisals
    TL-15977       Fixed SCORM cmi.interaction bug
    TL-16126       Fixed how choice activity data is reset by certification windows

Miscellaneous Moodle fixes:

    TL-16033       MDL-57649: Fixed removing of attached files in question pages of lesson module

                   Fixed bug in lesson activity which did not automatically remove files
                   attached to question pages when those pages were deleted.


Release 2.9.22 (23rd August 2017):
==================================


Important:

    TL-7753        The gauth authentication plugin has been removed from all versions of Totara

                   The gauth plugin has now been removed from Totara 10, 9.10, 2.9.22, 2.7.30,
                   and 2.6.47.
                   It was removed because the Google OpenID 2.0 API used by this plugin has
                   been shut down.
                   The plugin itself has not worked since April 2015 for this reason.
                   No alternative is available as a brand new plugin would need to be written
                   to use the API's currently provided by Google.

Security issues:

    TL-10753       Prevented viewing of hidden program names in Program completions block ajax

                   Previously, a user visiting an AJAX script for the program completions
                   block could see names of hidden programs if certain values were used in the
                   URL. Names of programs can now only be seen if the user has permission to
                   view them.

    TL-14213       Converted sesskey checks to use timing attack safe function hash_equals()

Improvements:

    TL-15006       Cleaned up and improved dataroot reset in behat and phpunit tests
    TL-15009       Added new faster static MUC cache for phpunit tests
    TL-15760       Updated hardcoded URLs to point to new community site location

                   Links to the community in code were updated from community.totaralms.com to
                   the new url of totara.community.

Bug fixes:

    TL-12295       Added replacement email verification for openbackpack connections

                   The Persona system has been shut down. (For more information see,
                   https://wiki.mozilla.org/Identity/Persona_Shutdown_Guidelines_for_Reliers).
                   This introduces a replacement email verification process to ensure the
                   badges functionality continues to be supported.

                   This is a backport of MDL-57429 / TL-14568.

    TL-12855       Fixed quiz statistics for separate groups
    TL-14148       Fixed static server version caching in database drivers
    TL-14170       Fixed LDAP/user profile custom field sync bug
    TL-14828       Forum posts only marked as read when full post is displayed
    TL-14953       Fixed missing JavaScript dependencies in the report table block

                   While the Report Table Block allows the use of embedded report sources, it
                   does not add embedded restrictions (which are only added on pages where the
                   embedded report is displayed already).
                   This means specific embedded restrictions will not be applied in the table
                   and content displayed in block might be different from content displayed on
                   page.
                   For example, Alerts embedded report page will display only user's messages,
                   while the same report in the Report Builder block will display messages for
                   all users. It is better to use non-embedded report sources and saved
                   searches to restrict information displayed.

    TL-14954       Fixed the display of translated month names in date pickers
    TL-14967       Fixed the suppress notification setting being ignored when allowing scheduling conflicts
    TL-14984       Fixed the display of grades in the Record of Learning grades column
    TL-15015       Increased spacing around visible text when filling out an appraisal
    TL-15022       Fixed 'Responsetime' for anonymous users from showing epoch date
    TL-15039       Fixed an SQL error that occurred when searching in filters using just a space
    TL-15040       Fixed the information sent in the attached ical when notifying users that a Seminar's date and details have been changed
    TL-15045       Fixed issue with settings for aggregate questions in cloned appraisals

                   TL-11316 was backported to fix errors on aggregate questions in cloned
                   appraisals

    TL-15057       ORACLE SQL keywords are now ignored when validating install.xml files
    TL-15086       Fixed SCORM view page to display content depending on permissions

                   If the user has the mod/scorm:savetrack capability, they can see the info
                   page and enter the SCORM lesson.
                   If the user has the mod/scorm:viewreport capability, they can see the SCORM
                   reports.

    TL-15095       Fixed known compatibility problems with MariaDB 10.2.7
    TL-15100       Fixed session start date link format without timezone
    TL-15103       Fixed handling of html markup in multilingual authentication instructions
    TL-15731       Fixed the display of personal goal text area custom fields in Appraisal snapshots
    TL-15738       Fixed program progress bar in Program Overview report source
    TL-15775       Fixed incorrect encoding of language strings in Appraisal dialogs
    TL-15811       Fixed admin tree rendering to handle empty sub items

Contributions:

    * Richard Eastbury at Think Associates - TL-15775


Release 2.9.21 (19th July 2017):
================================


Important:

    TL-14946       The webdav_locks table has been dropped from the database

                   The webdav_locks table has been dropped from the database.
                   It is a legacy table from Totara 1.1 and has never been used in Totara 2 or
                   above.
                   It had already been dropped from Totara 9 and 10.
                   The decision was made to drop the table from stable branches as it
                   contained a field that was using a name that had become a reserved word in
                   modern databases.
                   By dropping this unused table we can help ensure that database upgrades
                   will not be problematic in the supported stable releases.

Security issues:

    TL-9391        Made file access in programs stricter

                   Restricted File access in programs to:
                    * Users that are not logged in cannot see any files in programs.
                    * Users who are not assigned can only see the summary and overview files
                    * Only users who can view hidden programs can see the files in programs
                   that are not visible

    TL-12940       Applied account lockout threshold when using webservice authentication

                   Previously, the account lockout threshold, for number of incorrect
                   passwords, was not taken into account when webservice authentication was
                   being used. The account lockout functionality now applies to webservice
                   authentication. Please note that this refers to the authentication type
                   that allows users to log in with username and password, not when accessing
                   their account using a webservice token.

    TL-12942       Stopped the supplied passwords being logged in failed web services authentication

                   When web service authentication was used and legacy logging was enabled,
                   entries recorded to the logs for failed log in attempts included the
                   supplied password in plain text. This is no longer recorded.

                   The password was not added to entries in other logs included with Totara
                   aside from the legacy log.

Report Builder improvements:

    TL-6834        Improved the performance of Report Builder reports by avoiding unnecessary count queries

                   Previously when displaying a report in the browser the report query would
                   be executed either two or three times.
                   Once to get the filtered count of results.
                   Potentially once more to get the unfiltered count of results.
                   Once to get the first page of data.

                   The report page, and all embedded reports now use a new counted recordset
                   query that gives the first page of data and the filtered count of results
                   in a single query, preventing the need to run the expensive report query to
                   get the filtered count.
                   Additionally TL-14791 which is included in 9.9 and above prevents the need
                   to run the query to get the unfiltered count unless the site administrator
                   has explicitly requested it and the report creator explicitly turned it on
                   for that report.
                   This reduction of expensive queries greatly improves the performance of
                   viewing a report in the browser.

    TL-14398       Report Builder source caching is now user specific

                   Previously the Report Builder source cache was shared between users.
                   When scheduled reports were being run this could lead to several issues,
                   notably incorrect results when applying filters, and performance issues.
                   The cache is now user specific. This consumes more memory but fixes the
                   user specific scheduled reports and improves overall performance when
                   generating scheduled reports created by many users.

    TL-14780       Fixed the unnecessary use of LIKE within course category filter multichoice

                   The course category multichoice filter was unnecessarily using like for
                   category path conditions.
                   It can use = and has been converted to do so, improving the overall
                   performance of the report when this filter is in use.

Improvements:

    TL-14755       Added an environment test for misconfigured MSSQL databases

Bug fixes:

    TL-14341       Fixed page ordering for draft appraisals without stage due dates
    TL-14701       Removed unused 'timemodified' form element from learning plan competencies
    TL-14713       Fixed escape character escaping within the "sql_like_escape" database function
    TL-14750       Fixed restricted access based on quizzes using the require passing grade completion criteria

                   Previously, quizzes using the completion criteria "require passing grade"
                   were simply being marked as complete instead of as passed/failed. Since
                   they were correctly being marked as complete this had very little effect
                   except for restricted access. If a second activity had restricted access
                   based on the quiz where it required "complete with a passing grade", access
                   was never granted. This patch fixes that going forwards. To avoid making
                   assumptions about users completions, existing completion records have been
                   left alone. These can be manually checked with the upcoming completion
                   editor. In the mean time, if you are using the quiz completion criteria
                   "require passing grade" without the secondary "or all attempts used",
                   changing the access restriction to "Quiz must be marked as complete" will
                   have the same effect.

    TL-14765       Retrieving a counted recordset now works with a wider selection of queries
    TL-14803       Fixed certificate custom text to support multi-language content
    TL-14809       Corrected typos within graph custom settings inline help
    TL-14993       Prevented all access to the admin pages from the guest user
    TL-15014       Fixed inconsistencies in counted recordsets across all databases

                   The total count result is now consistent across all databases when
                   providing an offset greater than the total number of rows.

    TL-15036       Added missing column type descriptor in the Totara Connect report source

Miscellaneous Moodle fixes:

    TL-14927       MDL-59456: Fixed a CAS authentication bypass issue when running against an old CAS server

Contributions:

    * Alex Glover at Kineo UK - TL-14341
    * Artur Rietz at Webanywhere - TL-14398
    * Pavel Tsakalidis for proposing the approach used in TL-6834


Release 2.9.20 (21st June 2017):
================================


Security issues:

    TL-7289        Added environment check for XML External Entity Expansion

                   On upgrade or install, a check will be made to determine whether the
                   server's environment could be vulnerable to attackers including the
                   contents of external files via entities in user-supplied XML files. A
                   warning will only be shown if a vulnerability is identified. This check is
                   also available via the security report.

Improvements:

    TL-9224        Improved consistency of program exception restrictions

                   Previously some Programs code was still being executed on users with
                   exceptions, those places now check for valid user assignments before
                   processing the users. Some places identified were, the program completion
                   cron, the certification window opening cron, and the programs course
                   enrolment plugin.

    TL-9300        Updated the Date/time custom field so that it is not enabled by default

                   Making the Date/time custom fields disabled by default prevents the field
                   from being set inadvertently. When the custom field is marked as required
                   the field will always be enabled and default to the present date.

    TL-10502       Renamed Record of learning navigation block to "Learning" (from "Learning plans")
    TL-11264       Improved Atto editor autosave messaging and draft revert workflow

                   When a draft is automatically applied to an Editor, there is now a
                   page-level alert to let users know what has happened. In addition, the
                   default arrangement of toolbar icons now includes Undo/Redo which, when a
                   Draft is auto-applied, will toggle between original Database-saved content
                   and the Draft.

    TL-14288       Added logs relating to program and certification assignment changes
    TL-14385       Added checks for missing program and certification completion records

                   The program and certification completion checkers have been extended to
                   detect missing and unneeded program and certification completion records.
                   Automated fixes have been provided to allow admins to correct these
                   problems. After upgrade, you should use the completion checker to fix all
                   "Files" category problems which are reported (if any). After all problems
                   on the site have been fixed, if new problems are discovered then they
                   should be reported to Totara support.

Bug fixes:

    TL-10374       Fixed an Appraisal bug when trying to add a question without selecting a type
    TL-14140       Fixed security report check for whether Flash animation is enabled

                   The security report was checking for an outdated config setting when
                   checking whether Flash animation (using swf files) was enabled. The correct
                   config setting is now checked.

                   Flash animation is no longer enabled by default on new installations of
                   Totara, however this is not changed during upgrade for existing sites. If
                   Flash animation is not required on your site, you are encouraged to review
                   the security report and disable Flash animation and/or the Multimedia
                   plugin if they are not required.
                   Flash animations, when enabled, could only be added by trusted users who
                   had capabilities marked with XSS risk.

    TL-14144       Fixed ambiguous id column in course dialog when completion criteria is required
    TL-14251       Fixed the display order of goal scale values on the my goals page
    TL-14252       Fixed debug error when sending program messages with certain placeholders

                   Previously, if a program message (such as enrolment message) was sent out
                   for a user who was enrolled via multiple methods, and the message used the
                   %completioncriteria% or %duedate% placeholders, a debugging error is
                   thrown. This has now been fixed.

                   The %completioncriteria% placeholder was only designed to work when only
                   one enrolment method is in place for a user. Previously, the criteria
                   substituted into the email when a user did have multiple enrolment methods
                   was chosen randomly. Now the criteria will be taken from the enrolment with
                   the most recent assignment date/time.

    TL-14272       Fixed program and certification course enrolment suspension

                   Due to a recent change, users were being unenrolled from courses after
                   completing the primary certification path, when the courses were not part
                   of recertification. This has now been fixed, and any user enrolments
                   incorrectly suspended will be restored automatically by the "Clean
                   enrolment plugins" scheduled task. This patch also greatly improves the
                   performance of this task.

    TL-14291       Fixed user unassignment from programs and certifications

                   This patch includes several changes to the way program and certification
                   completion records are handled when users are unassigned. It includes
                   a fix for a problem that could occur when users are reassigned. It also
                   ensures that program and certification completion records are correctly
                   archived when a user is deleted (with the possibility of being undeleted),
                   rather than being left active.

    TL-14309       Fixed missing embedded fallback font causing error when viewing certificate
    TL-14335       Backup annotation no longer tries to write to the temp table it is currently reading from

                   Backup annotation handling was opening a recordset to a temporary table,
                   annotating over the results and writing to the same table while the
                   recordset was still open.
                   This was causing significant performance issues and occasional failures on
                   MSSQL.
                   Only large complex backups would be affected.
                   This change removes the code sequence responsible replacing it with batch
                   handling for the temp table.

    TL-14350       Fixed invalid program due date when a user is assigned with an exception

                   This patch includes automated fixes which can be triggered in the program
                   and certification completion editors to fix affected records.

    TL-14351       Ensured all images in appraisal print previews are responsive
    TL-14371       Added missing use of format_string() in hierarchy filter text
    TL-14387       Changes to Face-to-face notification templates now update unchanged notifications
    TL-14389       Improved the handling of incomplete AJAX requests when navigating away from a page
    TL-14399       Fixed the "Manage searches" button in the Audience view report
    TL-14411       Fixed reportbuilder exports for reports with embedded parameters
    TL-14419       Fixed problems when restoring users to certifications

                   There were some rare circumstances where the incorrect data was being set
                   when a user was reassigned to a certification. The most common problem was
                   that the due date was missing on records that were in the "expired" state.
                   The cause of the various problems has been prevented. Records which have
                   already been affected can be identified using the certification completion
                   checker and corrected using the certification completion editor and/or
                   automated fixes - see TL-14437.

    TL-14426       Fixed dialog scroll when adding "Fixed image" questions to an appraisal
    TL-14437       Added an automated fix for expired certifications missing a due date

                   An automated fix has been added to the certification completion editor.
                   When applied to expired completion records which are missing a due date, it
                   automatically sets the date to the latest certification completion history
                   expiry date which is before the current date. If no appropriate history
                   record is found then the due date must be set manually.

    TL-14447       Fixed double html escaping when searching for course names that include special characters
    TL-14672       Fixed permissions check for taking attendance within Face-to-face sessions

                   Previously it was not allowed to submit Seminar attendance without
                   mod/facetoface:addattendees or mod/facetoface:removeattendees permission.
                   Now mod/facetoface:takeattendance is enough.

    TL-14708       Fixed course id for the notifications when restoring a Face-to-face

API changes:

    TL-14413       Added two new methods to the DML to fetch recordsets and a total count at the same time

                   Two new methods have been added to the DML that allow for a recordset to be
                   fetched and simultaneously a total count returned in single query.
                   The two new methods are:
                   * moodle_database::get_counted_recordset_sql
                   * moodle_database::get_counted_records_sql

Contributions:

    * Eugene Venter at Catalyst NZ - TL-9300, TL-10502
    * Russell England at Kineo US - TL-14144


Release 2.9.19 (22nd May 2017):
===============================


Important:

    TL-14278       Changed mathjax content delivery network (CDN) from cdn.mathjax.org to cdnjs.cloudflare.com

                   cdn.mathjax.org is being shut down

    TL-14327       "Fileinfo" php extension is now required

                   This was previously required but not enforced by environment checks

Security issues:

    TL-14273       Fixed array key and object property name cleaning in fix_utf8() function
    TL-14331       Users are prevented from editing external blog links.
    TL-14332       Capability moodle/blog:search is checked when blog search is applied in browser url request
    TL-14333       Added sesskey checks to the course overview block

Bug fixes:

    TL-12785       Contrained the width of images in Appraisal snapshot print dialogs
    TL-12786       Fixed error when selecting objectives to review in an appraisal

                   When selecting Objectives to review in an appraisal, there is no longer an
                   error when there are only objectives from completed Learning Plans.
                   Objectives from both complete and incomplete Learning Plans are now shown,
                   providing the objectives are assigned to the learner and approved.

    TL-12950       Corrected content for plan status column and filter.
    TL-13968       Ensured that userids are unique when getting enrolled users

                   This was causing a debugging error when checking permissions of users with
                   multiple roles

    TL-14029       Fixed issues with caching requests using the same CURL connection
    TL-14046       Made the course list in user profiles take audience visibility into account
    TL-14128       Fixed duplicate values in location session custom field
    TL-14241       Fixed the inline help for course and audience options on the Totara Connect add client form
    TL-14284       Fixed missing set_url calls within Appraisal review question AJAX scripts
    TL-14292       Fixed typo in certificate module
    TL-14342       Ensured Atto drag & drop content images are responsive by default

Contributions:

    * Kineo UK - TL-14241


Release 2.9.18 (26th April 2017):
=================================


Security issues:

    TL-5678        Fixed sesskey handling within Hierarchy ajax scripts
    TL-13932       Fixed a security issue within TeX notation filtering

                   This fixes a regression introduced through changes made to make TeX
                   notation and MathJax filtering compatible with each other when both were
                   enabled.

                   The original compatibility fix lead to a security hole that could be
                   exploited in any content passed through the TeX filter.
                   The security vulnerability has been fixed, MathJax and TeX will no longer
                   fail over to the other. Sites using both filters should choose one or the
                   other.

Improvements:

    TL-12251       Improved the performance of adding and removing enrolled learning for an audience

                   This change improves the performance of adding and removing enrolled
                   learning by making adjustments to how the process occurs.
                   The changes can be summarised as follows:

                   * When adding one or more courses as enrolled learning to an audience, only
                   the courses that are being added are synchronised. Previously all courses,
                   including already existing courses, were synchronised.
                   * When adding or removing courses from a dynamic audience, an adhoc task is
                   used to offset the processing to the server. This means that changes will
                   happen the next time cron runs and that the user will not be forced to wait
                   for the synchronisation to complete.

    TL-12591       Reportbuilder scheduled report external email address validation now matches on the server and client

    TL-12869       Improved the confirmation message shown when deleting a block
    TL-14011       Lowered memory usage when running PHPUnit tests
    TL-14220       Updated Certificate Authority fallback bundle for Windows servers

Bug fixes:

    TL-4695        Fixed setType error for bulk add attendees form
    TL-12417       Fixed user enrolment into courses via competencies

                   Assigning and unassigning users from programs based on competencies now
                   correctly suspends and unsuspends users from the underlying courses

    TL-12600       Fixed HTML parsing for 'body' and 'manager prefix' fields in Seminar notification templates when the 'enable trusted content' setting is enabled
    TL-12684       Removed quiz report option "all users who have attempted the quiz" when separate group is selected as it does not make sense
    TL-12773       Fixed a bug when setting SCORM attribute values
    TL-12802       Fixed the display of the grade percentage within the Record of Learning reports when max grade is not 100
    TL-12866       Fixed a bug whereby managers could not remove Face-to-face allocations once learners had already self booked
    TL-12873       Fixed help string for report export settings
    TL-12892       Ensured HR Import manages special characters correctly when used with Menu custom user profile fields
    TL-13887       Fixed form parameters when expanding courses within the enhanced course catalog
    TL-13901       Fixed the validation of Face-to-face event custom fields configured to require unique values
    TL-13911       Fixed incorrect availability of certification reports when programs are disabled
    TL-13915       Removed space between filters and content of Report Builder reports in IE

                   TL-12451 introduced a large visual gap between Report Builder filters and
                   the Report Builder content in IE. This fix removes that gap.

    TL-13924       Fixed warnings when viewing Appraisal previews
    TL-13953       Fixed a typo in the Face-to-face activity 'userwillbewaitlisted' string
    TL-14064       Fixed the Record of Learning: Competencies report when Global Report Restrictions are enabled

Contributions:

    * Richard Eastbury at Think Associates - TL-13911


Release 2.9.17 (22nd March 2017):
=================================


Security issues:

    TL-2986        Added checks for the moodle/cohort:view capability to the audience filter in user, course, and program report sources
    TL-12452       Added validation to the background colour setting for TeX notation
    TL-12733       Email self-registration now validates recaptcha first and hides error messages relating to username and email if they exist
    TL-12907       Fixed user preference handling to prevent malicious serialised object attacks

Improvements:

    TL-12366       Improved the usability of the program assignments interface

                   There were some totals in the program assignments interface which could be
                   misleading given that they may not take into account whether the program is
                   active or not and may count users multiple times if they are in multiple
                   assigned groups. The number of assigned learners is now only shown while a
                   program is active (within available from and until dates, if they are
                   enabled).

    TL-12398       Created a new plaintext display class to ensure correct formatting in Report Builder exports

                   A new Report builder display class "plaintext" has been introduced to serve
                   two specific functions:

                   1. Ensure that plaintext columns such as "idnumber" are correctly formatted
                      in Report builder exports to formats such as Excel and ODS.
                   2. To improve the rendering performance of the above columns by avoiding
                       unnecessary formatting applied to text content by default.

    TL-12402       Added a CLI script to automatically fix scheduled reports without recipients

                   Prior to Totara 2.7.2 scheduled reports which were configured without any
                   recipients would be emailed to the creator despite them not being an actual
                   recipient.
                   In Totara 2.7.2 this was fixed and the scheduled report was sent
                   recipients.
                   This change in behaviour left some sites with scheduled reports that were
                   not being sent to the original creator.
                   To aid those affected by this behaviour we have created a script that will
                   find scheduled reports that have no recipients and add the creator of the
                   report as a recipient.
                   To run this report simply execute "php admin/cli/fix_scheduled_reports.php"
                   as the web user on your Totara installation.

    TL-12473       Added "Reset My learning page for all users" button in Default My Learning page layout editor
    TL-12605       The Face-to-face direct enrolment page now shows sign-up custom field values
    TL-12637       Introduced a new capability allowing users to view private custom field data within user reports

                   TL-9405 fixed a bug in user reports in which the users themselves could not
                   see custom field values when the visibility of the custom field was set to
                   "visible to user". In the original code however, while the users themselves
                   could not see the values, their managers could.

                   This patch creates a new capability
                   "totara/core:viewhiddenusercustomfielddata" to allow the code to work like
                   the original but with the fix from TL-9045. Now not only can the users
                   themselves see the values, everyone with the new capability can also do so.

    TL-12662       Ensured users with program management capabilities can always access management interface

                   Previously, users could have capabilities to modify various aspects of
                   programs, such as assigning users. They could access the relevant page by
                   entering the correct url but could not access them via the interface if
                   they did not have 'totara/program:configuredetails'. That capability was
                   only necessary to use the 'Edit' tab and should not prevent other access.

                   Users may now see the 'Edit program details' button when they have any
                   program edit capabilities for a given program. They may also access the
                   overview page via that button and the tabs they have access to from there.

    TL-12689       Removed 'View' button for appraisal stages without any visible pages

Bug fixes:

    TL-11255       Fixed incorrect indication that manager must complete an appraisal after completion
    TL-12451       Fixed the display of graphs within Report Builder when using the sidebar filter
    TL-12615       Stopped managers receiving Program emails for their suspended staff members
    TL-12621       Fixed navigation for multilevel SCORM packages
    TL-12643       Fixed guest access throwing error when using the enhanced catalog
    TL-12645       Fixed cache warnings on Windows systems occurring due to fopen and rename system command
    TL-12696       Ensured that read only evidence displays the "Edit details" button only when the user has the correct edit capability
    TL-12721       Fixed misspelt URL when adding visible learning to an audience
    TL-12734       Fixed how room conflicts are handled when restoring a Face-to-face activity
    TL-12739       Improved performance when using the progress column within a Certification overview report
    TL-12762       Prevented appraisal messages from being sent to unassigned users
    TL-12774       Added validation to prevent invalid Assignment grade setting combination

                   You must now select a Grade Type or the default Feedback Type if you want
                   to enable the 'Student must receive a grade to complete this activity'
                   completion setting.

    TL-12787       Added new capability: totara/program:markcoursecomplete

                   From 2.9.0 onwards, if a user had the capability moodle/course:markcomplete
                   set to allow in course or system contexts, they were able to mark courses
                   complete when viewing a users program page (accessed via required
                   learning). This was incorrect use of this capability, as that action would
                   only be valid if marking complete was enabled in course completion
                   criteria. This capability no longer allows marking complete via the program
                   page.

                   To allow for use cases described above, a new capability,
                   totara/program:markcoursecomplete, was added. This will allow marking a
                   course complete on a user's program page, regardless of course completion
                   criteria. This capability is checked in the course and system contexts. The
                   Site Manager role will receive this capability following upgrade.

    TL-12795       Fixed 'Program Name and Linked Icon' report column when exporting

                   The "Program Name and Linked Icon" report column, contained in several
                   report sources, now only contains the program name when exporting. Also,
                   the "Record of Learning: Certifications" report source had two columns
                   named "Certification name". One of them has now been renamed to
                   "Certification Name and Linked Icon", and likewise only contains the
                   certification name when exporting.

    TL-12798       Fixed the display of description for personal goals on the My Goals page


Release 2.9.16 (27th February 2017):
====================================


Security issues:

    TL-6810        Added sesskey checks to the programs complete course code

Improvements:

    TL-12359       Fixed the type of notifications used when signing up to a Face-to-face session

Bug fixes:

    TL-9264        Fixed a fatal error encountered in the Audience dialog for Program assignments
    TL-10082       Fixed the display of description images in the 360° Feedback request selection list
    TL-11230       Fixed disabled program course enrolments being re-enabled on cron

                   The clean_enrolment_plugins_task scheduled task now suspends and re-enables
                   user enrolments properly

    TL-12436       Fixed the Face-to-face backup and restore to correctly process user sign up status
    TL-12458       Fixed the visibility permissions for images in the event details field
    TL-12463       Prevented the submission of text longer than 255 characters on Appraisal and 360° Feedback short text questions
    TL-12464       Fixed a HTML validation issue on the user/preferences.php page
    TL-12596       Reverted change which caused potential HR Import performance cost

                   A change in TL-12262 made it likely that imported Positions and
                   Organisations in a Hierarchy framework would be processed multiple times,
                   rather than just once each. No data problems were caused, but the
                   additional database operations were unnecessary. That change has been
                   reverted.

    TL-12603       Course reminders are no longer sent to unenrolled users

                   Email reminders for course feedback activities were previously being sent
                   to users who were unenrolled or whose enrolments had been suspended.

    TL-12606       Fixed resending certification course set messages

                   The course set Due, Overdue and Completed messages were only being sent the
                   first time that they were triggered on each certification path. Now, they
                   will be triggered when appropriate on subsequent recertifications,
                   including after a user has expired.

    TL-12616       Fixed the Certification window open transaction log entry

                   It was possible that the Certification window opening log entry was being
                   recorded out of order, could be recorded even if the window open function
                   did not complete successfully, and could contain incorrect data. These
                   problems have now been fixed by splitting the window open log entry into
                   two parts.

    TL-12680       Made the user menu hide languages when the "Display language menu" setting is disabled

Contributions:

    * Eugene Venter, Catalyst - TL-12596 & TL-12436


Release 2.9.15 (25th January 2017):
===================================


Security issues:

    TL-10773       Added safeguards to protect user anonymity when providing feedback within 360 Feedback
    TL-12322       Improved validation within the 360° Feedback request confirmation form

                   Previously, if a user manipulated the HTML of the form for confirming
                   requests for feedback in 360° Feedback, they could change emails to an
                   invalid format or, in some cases, alter requests they should not have
                   access to.
                   Additional validation following the submission of the confirmation form now
                   prevents this.

    TL-12327       Added a setting to prevent the malicious deletion of files via the Completion Import tool

                   When adding completion records for courses and certifications via CSV, a
                   pathname can be specified instead of uploading a file. After the upload
                   occurs, the target file is deleted. Users with the capability to upload
                   completion records may have been able to delete other files aside from
                   those related to completion import. In some cases they were also being
                   shown the first line of the file. By default, only site managers have the
                   capability to upload completion records.
                   Additionally in order to exploit this the web server would need to have
                   been configured to permit read/write access on the targeted files.

                   There is now a new setting ($CFG->completionimportdir) for specifying how
                   the pathname must begin in order to add completion records with this
                   method. This setting can only be added via the config.php file. When a
                   directory is specified in this setting, files immediately within it, as
                   well as within its subdirectories, can be used for completion import.

                   If the setting is not added, completion imports can no longer be performed
                   via this method. They can still be performed by uploading a file using the
                   file picker.

    TL-12411       MDL-56225: Removed unnecessary parameters when posting to a Forum

                   Previously it was possible to maliciously modify a forum post form
                   submission to fake the author of a forum post due to the presence of a
                   redundant input parameter and poor forum post submission handling.
                   The unused parameter has been removed and the post submission handling
                   improved.

    TL-12412       MDL-57531: Improved email sender handling to prevent PHPMailer vulnerabilities from being exploited
    TL-12413       MDL-57580: Improved type handling within the Assignment module

                   Previously loose type handling when submitting to an assignment activity
                   could potentially be exploited to perform XSS attacks, stricter type
                   handling has been implemented in order to remove this attack vector.

Improvements:

    TL-10849       Improved the language strings used to describe Program and Certification exception types and actions
    TL-12261       Improved code exception validation in several unit tests

Bug fixes:

    TL-10416       Fixed an error when answering appraisal competency questions as the manager's manager or appraiser
    TL-11150       Fixed an undefined property error in HR Import on the CSV configuration page
    TL-11270       Fixed Course Completion status not being set to "Not yet started" when removing RPL completions

                   Previously, when you removed RPL completion using the Course administration
                   -> Reports -> Course completion report, it would set the record to "In
                   progress", regardless of whether or not the user had actually done anything
                   that warranted being marked as such. If the user had already met the
                   criteria for completion, the record would not be updated until the
                   completion cron task next ran.

                   Now, the records will be set to "Not yet started". Reaggregation occurs
                   immediately, and may update the user to "In progress" or "Complete"
                   depending on their progress. Note that if a course is set to "Mark as In
                   Progress on first view" and the user had previously viewed the course but
                   made no other progress, then their status will still be "Not yet started"
                   after reaggregation.

    TL-12256       Prevented an incorrect redirect occurring when dismissing a notification from within a modal dialog
    TL-12262       Fixed problem removing manager when only importing the manager column in HR Import

                   Previously, if the only position assignment column imported was for the
                   manager, and the value was an empty string, the value was ignored rather
                   than removing the manager. Now, as happens when your import includes other
                   position assignment columns, if the value is an empty string then it will
                   remove the manager from the user's primary position assignment.

    TL-12263       Fixed an issue with the display of assigned users within 360° Feedback

                   The assigned group information is no longer shown for 360° Feedback in the
                   Active or Closed state. In these states, the pages always reflect actual
                   assigned users.

    TL-12277       Corrected an issue where redirects with a message did not have a page URL set
    TL-12287       Ensured Hierarchy 'ID number' field type is set as string in Excel and ODS format exports to avoid incorrect automatic type detection
    TL-12297       Removed options from the Reportbuilder "message type" filter when the corresponding feature is disabled
    TL-12299       Fixed an error on the search page when setting Program assignment relative due dates
    TL-12301       Fixed the replacement of course links from placeholders in notifications when restoring a Seminar

                   Previously when a course URL was embedded in a seminar notification
                   template, it would be changed to a placeholder string when the seminar was
                   backed up. Restoring the seminar would not change the placeholder back to
                   the proper URL. This fix ensures it does.

    TL-12303       Fixed the HTML formatting of Seminar notification templates for third-party emails
    TL-12311       Fixed the "is after" criteria in the "Start date" filter within the Course report source

                   The "is after" start date filter criteria now correctly searching for
                   courses starting immediately after midnight in the users timezone.

    TL-12316       Added missing include in Hierarchy unit tests covering moving custom fields
    TL-12325       Fixed the Quick Links block to ensure it decodes URL entities correctly
    TL-12339       Reverted removal of style causing regression in IE

                   TL-11341 applied a patch for a display issue in Chrome 55.
                   This caused a regression for users of Edge / IE browsers making it
                   difficult and in some cases impossible to click grouped form elements.
                   The Chrome rendering bug has since been addressed.

    TL-12344       Fixed an error message when updating Competency scale values
    TL-12352       Fixed a bug in the cache API when fetching multiple keys having specified MUST_EXIST

                   Previously when fetching multiple entries from a cache, if you specified
                   that the data must exist, in some circumstances the expected exception was
                   not being thrown.
                   Now if MUST_EXIST is provide to cache::get_many() an exception will be
                   thrown if one or more of the requested keys cannot be found.


Release 2.9.14.1 (22nd December 2016):
======================================


Bug fixes:

    TL-12309       Fixed the display of aggregated questions within Appraisals

                   This was a regression from TL-11000, included in the 2.9.14 and 9.2
                   releases.
                   The code in that fix used functionality first introduced in PHP 5.6, and
                   which is not compatible with PHP5.5.
                   The effect of the resulting bug was purely visual.
                   We've now re-fixed this code in order to ensure it is compatible with all
                   supported versions of PHP.


Release 2.9.14 (21st December 2016):
====================================


Important:

    TL-11333       Fixes from Moodle 2.9.9 have been included in this release

                   Information on the issues included from this Moodle release can be found
                   further on in this changelog.

Security issues:

    TL-11133       Fixed Face-to-face activities allowing sign up even when restricted access conditions are not met
    TL-11194       Fixed get_users_by_capability() when prohibit permissions used
    TL-11335       MDL-56065: Fixed the update_users web service function
    TL-11336       MDL-53744: Fixed question file access checks
    TL-11338       MDL-56268: Format backtrace to avoid displaying private data within web services

Improvements:

    TL-10971       Improved Feedback activity export formatting

                   The following improvements were made to the exported responses for feedback
                   activities:
                   * Newlines in Long Text responses are no longer replaced with the html
                   <br/> tag
                   * The text wrap attribute is set for all response cells
                   * Long text, Short text and Information responses are no longer exported in
                   bold

    TL-11056       Added phpunit support for third party modules that use "coursecreator" role

Bug fixes:

    TL-4912        Fixed the missing archive completion option in course administration menu
    TL-6899        Fixed the display of user's names in Face-to-face attendees add/remove dialog to use user identity settings

                   The showuseridentity setting can now be used to make user names more
                   distinct (when 2 people have the same name and email address) in the
                   Face-to-face attendees add/remove dialog.

    TL-7666        Images used in hierarchy custom fields are now displayed correctly when viewing or reporting on the hierarchy
    TL-9500        Fixed "View full report" link for embedded reports in the Report table block
    TL-10101       Removed unnecessary permission checks when accessing hierarchies
    TL-10953       Fixed Learning Plans using the wrong program due date

                   Previously, given some unlikely circumstances, when viewing a program in a
                   learning plan, it was possible that the program due date could have been
                   displaying the due date for one of the course sets instead.

    TL-11000       When calculating the Aggregate rating for appraisal questions, not answered questions and zero values may now be included in aggregate calculations

                   Two new settings have been added to Aggregate rating questions within
                   Appraisals.
                   These can be used in new aggregate rating questions to indicate how the
                   system must handle unanswered questions, as well as questions resulting in
                   a zero score during the calculations.

    TL-11063       Fixed a PHP error in the quiz results statistics processing when a multiple choice answer has been deleted
    TL-11126       Fixed HR Import data validation being skipped in some circumstances

                   If the source was an external database, and the first record in the import
                   contained a null, then the data validation checks on that column were being
                   skipped. This has been fixed, and the data validation checks are now fully
                   covered by automated tests.

    TL-11129       Fixed url parameters not being added in pagination for the enrolled audience search dialog
    TL-11130       Fixed how backup and restore encodes and decodes links in all modules
    TL-11137       Courses, programs and certifications will always show in the Record of Learning if the user has made progress or completed the item

                   The record of learning is intended to list what the user has achieved.
                   Previously, if a user had completed an item of learning, this may sometimes
                   have been excluded due to visibility settings (although not in all cases
                   with standard visibility). The effect of audience visibility settings and
                   available to/from dates have been made consistent with that of standard
                   visibility. The following are now show on their applicable Record of
                   Learning embedded reports, regardless of enrolment status and current
                   visibility of the item elsewhere.

                   Courses:  Any course where a user's status is greater than 'Not yet
                   started'. This includes 'In-progress' and 'Complete'.

                   Programs: Any program where the user's status is greater than 'Incomplete'.
                   In existing Totara code, this will only be complete programs. This applies
                   to the status of the program only and does not take into account program
                   course sets. If just a course set were complete, and not the program, the
                   program would not show on the Record of Learning if it should not otherwise
                   be visible.

                   Certifications: Any certification where the user's status is greater than
                   'Newly assigned'. This includes 'In-progress', 'Certified' and 'Expired'.

    TL-11139       Fixed report builder access permissions for the authenticated user role

                   The authenticated user role was missed out when a report's access
                   restriction was "user role in any context" - even if this role was ticked
                   on the form. The fix now accounts for the authenticated user.

    TL-11148       Fixed suspended course enrolments not reactivating during user program reassignment
    TL-11200       Fixed the program enrolment plugin which was not working for certifications when programs had been disabled
    TL-11203       Allowed access to courses via completed programs consistently

                   Previously if a user was complete with a due date they could not access any
                   courses added to the program after completion, but users without a due date
                   could access the new courses. Now any user with a valid program assignment
                   can access the courses regardless of their completion state.

    TL-11209       Fixed errors in some reports when using report caching and audience visibility
    TL-11216       Fixed incorrect use of userid when logging a program view from required learning
    TL-11237       Deleting unconfirmed users no longer deletes the user record

                   Previously when unconfirmed users were deleted by cron the user record was
                   deleted from the database immediately after the standard deletion routines
                   were run.
                   Because it is possible to include unconfirmed users in dynamic audiences
                   they could end up with traces in the database which may not be cleaned up
                   by the standard deletion routines.
                   The deletion of the user record would then lead to these traces becoming
                   orphaned.
                   This behaviour has been changed to ensure that the user record is never
                   deleted from the database, and that user deletion always equates to the
                   user record being marked as deleted instead.

    TL-11239       Fixed type handling within the role_assign_bulk function leading to users not being assigned in some situations
    TL-11253       Fixed incorrect circular management detection if a user has a missing id number
    TL-11272       Fixed inaccessible files when viewing locked appraisal questions
    TL-11304       Fixed problems in HR Import invalid username sanity check
    TL-11329       Fixed program course sets being marked complete due to ignoring "Minimum score"

                   When a program or certification course set was set to "Some courses" and
                   "0", the "Minimum score" was being ignored. Even if a "Minimum score" was
                   set and was not reached, the course set was being marked complete. Now, if
                   a "Minimum score" is set, users will be required to reach that score before
                   the course set is marked complete, in combination with completing the
                   required number of courses.

                   If your site has a program or certification configured in this way, and you
                   find users who have been incorrectly marked complete, you can use the
                   program or certification completion editor to change the records back to
                   "Incomplete" or "Certified, window is open". You should then wait for the
                   "Program completions" scheduled task (runs daily by default) to calculate
                   which stage of the program the user should be at.

    TL-11331       Fixed HTML and multi language support for general and embedded reports
    TL-11341       Fixed report builder filter display issue in chrome 55

                   Previously there was a CSS statement adding a float to a legend which
                   appears to be ignored by most browsers. With the release of chrome 55, this
                   style was being interpreted.

    TL-12244       Fixed 'Allow extension request' setting not being saved when adding programs and certifications
    TL-12246       Fixed MSSQL query for Course Completion Archive page

Miscellaneous Moodle fixes:

    TL-11266       MDL-52317: Improved display of large images when Atto-supplied alignments are effective.

                   Improved display of large images on Course page when Atto-supplied
                   alignments are effective by removing horizontal scrollbars.

    TL-11337       MDL-51347: View notes capability is now checked using the course context
    TL-11339       MDL-55777: We now check libcurl version during installation
    TL-11426       MDL-56748: Fixed a memory leak when resetting MUC

Contributions:

    * Russell England at Kineo USA - TL-11239


Release 2.9.13 (22nd November 2016):
====================================


Important:

    TL-11157       Fixed data loss bug when learning plans are deleted under certain conditions

                   This bug occurs under very specific circumstances.

                   Due to the structure of the repository table involved, it is possible to
                   have relationship data from different learning plans and even different
                   components within the same learning plan co-existing within the same table.
                   Originally, the system deleted relationships between learning plan
                   components (e.g. course and objectives) using just the component identifier
                   e.g. objective ID.

                   However, in very rare situations, it is possible for the table to hold
                   values from unrelated components which use the same identifier. When the
                   system deleted a component using this identifier value alone *all*
                   components associated with it were removed. Hence the data loss.

                   The system now checks component type in addition to ID to prevent this
                   happening.

Security issues:

    TL-5178        Added a missing sesskey check to feedback/assignments.php
    TL-6615        Added a check for HTTP only cookies to the security report

                   The HTTP only cookies setting restricts access to cookies by client side
                   scripts in supported browsers making it more difficult to exploit any
                   potential XSS vulnerabilities.

    TL-10752       Implemented additional checks within the Appraisal review ajax script

Improvements:

    TL-9730        Allowed assign_user_position to manage roles in tests

                   Previously when running tests, role assignments had to be set up manually,
                   rather than using assign_user_position. Now, this function can set up the
                   roles during tests. This will improve testing, as the roles can now be set
                   up in tests using the same function that is used on live sites, rather than
                   having to simulate that functionality, avoiding possible discrepancies
                   between live code and test setup.

    TL-10203       Improved efficiency when importing users that include dropdown menu profile field data

                   A significant performance gain has been made when importing users through
                   HR Import on sites that use drop down menu profile custom fields.
                   The import process should now run much faster than before.

    TL-10627       Improved appraisal snapshot PDF rendering
    TL-10681       Added an environment test for mbstring.func_overload to ensure it is not set

                   Multibyte function overloading is not compatible with Totara.

    TL-10731       Added setting to allow limiting of feedback reminders sent out

                   A new setting has been added, 'reminder_maxtimesincecompletion', which can
                   be used to limit the number of days after course completion in which
                   feedback activity reminders will be sent. This may be used to prevent
                   reminders being sent for historic course completions after they are
                   imported via upload.

    TL-10782       Face-to-face direct enrolment instances within a course can now be manually removed when no longer wanted
    TL-10909       Improved wording of course activity reports visibility setting help
    TL-10917       Improved the performance of admin settings for PDF fonts
    TL-10965       Improved program assignments to recognise changes in hierarchies related to 'all below' assignments

                   Previously, if a change was made to a lower level of a hierarchy then the
                   change did not trigger deferred program assignment update. Instead, the
                   change would not be applied until the program user assignments cron task
                   was run.
                   Now, the change immediately flags the related program for update and will
                   be processed by the deferred program assignments task.

    TL-11001       Mark completion reaggregated after each record is processed

                   Previously, completion_regular_task would first process all records which
                   had a reaggregate flag greater than one, then finally set the flags on all
                   the records to 0. Now, the reaggregate flag is set to 0 after each record
                   is processed.

Bug fixes:

    TL-1944        Corrected move left / right feature in the Face-to-face activity menu on the course page
    TL-7752        Fixed problems with program enrolment messages

                   Program enrolment and unenrolment messages are now resent each time a user
                   is assigned or unassigned, rather than just the first time either of those
                   events occur.
                   All program messages are now covered by automated tests.

    TL-9301        Fixed Face-to-face event functionality when the cancellationnote default custom field has been deleted
    TL-9993        Fixed the display of images within textareas in Learning Plans and Record of Learning Evidence
    TL-9994        Stopped the actions column from being included when exporting Other Evidence report in the Record of Learning
    TL-10108       Prevented program due messages being sent when the user is already complete

                   This fix affects several messages: program due, program overdue, course set
                   due and course set overdue. In programs and certifications, just before one
                   of these messages is sent, a check is performed to ensure that the user
                   hasn't completed the program or certification in the mean time.

    TL-10213       Reduced the number of joins in appraisal details report with scale value questions

                   Multi-choice, single answer questions no longer need a join, while
                   multi-choice, multi-select questions now require just one join per role per
                   question (down from two).
                   A consequence of this change is that multi-choice columns will no longer be
                   sorted alphabetically in this report. Instead, if you sort a multi-choice
                   column, the records will be shown in the same order as the options are
                   defined and as they appear when completing the appraisal.
                   MySQL is inherently limited to 61 joins, but now more questions can be
                   added before this limit is reached.

    TL-10360       Competency completion calculations now correctly look at previously completed courses

                   Courses completed before the last time a competency is modified are now
                   correctly considered for competency assignment

    TL-10819       Added code to re-run an upgrade step to delete report data for deleted users

                   The issue was caused by TL-8711 and fixed by TL-10804

    TL-10837       Added workaround for iOS 10 bug causing problems with video playback
    TL-10891       Fixed overactive validation of Face-to-face cutoff against dates

                   Previously when editing a Face-to-face event in which the current date was
                   already within the cutoff period, if you attempted to edit the event you
                   could not save because the cutoff was too close, even in situations when
                   you were not changing the dates or the cutoff.
                   Cutoff validation is now only applied when the dates are changing, or when
                   the cutoff period is changing.

    TL-10901       Fixed missing course events from calendar when viewing all

                   Previously, many events were being excluded from the calendar when being
                   viewed by a user with the capability, moodle/calendar:manageentries, while
                   the site setting, 'calendar_adminseesall' was turned on. The process of
                   selecting events from courses to show in the calendar to fix this has been
                   improved. However, for performance reasons, there is still a limit on how
                   many courses have events shown in the calendar. This limit has been set at
                   50 courses by default. The limit can be adjusted using a new setting,
                   calendar_adminallcourseslimit. See config-dist.php for more information on
                   that setting.

    TL-10910       Fixed required permissions for appraisals aggregate questions
    TL-10955       Fixed database error when generating a report with search columns
    TL-10972       Deleting a Face-to-face now correctly removes orphaned notification records
    TL-10979       Ensured certification messages can be resent on subsequent recertifications

                   This patch ensures that all applicable certification messages are reset
                   when a user's recertification window opens, allowing them to be triggered
                   again for that user.

    TL-10998       Removed inaccessible options in Program Administration block
    TL-11020       Caused program completion to be checked on assignment

                   Now, when users are assigned to programs and certifications, completion
                   will immediately be calculated. If the user has already completed the
                   courses required for program completion or certification, they will be
                   marked complete. Previously, the user would have had to wait for the
                   Program Completions scheduled task to run, which occurs once each night by
                   default.

                   This change also causes the first course set completion record to be
                   correctly created. Previously, it was not created until the first course
                   set was completed. Because it is being created at the correct time, course
                   set due and overdue messages related to the first course set will now be
                   correctly triggered.

    TL-11047       Fixed an incorrect capability check made when checking whether a user can manage dashboards
    TL-11070       Fixed disabled Appraisal message entry fields
    TL-11102       Fixed a timing issue in totara_core_webservice PHPUnit tests
    TL-11118       Fixed the display of the Declare Interest button for past Face-to-face sessions

API changes:

    TL-9726        Added the system requirements for upgrades to Totara 10dev

Contributions:

    * Davo Smith at Synergy Learning - TL-10917
    * Jo Jones at Kineo - TL-11157


Release 2.9.12 (19th October 2016):
===================================


Important:

    TL-9223        Added new config.php setting $CFG->completionexcludefailures to keep previous course completion behaviour of excluding failed activities

                   Prior to Totara 2.9 if you configured a course to require activity completion, and that activity had a passing grade
                   defined in the gradebook, then the course would only consider the activity complete if the user had also achieved a
                   passing grade despite the activity being marked complete for the user.
                   Totara 2.9.0 introduced a new course completion behaviour where activity completions marked as complete but failed are
                   now considered complete when aggregating course completion.
                   This lead to an unexpected change in behaviour for some sites when upgrading to Totara 2.9.

                   This improvement adds a config.php setting $CFG->completionexcludefailures that can be used to revert this change of
                   behaviour.
                   Please note that in order to prevent completion changes this setting must be added to config.php before upgrade from
                   Totara 2.7 or right after a new installation.

                   This setting should not be changed if you have already upgraded your site to Totara 2.9.0 through to 2.9.11, because
                   there are new settings in activities that can be used to get similar behaviour and the already changed completions
                   cannot be reverted.

Security issues:

    TL-10339       Prevented dialog item information from being accessible to any logged in users

                   A logged in user was able to access some of the AJAX scripts used by program assignment dialogs and view information
                   such as user names and lists of positions. In some cases they would need to enter extra information into the URL such as
                   user ids to see this information. Information such as position names will have only been viewable if the user had the
                   necessary permissions.  However, these AJAX scripts are now only accessible if the user has permission to edit a given
                   program. A program id parameter is now required by these AJAX scripts as part of this change.

                   This change also involves ids of users or groups (such as audiences) that are assigned to a program being sent within
                   the parameters of a POST request rather than in the url of a GET request.

    TL-10481       Extra capability checks added to ajax scripts

                   Capability checks were added to the AJAX script used for the goal selection dialog as well as a Report Builder filter
                   script.

Improvements:

    TL-7645        Added warning notices about scheduled report content

                   It is important to note that when a user authors a scheduled report, the report content depends on what that specific
                   user is allowed to see. The content does not depend on what the report's recipients are allowed to see. Therefore with
                   scheduled reports, there is a chance that the report could be empty (because the author did not have the correct viewing
                   rights) or that it contains data that others are not supposed to see.

                   This has always been the behavior in Totara and this issue implementation just adds some clarification text to the
                   scheduled report definition page and the recipient email to highlight this.

    TL-7846        Added a new option to apply dynamic audience membership changes in the background

                   A new setting has been added which, when enabled, shifts processing of audience membership and enrolled learning changes
                   to cron when the user approves changes to dynamic audience rules.
                   Prior to this setting, all changes were applied immediately when a user clicked the button. If the change affected many
                   users, it could leave the user waiting for the page to load for several minutes or longer.
                   On sites in which audience memberships can grow into the thousands, and which also use the required learning
                   functionality, we recommend this setting be enabled, as it will provide their users with a better experience.

    TL-10312       Hid certification due date and renewal status within Report Builder reports when they are not applicable
    TL-10342       Show both certification paths to non-assigned users
    TL-10453       Added basic support for built-in PHP Development server

                   PHP's built-in web server can be now used by developers for basic testing purposes. It is not intended for use on any
                   kind of production server or in public networks.

                   The major limitation is that the server is single threaded, which is incompatible with some advanced functionality. Any
                   code that accesses the server via CURL may end up in dead-lock.

    TL-10496       Updated npm modules used from Grunt to produce consistent CSS and JS in stable branches, backported Grunt to 2.5 and 2.6 branches
    TL-10717       HR Import unit tests no longer require special configuration for external databases
    TL-10720       Added text clarifying behaviour of "Force password change for new users" option for generated passwords in HR import

                   In HR Import if a password is not specified for a new user then they are given a generated password. The user will be
                   forced to change this because of security concerns around emailing passwords. The new text clarifies this behaviour so an
                   admin will know this when the option is being changed.

    TL-10740       Added a cautionary disclaimer to the DB migration admin tool interface

Bug fixes:

    TL-8212        Backported various fixes for form dependencies
    TL-8817        Disabled "sort by" for concatenated columns in the Report Builder
    TL-9519        Corrected ordering when sorting by statistics columns in user report source

                   The statistics columns, which showed data such as courses completed for a user, were sorting zero counts last when the
                   sort was ascending and vice versa. This only occurred in sites using PostgreSQL and has now been corrected.

    TL-9719        Sorting on Required learning Programs and Certifications columns fixed
    TL-9763        Navigation and Administration blocks position are no longer reset when users customise their dashboards
    TL-10017       Prevented adding Totara Connect client to the same server
    TL-10066       Resetting course completion will now unlock assignment submissions
    TL-10073       Made selected and unselect links hide as expected on multiple choice questions
    TL-10088       Fixed pagination within the Totara Report block
    TL-10178       Fixed the validation of completion dates in course completion uploads
    TL-10326       Fixed error when exporting a report where sorted columns had been removed
    TL-10334       Fixed completion based on other course not always triggering

                   Previously, it was possible that a completion criteria based on another course could be left incomplete, even when the
                   dependent course was complete, when using course completion upload. Now, \core\task\completion_cron_task checks if any
                   records need to be marked complete.

    TL-10603       Made the automatic addition of goals to an appraisal question work for users that can view but not answer the question
    TL-10631       Prevented the add plan button showing when there are no Learning Plan templates with create permissions
    TL-10656       Fixed course access restrictions in IE8
    TL-10675       Removed Autosave from fixed text feedback360 and appraisal questions
    TL-10689       Fixed visibility of certification/program membership source in embedded report list
    TL-10692       Fixed automatic language detection in Safari browser
    TL-10716       Fixed a regression with coursename columns in the Program and Certification overview reports
    TL-10718       Fixed SOAP and XML-RPC protocol failure of web services
    TL-10733       Allowed timestamps to be imported into date and datetime profile fields via HR Import
    TL-10735       Fixed regression introduced by HR import circular reference tests
    TL-10783       Ensured prog table is always used in program completion history reports

                   When a user viewed their program completion history, in some cases the necessary database tables were not being included
                   when generating the data. This would cause an error for some users.

    TL-10786       Fixed WebDAV response encoding check inconsistencies
    TL-10803       Fixed spelling in Totara timezone fix tool
    TL-10804       Fixed upgrade problem with report builder schedules for deleted users
    TL-10856       Fixed the tinyMCE format selector in appraisals
    TL-10857       Prevented non-assigned users from seeing launch button in programs
    TL-10860       Ensured user profile field defaults are shown on the signup page
    TL-10869       Fixed password reset functionality when using loginhttps setting
    TL-10924       Fixed profile field warnings in Reportbuilder graphs


Contributions:

    * Eugene Venter at Catalyst NZ - TL-10735


Release 2.9.11 (20th September 2016):
=====================================


Important:

    TL-8675        Improvements to certification completion import

                   There were several bugs and unexpected behaviours in the import
                   certification completion module. This was often compounded by the confusion
                   about how the "Override" option was supposed to work.

                   To solve these problems, major changes were required. The internal
                   processes have been completely rewritten, allowing the result of importing
                   records to be clearly defined. Detailed logs are recorded in the
                   certification completion transaction logs.

                   To facilitate this, the "Override" option has been removed. To reduce
                   confusion and allow flexibility, it was replaced with a new setting called
                   "Import action" which has three possible settings; "Save to history",
                   "Certify uncertified users" and "Certify if newer". The old "Override off"
                   maps most closely to "Save to history", while "Override on" maps most
                   closely to "Certify if newer". Detailed help has been included for these
                   options in a popup, clearly explaining what will happen given any
                   combination of input record and existing data.

                   While "bulk" database transactions were maintained and improved, it is
                   possible that this change could lead to an increase in import processing
                   times. Most notably, user assignments are now being properly processed
                   during import, which could increase running time when importing a large
                   number of records for users who are not already assigned. This can be
                   avoided by assigning the users to the certification first, making sure to
                   wait for "deferred" user assignments to finish being processed by the
                   scheduled task, before importing the completion records.

                   Course completion import was not affected by this change.

    TL-9717        Prevent circular management structures being created using HR Import

                   TL-7902 prevented circular management structures being created using the
                   position assignments form. This patch enforces the same rules for data
                   imported using HR import.

                   If you attempt to import users with management structures that would lead
                   to circular references, all users forming the circular reference will fail
                   to import with a notice explaining why.

    TL-10487       Inclusion of Moodle 2.9.8

                   Please note not all changes included in Moodle 2.9.8 were included in this
                   release.
                   Specifically MDL-49026 was not included as we feel a more complete solution
                   can be found, TL-10488 will be used to find that complete solution.

Security issues:

    TL-10044       Removed unnecessary sesskey param when managing hierarchies

                   The sesskey param was previously passed on hierarchy management actions,
                   including those that had confirmation steps.
                   The sesskey is now only added when actually performing the action, and all
                   actions have been confirmed to redirect.
                   This ensures that the sesskey is never exposed unnecessarily when managing
                   hierarchies.

    TL-10355       Fixed information disclosure within Feedback 360 responses

                   Previously one of the Feedback 360 AJAX scripts could be used to test which
                   users had responded to a Feedback activity due to insufficient capability
                   checks.
                   Capability checks are now applied correctly and the output of the script
                   has been normalised so that it can no longer be used to test if a user has
                   responded.

    TL-10435       Capability checks when changing hierarchy item types are now explicit

                   Prior to this update access control when changing a hierarchy item type was
                   carried out by the admin setting page capabilities. This allowed a user
                   with only the capability to manage frameworks to change item types.
                   The totara/hierarchy:update capability is now explicitly checked when
                   changing the type of a hierarchy item.

    TL-10463       Applied stricter type validation when managing custom fields

                   Previously when creating, or editing custom fields it was possible to
                   manipulate the form markup and exploit the loose validation to execute
                   exploits.
                   All custom field input types have been reviewed and much stricter type
                   validation is now in place to ensure that incoming data is stringently
                   cleaned.

    TL-10489       Forgotten password workflow no longer exposes the token via headers

                   Previously if the theme introduced any external links on all pages, then
                   during the forgotten password process if the user followed these links the
                   token used to reset their password would be present in the referrer
                   information sent to the external page.
                   The token is no longer masked through a redirect on the initial request,
                   and is no longer exposed via referrer information.

Improvements:

    TL-9426        Program assignments with due date based on first login will be assigned immediately

                   Previously, if you assigned users to a program or certification and set
                   their due date to "within N days of first login" then the user assignment
                   and program and certification completion records were not being created
                   until the user first logged in. Now, these records are created immediately,
                   and will be updated with a due date when the user first logs in. This is
                   consistent with adding a user with no due date criteria and later adding
                   the "first login" criteria. Note that users who previously had been
                   assigned and were immediately given the "first login" criteria were not
                   showing in completion reports until they first logged in - now they will be
                   included in reports immediately. Previous report behaviour can be achieved
                   by using the "User First Access" report filter.

    TL-9491        Enhanced SCORM report source to use additional tracking fields
    TL-10161       Added accessibility text to action menus
    TL-10358       Deleted unused test course backup file
    TL-10469       Stopped duplicate log entries being created when creating an objective within a plan

Bug fixes:

    TL-8803        Fixed rules for first/last log in dates in dynamic audiences

                   This fixes an issue where users who have never logged in are incorrectly
                   included in dynamic audiences with a single rule, of the type first log in,
                   or last log in.
                   Users who have never logged in are now correctly excluded.

                   Please note this may lead to audience membership changes if you have any
                   dynamic audiences with a single rule, of the type first log in, or last log
                   in.

    TL-9275        Fixed the variable translations for course reminder templates
    TL-9405        Fixed the visibility of user profile custom fields in user reports
    TL-9431        Fixed the formatting of Report Builder titles when exporting to Excel
    TL-9480        Always reset activity grades when course completion is archived

                   Previously, when course completion was archived (due to certification
                   window opening, or by using the "Completions archive" link), it was
                   possible that under some specific circumstances activity grades were not
                   being reset, possibly leading to unwanted re-completion of the activity,
                   course and/or certification. Now, activity grades will always be reset, in
                   all activities, including custom activities. Activities which implement the
                   "_archive_completion" function are no longer required to
                   reset grades themselves, although they may continue to reset grades if they
                   do so already.

    TL-9490        Fixed the pagination of content when viewing a category
    TL-9512        Fixed incorrect uniqueness checks on empty user custom profile fields
    TL-9701        Report builder graph legend now sizes dynamically to better accommodate its content
    TL-9734        Corrected the "is equal to" proficiency filter in Competency Status report
    TL-9776        Corrected the string used by the "status" filter in Program Membership reports
    TL-9793        Fixed dimming of course names in course overview block when audience visibility is on
    TL-9801        Fixed incorrect API call when upgrading dashboards
    TL-9806        Fixed undefined event property when assigning goals to a hierarchy item
    TL-9889        Fixed undefined property allowduplicatedemails warning on HR import user CSV page
    TL-10033       Fixed program course sets set to "Some courses" and "0"
    TL-10088       Fixed pagination within the Totara Report block
    TL-10116       Fixed Face-to-face notification templates when manager copy prefix was missing
    TL-10181       Site managers within category context can now see users emails in program assignment dialogs
    TL-10229       Fixed upgrade of assignment submissions which had been graded twice
    TL-10235       Face-to-face events are now correctly shown on the site calendar when configured to do so
    TL-10251       Fixed HTML validation when viewing a single badge
    TL-10275       Removed empty link from Record of Learning previous course completion column
    TL-10313       Fixed Report builder graph placement issues in PDF exports
    TL-10341       Removed program status column for non-assigned users

                   The status column was recently inadvertently added when non-assigned users
                   were viewing a program or certification.

    TL-10400       Audience start and end dates are now shown correctly on the overview tab
    TL-10425       Searching without providing a term no longer leads to an error in Report Builder
    TL-10446       Removed invalid future 3.2 version from server environment tests

Contributions:

    * Andre Yamin at Kineo NZ - TL-9491
    * Russell England at Kineo USA - TL-10235


Release 2.9.10 (23rd August 2016):
==================================


Security issues:

    TL-9448        Search terms when searching user messaging are now strictly escaped

                   Previously it was possible to use the wildcard "%" character when searching
                   for users on the Messages page, and doing so would return a list of all
                   users.
                   While the result is correct, allowing the use of the wildcard character
                   here means large result sets can be easily returned.
                   While not strictly a security issue such functionality could be targeted by
                   the likes of DOS attacks as an effective page on which to generate
                   arbitrary load.
                   The search term is now strictly escaped, and "%" is now searched for as a
                   literal.

Bug fixes:

    TL-7902        Attempting to assign a manager that would lead to a circular dependency now results in a validation error

                   Previously it was possible to create a circular reporting path which could
                   lead to unexpected behaviour and possible errors.
                   A validation error is now displayed when attempting to set a users manager
                   if it would result in a circular reporting path.

    TL-9196        Course set completion state is now reset when editing Certification completion records

                   When a certification completion record is changed from "Certified, before
                   window opens" to any other state using the certification completion editor,
                   the corresponding course set completion records will be reset.
                   This prevents users being re-marked certified due to these records when
                   cron runs.
                   Please note that changes in the certification completion editor do not
                   affect course completion. And as a consequence, if the courses contained in
                   the course sets are still marked complete then this may lead to the course
                   sets being marked complete again. This may lead to re-certification.

    TL-9222        The Program and Certification completion editor now shows how a user is assigned
    TL-9262        Fixed a bug with Face-to-face iCal attachments for sessions with multiple dates

                   Previously when loading an iCal attachment from a Face-to-face seminar with
                   multiple dates into your chosen calendar application only a single date
                   (the first date) may have been imported.
                   Now the iCal attachment contains all of the correct information to allow
                   the calendar application to import the event on multiple dates.

    TL-9343        Horizontal scrolling in the grader report keeps users name visible
    TL-9394        Fixed inconsistent timezone handling in Face-to-face notifications when "User timezone" was selected
    TL-9395        Fixed inconsistent timezone handling on the "My Bookings" page in Face-to-face
    TL-9449        Improved the performance of the Course and Certification completion import report sources
    TL-9777        Fixed Face-to-face unit tests to use site specific module ids for testing
    TL-9820        Improved the reliability of behat testing when executing multiple scenarios

Contributions:

    * Eugene Venter at Catalyst NZ - TL-9777


Release 2.9.9 (26th July 2016):
===============================


Important:

    TL-9703        This release contains fixes made in Moodle 2.9.9

                   Moodle 2.9.9 received five fixes as noted below:

                   1. MDL-53431 tool_monitor: Access control for tool monitor subscriptions
                      Imported as TL-9551
                   2. MDL-55069 core: escape special characters in email headers
                      Imported as TL-9515
                   3. MDL-53019 environment: 3.2 requirements added
                      Imported as TL-9556
                   4. MDL-54564 behat: Wait after hover, to ensure page is ready
                      Imported as TL-9631
                   5. MDL-54620 ratings: display '0' when aggregate = 0
                      Imported as TL-9633


Security issues:

    TL-9340        Fixed access control when deleting calendar subscriptions

                   Users can only delete their own calendar subscriptions.
                   Previously it was possible to craft a special request that would allow you
                   to delete a calendar subscription regardless of whether you were the owner
                   or not.
                   The moodle/calendar:manageownentries capability is now consistently
                   checked.

    TL-9400        Fixed access control when deleting personal goals

                   A user's personal goals can only be deleted if one of the following
                   conditions is true for the current user:

                   1. They have the totara/hierarchy:managegoalassignments capability in the
                   system context.
                   2. They are a manager of the goal's owner and they have the
                   totara/hierarchy:managestaffpersonalgoal capability in the users context.
                   3. It is one of their own personal goals and they have the
                   totara/hierarchy:manageownpersonalgoal capability in the system context.

                   Previously it was possible to craft a special request that would allow you
                   to delete any personal goal, regardless of whether it was one of your
                   personal goals or not.
                   The relevant capability checks are now consistently applied.

    TL-9515        Fixed sanitisation of user's firstname and lastname when sending emails

                   Totara was not previously sanitising the users firstname and lastname
                   correct when compiling emails to the user.
                   An authenticated user could therefor alter their firstname or lastname in
                   Totara to contain invalid content including additional email addresses.
                   As their firstname and lastname was not being correctly sanitised this
                   could be abused to send spam to others.
                   The users firstname and lastname is now properly sanitised.

                   References MDL-55069

    TL-9668        Improved the security of all repository plugins

                   Previously it may have been possible to perform SSRF attacks on a server
                   through the repository API which was not working with installed repository
                   plugins to sanitise downloaded content.
                   With this update comes a change to the repository API that allows it to
                   work with the repository plugins to ensure that content requested for
                   download is expected and valid.
                   By default any plugin which attempts to use the repository API to download
                   content without implementing the now required get_file() method will stop
                   working as this is deemed a security risk.
                   We are aware that some subscribers do use third party repository plugins,
                   and that this change may stop those plugins from working.
                   Whilst it is our recommendation for those sites to get the affected third
                   party repositories updated to support the new API and downloading of files
                   we have also in 2.9 added a special setting to bypass the lacking support
                   and allow the plugins to function as they once did.
                   This setting is present in 2.9 only and will not be available in 9.0.
                   To enable this setting add the following to your config.php:
                   {code}
                   $CFG->repositoryignoresecurityproblems = 1;
                   {code}
                   Please be aware that adding this setting may open a security hole on your
                   site.
                   We do not recommend adding it.

Improvements:

    TL-8996        Added support for syncing a user's image during SSO login
    TL-9221        Added the ability to resolve dismissed program exceptions to the completion editor

                   The Program and Certification completion editors now display information
                   about dismissed program exceptions when viewing a user's completion data,
                   and allow dismissed exceptions to be overridden.

    TL-9265        Added a new filter for Audience Visibility to Course reports

                   Previously a Visibility filter could be added to Course reports, however
                   there was no corresponding Audience Visibility filter for those sites that
                   had Audience Visibility turned on.
                   A new Audience Visibility filter has been added to all Course reports so
                   that those sites that have Audience Visibility turned on can filter by the
                   relevant visibility options.

    TL-9276        Improved the description of Global Report Restrictions when only one restriction exists

                   If there is only one Global Report Restriction for a user it is
                   automatically applied for that user.
                   This was previously undocumented.
                   The description within the Global Report Restriction user interface has
                   been improved to elude to this behaviour.

    TL-9314        Improved the information shown when viewing a Certification

                   When a user views one of their certifications, they will see a more verbose
                   description of the status.
                   It is now clear when a user is not required to work on a certification.
                   When working on a specific certification path, only courses in that path
                   are shown (as before), otherwise both paths are shown, rather than trying
                   to show the last path completed (which cannot be calculated under several
                   circumstances).

                   Additionally, a warning has been added, and is shown when the user is due
                   to recertify but the window opening process has not yet occurred.

    TL-9344        Improved the time allowance strings used for Programs to ease translation
    TL-9378        Ensured that goal management capabilities are consistently applied

                   Personal goals created by either a site administrator or a user's manager
                   cannot be edited or deleted by the user.
                   Additionally the action icons for actions you can't preform are now greyed
                   out.

    TL-9383        Improved the performance of sidebar searches within Report Builder

                   For reports which had multi-check filters enabled in the sidebar, such as
                   in the course catalog, item counts shown in the filter were sometimes being
                   queried twice unnecessarily.
                   In cases where there were thousands of items, this had a noticeable effect
                   on performance.
                   These items counts are now only queried once, and only if needed.

    TL-9433        Developers may now disable editor autosave in forms where it is not desirable

                   Previously it was not possible for the developer to disable autoasaving
                   within an editor when defining a form.
                   A small improvement has been made to allow the developer to pass
                   ['autosave' = false] as an editor option, this gets passed through to the
                   editor initialisation and allows the developer to disable autosaving when
                   defining the form.

    TL-9456        Plugins can now define Report Builder filters within the plugin space

                   If is now possible for plugins to define their own Report Builder filters
                   for use within their own Report Sources.
                   In order to do this the plugin defines an class called
                   `rb_filter_"filtertype"` in a location it chooses, and then requires the
                   file containing the class within the Report Builder sources that will use
                   it.

    TL-9483        Fixed Behat file uploads to work in all browsers and in remote selenium instances
    TL-9484        Added a workaround for missing alert confirmation support in PhantomJS
    TL-9502        The Course and Activity completion reports now use the standard paging control

                   The Course completion and Activity completion reports now use the standard
                   paging control bar.
                   This helps bring the look and feel of these reports (which are not Report
                   Builder reports) inline with the other reports available in the system.

    TL-9556        Environment definition updated to reflect Moodle 3.2 requirements
    TL-9670        Course visibility filters show as not applicable depending on the sites audience visibility setting

Bug fixes:

    TL-7907        Fixed manager approval for Face-to-face direct enrolment when automatic signup is enabled

                   Previously if you had a Face-to-face activity that was configured to
                   require manager approval, within a course with a Face-to-face direct
                   enrolment instance added and configured to automatically sign new users up
                   to all available sessions, then when a new user signed up they would be
                   automatically booked to the session requiring manager approval, bypassing
                   the approval stage.
                   Now the Face-to-face direct enrolment plugin, with automatic signup
                   enabled, correctly respects the manager approval requirements for available
                   sessions.

    TL-8179        Program and Certification reports now order courseset data correctly

                   The Program and Certification overview reports now ensure that columns
                   displaying courseset information order the content in the same manner that
                   is applied when viewing the Program or Certification content.

    TL-8555        Recurring courses now respect the tempdir setting

                   When recurring courses were copied during cron, it was assumed that the
                   temp folder was set to its default rather than what was in the 'tempdir'
                   config setting. The temporary backup folder is now created in the location
                   specified by the 'tempdir' setting.

                   This fix also ensures that the copy recurring courses cron task will run
                   when certifications are disabled, but programs are enabled, as recurring
                   courses can only be used within programs.

    TL-8601        Fixed backup and restore of multi-select and file type Course custom fields
    TL-8985        Suspending a user no longer cancels past Face-to-face signups

                   Previously if you suspended a user any Face-to-face signups they had made
                   would be cancelled. Even when the Face-to-face session had already been
                   run.
                   Now when a user is suspended only Face-to-face signups for future sessions
                   are cancelled.

    TL-9056        Fixed program enrolment messages not being sent

                   It was possible that some program and certification enrolment messages were
                   not being sent. This would only occur in the unlikely event that the
                   program messaging scheduled task took some time to run, and that program
                   assignments changed during that time (either by a manual change made in the
                   Assignments interface when there were less than 200 users involved in the
                   program, or due to one of the two user assignment scheduled tasks running
                   at the same time). This has now been fixed. This patch does not
                   retroactively send program/certification enrolment messages that were
                   missed.

    TL-9086        HR Import now validates incoming user custom field values consistently

                   Previously HR Import was validating incoming user custom field data without
                   first passing it through the user custom fields API.
                   This could lead to invalid data passing validation as it had not been
                   appropriately translated.
                   HR Import now correctly passes incoming data through the user custom fields
                   API prior to validation to ensure any invalid data is detected and not
                   imported.

    TL-9115        Improved the display of averaged columns in Report Builder

                   When averaging a field the number of decimal places shown was the default
                   returned by the database.
                   The display has been improved to only show 2 decimal places.

    TL-9118        HR Import now converts mixed case usernames to lower case

                   This fixes a backwards compatibility issue introduced by TL-8502.
                   TL-8502 improved validation of usernames being imported through HR Import.
                   Unfortunately a previously added hack was present which was converting
                   mixed case usernames to lower case.
                   TL-8502 reverted this hack, ensuring only completely valid usernames could
                   be imported, and any invalid usernames would be skipped with an error.
                   After the release of 2.9.7 we received several reports of people relying on
                   this conversion to import their data.
                   After much discussion we decided to treat this as a backwards compatibility
                   issue and fix it as a bug in 2.7 and 2.9.
                   Now when you import a username with mixed case you will receive a warning,
                   the username will be converted to lower case and the user will be
                   imported.
                   Please note that in Totara 9.0 you will receive an error and the user will
                   not be imported.
                   We advise those who are getting these warning to fix the data they are
                   importing so as to make it accurate.

    TL-9135        Fixed the use of files within textarea type custom fields
    TL-9159        Multi-select custom field data params are now correctly deleted when the field is deleted

                   Previously data params for multi-select custom field values were not being
                   deleted when the multi-select custom field was deleted.
                   This resulted in orphaned data param records being left in the database.
                   Now when a multi-select custom field is deleted the data params for it are
                   also deleted.
                   Additionally an upgrade step will clean up any orphaned multi-select data
                   params that may be lurking in your database.

    TL-9162        Removing a user from an allocated Face-to-face session now returns capacity

                   Previously when a user was removed from an allocated spot in a Face-to-face
                   session by their manager the space they were occupying was not returned to
                   the available capacity of the session, nor were they being informed that
                   their allocation had been cancelled.
                   Now when a user is removed from an allocated spot the capacity is returned
                   and the user is notified.

    TL-9187        Fixed searching of the Program exceptions list by firstname and lastname
    TL-9210        Fixed a missing iCal attachment in the Face-to-face session allocation notification email
    TL-9235        Fixed the display of aggregated yes_or_no Report Builder columns

                   "Yes" is counted as 1, "No" is counted as 0. Aggregate functions use these
                   values for the calculations.

    TL-9241        Ensured the ability to choose an appraiser is not available when appraisals have been disabled
    TL-9261        Fixed the "Re-sort" button within the Certification management UI
    TL-9341        Fixed the User's Position Framework ID filter within the User report source
    TL-9362        Fixed the status display for certification in progress within the Record of Learning
    TL-9387        Fixed the display of Face-to-face sessions in the Face-to-face block
    TL-9388        Fixed the expansion of the Site Administration menu in IE8
    TL-9392        Available courses on the front page are no longer duplicated when the user is not enrolled in any courses

                   If the front page had been configured to display a list of Enrolled Courses
                   and the user was not enrolled on any courses then a list of available
                   courses would be displayed in stead.
                   Previously if you had also configured the front page to contain a list of
                   available courses this would then lead to the list of available courses
                   being displayed twice.
                   Now when the front page has been configured to display a list of available
                   courses and enrolled courses, when the user is not enrolled on courses then
                   nothing is printed.
                   This stops the list of available courses from being printed twice.

    TL-9397        Fixed an error encountered while exporting a Face-to-face cancellation report

                   This fixes a regression introduced by TL-6962, released in Totara 2.7.14,
                   2.9.6.

    TL-9434        Fixed a bug preventing roles from being assigned via audiences at the category level
    TL-9438        Fixed average aggregation within Report Builder when using MSSQL

                   MSSQL now ensures that it is using decimals when fetching average
                   aggregations.

    TL-9453        Prevented the adding of a Program to a Learning Plan from resetting Program status

                   When a program was already assigned to a user, if the same program was then
                   added to the user's learning plan, the status of the program was reset. The
                   program would likely be re-marked completed by cron, but only if the course
                   requirements were unchanged and the courses involved were still marked
                   complete.
                   Additionally, dates related to the program may have changed.
                   This fix prevents changes when adding a program to a learning plan if the
                   user is already assigned to the program.

    TL-9473        Fixed the "Download all" button within the file manager in IE and Safari
    TL-9485        Fixed data param handling in the core data object class
    TL-9669        Fixed the possibility of a "maximum SQL input variables" bug within the Face-to-face upgrade

Contributions:

    * Davo Smith at Synergy Learning - TL-9485
    * Francis Devine at Catalyst NZ - TL-9086
    * Nigel Cunningham at Catalyst AU - TL-8601, TL-9261


Release 2.9.8 (14th June 2016):
===============================


Important:

    TL-9190        Fixed Face-to-face session deletion custom field data loss

                   This is a regression introduced by TL-6962 in Totara 2.9.6, and 2.7.14,
                   both released April 2016.
                   In these releases the foreign key for Face-to-face custom fields was
                   changed from the "facetoface_signups_status" table to the
                   "facetoface_signups" table to ensure that data entered by the user was
                   maintained when their session signup status changed.
                   However the session deletion code was overlooked and was still using the
                   old relation to wipe the associated custom field data.
                   If a user deleted a Face-to-face session it may have incorrectly deleted
                   custom field data belonging to a different session.
                   This patch fixes the deletion code so it is using the correct relation and
                   ensures that when the session is deleted, the correct custom field data is
                   also deleted.

                   If you are on either of the mentioned versions we strongly recommend
                   upgrading to the latest release.


Improvements:

    TL-8708        Face-to-face session and event durations can now be aggregated and graphed

                   Face-to-face report sources now handle durations as integers.
                   This allows the the duration field to be aggregated, and used in graphs if
                   so desired.
                   The display of the field within the report is unchanged.

    TL-8719        Improved the help text for the "Add new courses" Totara Connect client setting
    TL-9050        Improved the help on Column and Row settings for textarea custom fields
    TL-9106        The user's name is now maintained when forcing use of the no-reply address

                   Previously in Totara 2.9, when the site had been configured to send all
                   emails from the no-reply email address, it was transposing the sending user
                   with the support user and overriding the support user's email address to
                   ensure all emails were coming from the no-reply address.
                   This meant that details of who was sending the email, including the
                   sender's name, were lost and replaced by the support user's details. This
                   was not the desired effect of this setting and was the result of a setting
                   conflict in 2.9.0.
                   The fix for this issue is to only override the email address, ensuring the
                   name of the sender is not lost.

                   Emails sent from the system when "Always send email from the no-reply
                   address?" is turned on still use the no-reply email address as the sender's
                   email address, however they now show the sender's name.


Bug fixes:

    TL-8152        Fixed the deletion of user accounts with empty email addresses

                   Previously if you attempted to delete two or more user accounts with empty
                   email addresses at the same time you would get an error.
                   This was visible most commonly in HR Import.
                   This has now been fixed. A unique non-numeric username is now generated as
                   a holding username during deletion.

    TL-8410        Fixed date validation when uploading Course and Certification completion data

                   Completion dates in course and certification uploads are now more
                   thoroughly validated.
                   If the value for the month is greater than 12, or the value for the day is
                   greater than what should be in the month, the date will be considered
                   invalid and the row will not be processed.

                   This patch also fixes a minor issue with how line endings are processed.
                   Now prior to processing the uploaded file all line endings in the file are
                   normalised.
                   This ensures that files generated on different operating systems are all
                   processed identically.

    TL-8650        Fixed result counting discrepancies within Appraisal reports

                   On the appraisals reports page there is a list of appraisals with counts
                   for "Overdue", "On target", "Complete", etc. There were some discrepancies
                   between these numbers and the reports displayed when clicking on the
                   counts.

    TL-8688        Corrected Face-to-face attendees dialog URL handling to prevent accidental removal of all attendees

                   Prior to this change, in some circumstances, the Face-to-face attendees
                   dialog would generate unexpected URL parameter value combinations which
                   could result in the removal of all users from the attendee list.

    TL-8790        Fixed the display of the user's full name for the roles column within the Face-to-face sessions report
    TL-8874        Face-to-face booking confirmation notification no longer ignores the "Manager copy" setting

                   Prior to this change the booking confirmation notification would be sent to
                   the attendee's manager even if the "Manager copy" setting was turned off.
                   The booking confirmation notification now correctly checks this setting.

    TL-8924        Fixed a display issue with empty due dates on the Program assignments tab
    TL-8928        Fixed Face-to-face notification substitutions in translated languages
    TL-9074        Fixed searching within the audience filter when the context id is not set
    TL-9079        Fixed a bug with header alignment when scrolling within the grader report
    TL-9102        The "Hide cost and discount" Face-to-face setting now correctly hides the discount code
    TL-9103        Fixed Face-to-face session location custom field filters
    TL-9248        Brackets are now correctly escaped during "LIKE" searches when using MSSQL

                   Previously during "LIKE" search operations, brackets "[" and "]" were not
                   being correctly escaped, which could on occasion lead to incorrect
                   results.
                   This was not exploitable but could have made searching difficult for some
                   MSSQL sites.

    TL-9273        Fixed MSSQL database structure consistency for sites upgraded from old Moodle installations


Release 2.9.7 (23rd May 2016):
==============================


Important:

    TL-8973        Activity completions will now always reset on archive regardless of their visibility

                   When activity completions were archived during a certification window
                   opening, the completions for activities set to hidden were ignored and
                   would be retained. Activity completions are now archived regardless of
                   their visibility.

                   This patch also fixes an issue where Face-to-face completion data was not
                   being fully reset if the completion time of the Face-to-face activity was
                   changed to a later time.

                   This change means that after the archive_course_activities function is run,
                   any activities that should not be complete will have no record in the
                   course_modules_completion table, rather than have one which is set to
                   incomplete.

                   As well as affecting certification window opening, these changes will also
                   apply when using the archive completions function within a course.


Security issues:

    TL-9057        Added sesskey checks when applying automatic fixes on the check completions page in the Program completion editor


Improvements:

    TL-6666        Enabled the randomisation of test table ids

                   In Moodle 2.9 phpunit testing was changed so that the auto-increment IDs of
                   records created in each table started with a different value. This was
                   designed to improve the testing quality by preventing code and tests from
                   using the wrong ID, but still coincidently passing the tests. For example,
                   if a course and certification were created during a test, previously it
                   could be possible to reference the course ID when the certification ID
                   should be used, because they were both 1, but with the change the two IDs
                   will be different, and use of the incorrect ID will be illuminated. This
                   improvement was initially disabled because many tests needed to be updated
                   to support it - the improvement has now been enabled and all of the core
                   tests have been updated to run with it. Any custom tests using fixed record
                   IDs may need to be updated.

    TL-7993        Removed the obsolete 'Manager emails' settings from Face-to-face
    TL-8237        Improved the reliability of behat in Firefox and Chrome using the latest version of Selenium

                   The behat integration has been tuned for the latest releases of Selenium.
                   Several old "hacks" made in Totara to get behat working have been removed
                   as the Selenium drivers have improved. This has greatly improved both the
                   performance and reliability of behat in core, though could have some impact
                   on any custom behat tests.

    TL-8376        Refactored the Face-to-face completion state SQL to improve performance
    TL-8629        Rephrased the help text for the SCORM activity's "Force new attempt" setting to improve its clarity
    TL-8665        Added an environment check for "always_populate_raw_post_data"
    TL-8705        Improved how deleted users are being handled on the browse list of users page

                   The detection of legacy deleted users, checking that their email address
                   field is set to an MD5, has been improved by using a regular expression.
                   Users who have an invalid email address (without an @ symbol) will now show
                   in the user list on the "Browse list of users" page.

    TL-8768        Improved the wording on the Documentation settings page, and ensured that all the settings work correctly
    TL-9020        Created initial Program and Certification completion transaction logs

                   If a user assigned to a Program or Certification did not have any
                   transaction logs, a snapshot log record will be created during upgrade.
                   Likewise, a certification settings snapshot will be created if none exists.
                   This will help with diagnosing future problems.

    TL-9022        Added confirmation when applying automated fixes to program completions

                   When using the program or certification completion checker or editor, when
                   you activate one of the fixes, you will now need to confirm the action.
                   This is to prevent accidental clicks, and provides additional warnings.

    TL-9033        Increased the form field lengths on the Certification settings form


Bug fixes:

    TL-8046        An editor is displayed when editing role descriptions

                   This change reverted MDL-50222 which converted the role description from an
                   editor to a plain text field as it was deemed to be internal only. Totara
                   however uses this field more than Moodle and the above change was reported
                   as a bug.

                   We have now reverted Moodle's change and returned the editor for that
                   field.

    TL-8247        Fixed missing history records when not overriding the current record during a course completion import

                   Previously if you ticked the override checkbox when uploading course
                   completions, but the most recent completion date in the upload was less
                   than the current completion date in an existing record, the upload would
                   ignore the first record and move straight on to the next one. That first
                   record is now placed into history instead.

    TL-8389        Fixed an issue with empty variables inside aggregate questions
    TL-8408        Fixed the Face-to-face user cancellation status by checking the user's current status, avoiding multiple cancellations
    TL-8472        Fixed the Terms & Conditions dialog box for Face-to-face self approval
    TL-8494        Fixed changes to editing trainer assignments not being reflected in the course catalog immediately
    TL-8554        Fixed zero values for appraisals rating questions

                   Both positive and negative numbers are valid values for ratings questions
                   in Appraisals, but a lot of the validation checks were using !empty() which
                   caused problems when trying to use zero. These checks have been updated and
                   zero is now an acceptable value in both numeric and custom ratings

    TL-8561        Changed the display of certification settings in the completion editor to use translatable strings
    TL-8587        Improved the 'Grade to pass' setting validation in Quizzes

                   If the 'Require passing grade' activity completion setting is enabled, the
                   'Grade to pass' must now have a greater-than-zero value. This restriction
                   is also enforced when editing Quiz settings in the gradebook.

    TL-8599        Corrected the completion time used for completion criteria based on completion of another course

                   Completion criteria based on completion of another course now use the
                   completion time of that course when setting the completion time of the
                   criteria, rather than using the time that the criteria was aggregated.

    TL-8605        Fixed certification completon records affected by the reassignment bug

                   Patch TL-6790 in the March release of Totara changed the behaviour when
                   re-assigning users onto a certification. This patch repairs certification
                   completion records for users who were, before that patch, re-assigned and
                   ended up being unable to certify. All users which are identified by the
                   completion checker as "Program status should be 'Program incomplete' when
                   user is newly assigned. Program completion date should be empty when user
                   is newly assigned.", and have an "unassigned" historical completion record,
                   will have that historical record restored, as is the current behaviour when
                   reassigning users. Any records which do not meet these specific criteria,
                   or would be in an invalid state if the change was applied, will not be
                   affected and must be processed manually.

    TL-8621        Fixed the progress bar on the Required learning page when Programs use "AND" courseset operators
    TL-8667        Added functionality to fix duplicated Face-to-face notifications

                   In extremely rare cases the automatic notifications for Face-to-face are
                   being duplicated. If this happens, a warning box will appear for course
                   administrators on the session or  notifications pages and it will be
                   possible to remove the duplicated notifications leaving only the required
                   ones.

    TL-8696        Added a workaround for incorrect rendering of tables in pdf exports using wkhtmltopdf
    TL-8721        Fixed the download file functionality for file pickers when using Internet Explorer
    TL-8732        Fixed capability checks around suspended users within the course enrolment interfaces
    TL-8753        Added an automatic fix for mismatched program and certification completion dates

                   The program completion date should match the certification completion date
                   when a user's certification is "Certified, before the window opens". To
                   repair records where the date is incorrect, this patch added an automated
                   fix which can be triggered to copy the certification completion date to the
                   program completion date. Relates to TL-9014.

    TL-8764        Deleted orphaned course reminders for deleted courses
    TL-8771        Removed duplicated Face-to-face event action icon titles
    TL-8785        Removed the incorrect 'leave this page' warning when editing Feedback activity questions
    TL-8788        Fixed the display of the program completion block when there are no programs visible
    TL-8793        Reminders which are sent belonging to a user are now deleted when the user is deleted
    TL-8807        Fixed search in the completion upload results report
    TL-8867        Fixed the incorrect display of hidden positions and organisations during self registration
    TL-8872        Fixed the ordering of the transaction logs in program and certification completion editors
    TL-8919        Fixed incorrect id being used when preparing an overview for a SCORM activity
    TL-8936        Fixed access to course/info.php when audience visibility is in use
    TL-8942        Fixed test failures caused by a new timezone in Venezuela
    TL-8960        Fixed an issue with creating courses, caused by a guest access setting

                   Previously if you set "require password" for the guest access enrolment
                   plugin, and set the plugin to add a new instance to all new courses. When
                   you then attempted to create a new course, the setting would not be copied
                   to the new course correctly.

    TL-8969        Updated the help string for the "fullnamedisplay" setting
    TL-8983        Fixed the repeating update of signup status for suspended and deleted users in Face-to-face

                   Previously a cancellation status update was written to the database for
                   each deleted or suspended user who had signed up to a Face-to-face session
                   prior to deletion/suspension, this happened every time cron ran.
                   Face-to-face now correctly checks if the user already has a cancellation
                   status and does not write a redundant duplicate status.

    TL-8991        Fixed the wrong date being used to calculate the certification completion date

                   If a certification had a course set with more than one course, where not
                   all of the courses were required for completion. And before being assigned
                   to the certification, a user completed (manually or via course completion
                   upload) enough of the courses to complete the course set. The certification
                   completion date was being incorrectly calculated as the latest date of all
                   completed courses, rather than the maximum course set completion date. This
                   patch corrects this, and provides an automated fix (part of the
                   Certification completion editor) to repair any affected records.

    TL-9017        Fixed the incorrect redirection to file serving scripts after interruption actions

                   This fix makes two changes:
                   1) File serving scripts no longer set themselves to the return URL if
                   triggered during an interruption action such as the user being forced to
                   change their password.
                   2) Blocks are no longer shown on the change password screen, it now uses
                   the "login" page layout, the same as the main login page.

    TL-9021        Fixed a race condition when loading JavaScript for Totara dialogs

                   When in an appraisal there was a chance that dialogues were attempted to be
                   initialised before the dialog code was downloaded.

    TL-9030        Fixed the removal of text formatting in the display of descriptions for Report builder reports
    TL-9044        Fixed the calculation of substitute colours within the custom Totara responsive theme

                   In the Custom Totara responsive theme, there was an error in the text
                   colour calculation where it would always result in going to the dark
                   colour, regardless of what the background colour was. Now if the background
                   colour is dark, the text will be light

    TL-9052        Fixed the status of notification templates when they were updated by a user
    TL-9061        Fixed an error when double clicking on filters in the enhanced catalog


Contributions:

    * Artur Poninski at Webanywhere - TL-8599
    * Carlos Jurado at Kineo - TL-8960
    * Chris Wharton at Catalyst NZ - TL-8867
    * Davo Smith at Synergy Learning - TL-8788
    * Francis Devine at Catalyst NZ - TL-8919
    * Malgorzata Dziodzio at Webanywhere - TL-8376
    * Stacey Walker at Catalyst EU - TL-8936


Release 2.9.6.1 (26th April 2016):
==================================

Important:

    TL-8846        Face-to-face upgrade restores the latest non-empty signup and cancellation custom field data

                   The 2.9.6 release contained a fix for TL-6962 which was not acting in a way
                   that all users wanted it to.
                   Given an upgrade step is non-reversible, we have put out an emergency
                   release to allow administrators to choose how they want the upgrade to
                   behave.

                   TL-6962 was fixing Face-to-face signup and cancellation custom field data
                   being lost when the user's signup status changed.
                   The root cause of that issue is that custom field data was being stored
                   against the user's signup status rather than the signup itself.
                   Because a user could pass through multiple statuses, this data was lost in
                   the user interface as soon as the user's status changed. This was most
                   commonly seen if manager approval was turned on, in which case the signup
                   note entered by the user when they signed up would be lost when the manager
                   approved them.
                   The solution was to change this, so that data is stored referencing the
                   signup rather than the signup status.
                   This ensures that the data is maintained throughout a user's signup,
                   regardless of which statuses they pass through.
                   During upgrade we had to remap existing data and a choice had to be made.
                   Either we kept the data consistent with what users had previously seen in
                   the system OR we restored the last non-empty value for the field for each
                   signup.
                   We chose in 2.9.6 to keep the data consistent with what users were seeing
                   prior to the upgrade.
                   Since the release we have had feedback that this is not what all sites
                   expected and that they would prefer to have the last non-empty value
                   restored.
                   We appreciate this request and have come up with a solution that will allow
                   each site to choose, if they wish, which they would like to happen.
                   There is now a special configuration variable that can be defined in PHP to
                   change the upgrade behaviour.
                   The following explains what this upgrade step does and how to choose the
                   alternative behaviour if you want it.

                   Default upgrade behaviour:
                   The upgrade finds the last non-empty value for each Face-to-face signup or
                   cancellation custom field and uses that as the value the user entered.
                   This is consistent with the behaviour you will see AFTER upgrading to this
                   version.
                   It is not consistent with the previous behaviour before upgrading, which
                   was not maintaining the value the user entered.
                   This is the default behaviour so you don't need to do anything except
                   upgrade.

                   Alternative upgrade behaviour:
                   Instead of the default behaviour, restore the latest value recorded for the
                   Face-to-face signup or cancellation field, which may be empty due to status
                   changes, and store that as the user's current value.
                   This is consistent with the behaviour prior to custom fields being fixed.
                   It is not consistent with the current behaviour which ensures the value the
                   user entered is maintained.
                   To get this behaviour you must add the following to your config.php before
                   upgrading to 2.9.6.1:
                       $CFG->facetoface_customfield_migration_behaviour === 'latest';

                   If you have already upgraded to the 2.9.6 release the alternative behaviour
                   would have already been applied as this was the only behaviour available in
                   that release.
                   It is possible to change from the alternative behaviour to the current
                   behaviour if you have a backup from prior to the upgrade.
                   If this is you please get in touch with us and we'll provide instructions
                   on how to re-run this part of the upgrade using the data in the backup.


Release 2.9.6 (20th April 2016):
================================


Important:

    TL-8417        Historical completion records belonging to a course are now deleted if the course gets deleted

                   Previously when a course was deleted the historical completion records were
                   forgotten about and left orphaned.
                   This lead to errors and visual inconsistencies when trying to work with the
                   historical completion records as they now referenced removed courses.
                   Now when a course is deleted all historical completion records held for it
                   are also deleted.
                   Please note that during upgrade all orphaned historical completion records
                   are deleted.

    TL-8711        Scheduled reports belonging to a user are now deleted when the user gets deleted

                   Previously when a user or audience was deleted any scheduled reports
                   belonging to that user were left in the system.
                   This lead to errors as the scheduled reports now referenced users that no
                   longer existed.
                   Now when a user is deleted all scheduled reports belonging to that user are
                   also deleted.
                   During upgrade all orphaned scheduled reports belonging to previously
                   deleted users will be cleaned up.

                   The same is true of audiences referenced as recipients of scheduled
                   reports.
                   Now when an audience is deleted orphaned scheduled report recipient records
                   are also cleaned up.
                   During upgrade all orphaned scheduled reports referencing audiences that no
                   longer exist will be cleaned up.


Improvements:

    TL-8174        Fixed visibility for the 'My Learning' SCORM activity overview
    TL-8358        Updated the help text for a custom fields' "Locked" setting
    TL-8393        Performance improvement when admins view a course with many activities

                   Previously, when an admin viewed a course, the admin's completion data for
                   each course would be checked for each activity multiple times. The higher
                   the number of activities in a course, the more often these completion
                   checks would occur, causing performance issues when there were many
                   activities involved. This only affected users who could access the course
                   but were not enrolled, such as site administrators. Enrolled users were not
                   affected by this bug. Guest users were also not affected as completion data
                   is not checked for them.

                   The issue occurred due to cached data being cleared incorrectly for the
                   affected users. This has been corrected and page load times for admins on
                   the view course page will now be similar to that of enrolled learners.

    TL-8421        Usernames on login page now avoid mobile OS default capitalisation
    TL-8522        Template library now has a context for admin_tool/list_templates_page
    TL-8595        Added cost information to Face-to-face direct enrolment for sign-up page
    TL-8657        Improved performance of adding courses and programs to audiences
    TL-8751        Added links to run completion checker over whole site

                   Links were added in Manage Programs and Manage Certifications which allow
                   privileged users to run the program and certification completion checkers
                   over all programs or certifications on a site, rather than having to run
                   them one program or certification at a time.

    TL-8759        Added help popup for Require view activity completion criteria


Bug fixes:

    TL-6962        Made signup and cancellation fields consistent throughout Face-to-face

                   Previously Face-to-face custom fields for signup and cancellations were
                   being attached to a users signup status.
                   This was a regression from the conversion of Face-to-face custom fields in
                   2.7 that has been the cause of a several hard to track down problems.
                   In this fix we have changed the attachment point from signup status to the
                   signup itself.
                   This has lead to several minor changes in the Face-to-face API's as noted
                   below.

                   * facetoface_get_attendee no longer returns the statusid or usernote as
                   part of the record it returns.
                   * facetoface_get_attendees no longer returns a usernote as part of the
                   record it returns.
                   * facetoface_user_signup the usernote argument 10 has been deprecated and
                   now displays a debugging notice if used.
                   * facetoface_update_signup_status the usernote argument 4 has been
                   deprecated and now displays a debugging notice if used.
                   * facetoface_get_cancellations no longer returns a cancellation reason
                   property as part of its response.
                   * facetoface_get_requests no longer returns a usernote property as part of
                   the record it returns.
                   * facetoface_user_import no longer imports a usernote (this was not being
                   stored correctly)

    TL-8101        Removed the incorrect "role change" warning for completed dynamic appraisals
    TL-8136        Fixed Face-to-face custom field filters when applied within the calendar
    TL-8156        Fixed capability checks when adding audiences and users as scheduled report recipients
    TL-8162        Ensured only eligible activities are selectable for feedback reminders

                   Only activities which are part of course completion can be tracked for
                   sending out feedback reminder messages. However, it was previously possible
                   to select other activities and the reminder would simply not go out. The
                   select options for activity completion to base a feedback reminder on are
                   now restricted to those that are part of course completion criteria.

    TL-8338        Fixed the Face-to-face signup by waitlist functionality by including overbook capability check
    TL-8346        Corrected Face-to-face terminology within the session report source

                   The Face-to-face session report now correctly refers to users who have self
                   booked via the self-booking service as "Self booked", previously they were
                   incorrectly referred to as "Reserved"

    TL-8355        Corrected spelling of re-classify and re-classifying by removing hyphen
    TL-8374        Ensured the certification completion date is only calculated on the current paths courses
    TL-8380        Removed the sorting from Progress column for Certification overview report
    TL-8399        Fixed the coursenames column in the Program/Certification overview report

                   Previously if a course's short name contained a comma it would be
                   incorrectly processed by the Program overview and Certification overview
                   reports.
                   It is now correctly handled.

    TL-8428        Fully disabled Syncing Position ID number field in HR Import when Position Hierarchies are disabled

                   The previous behaviour was that if you have any of the position fields
                   (Position Title, Position ID Number, Position start date, Position end
                   date) enabled for the sync and then Position hierarchies are disabled then
                   the position fields are removed from the interface but are still synced.
                   The new behaviour is that Position ID number will be completely disabled
                   (it will not sync the field until Position hierarchies are re-enabled) and
                   the other fields will not be disabled.

    TL-8434        Added columns for custom program fields to Program Completion, Program Membership and Program Overview report sources
    TL-8438        Added a missing string used within the Program membership report
    TL-8458        Face-to-face module can now be managed by users with the 'totara/core:modconfig' capability
    TL-8461        Fixed course deletion to more accurately update program coursesets

                   Previously, all competency course sets were inadvertently being removed
                   from programs whenever any course on the site was deleted. This, and
                   deletion of courses that left other course sets empty, also resulted in
                   exceptions being displayed when viewing programs.

                   This behaviour has been prevented.

                   Two methods have been added to the course_set abstract class in
                   totara/program/program_courseset.class.php, get_courses and delete_course.
                   These will be abstract methods in the next major release. For existing
                   releases, they will output a message if debugging has been turned on. If
                   any custom classes have been created that inherit from this class, it is
                   recommended that you overwrite these methods in the custom class.

    TL-8471        Fixed 'totara/dashboard:manage' capability for non admin user
    TL-8475        Fixed position type display function for Face-to-face session report
    TL-8502        HR Import now correctly handles invalid usernames by logging a warning and continuing on

                   This issue was only encountered when the user delete setting was set to
                   suspend users, and there were users being deleted. However this adds extra
                   validation to the HR import sanity checks so any invalid users will be
                   detected during any sync action.

    TL-8582        Updated course multiselect custom field type to update all data records when option text or icons are changed
    TL-8585        Face-to-face notification iCalendar attachments now support updates during signup status and/or session date changes

                   Please note, software that does not support iCalendar sequence will still
                   create new event each time instead of updating it.

    TL-8603        Fixed multilang support within Report description field
    TL-8633        Fixed use of the duedate placeholder in program messages
    TL-8634        Fixed Audience: Visible Learning report when no id is set
    TL-8653        Fixed image used by the template library when viewing the core/pix_icon template
    TL-8672        Fixed removal of seleted audiences when editing a course
    TL-8685        Fixed pagination when viewing global report restriction list
    TL-8691        Fixed the handling of locked custom course fields when creating a new course
    TL-8698        Fixed minor JavaScript error when using AMD get_strings implementation in IE9
    TL-8707        Made the 'Duration' column within Face-to-face reports translatable
    TL-8760        Fixed deactivation of the "require end reach" completion condition for Lesson activities


Release 2.9.5 (23rd March 2016):
================================


Important:

    TL-6790        Changed the default behaviour of certification reassignment

                   Previously a user who was unassigned and reassigned to a certification
                   would be placed back into their initial certification path. Depending on
                   their current course completions, their status may have been reaggregated
                   on the next cron run. Now the system will look for the latest unassigned
                   certification completion history record and the user will be restored to
                   their previous status instead. Any events that need to occur (such as
                   window opening) will take place when the relevant scheduled task runs (e.g.
                   update_certification_task).


Security issues:

    TL-8641        The following security fixes were included with the merge of Moodle 2.9.5

                   * MDL-51167 Hidden courses are shown to students in Event Monitor
                   * MDL-52378 Non-Editing Instructor role can edit exclude checkbox in Single
                   View
                   * MDL-52651 Add no referrer to links with _blank target attribute
                   * MDL-52727 Reflected XSS in mod_data advanced search
                   * MDL-52774 Enumeration of category details possible without
                   authentication
                   * MDL-52808 External function get_calendar_events return events that
                   pertains to hidden activities
                   * MDL-52901 External function mod_assign_save_submission does not check due
                   dates
                   * MDL-53031 CSRF in Assignment plugin management page


Improvements:

    TL-6296        Added an aria-label to select user checkbox when viewing course participants
    TL-6723        Added automatic test coverage of the security overview report
    TL-7864        Added haschildren class to top level totara menu items when applicable
    TL-8295        Improved perfomance when getting items assigned to plans

                   This get_assigned_items function by default was returning the counts of any
                   linked items. This was leading to performance issues when the counts were
                   not required. The function now returns this information only when required.

    TL-8422        Improved output of the standard logstore cleanup task
    TL-8478        Added pagination to the Global Report Restriction administration page
    TL-8484        Linked Report Builder Financial year setting labels to their inputs
    TL-8532        Added an accessible label when adding a comment to a learning plan


Bug fixes:

    TL-8205        Removed unassigned users that incorrectly show up in certification completion reports

                   Reports that used the 'Certification Completion' report source would
                   contain users that had been unassigned from a certification. This would
                   only be the case if the user was unassigned before their recertification
                   window opened and the data for these users would be incorrect for some
                   columns. Unassigned users will no longer show up in certification
                   completion reports, which is in line with documentation on this report
                   source.

                   Note that if you require a report that includes data for unassigned users.
                   You may like to create a report that uses the Record of Learning:
                   Certification report source.

    TL-8274        Fixed calendar navigation on non-default home page
    TL-8277        Fixed incorrect highlighting of menu items

                   When enhanced catalog was off, viewing the pages specific to the enhanced
                   catalog were leading to the Find Learning menu items being highlighted.
                   This has been corrected.

    TL-8280        Fixed manual changes to course completion competency proficiency being overridden

                   Before the patch, if a manager set a course completion competency to
                   proficient, it was being overridden by a cron task. Now, the change the
                   manager made will be kept.

    TL-8339        Fixed saving of due dates when creating and editing objectives in learning plans
    TL-8345        Ensured sum aggregation uses display function if available
    TL-8363        Ensured courses assigned to plans are removed when a course is deleted
    TL-8364        Removed extra line breaks in Face-to-face messages
    TL-8381        Ensured Hierarchy custom field data is deleted when a Hierarchy item is deleted
    TL-8407        Improved layout of the graph tooltip in Internet Explorer using a rtl langauge
    TL-8409        Prevented saving scheduled reports without a recipient
    TL-8412        Fixed 'menuofchoice' custom field for sidebar filter in report builder
    TL-8419        Fixed issue that prevented blocks from being edited with Totara Dashboard enabled as default home
    TL-8427        Fixed position selecting which was incorrectly disabled when disabling position hierarchies
    TL-8441        Increased maxlength of objective scales value name to 255 characters
    TL-8444        Fixed Program and Certification Membership reports for MSSQL
    TL-8457        Fixed a spelling mistake in the program extension request error message
    TL-8477        Fixed Date (No timezone) user profile field in Report Builder
    TL-8479        Fixed the MSSQL NVARCHAR migration upgrade step
    TL-8482        Removed empty labels when adding/editing External tools
    TL-8496        Fixed count of overdue users on Appraisals report page
    TL-8506        Fixed AJAX deletion of an assigned audience when creating a dashboard
    TL-8508        Fixed untranslatable string "Face-to-face name" in Face-to-face sessions report source
    TL-8521        Improved course participants template for template library
    TL-8538        Fixed dates in ODS exports to use current user timezone to match all other export options
    TL-8583        Session end time is now adjusted in IE11 when start time is adjusted


Release 2.9.4 (22nd February 2016):
===================================


Security issues:

    TL-8235        Included session key check in Face-to-face when adding and removing attendees
    TL-8392        Fixed handling of non-numeric answers to numeric feedback activity questions


New features:

    TL-8115        Added a new URL custom field type

                   This new custom field type can be used in Courses, Hierarchies, Goals and
                   Face-to-face.


Improvements:

    TL-7542        Added a new report source for language customisations
    TL-7970        Added the program and certification completion editor

                   Enabled with Site administration -> Advanced features -> Enable program
                   completion editor. Only users with the 'totara/program:editcompletion'
                   capability (site admins only, by default) will be able to access the new
                   tab 'Completion' when editing a program or certification. For more
                   information, see the community post:
                   https://totara.community/mod/forum/discuss.php?d=11040.

    TL-8276        Removed unused CFG settings from Totara messaging code

                   The removed settings are "message_contacts_refresh", "message_chat_refresh"
                   and "message_offline_time".

    TL-8290        Increased maximum value of sortorder field in the Feedback360 questions table.

                   When running MySQL in particular, the number of questions in one Feedback
                   questionnaire would be limited to 128. This was due to the sortorder field
                   in the corresponding table being of the type tinyint. This sortorder field
                   will now use the bigint datatype for all supported databases, which is
                   consistent with similar fields in other tables.

    TL-8294        Improved layout of the learning plan page at small screen widths
    TL-8365        Added a link to the course completion report from the user profile page
    TL-8371        Changed program course sets to display courses in the order they were added

                   The order of courses in a program or certification course set would vary
                   when returned by a database. They are now ordered by ID of the
                   prog_courseset_course table, making the order more consistent. This means
                   they will be in the same order as what they were when first added to the
                   course set.


Bug fixes:

    TL-6593        Fixed course completion status not updating when changing completion criteria

                   Users were not being marked "In progress" when the were assessed against
                   the new completion criteria.

    TL-8075        Fixed error in HR Import when setting the CSV delimiter to Tab
    TL-8078        Completion progress details page was reworded to more accurately indicate the status

                   Previously, the course status in the Completion progress details page
                   (accessed by clicking the "Progress" bar in Record of Learning: Courses or
                   "More details" in the Completion status block) would show "Not started"
                   even though the learner had actually viewed and completed a SCORM lesson.
                   Moreover, the SCORM activity status would be "Viewed the scorm, Achieved
                   grade" even though the learner had not achieved the grade to complete the
                   activity. These were fixed in this patch. Course status is now "incomplete"
                   as long as its activities are not complete and the activity status
                   correctly indicates the learner failed to achieve the required grade.

    TL-8226        Fixed an issue with the course completion scheduled task

                   There was a problem in the completion cron when a user had completed an
                   activity but hadn't had the module completion record written into the
                   database. The criteria review would call get_info() which now updates the
                   state, creating the module completion record. However the initial review
                   would then continue and due to a dataobject lacking the now existing record
                   it would try to write the record again, causing a conflict. The dataobject
                   is now created after the get_info() call, avoiding this issue.

    TL-8240        Fixed capability checks when assessing a user's access to resources using audience visibility

                   Prior to this fix, in rare situations where the current user was viewing
                   resources available to another user, the access checks were being
                   incorrectly performed for the current user. The checks are now correctly
                   performed for the given user rather than the current user in this
                   situation.

    TL-8253        Fixed a bug which occured in some situations when deleting audiences without members

                   Prior to this fix, if you attempted to delete an audience which had a role
                   assignment associated with it, but not members, you would receive a fatal
                   error. This has now been fixed and the audience is correctly deleted.

    TL-8319        Fixed the display of the "Add audiences" button when setting access rules for dashboards

                   When navigating to the access tab for a dashboard, if the restrict by
                   audience checkbox was already checked then the "Add audience" button would
                   incorrectly be disabled. The button now displays correctly when navigating
                   to the access tab.

    TL-8322        Fixed problem when upgrading to patch T-12199

                   The upgrade step in this patch was changing cohort visibility records for
                   certifications. It tried to change the program-type records to
                   certification-type. Now, if the certification-type record already exists,
                   the program-type record will instead be deleted.

    TL-8361        Fixed incorrect hardcoded max table name length in the XMLDB editor
    TL-8391        Fixed reportbuilder sorting and pagination when Restrict initial display is enabled
    TL-8397        Fixed scheduled task not completing for recurring courses

                   The scheduled task which backs up and restores a recurring course within a
                   program was not successfully completing. This has been fixed.


Contributions:

    * Eugene Venter at Catalyst NZ - TL-7542, TL-8276
    * Hamish Dewe at Orion Health - TL-8371
    * Jo from Kineo - TL-8392


Release 2.9.3 (18th January 2016):
==================================================


Important:

    TL-7896        Fixed dynamic audience rules that reference an organisation menu type custom field

                   Dynamic audience rules for Organisation menu custom fields can have one of
                   two operators, "Equal to" and "Not equal to".
                   Prior to this fix these operators functioned in reverse. "Equal to" would
                   lead to users within an organisation for which the custom field did NOT
                   include the selected options.
                   Likewise if "Not equal to" was used users within organisations for which
                   the selected value was used would be included as audience members.
                   After this fix the operators are applied correctly.

                   If you have dynamic audiences with rules based upon organisation menu
                   custom fields then we strongly recommend you review these dynamic audience
                   rules and the associated audience memberships.
                   During upgrade these rules will be corrected and audience memberships may
                   change.
                   If you have affected audiences, you can fix them without incurring
                   membership changes by following these steps:

                   1. Disable cron prior to your upgrade.
                   2. Upgrade your site.
                   3. Review the dynamic audiences that are affected. If you need memberships
                   to stay exactly the same then changing the condition on the rule from
                   "Equals to" to "Not equals to" (or vice-versa) will ensure that audience
                   memberships stay as they were prior to this version.
                   4. Approve your changes and review the audience memberships.
                   5. Re-enable and run the cron.

    TL-8047        Fixed a bug found within SQL search routines used by both dialog searches and dynamic audience rules

                   Prior to this fix if you had a dynamic audience with two or more rules in a
                   single ruleset, where the rules have comma separated values specified in
                   conjunction with any of the following conditions "Contains", "Is equal to",
                   "Starts with", "Ends with" then membership may be incorrect.
                   The bug is due to multiple value search SQL not being correctly wrapped in
                   brackets.

                   After this fix comma separated values are correctly applied when
                   determining dynamic audience membership.

                   Depending upon the order of the rules and how they apply to the users
                   currently in the audience, membership may or may not change.
                   If you have an audience you believe is affected we strongly urge that you
                   first test this version on a copy of your site and after upgrading, closely
                   review audience membership.
                   You will need to review and amend the audience rules if users are removed
                   and you require them to still be included.

                   This bug may also have affected dialog searches when multiple values were
                   being used. Searching in dialogs now correctly handles multiple search
                   values.


Improvements:

    TL-7560        Added missing foreign key to the type field in the pos_assignment table
    TL-7816        Time can now be set when assigning due dates for programs

                   Previously when setting fixed due dates for a program or certification,
                   only the date could be set but not the time, which would fall at the
                   beginning of the day for the person setting it. Now the time can also be
                   set which means less ambiguity for when a due date will expire,
                   particularly for sites where users are in different timezones from each
                   other.

                   If a user is requesting an extension of their due date in a program, they
                   can also specify the time.

                   If a manager's team members have pending extension requests, the manager
                   can now navigate to the page where these requests are updated via the 'My
                   Team' page. Previously they could only get to the page by a link in an
                   email or typing in the url.

    TL-7973        Added a warning for Report builder when internally required columns break custom aggregations
    TL-8133        Renamed the Position and Organisation Report builder filters to be more consistent
    TL-8144        Improved the multi-lang support for Appraisal management pages
    TL-8155        Improved compatibility with PostgreSQL 9.5
    TL-8166        Added a new column 'Course Completions as Evidence' to the My Team embedded report
    TL-8183        Improved the Face-to-face session room filter
    TL-8210        Replaced the logos in the standardtotararesponsive theme with SVG equivalents
    TL-8228        Improved the multi-lang support for Questions in Appraisals and Feedback360


Bug fixes:

    TL-7012        Fixed the course completion progress bar for courses within a completed learning plan

                   The course completion progress bar was not being correctly displayed in the
                   Record of Learning for course that are part of an already completed
                   learning plan.

    TL-7527        Fixed the default settings for the example appraisal

                   The example appraisal previously required some of the question content to
                   be opened and saved via the interface before goals or competencies could be
                   selected by learners. On a new install of Totara, example appraisals can
                   now be assigned and activated without having to open the question settings
                   beforehand. This also fixes certain instances where a manager could not
                   review goals after they had been reviewed by the learner.

    TL-7608        Increased the maximum character length to 255 for various user fields in HR Import

                   The maximum character length of the institution, department, address and
                   password fields have been increased to 255 to match those allowed through
                   the user interface.

    TL-7809        Updated the language strings for the learning plans "objectives approval" and "status" columns
    TL-7826        Course completions stats on the My Team report now include RPL completions

                   Switched the Course Completion statistics on the My Team embedded report to
                   use course_completion records instead of block_totara_stats.

    TL-7946        Removed the link from progress icon if the user is not enrolled in the course

                   If a user is enrolled in a program but not yet enrolled in a course within
                   that program (e.g. they have not yet launched the course), the progress
                   icon included a link to their completion status. Clicking on this would
                   take that user to a page with an error saying they are not yet enrolled.
                   The progress icon now only acts as a link if they are enrolled or already
                   have a completion status by some other means.

    TL-7978        Fixed the layout of strings in the Completion status block

                   A couple of strings in the completion status block were appended together
                   and were missing spaces. The second part of the string is now passed into
                   the language string which fixes the layout and also allow the string to be
                   translated correctly which previously was not possible.

    TL-8041        Fixed access controls when adding and removing audiences while editing courses

                   When adding audiences via the course edit page, the checks are now ensuring
                   that the cohort enrolment plugin is enabled and that the logged in user has
                   the capabilities 'moodle/course:enrolconfig' and 'enrol/cohort:config' in
                   the course or higher context. Also the audience selector now only displays
                   audiences that the user can view (with 'moodle/cohort:view' in the
                   necessary context).

    TL-8049        Fixed an error when hiding blocks on learning plan pages

                   Previously when trying to hide a block in a learning plan page
                   (totara/plan/component.php) an error would be displayed and the block would
                   not be hidden.

    TL-8056        Fixed styles for the assignment marking guide criterion form section
    TL-8083        Removed dashboards associated with deleted audiences
    TL-8124        Fixed error when deleting course with Face-to-face activities
    TL-8127        Fixed filters requiring javascript in embedded Audience Member reports
    TL-8128        Fixed the link edit current user profile in the navigation block
    TL-8129        Fixed the homepage redirect when a dashboard is set to be the default homepage
    TL-8135        Fixed the risk displayed for the totara/program:markstaffcoursecomplete capability
    TL-8160        HR Import now correctly sets the default language when creating users
    TL-8167        The Graphical report block now uses the default sort order of the report
    TL-8173        Fixed HTML validation error due to missing closing div tag on the program assignments page
    TL-8184        Stopped timezones being displayed in Face-to-face reports when they are disabled in the plugin settings
    TL-8185        Fixed the pagination on the "Manage programs" and "Manage certifications" pages
    TL-8191        Fixed the validation of Report builder date filters using number of days selectors
    TL-8197        Fixed text placement for RTL graphical reports in Internet Explorer and Edge
    TL-8207        Fixed notice when editing pages with custom block regions without JavaScript
    TL-8221        Course icons are now shown in all circumstances

                   With the enhanced course catalogue disabled, the course icons previously
                   had an incorrect URL causing them to not be displayed. We now validate the
                   URL to ensure it is correct.

    TL-8229        Changed the required learning page to show user's program details even if complete

                   Previously, if a manager tried to view a learner's program or certification
                   and it was complete, the manager would instead see their own status in the
                   program or the learner's Required Learning page, rather than their
                   student's.

    TL-8231        Switched the Face-to-face edit attendees from sending GET params to POST params

                   Prior to this change when editing the attendees of a Face-to-face session
                   the dialog would submit any changes made as GET params.
                   If the session had hundreds or thousands of attendees this could lead to an
                   exceptionally long URL.
                   The length of the URL may then cause problems on some systems, particularly
                   IIS and any site running Suhosin.

    TL-8245        Fixed cohort log data in site logs
    TL-8251        Fixed an error with updating competency properties in a learning plan with JavaScript disabled

                   When updating either priority or status for a competency in a Learning Plan
                   with JavaScript turned off there was a error message thrown. The update was
                   saved but an message was displayed every time there was an update.

    TL-8263        Fixed room validation during Face-to-face session creation when the datetime is not known

                   Prior to this fix when creating a Face-to-face sessions, if a room is
                   selected then a date is selected which causes a resource conflict when
                   saving. If the user then sets "date/time known" to "No" the validation
                   would still fail and stop the session from being saved.


Contributions:

    * Pavel Tsakalidis from Kineo UK - TL-7560
    * Russell England from Kineo USA - TL-8191


Release 2.9.2 (14th December 2015):
===================================


Security issues:

    TL-7957        Totara Connect now prevents reuse of ssotokens for user logins
    TL-7975        Added read/write access controls to group assignment code throughout Totara
    TL-8076        Prevented access to external blog pages when either blogs or external blogs are disabled


New features:

    TL-7679        New PDF export plugin in Report builder using wkhtmltopdf binary

                   This export plugin is compatible with RTL languages, has increased
                   performance, and lowered memory use.


Improvements:

    TL-4429        Added an advanced multi-item course name filter to various Report builder report sources
    TL-6283        Removed all uses of deprecated function sql_fullname in Feedback360
    TL-6474        Shortened the display of Report builder Graph labels and legend entries

                   It is now possible to specify limitations on label length for Report
                   builder graphs using the following syntax in the custom settings input:
                     label_shorten = 20
                     legend_shorten = 40

                   To get the previous behaviour without shortening use value 0.

    TL-7810        Improved the performance of building hierarchy lists
    TL-8020        Added advanced multi-item Position and Organisation name filters to various Report builder report sources
    TL-8061        Changed the default settings for badge owner notifications to always send email

                   This only effects new installs. Before this patch, by default, badge
                   creators would not receive an email (or any notification) if they were
                   logged in.

    TL-8065        Improved the accessibility of the question bank export
    TL-8066        Improved the performance of the Audience enrolments sync
    TL-8073        Blogs are now disabled by default in new installations
    TL-8093        Improved the display of select boxes with a specified size


Bug fixes:

    TL-6789        Fixed the handling of transactions when exceptions occur within HR Import user sync

                   Prior to this patch, if an exception was generated while assigning user
                   positions, the exception would be handled and processing would continue.
                   However, if the exception occurred within a database transaction, the
                   transaction was not being cleaned up.

    TL-7355        Managers approving Face-to-face booking requests are now notified of the updates success

                   Previously, when a manager approved staff requests for bookings into a
                   Face-to-face session, they would then be redirected to a page saying 'You
                   can not enrol yourself in this course' (assuming they were not enrolled or
                   did not have other permissions to view the attendees page). Following any
                   approvals (by a manager or any other user), the page will now refresh onto
                   the approval required page, with a message confirming the update was
                   successful.

    TL-7426        Fixed the course completion status of users after their RPL is deleted

                   Previously their completion would not be re-aggregated until they made
                   further progress in the course, now it is re-aggregated immediately.

    TL-7504        Updated the permissions for the unobscured user email column in Report builder reports

                   Previously the unobscured user email column and filter were only shown when
                   email was turned on in user identity settings, now it is also shown if the
                   user has the site:config capability. This ensures that the admin can use
                   these columns regardless of the user identity setting.

    TL-7521        Fixed values for position start and end dates when syncing with database source

                   If an external database that was being synced via HR Import contained a
                   Null value for position start date and position end date, this was throwing
                   an error. Null values will now mean that no value will be added to the
                   position details.

                   In addition to this, if a position start or end date field contained the
                   value 0, the value added into the position details in Totara would be the
                   current time. This has been changed such that 0 and null are equivalent and
                   result in no value being added to the position details. This is consistent
                   with imports via CSV.

    TL-7620        Fixed the display of defaults for text input custom fields in Report builder
    TL-7712        Fixed an issue with assigning a large number of users to programs

                   Previously when a large number of individuals were already assigned to a
                   program, adding more assignments could lead to an HTTP 414 error due to a
                   large amount of data being included in the URL.

    TL-7729        Replaced hardcoded strings with lang strings in the old program catalog
    TL-7731        Fixed the display of non-latin characters in program summaries when viewing Report builder reports
    TL-7781        Fixed pop-up behaviour for admins using a single-activity course with a file
    TL-7842        Fixed stuck certification completions due to a bug previously fixed in TL-6979

                   Certifications which experienced the problem which was fixed in TL-6979
                   would be stuck after upgrading. This patch will repair those records by
                   triggering the window open event again. The records will be in the correct
                   state after installing the upgrade and then running the Update
                   Certifications scheduled task.

    TL-7879        Stopped Program un-enrolment messages being sent to suspended users
    TL-7904        Fixed Terms & Conditions dialog box for Face-to-face direct enrolment plugin
    TL-7911        Fixed the restoration of certificate user information on different sites
    TL-7915        Added missing include to the Competency Status History report source
    TL-7917        Fixed the "User is suspended" dynamic audience rule when it is used more than once in the same rule set
    TL-7925        Fixed an issue with duplicate grade items when using the assignment submissions report source
    TL-7927        Fixed SCORM activities set to "display package" in the "new window (simple)" mode
    TL-7931        Fixed the booked-by & actioned columns in Face-to-face session report sources

                   The columns now display the actual user name and link instead of the
                   "Reserved" word for the "Booked by" and "Actioned by" columns.

    TL-7953        Stopped the surround legend style from being applied to child elements in Totara themes
    TL-7965        Fixed consecutive usage of the Face-to-face attendees menu option

                   Previously after adding or removing users via the attendees page, you would
                   have to refresh the page before it would work again.

    TL-7966        Replaced hardcoded "Advanced options" string with a translatable string in Report builder
    TL-7971        Corrected the positioning of short form date pickers for rtl languages
    TL-7980        Fixed the deletion of scheduled reports
    TL-7997        Fixed the shortname field for Goal types
    TL-8010        Removed unformatted html from output when exporting a user's Record of Learning to PDF
    TL-8026        Fixed the display of Face-to-face session details within Calendar events
    TL-8048        Fixed the sidebar filter for Report builder reports with paging

                   When a sidebar filter is changed, you will be taken back to the first page
                   of results (as happens with other search and filters). This patch also
                   fixes a problem which occurred if the toolbar search was used immediately
                   after using a sidebar filter.

    TL-8050        Prevent the deletion of unrelated scheduled report recipients when deleting a scheduled report

                   Previously if the ID of the scheduled report being deleted matched the ID
                   of a Report builder report, all recipients for scheduled reports based off
                   that report would also be incorrectly deleted.

    TL-8121        Corrected the display of certification due dates when exporting to pdf


Contributions:

    * Artur Rietz at Webanywhere - TL-8010
    * Chris Wharton at Catalyst NZ and Haitham Gasim at Kineo USA - TL-7980
    * Haitham Gasim at Kineo USA - TL-8026
    * Pavel Tsakalidis at Kineo UK - TL-7975
    * Tim Price at Catalyst Australia - TL-7911


Release 2.9.1.1 (9th December 2015):
====================================


Bug fixes:

    TL-8096        Fixed course module completion calculation

                   This fixes a regression introduced by TL-6981 in 2.9.1, 2.7.9, 2.6.26, and
                   2.5.33 in which the calculation of course module completion would lead to
                   all activities being marked complete incorrectly for a user.
                   The problem occurs when the user completes the first activity in the
                   course. It occurs if the user manually marks themselves complete, achieves
                   the completion criteria (with the exception of "Student must view this
                   activity to complete it"), or is marked complete by a trainer. The user
                   must then log out and log back in again in order to see the problem.
                   The problem will not occur if it is not the first activity in the course.
                   When this occurs all activities in the course will be marked complete,
                   regardless of which section or order they are within the course and
                   regardless of whether they are required for course completion or not.


Release 2.9.1 (16th November 2015):
==================================================


Security issues:

    TL-7886        Fixed access checks for the position assignment AJAX script
    TL-7829        Removed reliance on url parameter for choosing table

                   The script for getting the positions and organisations to assign to a
                   program relied on a url parameter to choose which table to access. The
                   table is now chosen according to the type of hierarchy that the query is
                   for.

    MoodleHQ       Security fixes from MoodleHQ http://docs.moodle.org/dev/Moodle_2.9.3_release_notes

                   Security related issues:
                   * MDL-51861 enrol: Don't get all parts in get_enrolled_users with groups
                   * MDL-51684 badges: Make sure 'moodle/badges:viewbadges' is respected
                   * MDL-51569 mod_choice: validate user actions and availability
                   * MDL-51091 core_registration: session key check in registration.
                   * MDL-51000 editor_atto: No autosave for guests
                   * MDL-50837 mod_scorm: Fix availability checks
                   * MDL-50426 messaging: Fix permissions checks when sending messages
                   * MDL-49940 mod_survey: Fix XSS
                   * MDL-48109 mod_lesson: prevent CSRF on password protected lesson


Improvements:

    TL-6282        Improved handling and displaying of the user's name in Core dialogs
    TL-6529        Added the manager's email as a selectable column for reports that include user's position fields
    TL-6657        Added actual due dates to program Assignments and audience Enrolled Learning tabs

                   The Assignments tab in programs and certifications and the Enrolled
                   Learning tab in audiences now include a column "Actual due date". This new
                   column shows the due date that the user will see. For group assignments
                   (such as audiences or organisations), clicking the "View dates" link will
                   show a popup with a list of all assigned users relating to that group
                   assignment. The help popup for the "Actual due date" column explains why
                   assignment due dates may be different from the actual due dates. After
                   upgrading, the "Actual due date" field can be manually added to the
                   "Audience: Enrolled Learning" embedded report, or you can reset it to the
                   default to have it automatically added.

    TL-7183        Trigger updates to program user assignments when changing assignments via the audience Enrolled Learning tab

                   When you make a change in an audience's Enrolled Learning tab, it will
                   immediately trigger an update of program and certification user
                   assignments. If there are less than 200 total users involved in the program
                   then the users will be processed immediately, otherwise the update will be
                   deferred. By default, deferred program user assignments are processed the
                   next time cron runs. This patch makes the behaviour consistent with making
                   changes in a program's Assignments tab.

    TL-7256        Mark programs for deferred user assignment update when assignment membership changes

                   This patch includes several improvements which cause program and
                   certification memberships to be updated sooner:
                   * When audience membership changes, either by a user manually editing an
                   audience or when a dynamic audience's membership is automatically updated,
                   related programs and certifications will be marked as having user
                   assignment changes deferred.
                   * When a user's assigned position, organisation or manager change, programs
                   and certifications related to the old and new positions, organisations and
                   management hierarchy are marked as having user assignment changes
                   deferred.

                   With this change in place, all changes to program membership should now be
                   detected as they occur and are either processed immediately or by the
                   "Deferred program assignments changes" scheduled task. As such, we
                   recommend setting the related tasks to their default schedules: "Deferred
                   program assignments changes" can be run every time cron runs, while
                   "Program user assignments" only needs to be run once per day.

    TL-7575        Removed Totara menu from the print layout
    TL-7741        Removed HTML table behind the Weekend Days setting
    TL-7745        Added labels to settings on Site administration > Front page > Front page settings
    TL-7748        Improved Accessibility when editing the Site administration > Plugins > Activity modules > Quiz Settings
    TL-7750        Improved layout of "User Fullname (with links to learning components)" Report builder column
    TL-7792        Added settings to enforce https access and prevent embedding of content in external Flash and PDF files
    TL-7813        Reduced events triggered when program user assignments are updated

                   Some events were being triggered unnecessarily when updating program and
                   certification user assignments. They will now only be triggered if it is
                   certain that there are changes that need to be signalled.

                   Please remember that user_assignments_task by default is scheduled to
                   execute just once per day, whereas assignments_deferred_task is designed to
                   be run every time cron runs.

    TL-7824        Moved program user assignment deferred flag reset to start of function

                   If changes are made to a program's assignments while the function is
                   running in cron, those changes will be processed the next time the deferred program
                   assignments scheduled task runs (default: Every cron run), rather than having to
                   wait until  program user assignments scheduled task runs (default: Nightly) or
                   another change is made.

    TL-7878        Added a page title when adding and removing Feedback360 requests with javascript turned off


Bug fixes:

    TL-6936        Face-to-face direct enrolment plugin allows users to signup to more then one Face-to-face.

                   Users can now sign up to one session per Face-to-face in the course via the
                   Face-to-face direct enrolment plugin. If at least one of the session
                   signups was successful then user will be enrolled to the course.

                   If all successful signups require managers approval then course enrolment
                   will be pending. T&Cs when enabled are required and will be checked before
                   any signups or enrolments are processed.

    TL-6957        Display correct due date value in the upcoming certifications block
    TL-6981        Reaggregate course completion when activity completion criteria are unlocked without deletion

                   Previously, course completion status was only reaggregated if "unlock with
                   delete" was used. If "unlock without delete" was used, it was possible that
                   users who meet the new completion criteria were not marked complete, and
                   this would not be fixed by any cron task. This could lead to users being
                   stuck with an incomplete status. Now, the records will be marked for
                   reaggregation and will be processed by the completion cron task.

    TL-7273        Corrected the help text for Report builder simple select filters

                   Filters that use a drop-down select with no additional options such as 'not
                   equal to' now have correct corresponding help text, rather than referring
                   to additional options that do not exist.

    TL-7437        Switched the badges backpack URL to use HTTPS
    TL-7514        Fixed the display order of Face-to-face sessions for the Face-to-face direct enrolment plugin

                   Sessions will now be displayed in order of their start date/times instead
                   of when they were created

    TL-7559        Enabled the transfer of position and organistion custom fields for the database source of HR Sync
    TL-7562        Fixed strings for audience rules based on course and program completion
    TL-7594        Fixed users booked on a Face-to-face session with no dates being incorrectly removed when another user cancels their booking
    TL-7602        Re-enabled the Save and Cancel buttons for the Face-to-face take attendance tab

                   Save and Cancel buttons present in previous versions have been reintroduced
                   to the Face-to-face take attendance tab. Save must be clicked in order to
                   update attendance records.

    TL-7611        Fixed the handling of username and suspended fields for external database sources in HR Import
    TL-7644        Corrected the amount of white space in the 'recordoflearning' language string
    TL-7659        Prevented cancellation notifications being sent to users booked in completed Face-to-face sessions when the course is deleted
    TL-7660        Fixed the behaviour of pagination on hierarchy index pages

                   When viewing Positions, Organisations, Competencies or Goals within a
                   framework, pagination was not working correctly and instead was displaying
                   all of the items even though the paging bar was displaying the correct
                   number of pages.

    TL-7664        Fixed dynamic audience rules based upon checkbox position custom fields
    TL-7675        Fixed the display of an aggregation warning for Report builder columns

                   The warning that column aggregation options may not be compatible with
                   reports that use aggregation internally is now shown only for reports that
                   actually use aggregation internally.

    TL-7676        Fixed the display of duplicate categories in pie charts
    TL-7686        Fixed URL validation when adding new links to the quicklinks block
    TL-7695        Re-aggregate when course completion criteria is changed without deletion

                   When changing course completion criteria, and unlocking without deleting
                   existing completion data, re-aggregation was not being performed. Now,
                   users who are assigned but not complete and match the new criteria will be
                   marked complete after cron re-aggregates them. To fix any users affected by
                   this problem, an upgrade script will mark all incomplete users in all
                   courses for re-aggregation, which will be performed by cron, and may take a
                   while to process on larger sites.

    TL-7698        Fixed the handling of position and organisation 'Text area' custom fields within HR Import
    TL-7711        Fixed the "duedate(extra info)" column for Report builder export to pdf
    TL-7724        Fixed an error when adding audience visibility during program creation.

                   A user who was assigned the site manager role within a category context
                   would previously be presented with an error when giving audiences
                   visibility during program creation. This error no longer appears.

    TL-7732        Allow HR import to set posenddate value as blank when posstartdate is set
    TL-7769        The Report builder "Manager's name" filter now counts users without a manager as "empty"
    TL-7770        Fixed date validation for Face-to-face sessions when removing dates or wait-listing a session
    TL-7783        Fixed the ordering of the Face-to-face waitlist

                   Previously when a user cancelled an overbooked session the Face-to-face
                   replaced them with a user from the waitlist based off the user's names, now
                   the replacement is decided based off their signup time.

    TL-7784        Fixed the help text for Face-to-face 'minimum capacity' setting
    TL-7789        Fixed the formatting of the Face-to-face intro page
    TL-7821        Fixed a Totara Connect upgrade step that introduced a non-existent local plugin
    TL-7833        Fixed cron failure when sending Face-to-face notifications

                   When scheduled Face-to-face notifications were being sent out, the cron
                   task would potentially fail if notifications were going to users who had
                   their session bookings approved by a manager. This has now been fixed,
                   notifications go out as normal, and cron is not disrupted.

    TL-7836        Ensured images are restricted by their assigned widths

                   If an image is resized from its native dimensions and then displayed,
                   Internet Explorer would display the image at its native size, and not the
                   size that had been requested.

    TL-7844        Grader report now scrolls when it is too wide for the screen
    TL-7849        Removed reports and saved searches from the report table block when users do not have access
    TL-7851        Fixed the display of the "duedates" column for program and certification overview Report builder reports
    TL-7876        Stopped the incorrect archiving of facetoface sessions without dates

                   Previously if a user was waitlisted on a Face-to-face session which had no
                   dates set, in a Certification Course. When the user's recertification
                   window opened, the signup would be marked as archived, meaning it would no
                   longer count towards course completion.

    TL-7881        Recreate course completion records when activity criteria are reset with deletion

                   Course completion records for users who were not complete according to the
                   new criteria were not being recreated immediately. Although the records
                   were being created when the completion cron task was run or when a user's
                   status in the course changed, it was possible that some unexpected
                   behaviour could have occurred due to the missing records.

    TL-7883        Fixed date handling on the Face-to-face block calendar page
    TL-7909        Make sure url params are passed when using report builder toolbar search
    TL-7921        Fixed regression with media playback when request_order="GPC" in PHP.ini


Release 2.9.0 (3rd November 2015):
==================================

New features:

    TL-2250        New options to turn off extension requests for programs and certifications

                   It is now possible to disable program/certification extension requests both
                   for the whole system and for individual instances via the edit details page.

    TL-2565        New "Fixed date" custom user profile field type

                   A fixed date new custom user profile field type has been added.
                   This new field type is designed to store absolute dates for which
                   timezones are irrelevant and not applied.
                   An example of where this field is applicable is your birthday.
                   Regardless of your location you birthday is the same.

                   Data for this date field is stored as midday UTC for the selected date.

    TL-7098        Dynamic audience rules for the new fixed date custom user profile field

                   These rules include being able to define an audience according to their
                   custom profile field date being before or after a fixed date.
                   There are also duration-based rules, meaning they can be set according to
                   the profile dates being within or before a previous number of days, as well
                   as within or after an upcoming number of days.

                   Notes:
                      * With duration-based rules audiences will be updated at midday UTC.

    TL-4485        New personal goal types with custom fields

                   Personal goal types can now be defined and custom fields added to them.
                   Preexisting goal types have been renamed to company goal types and continue to
                   function as they did previously.
                   Personal goal types differ from company goal types in that the custom field
                   values entered for a personal goal will be associated with the user whom
                   the personal goal belongs to.

                   Thanks to Ryan Lafferty at Learning Pool for the contribution.

    TL-5094        Support for anonymous 360 feedback

                   We have added a new "Anonymous" setting when creating or editing a
                   360 Feedback.
                   Enabling this hides the name of responders to the 360 Feedback.
                   Responders can see whether a 360 Feedback is anonymous in the header of the
                   response page, along with the number of requests sent.
                   This setting does restrict some functionality to maintain anonymity:

                      * When enabled, requests for feedback can only be added, they can not be
                        cancelled. This is to stop users from potentially cancelling all
                        requests in order to figure out who has replied.
                      * Reminders can still be sent but who will receive them will no
                        longer be displayed to you.

                   While we have endeavoured to enforce anonymity please be aware that
                   responders are still recorded and there are ways to get this information
                   out of the system, such as:
                      * Site logs.
                      * Malicious code written to reveal it.
                      * Direct database investigation.
                      * Activity logs
                      * Blocks displaying Recently logged in users

    TL-5097        New options to disable changes in membership for dynamic audiences

                   With this feature you can now control if users are automatically added or
                   removed from a dynamic audience's membership when they meet, or no longer
                   meet, the criteria defined by the rule set.
                   This is facilitated with a new setting "Automatically update membership"
                   which can be found on the rule sets tab when editing a dynamic audience.
                   This new setting has two options, both of which are enabled by default:

                      * Make a user a member when they meet rule sets criteria
                      * Remove a user's membership when they no longer meet the rule sets
                        criteria

                   Toggling these settings allows you to prevent new members being added to
                   the audience and/or prevent users from being removed from the audience.

    TL-5818        Report builder reports can now be cloned

                   Added a new action that clones reports to the Report builder manage
                   reports page. Cloning a report creates a full copy of the report
                   and its columns, filters and settings. It does not copy any scheduling
                   or caching that has been set up for the report.
                   Both user created and embedded reports can be cloned.

                   In order to clone a report the user must have the
                   totara/reportbuilder:managereports capability.

    TL-6023        New program completion block

                   A new program completion block has been added in this release that lists
                   one or  more selected programs and the users completion status for each.
                   If no programs have been selected the block is not displayed.

                   Thanks to Eugene Venter at Catalyst NZ for the contribution

    TL-6525        New report table block

                   Added new block that displays tabular data belonging to a selected Report
                   builder report.
                   Optionally a saved search for the report can also be selected to limit the
                   data shown in the block.

                   Notes:
                      * Backward incompatibility of saved searches with third party filters
                        might occur. If saved searches do not work with your third party
                        filters, please contact the developer of the filters to update them.
                      * Only user created reports can be selected. Embedded reports cannot
                        be used within this block at present.

                   Thanks to Dennis Heany at Learning Pool for the contribution.

    TL-6621        Report Builder export is now pluggable

                   Reportbuilder export code was abstracted into a new plugin type 'tabexport'
                   located in folder '/core/tabexport'.
                   Tabular export plugins may now have settings and administrator can disable
                   individual export plugins.
                   New plugins have to include a class named '\tabexport_xyz\writer' that extend
                   base class '\totara_core\tabexport_writer'.
                   The base class contains a basic developer documentation, included plugins
                   can be used as examples.

    TL-6684        Added new global report restrictions

                   Global report restrictions allows rules to be applied to a report
                   restricting the results to those belonging to the users you are allowed to
                   view.
                   This allows you to define relationships between groups of users limiting
                   the information a user can see to just information belonging to the
                   permitted group or groups of users.

                   Notes:
                      * All users including the administrator can be restricted.
                      * Global report restrictions are only supported by report sources where
                        data can be seen as owned by one or more users.
                      * There are internal limitations for some database backends. For example
                        MySQL is limited to 61 tables in one join which may limit the maximum
                        number of items in each restriction.
                      * Active restrictions may impact the performance of reports.
                      * Report caching is not compatible with Global Report Restrictions and
                        is ignored when restrictions are active.

                   Thanks to Maccabi Healthcare Services for funding the development of this
                   feature.

    TL-6942        Define and use different flavours of Totara during installation and upgrade

                   A new feature set including plugin type has been added called Flavours.
                   Flavours can change default settings, the sites actual settings, and force
                   settings into a specific state.
                   During installation and upgrade the selected Flavour is applied allowing it
                   to control which settings get turned on or off.
                   It is also given the opportunity to execute code post installation or
                   upgrade.

                   Notes:
                      * Sites that do not use a specific flavour will default to the
                        Enterprise flavour that ships with Totara.
                        This flavour does not make any configuration changes.
                      * This feature was added for the benefit of Totara Cloud to allow us to
                        control cloud functionality. It is not used by default by Totara and
                        provides no new functionality

    TL-7021        Added a new setting to the advanced features page that enables/disables Competencies
    TL-7105        Added optional support for producing PDF snapshots of Appraisals via wkhtmltopdf executable
    TL-7246        Totara Connect Client

                   Totara Connect makes it possible to connect one or more Totara LMS or
                   Totara Social installations to a master Totara LMS installation.
                   This connection allows for users, and audiences to be synchronised from the
                   master to all connected client sites.
                   Synchronised users can move between the connected sites with ease
                   thanks to the single sign on system accompanying Totara Connect.


Improvements:

    TL-2368        Capability to manage reminders added to courses

                   A new capability moodle/course:managereminders has been created to allow
                   fine grained control over which roles can manage reminders within a
                   course.
                   Prior to this capability the moodle/course:update capability was used.
                   Now both capabilities are required.
                   Only site managers are given this capability by default.

    TL-5020        Change of 'from' email address when sending emails

                   Now all Totara messages (Email and alerts) use the system wide
                   "noreply" address as the "From" address.
                   This can be configured by the admin via 'Site administration > Plugins >
                   Message outputs > Email > No reply address'.

                   In the case of Facetoface, where there is another setting in 'Site
                   administration > Plugins > Activity modules > Facetoface > Sender From' the
                   system will send Facetoface messages from that address if it is set, or from
                   the no-Reply address otherwise.

    TL-5088        Logo now scales for smaller screens

                   When using the Custom Totara Responsive theme with a custom logo uploaded,
                   it now scales on smaller screens

    TL-5239        Added several new columns to the site log report source

                   This change introduced several new columns to the site log report source:

                      * A new column "Event name with link"
                      * A new column "Context"
                      * A new column "Component"
                      * A new filter "Event name"

                   This now facilitates filtering by event name (for example this report can
                   can show only "Course Completed" events) as well as providing a column that
                   links to event source (corresponding course, report, user, etc).

    TL-5356        Added new user columns and filters to the Messages report source

                   The standard user information columns and filters have been added to the
                   messages report source.
                   This allows you to find out information such as who the message was sent
                   to.

    TL-5362        Assessor, Regional Manager and Regional Trainer roles were removed

                   Previously roles Assessor, Regional Manager and Regional Trainer were
                   automatically created during Totara installation. These roles did not have
                   any special capabilities by default.
                   As of Totara 2.9.0 these roles will no longer be automatically created.
                   If you need them you may easily create them and add any required
                   capabilities.

    TL-5394        The initialdisplay setting can now be passed when creating embedded reports

                   Thanks to Russell England at Vision NV for the contribution.

    TL-5511        Added pagination to the bottom of Report builder reports

                   When viewing a report pagination is now shown both above and below the
                   report.

    TL-5954        Added a link between the start and finish date pickers for Facetoface sessions

                   The start and finish date pickers are now linked so when changing the start
                   date the finish date will automatically change to the same day.

    TL-6022        Added the AND operator to course set operators in Programs and Certifications

                   This allows people to create rules such as "Learner must complete one of
                   course1, course2, course3 AND one of course4, course5, course6".

                   Thanks to Eugene Venter at Catalyst NZ for the contribution.

    TL-6154        Refactor filters to use named constants instead of numbers

                   Previously filter operators within code were represented just as integers.
                   This change introduced new constants to make the handling of these
                   operators much clearer.

    TL-6204        Customisable font when exporting a report to PDF

                   A new setting has been introduced that allows the font used within Report
                   builder PDF exports to be customised.
                   This allows those on non-standard installations to work around required
                   fonts that they do not have.

    TL-6206        Improvements of CSV export in Report builder

                   CSV export now starts sending data immediately instead of waiting for the
                   whole file to be generated.
                   The total processing time is the same, memory usage is decreased and users
                   may download the file in the background.

    TL-6308        Improve control over who can approve Facetoface attendance requests

                   A new capability mod/facetoface:approveanyrequest has been added that is
                   now required in order to approve Facetoface session attendance request.
                   Prior to this patch only site administrators or a user's assigned manager
                   could approve a request. Now anyone with this capability can also approve
                   an attendance request.
                   This capability is not given to anyone by default.

                   Thanks to Eugene Venter at Catalyst NZ for the contribution.

    TL-6383        Improved accessibility of the general settings page

                   Previously there was an empty label associated with the warnings checkbox
                   following the "Send notifications for" label. This has now been improved so
                   that "Send notifications for" is now a legend and the "Errors" and
                   "Warnings" checkboxes now have labels correctly assigned to them.

    TL-6413        Report builder sources may now be marked as ignored

                   The Report builder report source API has been extended so that report
                   sources can now inform Report Builder that they should be ignored.
                   Report builder can then choose to treat an ignored source differently, at
                   the very least ensuring that it is not accessible.

                   This can be used in situations such as when the source depends upon a
                   plugin or feature being enabled.
                   Previously this would could lead to errors if someone tried to use the
                   source.
                   Now it is dealt with gracefully.

    TL-6414        Added several new placeholders to Facetoface notifications

                   The following placeholders were added for notification emails regarding
                   Facetoface sessions:

                      * [lateststarttime] - Start time of the session. If there are multiple
                        session dates it will use the last one.
                      * [lateststartdate] - Date at the start of the session. If there are
                        multiple session dates it will use the last one.
                      * [latestfinishtime] - Finish time of the session. If there are multiple
                        session dates it will use the last one.
                      * [latestfinishdate] - Date at the end of the session. If there are
                        multiple session dates it will use the last one.

                   These can be used in conjunction with existing placeholders that use the
                   first session date. For example: "[starttime], [startdate] to
                   [latestfinishtime], [latestfinishdate]" will give the overall start and
                   finish times of a multi-date session.

    TL-6451        Added option in HR Import to force password reset when undeleting users

                   Previously, users would have their password reset if it was not provided in
                   the same import. This change makes the reset optional. If a password is
                   provided in the import then it will still take precedence and the reset
                   will not occur.

    TL-6453        Added a time due column to the program and certification completion report sources

                   Thanks to Eugene Venter at Catalyst NZ for the contribution

    TL-6454        Improved the robustness of Facetoface archive tests

                   Thanks to Andrew Hancox at Synergy Learning for the contribution.

    TL-6496        Added several new filters to the certification overview report source

                   The following filters have been added to the certification overview report
                   source:

                      * Add status
                      * Renewal status
                      * Time completed

                   Thanks to Eugene Venter at Catalyst NZ for the contribution.

    TL-6497        Added a timedue filter to the program overview report source

                   Thanks to Eugene Venter at Catalyst NZ for the contribution.

    TL-6531        Improved the performance of prog_get_all_programs

                   Thanks to Pavel Tsakalidis at Kineo UK for the contribution

    TL-6605        Improved the alignment of advanced checkbox labels in all themes
    TL-6629        DOMPDF library has been updated to 0.6.1

                   DOMPDF has been upgraded from 0.6.0 to 0.6.1.
                   This upgrade includes a large number of fixes to both bugs and stability.

    TL-6655        Removed legacy md5 hashing from lessons and lesson user / group overrides
    TL-6667        Course completion now correctly considers activity completion without a grade as complete

                   This patch fixes an inconsistency in how activity completion gets treated.
                   Prior to this patch if you achieved activity completion without getting a
                   passing grade for that activity some places would consider it as complete
                   and others would not.
                   This is now consistently and correctly considered to be complete by all
                   areas of Totara that work with activity completion.

    TL-6676        Improved responsive design when viewing an appraisal
    TL-6761        Updated jQuery dataTables plugin from version 1.9.4 to 1.10.7
    TL-6777        Minified Totara specific JavaScript files

                   Currently there are a large number of JavaScript files that are transferred
                   from your Totara server that are not minified. This issue minifies files
                   for Totara dialogues, and ensures the jQuery plugin files that we use are
                   minified. The minified files are only delivered if the cachejs
                   configuration value is set to true (as it should be on production sites).

                   Minified files reduce the amount of data that is transferred from the
                   server to the browser, resulting in faster page loading times (although
                   this may not be noticeable).

    TL-6880        Role definition page now shows the number of users having each role
    TL-6913        Reviewed language strings that used Moodle and improved them where required
    TL-6915        Forum post emails are now using "totaraforumX" in list ids.
    TL-6919        Installation and upgrade reliability improvements

                   The following changes have been made to the installation and upgrade
                   process:

                   * Fixes and improvements in install and upgrade logic
                   * Fixed Totara plugin environment tests
                   * Fixed missing 'canvas' theme when upgrading from Moodle

    TL-6920        Session Cookie names now use Totara as a prefix
    TL-6926        Improved memcached cache store prefix handling

                   Prior to this patch if no prefix was specified and the settings for the
                   cache store instance were changed, the cache environment could become
                   corrupt and the cache would need to be manually purged.
                   Now if no prefix is specified, a hash is generated from the store instance
                   settings and this is used as the prefix.
                   As the prefix will now change when the settings change, keys cannot conflict
                   and this avoids any need to manually purge the cache.
                   Those who have a prefix set will still need to manually manage their
                   memcached purging if they change any settings.

    TL-6931        Curl requests made by Totara now use a specific TotaraBot agent string
    TL-6943        Support generating of tree structures when bulk adding hierarchy items

                   It is now possible to use the bulk add functionality to generate a tree
                   structure instead of just a flat list of new items. Use 2 spaces in front
                   of the item name to indent an item by one level.

    TL-6961        Each custom field type is now managed by a single capability

                   In Totara 2.7 and all older versions every custom field type had three
                   capabilities used to manage them (create, edit, delete).
                   This improvement sees the capabilities simplified so that each custom field
                   type uses only a single capability for management instead of the three.
                   This ensures that any actions taken by a user can also be undone, it also
                   greatly simplifies management of capabilities around custom field types.
                   The old create, edit, and delete capabilities have been removed.

                   The following is a list of custom field types and the new capability that
                   is used to manage them:

                   * Facetoface custom fields managed by mod/facetoface:managecustomfield
                   * Course custom fields managed by totara/core:coursemanagecustomfield
                   * Program and Certification custom fields managed by
                     totara/core:programmanagecustomfield
                   * Competency custom fields managed by
                     totara/hierarchy:competencymanagecustomfield
                   * Goal custom fields managed by totara/hierarchy:goalmanagecustomfield
                   * Organisation custom fields managed by
                     totara/hierarchy:organisationmanagecustomfield
                   * Position custom fields managed by
                     totara/hierarchy:positionmanagecustomfield

    TL-7013        Installer now only shows available language packs

                   Prior to this change the installation process showed all possible language
                   packs, rather than just those that were available.

    TL-7019        Dashboard functionality can now be disabled via advanced features
    TL-7022        My Team functionality can now be disabled via advanced features
    TL-7031        The URL download repository is now disabled by default on new installations

                   This change is intended to improve security. We strongly recommend that those sites
                   which are upgrading also disable this repository, unless they are actually using its
                   functionality.
                   The repository itself allows content to be downloaded from the internet and
                   used within the site.
                   Whilst measures are taken to ensure the download and use of internet
                   content is handled safely and securely it is not possible to completely
                   inoculate the system from threat.
                   We recommend putting the Totara server into a DMZ if this repository is
                   enabled and used.

    TL-7038        Report default sort order is used consistently after report updates
    TL-7072        Converted Facetoface predefined room JS into an AMD module

                   The previously defined totaraDialog_handler_addpdroom has been converted to
                   an AMD module allowing the module to be loaded only when needed.

    TL-7140        Improved the display of Totara table borders such as those used for embedded reports
    TL-7159        Facetoface predefined rooms are no longer required to specify a name, building or address

                   The capacity field is still required.

    TL-7162        Converted the myappraisal JS to an AMD module

                   The M.totara_appraisal_myappraisal JavaScript code has been converted from
                   a statically loaded JS file to an AMD module.
                   This allows the JS to be loaded dynamically with much greater ease and
                   unlocks the benefits AMD brings such as minification and organisation.

                   This change removes the totara/appraisal/js/myappraisal.js file.

    TL-7197        Converted plan templates JS into an AMD module

                   The totara_plan_template JS class has been converted into an AMD module
                   allowing it to be required only when needed.

    TL-7198        Converted the totara_plan_component JS into an AMD module

                   The totara_plan_component JavaScript class has been converted into an AMD
                   module allowing it to be required only when needed.

                   The totara/plan/component.js file was removed as part of this change.

    TL-7236        Login prompt state is now maintained across invalid attempts

                   The state of the the login prompt is now maintained upon a failed
                   authentication attempt.
                   The username entered by the user will remain in the username field, and the
                   state of the remember username checkbox will persist.

    TL-7237        Serving of user submitted files has been hardened to improve security

                   The headers used when serving user submitted files has been improved.
                   Mime type handling has been improved and the following headers are now
                   included when the file being served is forcing a download:

                   * X-Content-Type-Options: nosniff
                   * X-Frame-Options: deny
                   * X-XSS-Protection: 1; mode=block

    TL-7244        Converted M.totara_cohort_assignroles JS into an AMD module

                   The file totara/cohort/assignroles.js has been removed as part of this
                   change.

    TL-7293        Session timezone is now used for date fields when editing Facetoface session
    TL-7297        Guest access is now disabled in new installations
    TL-7331        New framework for improved error message handling when AJAX calls fail

                   Previously there was no framework for when a jQuery AJAX call fails.
                   This can leave a number interactions in Totara with nondescript errors.
                   This fix provides a framework for errors to be caught, handled and
                   displayed. It also provides debugging information when debug has been
                   turned on allowing JS issues to be investigated with much more ease.

    TL-7384        Begin phasing out the "Hide" option for advanced Totara features

                   Previously many of the advanced features added in Totara could be set to
                   three states enabled, disabled, hidden.
                   The hidden state in many situations was poorly and inconsistently
                   implemented.
                   After discussions it was decided that the hidden state would be removed in
                   favour of a more straight forward enabled/disabled state.

                   Any sites using the "Hide" state will continue to experience the same
                   behaviour they have previously.
                   However the "Hide" state is no longer made available for selection.
                   In the future support for the "Hide" state will be removed.

    TL-7388        Improved reliability of the Atto editor superscript and subscript buttons
    TL-7389        Improved rtl language support for the collapse block icon

                   This introduces a new icon that points to the right in right to left
                   languages (such as Hebrew) when a block is collapsed (t/switch_plus_rtl)

    TL-7432        Added a new capability to allow marking of course completion for related users

                   A new capability has been added totara/core:markusercoursecomplete which
                   can be assigned within the user context and allows a user with that
                   capability to mark another user's required learning courses as complete.
                   Previously, this was only possible for managers marking completion for
                   their staff.

                   This new capability is not given to anyone by default.

    TL-7482        Updated the TCPDF library from 6.2.6 to 6.2.12
    TL-7510        Improved the flow of links within the Appraisal report source
    TL-7524        Improved the secure page layout within standard Totara responsive
    TL-7529        Fixed handling of RPL records when resetting or deleting a course or its completions

                   This change fixes how RPL records are handled when a course is reset by a
                   certification, deleted or reset by a user, or course completions unlocked
                   by a teacher.

                   When deleting or resetting a course, RPL completions are now also deleted
                   correctly. Previously these were not removed. An upgrade step will safely
                   remove invalid data records for deleted courses.

                   In 2.9.0 when a users course completion gets reset by a certification
                   window opening, all course and activity RPL completions will be removed.

                   As before, when a teacher unlocks course completion criteria and selects to
                   delete, course and activity RPL records will be kept and still count
                   towards a users completion.

                   Thanks to Eugene Venter at Catalyst NZ for the contribution

    TL-7530        Improved the display of error messages for date controls
    TL-7589        Added timezone support to plans and plan templates
    TL-7682        Improved the date display code to ensure it is consistent across all platforms

                   Language packs may now use all strftime parameters as listed here
                   http://php.net/manual/en/function.strftime.php


Accessibility improvements:

    TL-5275        Removed the fieldset surrounding the action buttons within a form

                   Previously the action (submit + cancel) buttons within a form were being
                   printed within a fieldset.
                   In order to improve accessibility across all forms by reducing the number
                   of nested fieldsets this particular fieldset was removed.

    TL-6234        Removed the HTML table used for layout when adding badge criteria

                   This patch also improves accessibility around single selects.

    TL-6239        Improved accessibility when setting a custom room

                   When editing a Facetoface session, the form elements for setting a custom
                   room had labels that were not correctly linked to their HTML elements, and
                   had an unnecessary HTML table.

    TL-6291        Improved the accessibility when viewing learning plans
    TL-6294        Removed the table used for layout on the course participants page

                   When viewing participants within a course, the filters at the top of the
                   page were within an HTML table. This has been removed and replaced with a
                   series of div elements making the page more accessible and responsive.

    TL-6310        Removed incorrect label on the Certificate settings page
    TL-6320        Replaced invalid use of HTML labels within the add activity dialog
    TL-6337        Removed the HTML table used on the group user management page for courses
    TL-6380        Improved accessibility of question bank export
    TL-6381        Improved accessibility of question bank import
    TL-6382        The fieldset template within forms now uses a fieldset

                   There were a number of form fieldsets that were incorrectly used, making a
                   number of web pages inaccessible to screen readers.
                   This change improves accessibility considerably but is likely to cause
                   display problems for themes that have restyled forms.
                   A good place to look at check your theme styles would be by reviewing the
                   form used when adding a Facetoface session.

    TL-6388        Improved accessibility on the users badge profile setting page
    TL-6390        Removed empty labels and legend attributes from all form element
    TL-6394        Removed the HTML label from admin settings when it was not referencing anything

                   What were formerly HTML label elements are now spans with the admin-label
                   css class. Any CSS styles that were applied to HTML labels (in the admin
                   area), will also need to be applied to this class (adjusting the font-size
                   will need to override ".form-item .admin-label")

    TL-6423        Removed the HTML table within a course user details view
    TL-6585        Improved accessibility uploading images into the Certificate module
    TL-7139        Removed heading around no results messages for flexible tables
    TL-7187        Removed the HTML table around user details when inside a chat activity
    TL-7188        Removed the HTML table used for layout when entering a chat message
    TL-7552        Replaced individual hierarchy item description table with a datalist


Database schema changes
=======================

New tables:
Bug ID   New table name
-----------------------------
TL-4485  goal_user_info_data
TL-4485  goal_user_info_data_param
TL-4485  goal_user_info_field
TL-4485  goal_user_type_cohort
TL-4485  goal_user_type
TL-6684  report_builder_global_restriction
TL-6684  reportbuilder_grp_cohort_record
TL-6684  reportbuilder_grp_org_record
TL-6684  reportbuilder_grp_pos_record
TL-6684  reportbuilder_grp_user_record
TL-6684  reportbuilder_grp_cohort_user
TL-6684  reportbuilder_grp_org_user
TL-6684  reportbuilder_grp_pos_user
TL-6684  reportbuilder_grp_user_user
TL-7246  auth_connect_servers
TL-7246  auth_connect_users
TL-7246  auth_connect_user_collections
TL-7246  auth_connect_sso_requests
TL-7246  auth_connect_sso_sessions

New fields:
Bug ID   Table name                New field name
------------------------------------------------------------
TL-2250  prog                      allowextensionrequests
TL-4485  goal_personal             typeid
TL-4485  goal_personal             visible
TL-5094  feedback360               anonymous
TL-5097  cohort_rule_collections   addnewmembers
TL-5097  cohort_rule_collections   removeoldmembers
TL-6684  report_builder            globalrestriction

Modified fields:
Bug ID   Table name                Field name
------------------------------------------------------------
TL-6621  report_builder_schedule   format        Converted from int to char


API Changes
===========

TL-2250 New options to turn off program extension requests
----------------------------------------------------------
 * New totara_prog_extension_allowed function returns true if the given program allows extension requests

TL-2565 Fixed date custom user profile field type
-------------------------------------------------
 * totara_date_parse_from_format has a new forth argument $forcetimezone

TL-4485 New personal goal types with custom fields
----------------------------------------
 * New report source class rb_source_goal_custom
 * New totara_cohort_get_goal_type_cohorts function returns the cohorts associated with a personal goal type
 * customfield_base::customfield_definition has a new optional sixth argument $disableheader
 * totara_customfield_renderer::get_redirect_options has a new optional third argument $class
 * totara_customfield_renderer::customfield_manage_edit_form has a new ninth argument $class
 * hierarchy::get_type_by_id new optional second argument $usertype
 * hierarchy::display_add_type_button new optional second argument $class
 * hierarchy::delete_type new optional second argument $class
 * hierarchy::delete_type_metadata new optional second argument $class
 * New totara_hierarchy_save_cohorts_for_type function to save the cohort/audience data against the
   hierarchy type
 * totara_hierarchy_renderer::mygoals_personal_table new third optional argument $display

TL-5020 Change of 'from' email address when sending emails
----------------------------------------------------------
 * New totara_get_user_from function returns a user to use as the from user when sending emails

TL-5094 Support for anonymous 360 feedback
------------------------------------------
 * New property feedback360->anonymous [bool]
 * totara_feedback360_renderer->display_feedback_header() has two new arguments $anonymous and $numresponders
 * totara_feedback360_renderer->view_request_infotable() has a new argument $anonymous
 * totara_feedback360_renderer->system_user_record() has a new argument $anonymous
 * totara_feedback360_renderer->external_user_record() has a new argument $anonymous
 * totara_feedback360_renderer->nojs_feedback_request_users() has a new argument $anonymous

TL-5097 New options to disable changes in membership for dyanmic audiences
--------------------------------------------------------------------------
 * New event totara_cohort\event\option_updated fired when ever cohort options are updated.
 * New totara_cohort_update_membership_options function to update cohort options. Fires the above event.

TL-5356 Added new user columns and filters to the Messages report source
---------------------------------------------------------------------
 * rb_base_source->add_user_table_to_joinlist() has a new argument $alias
 * rb_base_source::add_user_fields_to_filters() has a new argument $addtypetoheading

TL-5818 Report builder reports can now be cloned
------------------------------------------------
 * New totara_reportbuilder\event\report_cloned event that gets fired when a report is cloned.
 * New reportbuilder_set_default_access function that sets the default restrictive access for new report
 * New reportbuilder_clone_report function to clone a report

TL-6234 Removed the HTML table used for layout when adding badge criteria
-------------------------------------------------------------------------
 * core_renderer::single_select has a new argument $attributes

TL-6310 Removed incorrect label on the Certificate settings page
----------------------------------------------------------------
 * Deleted mod/certificate/adminsetting.class.php and the mod_certificate_admin_setting_upload class within

TL-6413 Report builder sources may now be marked as ignored
-----------------------------------------------------------
 * New reportbuilder::reset_caches static method to reset user permitted report caches
 * New reportbuilder::get_user_permitted_reports static method to get the reports a user can access
 * reportbuilder_get_reports has been deprecated, please use reportbuilder::get_user_permitted_reports instead
 * New rb_base_source::is_ignored method that can be overridden if the report should always be available

TL-6525 New report table block
------------------------------
 * New reportbuilder::overrideuniqueid() to set a unique ID.
 * New reportbuilder::overrideignoreparams() tells report builder to ignore the standard params when
   constructing the next report.
 * New reportbuilder->get_uniqueid() to report the reports unique ID. All external calls to $report->_id
   should be upgraded to use this method.

TL-6684 Added new global report restrictions
--------------------------------------------
 * New rb_global_restriction class which manages report restrictions rules
 * New rb_global_restriction_set class which integrates restrictions into report builder
 * New parameter in reportbuilder constructor which expects instance of rb_global_restriction_set
 * New rb_base_source::global_restrictions_supported method which should be overridden by report sources
   that support Global Report Restrictions
 * New rb_base_source::get_global_report_restriction_join method to inject Global Report Restrictions
   SQL snippet into base query
 * New parameters signature in rb_base_source::__construct now it should be ($groupid, rb_global_restriction_set
   $globalrestrictionset = null) for all inherited classes
 * New reportbuilder::display_restriction method which displays current restrictions and options to change
   them on report pages
 * New rb_base_embedded::embedded_global_restrictions_supported method which should be overridden by embedded
   report classes to indicate their Global Report restrictions support

TL-6942 Define and use different flavours of Totara during installation and upgrade
-----------------------------------------------------------------------------------
 * New Totara Flavour plugins, component is totara_flavour, plugins are flavour_pluginname.
 * New totara_flavour\definition class that all flavours must extend.

TL-6961 Each custom field type is now managed by a single capability
--------------------------------------------------------------------
 * New method totara_customfield\prefix\*_type->get_capability_managefield()
 * Deleted method totara_customfield\prefix\*_type->get_capability_editfield()
 * Deleted method totara_customfield\prefix\*_type->get_capability_createfield()
 * Deleted method totara_customfield\prefix\*_type->get_capability_createfield()
 * Deleted method totara_customfield\prefix\competency_type->get_capability_deletefield()

Capability changes
 * Added capability: totara/core:coursemanagecustomfield
 * Added capability: totara/core:programmanagecustomfield
 * Added capability: mod/facetoface:managecustomfield
 * Added capability: totara/hierarchy:positionmanagecustomfield
 * Added capability: totara/hierarchy:organisationmanagecustomfield
 * Added capability: totara/hierarchy:goalmanagecustomfield
 * Added capability: totara/hierarchy:competencymanagecustomfield
 * Removed capability: totara/core:createcoursecustomfield
 * Removed capability: totara/core:updatecoursecustomfield
 * Removed capability: totara/core:deletecoursecustomfield
 * Removed capability: totara/core:createprogramcustomfield
 * Removed capability: totara/core:updateprogramcustomfield
 * Removed capability: totara/core:deleteprogramcustomfield
 * Removed capability: mod/facetoface:updatefacetofacecustomfield
 * Removed capability: mod/facetoface:createfacetofacecustomfield
 * Removed capability: mod/facetoface:deletefacetofacecustomfield
 * Removed capability: totara/hierarchy:updatecompetencycustomfield
 * Removed capability: totara/hierarchy:createcompetencycustomfield
 * Removed capability: totara/hierarchy:deletecompetencycustomfield
 * Removed capability: totara/hierarchy:updategoalcustomfield
 * Removed capability: totara/hierarchy:creategoalcustomfield
 * Removed capability: totara/hierarchy:deletegoalcustomfield
 * Removed capability: totara/hierarchy:updateorganisationcustomfield
 * Removed capability: totara/hierarchy:createorganisationcustomfield
 * Removed capability: totara/hierarchy:deleteorganisationcustomfield
 * Removed capability: totara/hierarchy:updatepositioncustomfield
 * Removed capability: totara/hierarchy:createpositioncustomfield
 * Removed capability: totara/hierarchy:deletepositioncustomfield

TL-7237 Serving of user submitted files has been hardened to improve security
-----------------------------------------------------------------------------
 * New totara_tweak_file_sending function that gets called before serving files.

TL-7246 Totara Connect Client
-----------------------------
 * Totara Connect makes it possible to connect one or more Totara LMS or Totara Social
   installations to a master Totara LMS installation.
 * This connection allows for users, and audiences to be synchronised from the
   master to all connected client sites.
 * Synchronised users can move between the connected sites with ease thanks to the
   single sign on system accompanying Totara Connect.

TL-7529 Fixed handling of RPL records when resetting or deleting a course or its completions
--------------------------------------------------------------------------------------------
 * New method completion_info->delete_course_completion_data_including_rpl()


Deleted files
=============

Bug ID   File
----------------------------------------------
TL-5094 totara/feedback360/request/save.php
TL-6310 mod/certificate/adminsetting.class.php
TL-6684 mod/feedback/rb_sources/lang/en/rb_source_feedback_questions.php
TL-6684 mod/feedback/rb_sources/lang/en/rb_source_graphical_feedback_questions.php
TL-6684 mod/feedback/rb_sources/rb_preproc_feedback_questions.php
TL-6684 mod/feedback/rb_sources/rb_source_feedback_questions.php
TL-6684 mod/feedback/rb_sources/rb_source_graphical_feedback_questions.php
TL-6777 totara/core/js/lib/jquery.dataTables.js
TL-6777 totara/core/js/lib/jquery.dataTables.min.js
TL-6777 totara/core/js/lib/jquery.placeholder.js
TL-6777 totara/core/js/lib/jquery.placeholder.min.js
TL-6777 totara/core/js/lib/jquery.treeview.js
TL-6777 totara/core/js/lib/jquery.treeview.min.js
TL-6777 totara/core/js/lib/load.placeholder.js
TL-6777 totara/core/js/lib/readme_totara.txt
TL-6777 totara/core/js/lib/totara_dialog.js
TL-7013 install/lang/ Multiple language files for unsupported languages
TL-7162 totara/appraisal/js/myappraisal.js
TL-7197 totara/plan/templates.js
TL-7198 totara/plan/component.js
TL-7244 totara/cohort/assignroles.js


Contributions:

    * Andrew Hancox at Synergy Learning - TL-6454
    * Dennis Heany at Learning Pool - TL-6525
    * Ryan Lafferty at Learning Pool - TL-4485
    * Eugene Venter at Catalyst NZ - TL-6022, TL-6023, TL-6308, TL-6453, TL-6496, TL-6497, TL-7529
    * Maccabi Healthcare Services a client of Kineo Israel - TL-6684
    * Pavel Tsakalidis at Kineo UK   - TL-6531
    * Russell England at Vision NV - TL-5394

 */
