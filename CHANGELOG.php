<?php
/*

Totara Learn Changelog

Release 9.43 (29th April 2020):
===============================


Security issues:

    TL-24490       Shibboleth attributes are now validated against a blacklist of common $_SERVER variables

                   Prior to this change Shibboleth attribute mapping could access any
                   variables stored in $_SERVER, allowing for malicious configurations to be
                   created.
                   All user attributes are now validated to ensure that they are not in a list
                   of commonly available $_SERVER variables that do not belong to Shibboleth.

    TL-24587       HTML block no longer allows self-XSS

                   Prior to this change, users could perform XSS attacks on themselves by
                   adding an HTML block when customising their dashboard, giving it malicious
                   content, saving it, and then editing it again.
                   When customised, a dashboard is only visible to the owning user. However
                   admins could still experience the malicious block using the login as
                   functionality.

                   This has now been fixed, and when editing an HTML block on user pages the
                   content is cleaned before it is loaded into the editor.

    TL-24618       Backported MDL-67861: IP addresses can be spoofed using X-Forwarded-For

                   If your server is behind multiple reverse proxies that append to
                   the X-Forwarded-For header then you will need to specify a comma
                   separated list of ip addresses or subnets of the reverse proxies to be
                   ignored in order to find the users correct IP address.

Bug fixes:

    TL-23459       Made sure Quiz activity takes passing grade requirement into account when restoring from course backups made with Totara 2.7 or earlier
    TL-24779       Ensured "inlist" type audience rule SQL parameters use unique names

                   This occurred when multiple inlist rules were added to an audience and were
                   using the IS EMPTY operator.
                   If encountered a fatal error was produced.
                   The inlist rule has now been updated to ensure it uses unique parameter
                   names.

API changes:

    TL-22910       Send filename* instead of filename in the Content-Disposition response header

                   This patch will particularly resolve the file name corruption (mojibake)
                   when downloading a file with name containing non-ASCII characters on
                   Microsoft Edge 18 or older, by sending the filename* field introduced in
                   RFC 6266.
                   On the other hand, the filename field (without asterisk) is no longer sent
                   to prevent a browser bug in Apple Safari.

Contributions:

    * Sergey Vidusov at Androgogic - TL-24779


Release 9.42 (27th March 2020):
===============================


Security issues:

    TL-23720       Validation of URLs handled by the URL repository has been tightened

                   URL validation across the entire platform was improved in Totara 12 to make
                   it more robust.
                   These changes have been backported in part, specifically for and limited to
                   the URL download repository as it is a high risk plugin.


Release 9.41 (26th February 2020):
==================================


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

Bug fixes:

    TL-7631        Conditional fields when editing certification course sets are now correctly disabled when not relevant
    TL-23740       Fixed compatibility with UUID PHP extension
    TL-23852       The current learning block no longer triggers a re-aggregation of program courseset completion

                   The current learning block in some situations was causing program courseset
                   completion to be re-aggregated, leading to courseset completion time being
                   incorrectly updated if the courseset had already been completed.
                   This has been fixed and the courseset completion date is no longer updated
                   after it has been initially set.


Release 9.40 (22nd January 2020):
=================================


API changes:

    TL-23511       The new minimum required Node.js version has changed to 12

                   It is recommended now to run at least Node.js 12 to run grunt builds.
                   Node.js 8 is almost out of support; we recommend to use the latest Node.js
                   12 to run grunt builds. However to avoid compatibility issues in stable
                   releases running Node 8 is still supported.


Release 9.39 (30th December 2019):
==================================


Important:

    TL-22800       Reworked the appraisal role assignment process to prevent duplicate assignments

                   1) This patch adds a new index to the appraisal_user_assignment table – a
                      unique index on an appraisal ID/appraisee ID combination.

                      It is possible that a site's appraisal_user_assignment table already has
                      duplicates; in this case, the site upgrade will fail. Therefore before
                      doing this upgrade, back up the site and then run this SQL query:

                      SELECT userid, appraisalid, count(appraisalid) as duplicates
                      FROM mdl_appraisal_user_assignment
                      GROUP BY appraisalid, userid
                      HAVING count(appraisalid) > 1

                      If this query returns a result, it means that the table has duplicates, and
                      they must be resolved first before an upgrade can successfully run. For
                      help, get in touch with the Totara support team and indicate the site has
                      been affected by TL-22800.

                   2) The behaviour has changed when the 'Update now' button is pressed in the
                      appraisal assignment tab. This is only for dynamic appraisals and the
                      button appears when appraisal assignments are added/removed after
                      activation. Previously when the button was pressed, the assignments were
                      updated in real time and the user would wait until the operation completed.
                      The refreshed screen would then show the updated list of appraisees.

                      With this patch, pressing the button spawns an ad hoc task instead and the
                      refreshed screen does not show the updated list of appraisees. Only when
                      the ad hoc task runs (depending on the next cron run – usually in the
                      next minute) are the assignments updated. When the user revisits the
                      appraisal assignment page, it will show the updated list of appraisees.

Security issues:

    TL-21671       Legacy internal flag 'ignoresesskey' is now usable within one request only, to prevent any potential security issues

Improvements:

    TL-22697       Added a label to the seminar sign-in sheet download form

Bug fixes:

    TL-23165       Fixed inconsistency of Bootstrap Javascript versions

                   Previously, the thirdpartylibs.xml stated that the bootstrap Javascript
                   version in use was 3.3.7, when in fact it was version 3.3.4.

                   There were no code changes and all security fixes included in 3.4.1 are
                   still present.

    TL-23237       Fixed an issue where incorrect links were generated for certificate downloads

                   Previously the list of certificate files used to generate the links
                   included directories, and when generating the links the filenames were
                   overridden with the next one in the list. Due to the sort order of some
                   databases this could result in the filename in the link being replaced by
                   the full-stop representing the directory.


Release 9.38 (26th November 2019):
==================================


Security issues:

    TL-23017       Backport MDL-66228: Prevented open redirect when editing Content page in Lesson activity

Performance improvements:

    TL-22827       Improved appraisal assignment tab performance

                   Some appraisal functions in the assignment page have been rewritten to use
                   bulk SQL queries to improve their performance. Previously, the code worked
                   with one entity at a time.

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

    TL-22840       Added system information to upgrade logs
    TL-22890       Backported TL-22783 / MDL-62891

                   Backported the following commits:
                    # [MDL-62891|https://tracker.moodle.org/browse/MDL-62891] core: Stop using
                   var_export() to describe callables
                    # [MDL-62891|https://tracker.moodle.org/browse/MDL-62891] core: Introduce
                   new get_callable_name() function

Bug fixes:

    TL-22863       Fixed use of MySQL 8 reserved keyword 'member' in Report builder sources
    TL-22886       Password length restriction was removed from user signup forms
    TL-22930       Made sure microphone and camera access is allowed from the iframe in the External Tool activities


Release 9.37 (25th October 2019):
=================================


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
    TL-22503       Backport TL-22045: Login form is now only submitted once per page load
    TL-22559       Seminar notification sender no longer reuses the sending user object

                   The reuse of the sending user object when sending notifications from within
                   the seminar occasionally led to an issue where notifications would appear
                   to come from random users. This has now been fixed by ensuring a fresh
                   sending user object is used for each notification.

    TL-22576       All areas displaying a program or certification fullname are now formatted consistently

                   Prior to this change there were a handful of areas not correctly formatting
                   program and certification full names before displaying them. These have all
                   been tidied up and program and certification fullname is now formatted
                   correctly and consistently.


Release 9.36 (19th September 2019):
===================================


Bug fixes:

    TL-22208       Fixed file support in Totara form editor element

                   Prior to this patch when using an editor element with totara forms, images
                   that had previously been uploaded to the field were not displaying properly
                   during editing.

                   Note: This form element is not currently in use anywhere in a way that
                   would be affected by this.


Release 9.35 (22nd August 2019):
================================


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

    TL-21928       Ensured capabilities are checked when creating a course using single activity format

                   When creating a course using the single activity course format, permissions
                   weren't being checked to ensure the user was allowed to create an instance
                   of an activity. Permissions are now checked correctly and users can only
                   create single activity courses using activities they have permission to
                   create.

Improvements:

    TL-21437       Added button to allow manual downloading of site registration data

                   It is now possible to manually download an encrypted copy of site
                   registration data from the register page, in cases where a site cannot be
                   registered automatically.

Bug fixes:

    TL-21358       Fixed a permission error preventing a user from viewing their own goals in complex hierarchies

                   Prior to this fix if a user had two or more job assignments where they were
                   the manager of, and team member of, another user at the same time, they
                   would encounter a permissions error when they attempted to view their own
                   goals pages.
                   This has now been fixed, and users in this situation can view their own
                   goals.

    TL-21581       Added 'debugstringids' configuration setting support to core_string_manager

                   Fixed issue when "Show origin of languages strings" in Development >
                   Debugging is enabled, in some rare cases, not all strings origins were
                   displayed.

    TL-21886       Fixed typos in the reportbuilder language strings

                   The following language strings were updated:
                   - reportbuilderjobassignmentfilter
                   - reportbuildertag_help
                   - occurredthisfinancialyear
                   - contentdesc_usertemp

Contributions:

    * Jo Jones at Kineo UK - TL-21581


Release 9.34 (17th July 2019):
==============================


Bug fixes:

    TL-19138       Fixed warning message when deleting a report builder saved search

                   If a report builder saved search is deleted, any scheduled reports that use
                   that saved search are also deleted. The warning message to confirm the
                   deletion of the saved search now also correctly displays any scheduled
                   reports that will also be deleted.


Release 9.33 (19th June 2019):
==============================


Security issues:

    TL-21071       MDL-64708: Removed an open redirect within the audience upload form
    TL-21243       Added sesskey checks to prevent CSRF in several Learning Plan dialogs

Bug fixes:

    TL-21175       Added the ability to fix out of order competency scale values

                   Previously when a competency scale was assigned to a framework, and users
                   had achieved values from that scale, it was not possible to correct any
                   ordering issues involving proficient values being below non-proficient
                   values.

                   Warnings are now shown when proficient values are out of order, and it is
                   possible to change the proficiency settings of these scales to correct this
                   situation.


Release 9.32 (22nd May 2019):
=============================


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


Release 9.31 (29th April 2019):
===============================


Security issues:

    TL-20532       Fixed a file path serialisation issue in TCPDF library

                   Prior to this fix an attacker could trigger a deserialisation of arbitrary
                   data by targeting the phar:// stream wrapped in PHP.
                   In Totara 11, 12 and above The TCPDF library  has been upgraded to version
                   6.2.26.
                   In all older versions the fix from the TCPDF library for this issue has
                   been cherry-picked into Totara.

    TL-20607       Improved HTML sanitisation of Bootstrap tool-tips and popovers

                   An XSS vulnerability was recently identified and fix in the Bootstrap 3
                   library that we use.
                   The vulnerability arose from a lack of sanitisation on attribute values for
                   the popover component.
                   The fix developed by Bootstrap has now been cherry-picked into all affected
                   branches.

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

    TL-20622       Totara form editor now consistently cleans content before loading it into the editor

Bug fixes:

    TL-12258       Backport from TL-19936 to fix custom rating default value.

                   Without the backport, when the admin views an activated appraisal, all the
                   previously selected default values for a custom rating question would show.
                   With the backport, only  the current default value will be shown.

    TL-20148       Fixed a web services error that occurred when the current language resolved to a language that was not installed
    TL-20488       Added batch processing of users when being unassigned from or reassigned to a program
    TL-20586       Fixed event generation when deleting hierarchy items

                   Prior to the patch the same event was generated for all descendant
                   hierarchy items when deleting an item with children.

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


Release 9.30 (22nd March 2019):
===============================


Security issues:

    TL-20498       MDL-64651: Prevented links in comments from including the referring URL when followed
    TL-20518       Changed the Secure page layout to use layout/secure.php

                   Previously the secure page layout was using the standard layout PHP file in
                   both Roots and Basis themes and unless otherwise specified, in child
                   themes.

Bug fixes:

    TL-20033       Fixed the SQL pattern for word matching regular expressions in MySQL 8
    TL-20228       Fixed memory leaks in totara_program PHPUnit tests
    TL-20302       Fixed 'Allow cancellations' form setting for users without 'Configure cancellation' capability when adding an event
    TL-20339       Fixed deletion of multiple goals when a single goal was unassigned from a user

                   When a user is assigned to the same organisation via several job
                   assignments and then simultaneously unassigned from the organisation, the
                   goals assigned to this user via an organisation are converted to individual
                   duplicated goal assignments. Previously, when a single goal was deleted,
                   the duplicate records were deleted as well. After the patch, the individual
                   goal assignments are removed separately.


Release 9.29 (14th February 2019):
==================================


API changes:

    TL-20109       Added a default value for $activeusers3mth when calling core_admin_renderer::admin_notifications_page()

                   TL-18789 introduced an additional parameter to
                   core_admin_renderer::admin_notifications_page() which was not indicated and
                   will cause issues with themes that override this function (which
                   bootstrapbase did in Totara 9). This issue adds a default value for this
                   function and also fixes the PHP error when using themes derived off
                   bootstrap base in Totara 9.

Performance improvements:

    TL-19810       Removed unnecessary caching from the URL sanitisation in page redirection code

                   Prior to this fix several functions within Totara, including the redirect
                   function, were using either clean_text() or purify_html() to clean and
                   sanitise URL's that were going to be output. Both functions were designed
                   for larger bodies of text, and as such cached the result after cleaning in
                   order to improve performance. The uses of these functions were leading to
                   cache bloat, that on a large site could be have a noticeable impact upon
                   performance.

                   After this fix, places that were previously using clean_text() or
                   purify_html() to clean URL's now use purify_uri() instead. This function
                   does not cache the result, and is optimised specifically for its purpose.

    TL-20026       Removed an unused index on the 'element' column in the 'scorm_scoes_track' table

Bug fixes:

    TL-19916       MySQL Derived merge has been turned off for all versions 5.7.20 / 8.0.4 and lower

                   The derived merge optimisation for MySQL is now forcibly turned off when
                   connecting to MySQL, if the version of MySQL that is running is 5.7.20 /
                   8.0.4 or lower. This was done to work around a known bug  in MySQL which
                   could lead to the wrong results being returned for queries that were using
                   a LEFT join to eliminate rows, this issue was fixed in versions 5.7.21 /
                   8.0.4 of MySQL and above and can be found in their changelogs as issue #26627181:
                    * https://dev.mysql.com/doc/relnotes/mysql/5.7/en/news-5-7-21.html
                    * https://dev.mysql.com/doc/relnotes/mysql/8.0/en/news-8-0-4.html

                   In some cases this can affect performance, so we strongly recommend all
                   sites running MySQL 5.7.20 / 8.0.4 or lower upgrade both Totara, and their
                   version of MySQL.

    TL-20018       Removed exception modal when version tracking script fails to contact community
    TL-20102       Fixed certificates not rendering text in RTL languages.


Release 9.28 (24th January 2019):
=================================


Security issues:

    TL-19900       Applied fixes for Bootstrap XSS issues

                   Bootstrap recently included security fixes in their latest set of releases.
                   To avoid affecting functionality using the current versions of Bootstrap,
                   only the security fixes have been applied rather than upgrading the version
                   of Bootstrap used.

                   It is expected that there was no exploit that could be carried out in
                   Totara due to this vulnerability, as the necessary user input does not go
                   into the affected attributes when using Bootstrap components. However we
                   have applied these fixes to minimise the risk of becoming vulnerable in the
                   future.

                   The Bootstrap library is used by the Roots theme.

Improvements:

    TL-18759       Improved the display of user's enrolment status

                   Added clarification to the Status field on the course enrolments page. If
                   editing a user's enrolment while the corresponding enrolment module is
                   disabled, the status will now be displayed as 'Effectively suspended'.

Bug fixes:

    TL-19471       Fixed unavailable programs not showing in user's Record of Learning items when the user had started the program
    TL-19797       Fixed minimum bookings notification being sent for cancelled events
    TL-19877       Fixed bug where multi-framework rules were flagged as deleted in Audiences dynamic rules
    TL-20007       Fixed an error with audience rules relying on a removed user-defined field value

                   This affected the 'choose' type of audience rules on text input user custom
                   fields. If a user-defined input value was used in the rule definition, and
                   that value was then subsequently removed as a field input, a fatal error
                   was thrown when viewing the audience. This is now handled gracefully,
                   rather than displaying an object being used as an array error the missing
                   value can now be removed from the rule.


Release 9.27 (19th December 2018):
==================================


Security issues:

    TL-19593       Improved handling of seminar attendee export fields

                   Validation was improved for fields that are set by a site admin to be
                   included when exporting seminar attendance, making user information that
                   can be exported consistent with other parts of the application.

                   Permissions checks are now also made to ensure that the user exporting has
                   permission to access the information of each user in the report.

Bug fixes:

    TL-18892       Fixed problem with redisplayed goal question in appraisals

                   Formerly, a redisplayed goal question would display the goal status as a
                   drop-down list - whether or not the user had rights to change/answer the
                   question. However, when the goal was changed, it was ignored. This patch
                   changes the drop-down into a text string when necessary so that it cannot
                   be changed.

    TL-19373       Added two new seminar date columns which support export

                   The new columns are "Local Session Start Date/Time" and "Local Session
                   Finish Date/Time" and they support exporting to Excel and Open Document
                   formats.

    TL-19481       Fixed the course restoration process for seminar event multi-select customfields

                   Previously during course restoration, the seminar event multi-select
                   customfield was losing the value(s) if there was more than one value
                   selected.

    TL-19615       Fixed a permission error when a user tried to edit a seminar calendar event
    TL-19692       Fixed a naming error for an undefined user profile datatype in the observer class unit tests
    TL-19696       Fixed the handling of calendar events when editing the calendar display settings of a seminar with multiple sessions

                   Previously with Seminar *Calendar display settings = None* and if the
                   seminar with multiple events was updated, the user calendar seminar dates
                   were hidden and the user couldn't see the seminar event in the calendar.

    TL-19760       Fixed multi-language support for custom headings in Report Builder
    TL-19779       Fixed an error when signing up to a seminar event that requires approval with no job assignment and temporary managers disabled

Contributions:

    * Ghada El-Zoghbi at Catalyst AU - TL-19692
    * Learning Pool - TL-19779


Release 9.26 (4th December 2018):
=================================


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

    TL-19028       SCORM package download protection is now on by default

                   Previously this setting was off by default.
                   Turning it on ensures that sites are more secure by default.

Improvements:

    TL-18963       Improved the help text for the 'Enable messaging system' setting on the advanced settings page

Bug fixes:

    TL-19312       Added the 'readonlyemptyfield' string that was missing from customfields
    TL-19250       Fixed Totara forms file manager element with disabled subdirectories bug when uploading one file only
    TL-19248       Report builder filters supply the report id when changing

                   Previously there were some filters that did not supply the report id when
                   changing the filter. This issue ensures the access checks are done
                   correctly for the report

    TL-19215       Improved handling of text in autocomplete forms

                   Previously when adding HTML tags to an autocomplete field, they would be
                   interpreted by the browser. This issue ensures that they are displayed as
                   plain text, with offending content being removed when the form being
                   reloaded.

                   This is not a security fix as the only person who could be affected is the
                   person who is entering the data, when they are first entering the data (and
                   not on subsequent visits).

    TL-19195       Fixed display issue when using "Hide if there is nothing to display" setting in the report table block

                   If the setting "Hide if there is nothing to display" was set for the report
                   table block then the block would hide even if there was data. The setting
                   now works correctly and only hides the block if the report contains no
                   data.

    TL-19155       Fixed Google maps Ok button failure in Behat tests
    TL-19124       Internal implementation and performance of organisation and position based report restrictions

                   This is a backport of TL-19086, which was included in October evergreen
                   release.

    TL-19122       Fixed an issue in the recurring courses where after the course restarts the enrolment date remained the date from the original course
    TL-19000       Changed Seminar event approver notification type from alert to task so that dashboard task block is created
    TL-18932       Added an ability to detect the broken audience rules when scheduled task starts running to update the audience's members

                   Prior to this patch, when the scheduled task
                   (\totara_cohort\task\update_cohort_task) was running, there was no way that
                   it could detect whether the rules were still referencing to the invalid
                   instance records or not (for example: course, program, user's position, and
                   so on). Therefore, if the rule had a reference to an invalid instance
                   record, audience will not be able update its members correctly.

                   With this patch, it will start checking whether the referenced instance
                   records are valid or not before the process of updating members. If there
                   are any invalid instance records, then the system will send an email out to
                   notify the site administrator.

    TL-18895       Added warning text to the audience's rules if there are any rules that are referencing a deleted item

                   Prior to the patch: when an item (for example: program, course, position
                   and so on) that was referenced in an audience rule got deleted, there were
                   no obvious way to tell the user that this item had been deleted.

                   With this patch: there will be a warning text, when user is viewing the
                   rule that is still referencing a deleted item.

    TL-18806       Prevented prog_write_completion from being used with certification data
    TL-18558       Fixed display activity restrictions for editing teachers.

                   Editing teachers can see activity restrictions whether they match them or
                   not.

    TL-17804       Fixed certification expiry date not being updated when a user is granted an extension

                   Additional changes include:
                    * new baseline expiry field in the completion editor which is used to calculate subsequent expiry dates
                    * preventing users from requesting extension after the certification expiry


Release 9.25 (25th October 2018):
=================================


Security issues:

    TL-18957       Fixed permission checks for learning plans

                   Prior to this patch all plan templates were being checked to see if a user
                   had a permission (e.g. update plan). Now only the template that the plan is
                   based off is checked for the permission.

Improvements:

    TL-17586       Greatly improved the performance of the update competencies scheduled task

                   The scheduled task to reaggregate the competencies
                   "\totara_hierarchy\task\update_competencies_task" was refactored to fix a
                   memory leak. The scheduled task now loops through the users and loads and
                   reaggregates items per user and not in one huge query as before. This
                   minimises impact on memory but increases number of queries and runtime.

    TL-18565       Improved the wording around the 'Override user conflicts' settings page in seminars

                   The 'Override user scheduling conflicts' setting was initially intended for
                   use with new events where the assigned roles resulted in conflicts with
                   existing events. It was not originally designed to work with existing
                   events.
                   We changed the event configuration flow by moving the 'override' action out
                   of the settings page and into the 'save' modal dialog where it belongs.
                   So in essence you will be able override conflicts upon creation and edit.

    TL-18852       Database table prefix is now required for all new installations

                   Previously MySQL did not require database prefix to be set in config.php,
                   since MySQL 8.0 the prefix is however required. To prevent problems in
                   future upgrades Totara now requires table prefix for all databases.

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

    TL-17629       Fixed failures in the Seminar send_notification_task when performed under high load

                   Some sites with large number of Seminar activities (100 000+) experienced
                   'out of memory' failures during execution of the scheduled task
                   (send_notifications_task). This task has now been optimised to use less
                   memory.

    TL-18802       Changed the date format of Session Date related columns within Seminar Sign-ups report source

                   Previously the report columns 'Session Start' and 'Session Finish' were
                   formatted differently than the 'Session Start (linked to activity)' column.
                   These columns are now formatted consistently.

    TL-19072       Fixed wait-listed attendees not being automatically added to the Seminar's attendees list after a reservation is deleted

API changes:

    TL-18845       Removed a superfluous unique index on the job_assignment.id column
    TL-18985       Unit tests may now override lang strings


Release 9.24 (19th September 2018):
===================================


Important:

    TL-14270       Added additional information about plugins usage to registration system
    TL-18788       Added data about installed language packs into registration system
    TL-18789       Added data about number of active users in last 3 months to registration system

Improvements:

    TL-11243       Removed ambiguity from the confirmation messages for Seminar booking requests
    TL-18777       Allowed plugins to have custom plugininfo class instead of just type class

Bug fixes:

    TL-18494       Fixed 'Bulk add attendees' results in Seminar to show ID Number instead of internal user ID
    TL-18571       Fixed access rights bug when viewing goal questions in completed appraisals

                   If an appraisal has a goal question and the appraisal was completed, then
                   it is the current learner's manager who can see the goal question. However,
                   there was an issue when a learner and their manager completed the appraisal
                   but then a new manager was assigned to the learner. In this case, only the
                   old manager could see the completed appraisal but they could not see the
                   goal question because they didn't have the correct access rights. The new
                   manager could not see the completed appraisal at all.

                   This applies to static appraisals.

    TL-18588       Prevented duplicate results when searching in Seminar dialogs

                   Seminar dialogs that provide search functionality (such as the rooms and
                   assets selectors) now ensure that search results are unique.

    TL-18602       Fixed Seminar's event decline emails to not include iCalendar attachments

                   When a booking approval request with a setting of email confirmation set as
                   'Email with iCalendar appointment' gets declined, then the iCalendar
                   attachment will not be included in the email sent back to the user who made
                   the request.

    TL-18742       Fixed failing unit tests in totara_job_dialog_assign_manager_testcase
    TL-18772       Backported MDL-62239 to fix broken drag-drop of question types on iOS 11.3


Release 9.23 (24th August 2018):
================================


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

    TL-17780       Added a warning message about certification changes not affecting users until they re-certify
    TL-18675       Added 'not applicable' text to visibility column names when audience visibility is enabled

                   When audience based visibility is enabled it takes priority over other
                   types of visibility. Having multiple visibility columns added to a report
                   may cause confusion as to which type of visibility is being used. '(not
                   applicable)' is now suffixed to the visibility column to clarify which type
                   of visibility is inactive, e.g. 'Program Visible (not applicable)'.

Bug fixes:

    TL-17349       Made username field in admin/users visible to make users aware autofill has taken place

                   Chrome very stubbornly ignores attempts to prevent autocompletion of forms,
                   particularly when a username is involved. We aren't able to fix it without
                   creating breaking changes to the layout of the admin/user form (fixed from
                   t10), so instead, we opted to make the field more visible so that at least
                   the user is aware that autocomplete has taken place.

    TL-17734       Fixed OpenSesame registration
    TL-17767       Fixed multiple blocks of the same type not being restored upon course restore
    TL-17846       Content restrictions are now applied correctly for Report Builder filters utilising dialogs

                   Before Totara Learn 9 the organisation and position content restriction
                   rules were applied when displaying organisation and position filters in
                   reports.

                   With the introduction of multiple job assignments in Totara Learn 9,
                   organisation and position report filters now use the generic totara dialog
                   to display available organisation and position filter values.

                   This patch added the application of the missing report content restriction
                   rules when retrieving the data to display in totara dialogs used in report
                   filters.

    TL-17936       Report builder graphs now use the sort order from the underlying report

                   When scheduled reports were sent, the report data was correctly ordered,
                   but the graph (if included) was not being ordered correctly. The ordering
                   of the graph now matches the order in the graph table.

    TL-17977       Users editing Program assignments are now only shown the option to assign audiences if they have the required capability

                   Previously if a user did not have moodle/cohort:view capability and tried
                   to assign an audience to a program an error would be thrown. The option to
                   add audiences is now hidden from users who do not have this capability.

    TL-18488       Fixed a regression in DB->get_in_or_equal() when searching only integer values within a character field

                   This is a regression from TL-16700, introduced in 2.6.52, 2.7.35, 2.9.27,
                   9.15, 10.4, and 11.0. A fatal error would be encountered in PostgreSQL if
                   you attempted to call get_in_or_equal() with an array of integers, and then
                   used the output to search a character field.
                   The solution is ensure that all values are handled as strings.

    TL-18499       Fixed an issue where searching in glossary definitions longer than 255 characters would return no results on MSSQL database

                   The issue manifested itself in the definitions where the search term
                   appeared in the text only after the 255th character due to incorrectly used
                   concatenation in an SQL query.

    TL-18544       Fixed SQL error on reports using Toolbar Search when custom fields are deleted

                   If a custom field that is included as part of the Toolbar Search for a
                   Report Builder report (configured in the report settings) gets deleted then
                   an SQL error is generated. This only occurs after a search is done, viewing
                   the page normally will not display the error.

    TL-18546       Fixed missing string parameter when exporting report with job assignment filters
    TL-18574       Fixed a return type issue within the Redis session management code responsible for checking if a session exists
    TL-18590       Made sure that multiple jobs are not created via search dialogs if multiple jobs are disabled sitewide
    TL-18618       Restoring a course now correctly ignores links to external or deleted forum discussions
    TL-18649       Improved the Auto login guest setting description

                   The auto login guest setting incorrectly sets the expectation that
                   automatic login only happens when a non-logged in user attempts to access a
                   course. In fact it happens as soon as the user is required to login,
                   regardless of what they are trying to access. The description has been
                   improved to reflect the actual behaviour.

Contributions:

    * Russell England, Kineo USA - TL-17977


Release 9.22 (18th July 2018):
==============================


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

    TL-17385       Fixed an error when viewing the due date column in program reports that don't allow the display of the total count
    TL-17420       Formatted any dates in program emails based on the recipient's selected language package
    TL-17531       Fixed user report performance issue when joining job assignments

                   This fix improves performance for certain reports when adding columns from
                   the "All User's job assignments" section. The fix applies to the following
                   report sources:
                    * Appraisal Status
                    * Audience Members
                    * Badges Issued
                    * Competency Status
                    * Competency Status History
                    * Goal Status
                    * Learning Plans
                    * Program Completion
                    * Program Overview
                    * Record of Learning: Recurring Programs
                    * User

    TL-17657       Fixed an error causing a debugging message in the facetoface_get_users_by_status() function

                   Previously when the function was called with the include reservations
                   parameter while multiple reservations were available, there were some
                   fields added to the query that were causing a debugging message to be
                   displayed.

    TL-17845       Fixed SCORM height issue when side navigation was turned on

                   In some SCORM modules the height of the player was broken when the side
                   navigation was turned on. The height of the player is now calculated
                   correctly with both side and drop down navigation.

    TL-17847       Reduced specificity of fix for TL-17744

                   The June releases of Totara included a fix for heading levels in an HTML
                   block. This increased the specificity of the CSS causing it to override
                   other CSS declarations (this included some in the featured links block).
                   This is now fixed in a different manner, maintaining the
                   existing specificity.

    TL-17868       Fixed a bug which assumed a job must have a manager when messaging attendees of a Seminar

                   Prior to this fix due to a bug in code it was not possible to send a
                   message to Seminar attendees, cc'ing their managers, if the attendee job
                   assignments were tracked, and there was at least one attendee who had a
                   manager, and at least one attendee who had a job assignment which did not
                   have a manager. This has now been fixed.

                   When messaging attendees, having selected to cc their managers, if an
                   attendee does not have a manager the attendee will still receive the
                   message.

    TL-17869       Fixed SQL query in display function in "Pending registrations" report

                   The SQL being used in the display function caused an error in MySQL and
                   MariaDB

    TL-17885       Display seminar assets on reports even when they are being used in an ongoing event

                   When the Asset Availability filter is being used in a report, assets that
                   are available but currently in use (by an ongoing event at the time of
                   searching) should not be excluded from the report. Assets should only be
                   excluded if they are not available between the dates/times specified in the
                   filter.

    TL-17894       Fixed the display of Seminar approval settings when they have been disabled at the system level

                   When an admin disabled an approval option on the seminar global settings
                   page, and there was an existing seminar using the approval option, the
                   approval option would then display as an empty radio selector on that
                   seminar's settings page, and none of the approval options would be
                   displayed as selected. However unless a different approval option was
                   selected the seminar would continue using the disabled option.
                   This patch fixes the display issue by making the previously empty radio
                   selector correctly display the disabled setting's name, and marking it as
                   selected. As before, the disabled approval option can still be used for
                   existing seminars until it is changed to a different setting. When the
                   setting is changed for the seminar the now disabled approval option will no
                   longer be displayed.

Contributions:

    *  Grace Ashton at Kineo.com - TL-17657


Release 9.21 (20th June 2018):
==============================


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

    TL-17288       Missing Seminar notifications can now be restored by a single bulk action

                   During Totara upgrades from earlier versions to T9 and above, existing
                   seminars are missing the new default notification templates. There is
                   existing functionality to restore them by visiting each seminar
                   notification one by one, which will take some time if there are a lot of
                   seminars. This patch introduces new functionality to restore any missing
                   templates for ALL existing seminars at once.

    TL-17414       Improved information around the 'completions archive' functionality

                   It now explicitly expresses that completion data will be permanently
                   deleted and mentions that the data that will be archived is limited to: id,
                   courseid, userid, timecompleted, and grade. It also mentions that this
                   information will be available in the learner's Record of Learning.

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

    TL-16967       Fixed an 'invalidrecordunknown' error when creating Learning Plans for Dynamic Audiences

                   Once the "Automatically assign by organisation" setting was set under the
                   competencies section of Learning Plan templates, and new Learning Plans
                   were created for Dynamic Audiences, a check for the first job assignment of
                   the user was made. This first job assignment must exist otherwise an error
                   was thrown for all users that did not have a job assignment. This has now
                   been fixed and a check for all of the user's job assignments is made
                   rather than just the first one.

    TL-17102       Fixed saved searches not being applied to report blocks
    TL-17289       Made message metadata usage consistent for alerts and blocks
    TL-17364       Fixed displaying profile fields data in the self-registration request report
    TL-17405       Fixed setuplib test case error when test executed separated
    TL-17416       Prevented completion report link appearing in user profile page when user does not have permission to view reports.
    TL-17523       Removed the ability to create multiple job assignments via the dialog when multiple jobs is disabled
    TL-17524       Fixed exporting reports as PDF during scheduled tasks when the PHP memory limit is exceeded

                   Generating PDF files as part of a scheduled report previously caused an
                   error and aborted the entire scheduled task if a report had a large data
                   set that exceeded the PDF memory limit. With this patch, the exception is
                   still raised, but the export completes with the exception message in the
                   PDF file notifying the user that they need to change their report. The
                   scheduled task then continues on to the next report to be exported.

    TL-17541       Fixed the help text for a setting in the course completion report

                   The help text for the 'Show only active enrolments' setting in the course
                   completion report was misleading, sounding like completion records for
                   users with removed enrolments were going to be shown on the report. This
                   has now been fixed to reflect the actual behaviour of the setting, which
                   excludes records from removed enrolments.

    TL-17542       Made sure that RPL completion information remains collapsed on the course completion report until it is explicitly expanded
    TL-17610       Setup cron user and course before each scheduled or adhoc task

                   Before this patch we set the admin user and the course at the beginning of
                   the cron run. Any task could have overridden the user. But if the task did
                   not take care of resetting the user at the end it affected all following
                   tasks, potentially creating unwanted results. Same goes for the course. To
                   avoid any interference we now set the admin user and the default course
                   before each task to make sure all get the same environment.

    TL-17612       Added a warning by the "next page" button when using sequential navigation

                   When the quiz is using sequential navigation, learners are unaware that
                   they cannot navigate back to a question. A warning has been introduced when
                   sequential navigation is in place to make the learner aware of this.

    TL-17630       Fixed Error in help text when editing seminar notifications

                   in the 'body_help' string replaced [session:room:placeholder] with
                   [session:room:cf_placeholder] as all custom field placeholders have to have
                   the cf_ prefix in the notification.

    TL-17633       Removed misleading information in the program/certification extension help text

                   Previously the help text stated "This option will appear before the due
                   date (when it is close)" which was not accurate as the option always
                   appeared during the program/certification enrollment period. This statement
                   has now been removed.

    TL-17647       Raised MySQL limitation on the amount of questions for Appraisals.

                   Due to MySQL/MariaDB row size limit there could only be about 85 questions
                   of types "text" in one appraisal. Creating appraisals with higher numbers
                   of questions caused an error on activation. Changes have been made to the
                   way the questions are stored so that now it's possible to have up to about
                   186 questions of these types when using MySQL/MariaDB.

                   On the appraisal creation page a warning message has been added that is
                   shown when the limit is about to be exceeded due to the amount of added
                   questions.

                   Also, when this error still occurs on activation, an informative error
                   message will be shown instead of the MySQL error message.

    TL-17702       Fixed display issue when editing forum subscribers
    TL-17724       Fixed nonfunctional cleanup script for incorrectly deleted users
    TL-17732       Fixed a regression in the Current Learning block caused by TL-16820

                   The export_for_template() function in the course user learning item was
                   incorrectly calling get_owner() when it should have been using has_owner().

    TL-17744       Fixed header tags being the same size as all other text in the HTML block

Contributions:

    * Jo Jones at Kineo UK - TL-17524


Release 9.20 (14th May 2018):
=============================


Security issues:

    TL-17382       Mustache str, pix, and flex helpers no longer support recursive helpers

                   A serious security issue was found in the way in which the String, Pix
                   icon, and Flex icon Mustache helpers processed variable data.
                   An attacker could craft content that would use this parsing to instantiate
                   unexpected helpers and allow them to access context data they should be
                   able to access, and in some cases to allow them to get malicious JavaScript
                   into pages viewed by other users.
                   Failed attempts to get malicious JavaScript into the page could still lead
                   to parsing issues, encoding issues, and JSON encoding issues. Some of which
                   may lead to other exploits.

                   To fix this all uses of these three mustache helpers in core code have been
                   reviewed, and any uses of them that were using user data variables have
                   been updated to ensure that they are secure.

                   In this months Evergreen release and above the API for these three helpers has
                   been revised. User data variables can no longer be used in Mustache
                   template helpers.

                   We strongly recommend all users review any customisations they have that
                   make use of Mustache templates to ensure that any helpers being used don't
                   make use of context data variables coming from user input.
                   If you find helpers that are using variables containing user data we strongly
                   recommend preparing new pre-resolved context variables in PHP or JavaScript
                   and not passing that information through the helpers.

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

    TL-15037       Fixed name_link display function of the "Event name" column for the site log report source

                   The Event name (linked to event source) column in the Site Logs reporting
                   source was not fully restoring the event data.

    TL-17387       Fixed managers not being able to allocate reserved spaces when an event was fully booked
    TL-17471       Fixed Google reCAPTCHA v2 for the "self registration with approval" authentication plugin
    TL-17528       Removed some duplicated content from the audience member alert notification
    TL-17535       Fixed hard-coded links to the community site that were not being redirected properly

Contributions:

    * Marcin Czarnecki at Kineo UK - TL-17387


Release 9.19 (19th April 2018):
===============================


Improvements:

    TL-10295       Added link validation for report builder rb_display functions

                   In some cases if a param value in rb_display function is empty the function
                   returns the HTML link with empty text which breaks a page's accessibility.

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

    TL-16122       Added the 'Enrolments displayed on course page' setting for the Seminar direct enrolment plugin and method

                   Previously the amount of enrolments on the course page was controlled by
                   the 'Events displayed on course page' course setting. Now there are two new
                   settings, one is under "Site administration > Plugins > Enrolments >
                   Seminar direct enrolment plugin" where the admin can set a default value
                   for all courses with the Seminar direct enrolment method. The other is
                   under the Course seminar direct enrolment method where the admin can set a
                   different value. The available options are "All(default), 2, 4, 8, 16" for
                   both settings.

    TL-16724       Fixed an error while backing up a course containing a deleted glossary

                   This error occurred while attempting to backup a course that contained a
                   URL pointing to a glossary activity that had been deleted in the course
                   description. Deleted glossary items are now skipped during the backup
                   process.

    TL-16821       Removed an error that was stopping redisplay questions in Appraisals from displaying the same question twice
    TL-16898       Fixed the seminar booking email with iCal invitation not containing the booking text in some email clients.

                   Some email clients only display the iCal invitation and do not show the
                   email text if the email contains a valid iCal invitation. To handle this
                   the iCal description will now include the booking email text as well as
                   Seminar and Seminar session description.

    TL-16926       Limited the maximum number of selected users in the Report builder job assignment filter

                   Added 'selectionlimit' option to manager field filters, also introduced
                   "$CFG->totara_reportbuilder_filter_selected_managers_limit" to limit the
                   number of selected managers in the report builder job assignment filter
                   dialog. The default value is 25, to make it unlimited, set it to 0.

                   This patch also removed the equals and not-equals options from the job
                   assignment filter when multiple job assignments are not enabled.

    TL-17254       Fixed a custom field error for appraisals when goal review question was set up with "Multiple fields" option
    TL-17267       Fixed the resetting of the 'Automatically cancel reservations' checkbox when updating a Seminar
    TL-17358       Fixed notification preference override during Totara Connect sync

                   Changes made to a user's notification preferences on a Totara Connect
                   client site will no longer be overridden during sync.

    TL-17386       Fixed the syncing of the suspended flag in Totara Connect

                   When users are synced between a Totara Connect server and client, a user's
                   suspended flag is only changed on the client when a previously
                   deleted/suspended user is restored on the server and then re-synced to the
                   client with the "auth_connect/removeuser" configuration setting set to
                   "Suspend internal user"

    TL-17392       Fixed the seminar events report visibility records when Audience-based visibility is enabled

                   When a course had audience-based visibility enabled and the course
                   visibility was set to anything other than "All users", the seminar events
                   report was still displaying the course to users even when they didn't match
                   the visibility criteria. This has been corrected.

    TL-17415       Stopped updating calendar entries for cancelled events when updating the seminar information

                   Previously the system re-created the site, course, and user calendar
                   entries when updating seminar information. This patch added validation to
                   calendar updates for cancelled events.


Release 9.18 (23rd March 2018):
===============================


Important:

    TL-14114       Added support for Google ReCaptcha v2 (MDL-48501)

                   Google deprecated reCAPTCHA V1 in May 2016 and it will not work for newer
                   sites. reCAPTCHA v1 is no longer supported by Google and continued
                   functionality can not be guaranteed.

    TL-17228       Added description of environment requirements for Totara 12

                   Totara 12 will raise the minimum required version of PostgreSQL from 9.2 to
                   9.4

Security issues:

    TL-17225       Fixed security issues in course restore UI

Improvements:

    TL-15003       Improved the performance of the approval authentication queue report
    TL-16864       Improved the template of Seminar date/time change notifications to accommodate booked and wait-listed users

                   Clarified Seminar notification messages to specifically say that it is
                   related to the session that you are booked on, or are on the wait-list for.
                   Also removed the iCal invitations/cancellations from the templates of users
                   on the wait-list so that there is no confusion, as previously users who
                   were on the wait-list when the date of a seminar was changed received an
                   email saying that the session you are booked on has changed along with an
                   iCal invitation which was misleading.

    TL-16914       Added contextual details to the notification about broken audience rules

                   Additional information about broken rules and rule sets are added to email
                   notifications. This information is similar to what is displayed on
                   audiences "Overview" and "Rule Sets" tabs and contains the broken audience
                   name, the rule set with broken rule, and the internal name of the broken
                   rule.

                   This will be helpful to investigate the cause of the notifications if a
                   rule was fixed before administrator visited the audience pages.

    TL-17149       Fixed undefined index for the 'Audience visibility' column in Report Builder when there is no course present

Bug fixes:

    TL-6209        Fixed goal summary report source not displaying headings for the "Scale count columns" column
    TL-10394       Fixed bad grammar in the contextual help for Seminars > Custom fields > text input
    TL-16549       Cancelling a multi-date session results in notifications that do not include the cancelled date

                   Changed the algorithm of iCal UID generation for seminar event dates. This
                   allows reliable dates to be sent for changed\cancelled notifications with
                   an attached iCal file that would update the existing events in the
                   calendar.

    TL-16820       Fixed the current learning block using the wrong course URL when enabling audience based visibility
    TL-16831       Fixed Totara Sync not always creating job assignments when users are created by the sync
    TL-16838       Stopped reaggregating competencies using the ANY aggregation rule when the user is already proficient
    TL-16856       Fixed text area user profile fields when using Self-registration with approval plugin

                   Using text area user profile fields on the registration page was stopping
                   the user and site administrator from attempting to approve the account.

    TL-16865       Fixed the length of the uniquedelimiter string used as separator for the MS SQL GROUP_CONCAT_D aggregate function

                   MS SQL Server custom GROUP_CONCAT_* aggregate functions have issues when
                   the delimeter is more than 4 characters.

                   Some report builder sources used 5 character delimiter "\.|./" which caused
                   display issues in report. To fix it, delimeter was changed to 3 characters
                   sequence: "^|:"

    TL-16882       Removed the "allocation of spaces" link when a seminar event is in progress
    TL-16925       Fixed the calculation of SCORM display size when the Navigation panel is no longer displayed
    TL-17111       Renamed some incorrectly named unit test files
    TL-17207       Fixed a missing include in user/lib.php for the report table block

Contributions:

    * Ben Lobo at Kineo UK - TL-16549
    * Francis Devine at Catalyst - TL-16831
    * Russell England at Kineo USA - TL-17149


Release 9.17 (12th March 2018):
===============================


Important:

    TL-17166       Added support for March 1, 2018 PostgreSQL releases

                   PostgreSQL 10.3, 9.6.8, 9.5.12, 9.4.17 and 9.3.22 which were released 1st
                   March 2018 were found to not be compatible with Totara Learn due to the way
                   in which indexes were read by the PostgreSQL driver in Learn.
                   The method for reading indexes has been updated to ensure that Totara Learn
                   is compatible with PostgreSQL.

                   If you have upgraded PostgreSQL or are planning to you will need to upgrade
                   Totara Learn at the same time.


Release 9.16 (28th February 2018):
==================================


Security issues:

    TL-16789       Added output filtering for event names within the calendar popup

                   Previously event names when displayed within the calendar popup were not
                   being cleaned accurately.
                   They are now cleaned consistently and accurately before being output.

    TL-16814       Fixed a typo in Moodle capability definitions that was leading to risks not being correctly registered

                   A typo had been introduced in 8 core capabilities that meant that the risks
                   that wanted to register were not correctly registered.
                   These capabilities may have been assigned to roles in the system without
                   the assigner being aware that there were risks associated with them.
                   We recommend you review the following capabilities and confirm that you are
                   happy with the roles that they have been assigned to:
                   * moodle/user:managesyspages
                   * moodle/user:manageblocks
                   * moodle/user:manageownblocks
                   * moodle/user:manageownfiles
                   * moodle/user:ignoreuserquota
                   * moodle/my:configsyspages
                   * moodle/badges:manageownbadges
                   * moodle/badges:viewotherbadges

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

Performance improvements:

    TL-16189       Moved audience learning plan creation from immediate execution onto adhoc task.

                   Before this change, when learning plans were created via an audience, they
                   would be created immediately. This change moves the plan creation to an
                   adhoc task that is executed on the next cron run. This reduces any risk of
                   database problems and the task failing.

    TL-16314       Wrapped the Report builder create cache query in a transaction to relax locks on tables during cache regeneration in MySQL

                   Report Builder uses CREATE TABLE SELECT query to database in order to
                   generate cache which might take long time to execute for big data sets.

                   In MySQL this query by default is executed in REPEATABLE READ isolation
                   level and might lock certain tables included in the query. This leads to
                   reduced performance, timeouts, and deadlocks of other areas that use same
                   tables.

                   To improve performance and avoid deadlocks this query is now wrapped into
                   transaction, which will set READ COMMITTED isolation level and relax locks
                   during cache generation.

                   This will have no effect in other database engines.

Improvements:

    TL-16764       Course activities and types are now in alphabetical order when using the enhanced catalog

                   This also makes the sort order locale aware (so users using Spanish
                   language will have a different order to those using English).

                   This is a backport of TL-12741 which was included in the Totara Learn 10.0
                   release.

Bug fixes:

    TL-10317       Fixed dialog JavaScript within the Element Library

                   There was a JavaScript fault on the dialog page within the Element Library
                   which stopped the dialogs used for testing purposes from opening.
                   This has now been fixed.

    TL-16499       Fixed name collision with form fields in Appraisals when there are multiple goal questions

                   Added an extra parameter to the constructor of the customfield_base class
                   which allows a custom suffix to be added along with the item id when the
                   $suffix parameter is true. There is a default value for this parameter
                   of an empty string so child classes will need to add this parameter to
                   their constructors.

                   The parameter has also been added to functions that make customfield_base
                   objects. These are customfield_definition, customfield_load_data
                   and customfield_save_data.

    TL-16540       Fixed yes_or_no display function in Report Builder not handling null value correctly

                   In the legacy version (rb_display_yes_or_no) nulls are handled by
                   displaying an empty field, but in
                   totara/reportbuilder/classes/rb/display/yes_or_no.php null values are
                   displaying "No" as their output when it should be empty. This has been
                   fixed.

                   Please note that the filter behaves as expected and although null
                   values were displaying 'No' they would have not matched the 'No' value in
                   the filter.

    TL-16592       Fixed typos in Seminar event minimum capacity help strings

                   Previously the strings were pointing to an invalid location for the Seminar
                   general settings.

    TL-16662       Cleaned up orphaned data left after deleting empty course sets from within a Program or Certification

                   The orphaned data happens when there are no orphaned program courses but
                   there are orphaned program course sets.
                   This is only known to affect sites running Totara Learn 2.7.3 or earlier.
                   An upgrade step has been added to remove any orphaned records from the
                   database.

    TL-16673       Fixed error being thrown in Moodle course catalog when clicking "Expand all" with multiple layers of categories
    TL-16741       Inputs are no longer shown for questions the user cannot provide answers for within Appraisals
    TL-16742       Fixed a fatal error within the quiz module statistics report after a multiple answer multichoice question was deleted
    TL-16748       Prevented users from signing up to a cancelled Seminar sessions by following the emailed direct link
    TL-16749       Fixed a regression from TL-14803 to allow HTML in mod certificate custom text

                   This patch fixes a regression caused by TL-14803 which affected the display
                   of the custom text when used with multilang content in all versions back to
                   2.7.  Data has not been affected with the regression. The change updates
                   the use of format_string() function to format_text().

    TL-16759       Enabled answers in Appraisals to display for roles that have no user associated with them or the user has been deleted

                   In the populate_roles_element function in the appraisal_question class
                   empty question roles are no longer excluded from the appraisal question
                   role info.

    TL-16761       Fixed Seminar notification templates remaining enabled at a the Seminar level after being disabled globally

                   This patch includes a fix for a local Seminar event registration
                   notification not being disabled after propagating global settings for it.

                   It also includes the fix for a case when notification is disabled, but a
                   user still sees checkboxes or dropdown prompting whether the notification
                   should be sent.

    TL-16776       Improved the display of the gradebook report in IE

                   Previously column headers and users names were getting out of sync with
                   their results in the gradebook with IE11. This is now fixed

    TL-16791       Fixed Certificate generation when using Traditional Chinese (zh_tw)
    TL-16798       Fixed a pagination error when searching rooms within a Seminar activity
    TL-16799       Fixed exported ID in the Course Completion report

                   Backported a fix applied to T10 and T11 that fixes an error with exports of
                   the course completion report (Course administration > Reports > Course
                   completion) and removes html tags from the output.

    TL-16813       Grading by rubric now works when using the keyboard only
    TL-16847       Fixed the 'Event cancelled' status not being displayed if the Seminar sign-up period is specified

                   When viewing information related to an event in a Seminar that had sign-up
                   period specified the column status was not being updated if the event was
                   cancelled.

                   "Event cancelled" status should have priority over any other event status.

    TL-16955       Added a workaround for sqlsrv driver locking up during restore

                   In rare cases during the restoration of a large course MSSQL, would end up
                   in a locked state whilst waiting for two conflicting deadlocks.
                   This occurred due to a table being both read-from and written-to within a
                   single transaction.

Contributions:

    * Eugene Venter at Catalyst NZ - TL-16799
    * Learning Pool - TL-16791


Release 9.15 (18th January 2018):
=================================


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
    TL-16479       Fixed inconsistent use of terminology in Seminar
    TL-16627       A user's current course completion record can now be deleted

                   Using the course completion editor, it is now possible to delete a user's
                   current course completion record. This is only possible if the user is no
                   longer assigned to the course.

    TL-16653       Reportbuilder now shows an empty graph instead of an error message when zero values are returned

Bug fixes:

    TL-11097       Removed duplicated seminar attendees overbooking notification
    TL-16016       Changed message on Appraisal missing roles page when a job assignment has not yet been selected
    TL-16536       Added missing string on the Feature overview page
    TL-16630       Fixed error caused by adding a role column as the first column to Seminar sessions report
    TL-16631       Fixed SCORM package display in simple popup window when package does not provide player API
    TL-16700       Added workaround in DML for fatal errors when get_in_or_equal() used with large number of items
    TL-16707       Fixed multi-lang support for dashboard names

Contributions:

    * Dustin Brisebois at Lambda Solutions - TL-16707


Release 9.14 (21st December 2017):
==================================


Security issues:

    TL-16451       Fixed permissions not being checked when performing actions on the Seminar attendees page
    TL-16550       Deletion of a job assignment now removes the staff manager role from the manager in the job assignment

Improvements:

    TL-9277        Added additional options when selecting the maximum Feedback activity reminder time
    TL-16241       Fixed breadcrumb trail when viewing a user's completion report
    TL-16256       Allowed appraisal messages to be set to "0 days" before or after event

                   Some immediate appraisals messages were causing performance issues when
                   sending to a lot of users.
                   This improvement allows you to set almost immediate messages that will send
                   on the next cron run after the action was triggered to avoid any
                   performance hits. The appraisal closure messages have also been changed to
                   work this way since they don't have any scheduling options.

    TL-16494       Improved embedded reports test coverage

Bug fixes:

    TL-8062        Fixed Seminar notifications not being sent when the room has been changed
    TL-9885        Fixed validation for sending an extension request for a program

                   When a learner opened the page to request an extension, and in the
                   meanwhile an admin deactivated the possibility to send a request, the
                   learner could still send the request. The validation was fixed and made
                   consistent to prevent these cases. The same goes for direct calls to the
                   sending url.

    TL-10897       Fixed incomplete validation message for recertification window period
    TL-15804       Feedback Reminder periods help text has been clarified to explain it counts weekdays and not weekends

                   The Feedback Reminder period is calculated only using weekdays. All
                   weekends will be skipped and added to the period. To make this existing
                   behaviour clearer we modified the help text accordingly.

    TL-16015       Goal Custom fields are disabled in appraisals where applicable

                   If a user cannot answer the appraisal or does not have the necessary
                   permissions to edit a goal's custom fields then they will not be able to
                   edit the form fields for the custom field in the appraisal.

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

    TL-16300       Fixed automated backup when using specified directory for automated backups setting
    TL-16458       Fixed Totara Connect SSO login process to update login dates and trigger login event
    TL-16462       Fixed display of custom dashboard menu item in the Totara menu
    TL-16472       Fixed Seminar direct enrolment not honouring restricted access
    TL-16473       Fixed Seminar trainers not receiving booking time/date changed notifications
    TL-16476       Fixed custom favicon in Basis theme
    TL-16492       Allow less privileged reviewers and respondents to a 360° Feedback to access the files added to a response
    TL-16521       Fixed certification messages that were not reset before upgrading to TL-10979

                   When patch TL-10979 was included in Totara 2.9.13 and 9.1, it did not
                   include an upgrade to reset messages which were not reset when the
                   recertification window opened before the upgrade. This patch resets those
                   messages, where possible, allowing the messages to be sent again. Users
                   whose recertification windows have reopened since upgrading to the above
                   mentioned versions will not be affected because they should already be in
                   the correct state.

    TL-16530       Fixed report builder cache generator

                   Previously the Report Builder source cache was removing the old cache table
                   before creating a new one, which was creating a problem whereby the user
                   couldn't use the old cache table and the new one wasn't ready.
                   The fix was to keep the old table until the new table was ready, at which
                   point the old table is removed.

    TL-16553       Fixed lock timeout value for memcached 3.x being too long
    TL-16554       Added language menu when creating new user via form

                   When a user is created a language menu is now displayed in the form to
                   allow the creator to set the user's language.
                   This ensures that any notifications the user is sent during or immediately
                   after the creation of their account are sent in their language.

    TL-16584       Site administration and Navigation blocks can be set to show on all pages after removal
    TL-16603       Ported MDL-55469 to allow learners to completely finish a final SCORM attempt

                   Important consideration: This fix relies on correct data submitted by the
                   SCORM package. If the SCORM reported that "cmi.core.lesson_status" is
                   either "completed", "failed", or "passed", then the attempt will be counted
                   as final even if user exited the activity without submitting/finalising the
                   attempt.

    TL-16605       Fixed report title alignment for right-to-left languages when exporting to PDF in Report Builder
    TL-16614       Fixed event roles from a cancelled event preventing users being assigned to a new event with the same date and time
    TL-16629       Fixed the incorrect resolution of promises when loading forms via AJAX fails

Miscellaneous Moodle fixes:

    TL-16076       MDL-59504: Updated the Mahara logo

Contributions:

    * Barry Oosthuizen at Learning Pool - TL-9277
    * Jo Jones at Kineo UK - TL-16530


Release 9.13 (22nd November 2017):
==================================


Security issues:

    TL-16270       360° Feedback now correctly disposes of the user's access token when no longer needed

                   Previously if a user accessed a 360° Feedback instance using a token, that
                   token would be stored in the user's session and would allow them to access
                   the 360° Feedback as a user (not with a token).
                   The token used to access the first 360° Feedback instance is now disposed
                   of correctly.

Improvements:

    TL-15907       Improved how evidence custom field data is saved when importing completion history

Bug fixes:

    TL-9360        Managers approving Seminar booking requests are now notified of the updates success

                   Previously, when a manager approved staff requests for bookings into a
                   Seminar event, they would then be redirected to a page saying 'You can not
                   enrol yourself in this course' (assuming they were not enrolled or did not
                   have other permissions to view the attendees page). Following any approvals
                   (by a manager or any other user), the page will now refresh onto the
                   approval required page, with a message confirming the update was
                   successful.

    TL-10880       Fixed language string fault in deprecated menu functionality.
    TL-13934       Fixed 'user' join not in join list for content in the message report
    TL-15029       Fixed brief positioning issue when scrolling a 360° Feedback page
    TL-15956       Set the RPL fields on the course completion report to read only when appropriate

                   Previously, the RPL fields were allowing data to be entered/edited when
                   users were already complete. The form will now set them to read only in
                   this situation. There is also now a column with a link to the course
                   completion editor, which should be used if changes are required.

    TL-16253       HTML pasted into Atto is now sanitised to remove markup known to cause display issues

                   When copying HTML into an Atto editor instance, script, iframe and head
                   HTML tags are now removed. These tags can be added manually when editing
                   the text in source mode.

    TL-16287       Fixed renaming of user profile fields breaking HR Import user source settings

                   If the HR Import user source (CSV or Database) was configured to import a
                   custom profile field and the field short name was changed then HR Import
                   would no longer import data to it. In some situations it would then be
                   impossible to re-add the field. This has now been fixed.

    TL-16296       Fixed a bug leading to schedule changes for reports being forgotten
    TL-16312       Fixed formatting of text area fields in the Database course activity when exporting

                   When exporting text area field data from the Database activity the field
                   content included HTML tags. It now converts the HTML to standard text.

    TL-16318       Fixed calendar events for single Seminar sessions with multiple dates
    TL-16376       Fixed LDAP sync for user profile custom menu field

                   TL-14170 fixed a problem where custom user profile fields were not being
                   synced with an LDAP backend. The fix worked for all user profile custom
                   fields except for menu dropdowns which required an extra processing step
                   during the LDAP syncing. This has now been fixed.

    TL-16386       Fixed dashboard reset error with deleted users
    TL-16396       Fixed an SQL error occurring due to a missing default

                   This may have affected sites that have upgraded through Totara 2.5, and
                   which were using Seminar room functionality.
                   A missing upgrade step may have lead to an incorrect null default value
                   existing in the facetoface_room table.
                   The fix for this issue has added the missing upgrade step which correctly
                   removes the null values and replaces them with the expected "0".

    TL-16422       Fixed and removed forgotten deprecated location code in Seminar
    TL-16429       Fixed session details missing from Trainer confirmation email
    TL-16430       Fixed alphabetical order user list when selecting a manager
    TL-16435       Fixed missing "Notification does not exist" string
    TL-16443       Fixed an SQL error in the Appraisal details report due to multi-select questions

Contributions:

    * Grace Cooper at Kineo UK - TL-16396
    * Richard Eastbury at Think Associates - TL-16376


Release 9.12 (27th October 2017):
=================================


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

Performance improvement:

    TL-16161       Reduced load times for the course and category management page when using audience visibility

Improvements:

    TL-11296       Added accessible text when creating/editing profile fields and categories
    TL-15835       Made some minor improvements to program and certification completion editors

                   Changes included:
                    * Improved formatting of date strings in the transaction interface and
                   logs.
                    * Fixed some inaccurate error messages when faults might occur.
                    * The program completion editor will now correctly default to the
                   "invalid" state when there is a problem with the record.

    TL-16381       The new release notification was updated to use new end point

Bug fixes:

    TL-15846       Removed an incorrectly displayed sidebar report filters dropdown from the Basis theme
    TL-15885       Fixed Navigation block problems with course visibility
    TL-15923       Fixed duplicate calendar records for Seminar wait-list user calendar
    TL-15932       Fixed problem of SCORM window size cutting off content
    TL-15988       Prevented autofill of non-login passwords in Chrome
    TL-15997       Fixed saving of new/changed Seminar direct enrolment custom fields
    TL-16124       Fixed Seminar booking confirmation sent to manager when no approval required
    TL-16163       Your progress text no longer is displayed on top of the user menu
    TL-16212       Fixed issue where self completion from within a certificate activity may complete a different activity
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

    TL-16237       Fixed upgrade issue when different Seminar notifications have same title
    TL-16242       Scorm loading placeholders are now displayed correctly in RTL languages
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
    TL-16274       Fixed an issue when updating user Forum preferences when user's username contains uppercase characters
    TL-16279       Added additional checks when displaying and validating self completion from within an activity
    TL-16286       Fixed incorrect appraisal status on reassigned users
    TL-16288       Checkbox and radio options lists no longer have bold input labels
    TL-16289       Fixed course completion editor link requiring incorrect capability

                   The link no longer requires the 'moodle/course:update' capability. It now
                   only requires the 'totara/completioneditor:editcoursecompletion'
                   capability.

    TL-16292       Fixed saving of seminar custom fields for all users
    TL-16301       Fixed calendar filtering on seminar room fields
    TL-16392       Fixed namespace of activity completion form

Miscellaneous Moodle fixes:

    TL-16037       MDL-59527: Fixed race condition when using autocomplete forms

Contributions:

    * Nicholas Hoobin at Catalyst AU - TL-16212


Release 9.11 (22nd September 2017):
===================================


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
    TL-16118       Fixed the logic in checking whether users can view course profiles
    TL-16119       Fixed incomplete escaping on the Feedback activity contact form
    TL-16120       Added warning to admins when a development libs directory exists.

New features:

    TL-4156        Added the course completion editor

                   The course completion editor is accessible in Course administration >
                   Course completion, to all users who have the
                   'totara/completioneditor:editcoursecompletion' capability in the course
                   context (default is administrators only). The editor allows you to edit
                   course completion, criteria completion, activity completion and history
                   data, allowing you to put this data into any valid state. It includes
                   transaction logs, which record all changes that are made to these records
                   (both from within the editor and in other areas of Totara, e.g. completion
                   of an activity, or when cron reaggregates completion). It also includes a
                   checker, which can be used to find records which have data in an invalid
                   state.

Improvements:

    TL-14244       Updated default branding to Totara Learn

                   Changed language strings and logos to use the new product name "Totara
                   Learn" instead of "Totara LMS".

    TL-14275       Users can now cause self completion from within a course activity

                   This ability has been added to all core modules excluding Lesson and Quiz
                   (where a user should at least attempt the activity). Non-core modules will
                   need to be modified to support this functionality

    TL-15056       Added warning notice to the top of delete category page
    TL-15834       Improved Datepicker in Totara forms
    TL-15996       Improved test environment init when switching PHP versions
    TL-16148       Improved performance of category management page

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
    TL-14996       Disabled multiple selection during manager selection in signup form
    TL-15038       Fixed error when trying to save a search with availability filter in Rooms and Assets reports
    TL-15785       Fixed the display of manager and appraiser filters while creating a saved search
    TL-15843       Updated job assignments sync to allow email to be omitted.

                   Previously, it was not possible to use HR Import to add / update User
                   source job assignment data without encountering a problem if this email
                   field was omitted. This has been corrected.

    TL-15852       Fixed Restrict initial display when counting a last filter
    TL-15879       Fixed missing icon from Progress column in Record of Learning in some cases
    TL-15884       Fixed an Job assignment error when taking attendance for a Seminar activity
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

    TL-15892       Ensured course deletion does not effect awarded course badges
    TL-15897       Fixed some typos in Certification language strings
    TL-15899       Corrected inconsistent validation of Seminar sender address setting
    TL-15900       Fixed manager's manager not updating in dynamic appraisals

                   After upgrade, the next time the "Update learner assignments to appraisals"
                   scheduled task is run, it will update any managers' managers that have
                   changed, where the update is appropriate.

    TL-15919       Fixed missing delete assignment button for active appraisals
    TL-15921       Fixed multiple display of seminar attendees that have been approved more than once
    TL-15936       Fixed detection of non-lowercase authentication plugin names in HR Sync on OSX and Windows
    TL-15937       Added missing appraisal data generator reset
    TL-15977       Fixed SCORM cmi.interaction bug
    TL-16010       Added reset method to hierarchy generator
    TL-16121       Fixed View Details link not working when user is viewing appraisal answers only
    TL-16126       Fixed how choice activity data is reset by certification windows

Miscellaneous Moodle fixes:

    TL-16033       MDL-57649: Fixed removing of attached files in question pages of lesson module

                   Fixed bug in lesson activity which did not automatically remove files
                   attached to question pages when those pages were deleted.


Release 9.10 (23rd August 2017):
================================


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

    TL-12886       Improved formatting when viewing user details within a course
    TL-14368       Added an autosubmit handler to Totara forms
    TL-14726       Stopped duplicate calls to the core_output_load_template webservice

                   When requesting the same template numerous times in quick succession via
                   JavaScript, the template library was firing duplicate requests to the
                   server. This improvement stops duplicate requests from happening.

    TL-14781       Improved efficiency of job assignment filter joins

                   Previously, job assignment filters were joining to the user table. Now,
                   they can join to the user id in another table, such as the report's base
                   table. If data from the user table is not needed then that join will no
                   longer be needed in order to use the job assignment filters. These changes
                   potentially result in a small performance improvement.

    TL-14986       Added proficiency achieved date to competencies

                   Added new column called "timeproficient" to both the comp_record and
                   comp_record_history tables, this field defaults to the first time when a
                   user is marked proficient in a competency. There are also new "Date
                   proficiency achieved" columns/filters for the competency report sources,
                   and a date selector on the set competency status form allowing you to edit
                   the field. Please note that this field only works for future proficiencies,
                   but existing ones can be edited via the competency status form.
                   This change has also added a default value when the default competency
                   scale is created, so new installs will include a default value of 'Not
                   competent'.

    TL-14988       Ensured that a competency status is displayed on the Record of Learning even if a learning plan has been deleted
    TL-15002       Added navigation links on the Approval plugin edit signup page
    TL-15006       Cleaned up and improved dataroot reset in behat and phpunit tests
    TL-15009       Added new faster static MUC cache for phpunit tests
    TL-15016       Improved the summary of the mod/facetoface:signupwaitlist capability to avoid confusion
    TL-15755       Unnecessary confirmation related emails are not sent when request is approved automatically in Self-registration with approval
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

    TL-12459       Prevented the leave page confirmation when approving changes after adding an Audience rule
    TL-12855       Fixed quiz statistics for separate groups
    TL-14148       Fixed static server version caching in database drivers
    TL-14170       Fixed LDAP/user profile custom field sync bug
    TL-14239       The required fields note now appears correctly when a Totara form is loaded via JavaScript
    TL-14316       Fixed the loading of YUI dialogs within Totara dialogs
    TL-14805       Ensured appraisal question field labels display consistently
    TL-14813       Pix to Flex icon conversion now honours custom pix title attributes
    TL-14828       Forum posts only marked as read when full post is displayed
    TL-14935       Ensured that programs and their courses appear within the Current Learning Block when they are within an approved Learning Plan
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
    TL-14984       Fixed the display of grades in the Record of Learning grades column
    TL-14994       Added missing parameter to job assignments url on the user profile page
    TL-15000       Removed duplicate error messages when approving signups
    TL-15011       Added check for valid hierarchy ids when accessing auth approved signup page with external defaults
    TL-15022       Fixed 'Responsetime' for anonymous users from showing epoch date
    TL-15024       Fixed an error that occurred when exporting assignees and their job assignments for Seminar events
    TL-15039       Fixed an SQL error that occurred when searching in filters using just a space
    TL-15040       Fixed the information sent in the attached ical when notifying users that a Seminar's date and details have been changed
    TL-15054       Fixed inconsistent behaviour when changing number of course sections
    TL-15057       ORACLE SQL keywords are now ignored when validating install.xml files
    TL-15080       Fixed context of dynamic audiences rules permission check

                   totara/cohort:managerules permissions were incorrectly checked in System
                   context in some cases instead of in the Category context.

    TL-15083       Updated the capability check in totara_gap_can_edit_aspirational_position to ensure new users can be created without error

                   When a new user is added, their id is -1 until their record has been
                   created. The totara_gap_can_edit_aspirational_position function has been
                   updated to recognise this and to allow for new users to be added.

    TL-15086       Fixed SCORM view page to display content depending on permissions

                   If the user has the mod/scorm:savetrack capability, they can see the info
                   page and enter the SCORM lesson.
                   If the user has the mod/scorm:viewreport capability, they can see the SCORM
                   reports.

    TL-15095       Fixed known compatibility problems with MariaDB 10.2.7
    TL-15097       Added a missing language string used within course reset
    TL-15100       Fixed session start date link format without timezone
    TL-15103       Fixed handling of html markup in multilingual authentication instructions
    TL-15303       Fixed element heights set by JavaScript in grader report
    TL-15731       Fixed the display of personal goal text area custom fields in Appraisal snapshots
    TL-15738       Fixed program progress bar in Program Overview report source
    TL-15775       Fixed incorrect encoding of language strings in Appraisal dialogs
    TL-15811       Fixed admin tree rendering to handle empty sub items
    TL-15838       Fixed Seminar Message Users to send a message to CC user manager

Contributions:

    * Richard Eastbury at Think Associates - TL-15775
    * Russell England at Kineo USA - TL-15083


Release 9.9 (19th July 2017):
=============================


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

    TL-2821        Capability to configure a second database connection for Report Builder

                   It is now possible to configure a second database connection for use by
                   Report Builder.
                   The purpose of this secondary connection is so that you can direct the main
                   Report Builder queries at a read-only database clone.
                   The upside of which is that you can isolate the database access related
                   performance cost of Report Builder to an isolated database server.
                   This in turn prevents the expensive report builder queries from being
                   executed on the primary database, hopefully leading to a better user
                   experience on high concurrency sites.
                   These settings should be considered highly advanced.
                   Support cannot be provided on configuring a read only slave, you will need
                   in house expertise to achieve this.
                   Those wishing to use the second database connection can find instructions
                   for it within config-dist.php.

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
                   Additionally TL-14791 prevents the need to run the query to get the
                   unfiltered count unless the site administrator has explicitly requested it
                   and the report creator explicitly turned it on for that report.
                   This reduction of expensive queries greatly improves the performance of
                   viewing a report in the browser.

    TL-14237       Fixed an SQL error when caching a report with Job Assignment fields

                   Removed an issue where caching of a report failed due to the SQL failing.
                   This is only for the User's Position(s), User's Organisation(s), User's
                   Manager(s) and User's Appraiser(s) filters.

    TL-14398       Report Builder source caching is now user specific

                   Previously the Report Builder source cache was shared between users.
                   When scheduled reports were being run this could lead to several issues,
                   notably incorrect results when applying filters, and performance issues.
                   The cache is now user specific. This consumes more memory but fixes the
                   user specific scheduled reports and improves overall performance when
                   generating scheduled reports created by many users.

    TL-14421       Improved the performance of the Site log report source when the event name filter was available

                   The "Event name" filter has been changed from an option selector to a
                   freetext filter improving the performance of the site log report.

    TL-14432       Improved performance when generating report caches for reports with text based columns

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

    TL-14744       Fixed a JavaScript bug within the enhanced course catalog when no filters are available
    TL-14761       New better performing Job columns

                   Several new Job columns have been added to the available user columns in
                   reports that can include user columns.

                   The new Job columns can be found under the "User" option group, the
                   available columns are as follows:

                   * User's Position Name(s)
                   * User's Position ID Numbers(s)
                   * User's Organisation Name(s)
                   * User's Organisation ID Numbers(s)
                   * User's Manager Name(s)
                   * User's Appraiser Name(s)
                   * User's Temporary Manager Name(s)
                   * Job assignments

                   There are already several Job columns available in many sources, however
                   they operate slightly differently and perform very poorly on large sites.
                   The new columns have nearly the same result, but are calculated much more
                   quickly. In testing they were between 70-90% faster than the current
                   columns.

                   There is only one difference between the new and old columns and that is
                   how they are sorted when the user had multiple jobs.
                   The old columns all sorted the information in the column by the Job sort
                   order. This meant that all of the old columns were sorted in the same way
                   and the information aligned across multiple columns.
                   The new columns sort the data alphabetically, which means that when viewing
                   multiple columns the first organisation and the first position may not
                   belong to the same Job.

                   We strongly recommend that all reports use the new columns.
                   This needs to be done manually by changing from the Job columns shown under
                   "All User's Job Assignments" to those appearing under "User".
                   If you must use the old columns please be aware that performance,
                   particularly on MySQL and MSSQL could be a major issue on large sites.

                   The old fields are now deprecated and will be removed after the release of
                   Totara 10.

    TL-14780       Fixed the unnecessary use of LIKE within course category filter multichoice

                   The course category multichoice filter was unnecessarily using like for
                   category path conditions.
                   It can use = and has been converted to do so, improving the overall
                   performance of the report when this filter is in use.

    TL-14791       Report Builder reports no longer show a total count by default

                   The total unfiltered count of records is no longer shown alongside the
                   filtered count in Report Builder reports.
                   If you want this functionality back then you must first turn on "Allow
                   Report Builder reports to show Total Count" at the site level, and then for
                   each report where you want it displayed edit the report and turn on
                   "Display a Total Count of records" (found under the Performance tab).
                   Please be aware that for performance reasons we recommend you leave these
                   settings off.

    TL-14793       Filters which are not compatible with report caching can now prevent report caching

                   Previously filters that were not compatible with report caching, such as
                   those filters using correlated subqueries, could be added to a report and
                   report caching turned on.
                   This either lead to an error or poor performance.
                   When such a filter is in use in a report, report caching is now prevented.

    TL-14816       Added detection of filters that prevent report caching

                   Report Builder now reviews the filters that are being used on a report that
                   is configured to be cached before attempting to generate the cache in order
                   to check if the filter is compatible with caching.
                   If the filter is not compatible with caching then the report will not use
                   caching.
                   This prevents errors being encountered when trying to filter a cached
                   report for filters that are not compatible with caching.

    TL-14824       Improved the performance of the Site logs report source

                   Several columns in the Site logs report source were requiring additional
                   fields that did not perform well, and were not actually required for the
                   display of the columns in the report.
                   These additional fields have been removed, improving the performance of the
                   Site logs report source.

New features:

    TL-11096       New signup with approval authentication plugin

                   Thanks to Learning Pool for providing an initial plugin which informed the
                   design of this piece of work.

                   The new auth_approved plugin is similar to the existing auth_email plugin.
                   However, the auth_approved plugin has an approval process in which the
                   applicant gets a system access only if an approver approves of the signup.
                   The approver is any system user that has the new auth/approved:approve
                   capability. In addition, if the user also has the
                   totara/hierarchy:assignuserposition capability, he can change the
                   organisation/position/manager details that the applicant provided in his
                   signup.

                   The new plugin also has features to bulk approve or reject signups as well
                   as send custom emails to potential system users.

                   Finally, the new plugin also defines a report source that can be used as a
                   basis for custom reports.

Improvements:

    TL-3212        Improved notification template field behavior for seminar activity
    TL-11294       Added additional link text to the previous certification completions column when viewing a users record of learning
    TL-11295       Added accessibility link text to the previous program completions column when viewing a user's record of learning
    TL-12659       Added labels to linked component checkboxes in learning plans
    TL-12748       Speed up password hashing when importing users in HR Import
    TL-12960       Drag and drop question images are scaled when they are too big for the available space
    TL-14709       Changed manager job selection dialog to optionally disallow new job assignment creation
    TL-14755       Added an environment test for misconfigured MSSQL databases
    TL-14762       Added support for optgroups in Totara form select element
    TL-14820       Improved unit test performance and coverage for all Reportbuilder sources
    TL-14947       Improved unit test coverage of DB reserved words

Bug fixes:

    TL-14336       Removed audience visibility checks for courses added to Learning Plans

                   This change is to bring Learning Plans in line with the behaviour that
                   already exists within Programs and Certifications.

    TL-14341       Fixed page ordering for draft appraisals without stage due dates
    TL-14361       Fixed Seminar direct enrolment not allowing enrolments after upgrade
    TL-14379       Fixed double encoding of report names on "My Reports" page
    TL-14435       Fixed the use of an unexpected recordset when removing Seminar attendees
    TL-14446       Fixed incorrect link to Course using audience visibility when viewing a Program
    TL-14680       Hide manager reservation link when seminar event is cancelled
    TL-14701       Removed unused 'timemodified' form element from learning plan competencies
    TL-14713       Fixed escape character escaping within the "sql_like_escape" database function
    TL-14719       Prevented duplicate form ID attributes from being output on initial load and dynamic dialog forms
    TL-14735       JavaScript pix helper now converts pix icons that only supply the icon name to flex icons
    TL-14741       Fixed a php open_basedir restriction issue when used with HR Import directory check
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
    TL-14778       Added new strings to the Seminar language pack to ease translation

                   Several strings being used by the Seminar module from the main language
                   have now been copied and are included in the Seminar language files in
                   order to allow them to be translated specifically for Seminar activities.

    TL-14794       Fixed Seminar list under course activity
    TL-14798       Ensured html entities are removed for export in the orderedlist_to_newline display class
    TL-14803       Fixed certificate custom text to support multi-language content
    TL-14804       Fixed issue with null in deleted column when using HR import

                   When importing an element using database HR Import if there is a null in
                   the database column a database write error was thrown. Now a null value
                   will be treated as 0 (not deleted).

    TL-14806       Ensured when enabling or disabling an HR Import element, the notification is not incorrectly displayed multiple times
    TL-14809       Corrected typos within graph custom settings inline help
    TL-14814       Close button in YUI dialogs is fully contained within the header bar
    TL-14929       Fixed the display of available activities if the user holds the viewhiddenactivities capability

                   Previously available and visible activities were shown to the user as
                   hidden (dimmed) if the user held the viewhiddenactivities capability,
                   despite the activity being both visible and available.
                   Activities are now shown as visible correctly when the user can both access
                   them and holds the above mentioned capability.

    TL-14934       Fixed a coding error when using fasthashing for passwords in HR Import
    TL-14993       Prevented all access to the admin pages from the guest user
    TL-15014       Fixed inconsistencies in counted recordsets across all databases

                   The total count result is now consistent across all databases when
                   providing an offset greater than the total number of rows.

    TL-15036       Added missing column type descriptor in the Totara Connect report source

Miscellaneous Moodle fixes:

    TL-14920       MDL-56565: Prevented other users' username being displayed when manipulating URLs
    TL-14927       MDL-59456: Fixed a CAS authentication bypass issue when running against an old CAS server

Contributions:

    * Alex Glover at Kineo UK - TL-14341
    * Artur Rietz at Webanywhere - TL-14398
    * Jo Jones at Kineo UK - TL-14432
    * Russell England at Kineo USA - TL-14435
    * Pavel Tsakalidis for proposing the approach used in TL-6834


Release 9.8 (21st June 2017):
=============================


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

    TL-9775        Added Behat tests for Dynamic Audience Based Learning Plan creation
    TL-10502       Renamed Record of learning navigation block to "Learning" (from "Learning plans")
    TL-11264       Improved Atto editor autosave messaging and draft revert workflow

                   When a draft is automatically applied to an Editor, there is now a
                   page-level alert to let users know what has happened. In addition, the
                   default arrangement of toolbar icons now includes Undo/Redo which, when a
                   Draft is auto-applied, will toggle between original Database-saved content
                   and the Draft.

    TL-11325       Added labels to the manage learning plan templates page
    TL-11444       Added table headings when showing current forum subscribers
    TL-14271       Fixed dynamic audience performance issue for user profile custom fields
    TL-14288       Added logs relating to program and certification assignment changes
    TL-14367       The login page now allows the configured registration plugin to control the onscreen signup message
    TL-14375       Embedded reports may now define custom required columns
    TL-14383       Improved performance of reportbuilder job assignment content restraints
    TL-14385       Added checks for missing program and certification completion records

                   The program and certification completion checkers have been extended to
                   detect missing and unneeded program and certification completion records.
                   Automated fixes have been provided to allow admins to correct these
                   problems. After upgrade, you should use the completion checker to fix all
                   "Files" category problems which are reported (if any). After all problems
                   on the site have been fixed, if new problems are discovered then they
                   should be reported to Totara support.

    TL-14429       Added support for relative dates in new forms in behat tests
    TL-14430       Converted the Reportbuilder source directory cache into a defined cache
    TL-14445       Added full details link to review items in Appraisals

                   When goals, objectives or competencies are selected for review in an
                   appraisal, a link will now be available which opens the full details of
                   that item in a new window. This link will only be shown if the user has
                   permission to view those details normally outside the appraisal.

                   This feature has only been added for the aforementioned review types so
                   far.

                   When adding items for review for any review questions, these items no
                   longer have their own collapsible header and will instead be collapsible
                   under the entire review question. Non-question elements such as fixed text,
                   fixed image and profile information also no longer have a collapsible
                   header as part of this change.

                   For any custom themes that impact on Appraisals or Feedback 360, it is
                   recommended that you review the appearance of these areas following
                   upgrade.

Bug fixes:

    TL-10374       Fixed an Appraisal bug when trying to add a question without selecting a type
    TL-12672       Fixed a php notice when saving data in location and textarea unique custom fields
    TL-12769       Fixed disabling of multi-select custom fields when set to locked

                   There was an issue with multi-select custom fields when they were set to
                   locked. This would result in only the first check box being disabled or
                   none of the check boxes being disabled (this depended on the browser).

    TL-14048       Fixed a bug resulting in duplicate entries in the "Record of Learning: Courses" report source

                   Previously the "Record of Learning: Courses" report source would show
                   duplicate records if no Learning Plan columns had been added to the
                   report.
                   This has been fixed and the "Record of Learning: Courses" report source now
                   correctly eliminates duplicates.

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

    TL-14289       Improved the layout when requesting a program extension from inside of a learning plan
    TL-14291       Fixed user unassignment from programs and certifications

                   This patch includes several changes to the way program and certification
                   completion records are handled when users are unassigned. It includes
                   a fix for a problem that could occur when users are reassigned. It also
                   ensures that program and certification completion records are correctly
                   archived when a user is deleted (with the possibility of being undeleted),
                   rather than being left active.

    TL-14309       Fixed missing embedded fallback font causing error when viewing certificate
    TL-14315       Added HR Import check to ensure user's country code is two characters in length
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

    TL-14357       Fixed a problem with the self-enrolment method not allowing unauthenticated users to enrol in a course
    TL-14365       Added missing $PAGE->set_url() calls when setting up a single activity course wiki
    TL-14369       Auth plugins may now define external setting pages that do not require site config capability
    TL-14371       Added missing use of format_string() in hierarchy filter text
    TL-14381       Ensured the hierarchy filter displays any saved selections on page reload
    TL-14387       Changes to Seminar notification templates now update unchanged notifications
    TL-14389       Improved the handling of incomplete AJAX requests when navigating away from a page
    TL-14390       Fixed inconsistency in icon markup on Report Builder columns when replaced via AJAX

                   The markup of the icons for Delete, Move up and Move down were different
                   when loading the page (after clicking "Save changes") and when the icons
                   were replace via AJAX (eg. when deleting a row).

    TL-14399       Fixed the "Manage searches" button in the Audience view report
    TL-14400       Form selection elements now accept integers in current values
    TL-14401       Removed incorrect link to the user profile in Report builder for missing data
    TL-14411       Fixed reportbuilder exports for reports with embedded parameters
    TL-14414       Fixed auto-update of saved searches list in report table block editing form
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
    TL-14672       Fixed permissions check for taking attendance within Seminar events

                   Previously it was not allowed to submit Seminar attendance without
                   mod/facetoface:addattendees or mod/facetoface:removeattendees permission.
                   Now mod/facetoface:takeattendance is enough.

    TL-14686       Fixed a typo in a variable name used in organisation file type custom fields
    TL-14690       Fixed error when creating a plan where a user has multiple jobs with duplicate position competencies.

API changes:

    TL-14413       Added two new methods to the DML to fetch recordsets and a total count at the same time

                   Two new methods have been added to the DML that allow for a recordset to be
                   fetched and simultaneously a total count returned in single query.
                   The two new methods are:
                   * moodle_database::get_counted_recordset_sql
                   * moodle_database::get_counted_records_sql

Contributions:

    * Artur Rietz at Webanywhere - TL-14271
    * Barry Oosthuizen at Learning Pool - TL-14445
    * Eugene Venter at Catalyst NZ - TL-9300, TL-10502
    * Francis Devine at Catalyst NZ - TL-14430
    * Michael Trio at Kineo UK - TL-14357
    * Russell England at Kineo US - TL-14144


Release 9.7 (22nd May 2017):
============================


Important:

    TL-12803       Ensured the default run times for scheduled tasks are set correctly

                   The default run times for several scheduled tasks were incorrectly
                   configured to run every minute during the specified hour, rather than just
                   once per day. To schedule a task to run once per day at a specific time,
                   both the hour and minute must be specified. The defaults have now been
                   fixed by changing the 'minutes' from '*' to '0'. Any scheduled tasks that
                   were using the default schedule have been updated to use the new default.
                   If any of your scheduled tasks intentionally needed to use the old default
                   schedule, or are not using the default schedule, you should manually check
                   that they are configured correctly after running the upgrade.

    TL-14278       Changed mathjax content delivery network (CDN) from cdn.mathjax.org to cdnjs.cloudflare.com

                   cdn.mathjax.org is being shut down

    TL-14327       "Fileinfo" php extension is now required

                   This was previously required but not enforced by environment checks

    TL-14353       Merged Moodle 3.0.10

Security issues:

    TL-14258       Improved access control of files used in custom fields

                   Previously inconsistent checks were made when accessing files used in
                   custom fields. A brand new segment of API has been added to allow each area
                   to accurately validate access to files used within it, and all custom field
                   areas have been updated to use the new API.

    TL-14273       Fixed array key and object property name cleaning in fix_utf8() function
    TL-14331       Users are prevented from editing external blog links.
    TL-14332       Capability moodle/blog:search is checked when blog search is applied in browser url request
    TL-14333       Added sesskey checks to the course overview block

Improvements:

    TL-12732       Added accessible text to Seminar Room and Asset availability filter types
    TL-12964       Updated the standard course catalog search to allow single character searches
    TL-14242       Backported TL-12276 making learning enrolment/assignment instant for self-registered users

                   Self registered users are now added to audiences, courses, programs, and
                   certifications on confirmation.

    TL-14277       totara_core\jsend now automatically removes invalid utf-8 characters and null bytes from received data

Bug fixes:

    TL-9279        Fixed the display of images in Seminar Room and Asset textarea customfields
    TL-12415       Fixed the iCalendar cancellation email settings message for Seminars
    TL-12786       Fixed error when selecting objectives to review in an appraisal

                   When selecting Objectives to review in an appraisal, there is no longer an
                   error when there are only objectives from completed Learning Plans.
                   Objectives from both complete and incomplete Learning Plans are now shown,
                   providing the objectives are assigned to the learner and approved.

    TL-13931       Fixed JavaScript issue where activity self completion may not work
    TL-13968       Ensured that userids are unique when getting enrolled users

                   This was causing a debugging error when checking permissions of users with
                   multiple roles

    TL-14029       Fixed issues with caching requests using the same CURL connection
    TL-14046       Made the course list in user profiles take audience visibility into account
    TL-14101       Fixed Report builder saved searches for job assignment filters

                   Previously on upgrade to T9 or higher, saved searches using old position
                   assignment filters were not upgraded, they are now mapped to the
                   corresponding job assignment filter. There was also an issue creating new
                   saved searches based on some job assignment fields which has been fixed as
                   part of this patch.

    TL-14240       Fixed search tab in appraiser/manager dialog boxes for job assignments report builder filters
    TL-14241       Fixed the inline help for course and audience options on the Totara Connect add client form
    TL-14261       Fixed program completion editor not working in some circumstances
    TL-14264       Fixed RTL CSS inheritance in non-less themes

                   Prior to TL-13909, RTL wasn't being inherited correctly in themes that used
                   LESS to compile CSS (such as Roots and Basis). TL-13909 introduced a
                   regression where RTL CSS was not being inherited correctly (as used in
                   Standard Totara Responsive).

                   The theme stack now checks for a stylesheet with a suffix -rtl.css, and if
                   it exists, includes it, otherwise includes the standard stylesheet (which
                   can use the .dir-rtl body class to specify any RTL specific css)

    TL-14284       Fixed missing set_url calls within Appraisal review question AJAX scripts
    TL-14290       Fixed invalid Program due dates in Learning Plans

                   The due date would sometimes show "01/01/1970" rather than being empty. The
                   cause, and existing data, have been fixed.

    TL-14292       Fixed typo in certificate module
    TL-14305       Fixed saving user reports after filtering by position
    TL-14329       Fixed debugging warning when editing forum post
    TL-14342       Ensured Atto drag & drop content images are responsive by default

Contributions:

    * Kineo UK - TL-13931, TL-14241


Release 9.6 (26th April 2017):
==============================


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

    TL-12591       Email address validation is now inline with the WHATWG recommendation and webkit operation

                   Previously a custom regular expression was used to validate email addresses
                   in Totara.
                   This was not consistent with current recommendations or browser operation.
                   With this change we now use the regular expression recommended by WHATWG in
                   their HTML living standard specification.

                   You can find the regular expression we use at
                   https://html.spec.whatwg.org/multipage/forms.html#e-mail-state-(type=email]).
                   This is the same regular expression used by WebKit browsers to validate
                   their HTML5 email inputs.

    TL-12869       Improved the confirmation message shown when deleting a block
    TL-13882       Improved HTML of the progress bars in the last course accessed block and record of learning
    TL-14011       Lowered memory usage when running PHPUnit tests
    TL-14220       Updated Certificate Authority fallback bundle for Windows servers

Bug fixes:

    TL-12417       Fixed user enrolment into courses via competencies

                   Assigning and unassigning users from programs based on competencies now
                   correctly suspends and unsuspends users from the underlying courses

    TL-12600       Fixed HTML parsing for 'body' and 'manager prefix' fields in Seminar notification templates when the 'enable trusted content' setting is enabled
    TL-12641       Fixed a scheduling issue in HR Import where the sync was being triggered more times than required.
    TL-12684       Removed quiz report option "all users who have attempted the quiz" when separate group is selected as it does not make sense
    TL-12736       Added a sanity check for the Auth field in HR Import to ensure the specified authentication type exists
    TL-12773       Fixed a bug when setting SCORM attribute values
    TL-12802       Fixed the display of the grade percentage within the Record of Learning reports when max grade is not 100
    TL-12866       Fixed a bug whereby managers could not remove seminar allocations once learners had already self booked
    TL-12873       Fixed help string for report export settings
    TL-12891       Fixed and improved RTL languages support in Report Builder export formats
    TL-12892       Ensured HR Import manages special characters correctly when used with Menu custom user profile fields
    TL-12947       Fixed step, min and max attributes not being set in number form elements
    TL-12966       Added framework information to Hierarchy rules in dynamic audiences
    TL-12973       Fixed HTML validation in the current learning block when a user does not have any current learning
    TL-13881       Fixed Report builder side bar filters for multi-check customfields
    TL-13887       Fixed form parameters when expanding courses within the enhanced course catalog
    TL-13901       Fixed the validation of Seminar event custom fields configured to require unique values
    TL-13909       Fixed RTL CSS cascading

                   Previously if a theme used Basis or Roots as a parent theme, the RTL CSS
                   from these themes was not sent. This patch resolves that problem. If you
                   are using less compilation of CSS, and have included totara.less from these
                   themes, to avoid css duplication you may wish to exclude the totara and
                   totara-rtl css from the parent theme.

    TL-13911       Fixed incorrect availability of certification reports when programs are disabled
    TL-13915       Removed space between filters and content of Report Builder reports in IE

                   TL-12451 introduced a large visual gap between Report Builder filters and
                   the Report Builder content in IE. This fix removes that gap.

    TL-13924       Fixed warnings when viewing Appraisal previews
    TL-13953       Fixed a typo in the Seminar activity 'userwillbewaitlisted' string
    TL-14064       Fixed the Record of Learning: Competencies report when Global Report Restrictions are enabled
    TL-14145       Fixed a bug occuring when trying to move Course sections multiple times without refreshing

Contributions:

    * Richard Eastbury at Think Associates - TL-13911


Release 9.5 (22nd March 2017):
==============================


Security issues:

    TL-2986        Added checks for the moodle/cohort:view capability to the audience filter in user, course, and program report sources
    TL-12452       Added validation to the background colour setting for TeX notation
    TL-12733       Email self-registration now validates recaptcha first and hides error messages relating to username and email if they exist
    TL-12907       Fixed user preference handling to prevent malicious serialised object attacks

Improvements:

    TL-11292       Added accessible text to the "Number of Attendees (linked to attendee page)" column when viewing Seminar events embedded report
    TL-11311       Added labels to the default messages page to improve accessibility
    TL-11320       Added accessible text when exporting a hierarchy
    TL-12366       Improved the usability of the program assignments interface

                   There were some totals in the program assignments interface which could be
                   misleading given that they may not take into account whether the program is
                   active or not and may count users multiple times if they are in multiple
                   assigned groups. The number of assigned learners is now only shown while a
                   program is active (within available from and until dates, if they are
                   enabled).

    TL-12396       Upgraded jQuery to 2.2.4 and jQuery Migrate to 1.2.1
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

    TL-12665       Added a page title when completing a learning plan
    TL-12689       Removed 'View' button for appraisal stages without any visible pages
    TL-12745       Added unit tests to report builder date filter.

Bug fixes:

    TL-11255       Fixed incorrect indication that manager must complete an appraisal after completion
    TL-12451       Fixed the display of graphs within Report Builder when using the sidebar filter
    TL-12454       Corrected handling when organisation parentid equals 0

                   Before the fix, if a parentidnumber of 0 was used when importing
                   organisation data using HR Import it would be ignored and treated as an
                   empty value. Consequently, if you had an organisation structure where
                   idnumber for the top most level was 0, when the second level of
                   organisations are imported, they would be added at the top level (because
                   HR import consider them to have no parent). This has now been fixed.

    TL-12615       Stopped managers receiving Program emails for their suspended staff members
    TL-12621       Fixed navigation for multilevel SCORM packages
    TL-12643       Fixed guest access throwing error when using the enhanced catalog
    TL-12645       Fixed cache warnings on Windows systems occurring due to fopen and rename system command
    TL-12669       Ensured Evidence Custom Fields unique and locked setting worked correctly
    TL-12681       Replaced duplicate 'Draft' Plan Status filter option with 'Pending Approval' in learning plan report source.
    TL-12696       Ensured that read only evidence displays the "Edit details" button only when the user has the correct edit capability
    TL-12721       Fixed misspelt URL when adding visible learning to an audience
    TL-12734       Fixed how room conflicts are handled when restoring a Seminar activity
    TL-12739       Improved performance when using the progress column within a Certification overview report
    TL-12747       Ensured User Profile fields set as unique do not include empty values when determining uniqueness
    TL-12762       Prevented appraisal messages from being sent to unassigned users
    TL-12774       Added validation to prevent invalid Assignment grade setting combination

                   You must now select a Grade Type or the default Feedback Type if you want
                   to enable the 'Student must receive a grade to complete this activity'
                   completion setting.

    TL-12778       Fixed the display of the "add another option" link in Appraisals and 360 Feedback multi-choice questions

                   Previously the "add another option" link would correctly be removed when
                   you added the tenth option to a multi-choice question, but would be
                   displayed again when you edited the question. Clicking the link when you
                   already had the maximum amount of options would make the link disappear
                   again without doing anything, now the link will not be displayed at all.

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

    TL-12793       Fixed a bug when trying to remove a regular expression validation from a text custom field
    TL-12795       Fixed 'Program Name and Linked Icon' report column when exporting

                   The "Program Name and Linked Icon" report column, contained in several
                   report sources, now only contains the program name when exporting. Also,
                   the "Record of Learning: Certifications" report source had two columns
                   named "Certification name". One of them has now been renamed to
                   "Certification Name and Linked Icon", and likewise only contains the
                   certification name when exporting.

    TL-12798       Fixed the display of description for personal goals on the My Goals page
    TL-12801       Fixed exporting course completion reports to excel after filtering by organisation

Contributions:

    * Russell England - TL-12669


Release 9.4 (27th February 2017):
=================================


Security issues:

    TL-6810        Added sesskey checks to the programs complete course code

Improvements:

    TL-11291       Replaced the input button with text when editing a users messaging preferences
    TL-11317       Added labels to the add rule dropdown when editing the rules of a dynamic audience
    TL-11318       Added accessibility labels to Hierarchy framework searches and bulk actions
    TL-12314       Improved HTML validation when searching within a Hierarchy framework
    TL-12594       Added default html clean up to the static_html form element

                   Developers need to use
                   \totara_form\form\element\static_html::set_allow_xss(true) if they want to
                   include JavaScript code in static HTML forms element.

Bug fixes:

    TL-8375        Fixed issues with audiences in the table for restricting access to a menu item

                   Added the correct module to the url when rendering the table rows through
                   ajax. Also, when the form is saved, if "Restrict access by audience" is not
                   checked then it will remove all audience restrictions from the database so
                   they will not be incorrectly loaded later.

    TL-9264        Fixed a fatal error encountered in the Audience dialog for Program assignments
    TL-10082       Fixed the display of description images in the 360° Feedback request selection list
    TL-10871       Fixed duplicated error message displayed when creating Seminar sessions with multiple dates
    TL-11062       Seminar events that are in progress are now shown under the upcoming sessions tab

                   Previously events that were in progress were being shown under the previous
                   events tab. This lead to them being easily lost, and after a UX review it
                   was decided that this was indeed the wrong place to put them and they were
                   moved back to the upcoming events until the event has been completed.

                   In the course view page, if "sign-up for multiple events" is disabled, then
                   users who are signed-up will see only the event where they are signed-up to
                   as they won't be able to sign-up for another event within that Seminar. If
                   "sign-up for multiple events" is enabled, then the signed-up users will see
                   all upcoming events ("in progress" and "upcoming" ones).

    TL-11106       Fixed row duplication of Seminar events within the Seminar events report source
    TL-11186       Changed user completion icons into font icons
    TL-11230       Fixed disabled program course enrolments being re-enabled on cron

                   The clean_enrolment_plugins_task scheduled task now suspends and re-enables
                   user enrolments properly

    TL-12252       Disabled selection dialogs for Hierarchy report filters when the filter is set to "is any value"
    TL-12286       Corrected the table class used in Course administration > Competencies
    TL-12298       Fixed RTL CSS flipping in Appraisals

                   Previously there were a number of anomalies when viewing appraisals in
                   right to left languages such as Hebrew. This fixes the CSS so that they are
                   now displayed correctly.

    TL-12341       Removed unnecessary code to prevent page jump on click of action menu

                   Removed a forced jQuery repaint of the action menu which was originally
                   required to work around a Chrome display bug, but which is no longer
                   required.

    TL-12342       Moved the block hide icon to the right in Roots and Basis themes
    TL-12443       Fixed RTL CSS flipping in 360° Feedback

                   Previously there were a number of anomalies when viewing 360° feedback in
                   right to left languages such as Hebrew. This issue alters CSS so that they
                   are now displayed correctly.

    TL-12445       Fixed completion recording for some SCORMs with deep navigation structure (3+ levels)
    TL-12455       Backport TL-11198 - Added support for add-on report builder sources in column tests

                   Add-on developers may now add phpunit_column_test_add_data() and
                   phpunit_column_test_expected_count() methods to their report sources to
                   pass the full phpunit test suit with add-ons installed.

    TL-12458       Fixed the visibility permissions for images in the event details field
    TL-12463       Prevented the submission of text longer than 255 characters on Appraisal and 360° Feedback short text questions
    TL-12464       Fixed a HTML validation issue on the user/preferences.php page
    TL-12465       Fixed the display of multi-lang custom field names on the edit program and certification forms
    TL-12585       Fixed a fatal error when trying to configure the Stats block without having staff
    TL-12593       Fixed double escaping in the select and multiselect forms elements
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

    TL-12649       Fixed the rendering of Totara form errors when get_data() is not called
    TL-12656       Remove incorrect quotations from mustache template strings

                   Quotations around template strings have been removed to avoid prevention of
                   key usage in string arrays.

    TL-12680       Made the user menu hide languages when the "Display language menu" setting is disabled

API changes:

    TL-10990       Ensured JS Flex Icon options are equivalent to PHP API

                   The core/templates function renderIcon may alternatively be called with two
                   parameters, the second being a custom data object.

Contributions:

    * Eugene Venter, Catalyst - TL-12596


Release 9.3 (25th January 2017):
================================


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

    TL-9016        Added content restrictions to the Goal custom fields report source

                   Content restrictions for restricting records by management, organisation
                   and position have been added to the Goal custom fields report source.

    TL-9756        Removed an HTML table when viewing a Learning plan that has been changed after being approved
    TL-10849       Improved the language strings used to describe Program and Certification exception types and actions
    TL-11074       Added additional text to the manager and approver copies of original Seminar notifications
    TL-12261       Improved code exception validation in several unit tests

Bug fixes:

    TL-10416       Fixed an error when answering appraisal competency questions as the manager's manager or appraiser
    TL-10945       Prevented loops in management job assignments in HR Import

                   Previously, if a circular management assignment was imported, HR Import
                   would fail without sensible warning. Now, if a circular management is found
                   when importing a manager with HR Import, then one or more of the users
                   forming the circular reference will fail to have their manager assigned,
                   with a notice explaining why. When importing, as many manager assignments
                   as possible will be assigned.

    TL-11150       Fixed an undefined property error in HR Import on the CSV configuration page
    TL-11238       Fixed the Seminar name link column within the Seminar sessions report
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

    TL-11316       Fixed an error when cloning an Appraisal containing aggregated questions
    TL-12243       Fixed a Totara menu issue leading to incorrectly encoded ampersands
    TL-12256       Prevented an incorrect redirect occurring when dismissing a notification from within a modal dialog
    TL-12263       Fixed an issue with the display of assigned users within 360° Feedback

                   The assigned group information is no longer shown for 360° Feedback in the
                   Active or Closed state. In these states, the pages always reflect actual
                   assigned users.

    TL-12277       Corrected an issue where redirects with a message did not have a page URL set
    TL-12280       Fixed a bug preventing block weights being cloned when a dashboard is cloned
    TL-12283       Fixed several issues on the waitlist page when Seminar approval type is changed

                   The waitlist page showed the wrong approval date (1 Jan 1970) and debug
                   messages when a seminar changed its approval type from no approval required
                   to manager approved.

    TL-12284       Fixed an upgrade error due to an incorrectly unique index in the completion import tables on SQL Server

                   Previously, if a site running SQL Server had imported course or
                   certification completions, there could have been an error when trying to
                   upgrade to Totara 9. This has been fixed. Sites that had already
                   successfully upgraded will have the unique index replaced with a non-unique
                   equivalent.

    TL-12287       Ensured Hierarchy 'ID number' field type is set as string in Excel and ODS format exports to avoid incorrect automatic type detection
    TL-12297       Removed options from the Reportbuilder "message type" filter when the corresponding feature is disabled
    TL-12299       Fixed an error on the search page when setting Program assignment relative due dates
    TL-12301       Fixed the replacement of course links from placeholders in notifications when restoring a Seminar

                   Previously when a course URL was embedded in a seminar notification
                   template, it would be changed to a placeholder string when the seminar was
                   backed up. Restoring the seminar would not change the placeholder back to
                   the proper URL. This fix ensures it does.

    TL-12303       Fixed the HTML formatting of Seminar notification templates for third-party emails
    TL-12305       Fixed incorrect wording in Learning Plan help text
    TL-12311       Fixed the "is after" criteria in the "Start date" filter within the Course report source

                   The "is after" start date filter criteria now correctly searching for
                   courses starting immediately after midnight in the users timezone.

    TL-12315       Waitlist notifications are now sent when one message per date is enabled

                   If a Seminar event was created with no dates, people could still sign up
                   and be waitlisted.
                   However, they would only receive a sign up email if the "one message per
                   date" option was off.
                   Now, the system will send the notification regardless of this setting.

    TL-12323       Removed references to the SCORM course format from course format help string
    TL-12325       Fixed the Quick Links block to ensure it decodes URL entities correctly
    TL-12333       Made improvements to the handling of invalid job assignment dates
    TL-12337       Fixed the formatting of event details placeholder in Seminar notifications
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

    TL-12369       Marked class totara_dialog_content_manager as deprecated

                   This class is no longer in use now that Totara has multiple job
                   assignments. Class totara_job_dialog_assign_manager should be used instead.

Miscellaneous Moodle fixes:

    TL-12406       MDL-57100: Prevented javascript exceptions from being displayed during an AJAX request
    TL-12407       MDL-56948: Fixed Assignment bug when viewing a submission with a grade type of "none"
    TL-12409       MDL-57170: Fixed fault in legacy Dropbox API usage
    TL-12410       MDL-57193: Fixed external database authentication where more than 10000 users are imported

Contributions:

    * David Shaw at Kineo UK - TL-12243


Release 9.2.2 (23rd December 2016):
===================================


Bug fixes:

    TL-12312       Fixed HR Import User Link to job assignment invalid settings

                   In HR Import, after setting "Link job assignment" to "using the user's job
                   assignment ID number", and then successfully performing an import, the
                   setting was supposed to become locked. This is to prevent problems which
                   could occur, where imported data is written into the wrong job assignment
                   records.

                   Due to a bug, it was possible that the setting would change to link "to the
                   user's first job assignment" and remain locked on this setting.

                   This patch ensures that, after doing an import with the setting set to link
                   to "using the user's job assignment ID number", it will always link this way in
                   future. The cause of the bug has been fixed, extra checks have been
                   implemented to ensure that imports will be prevented if the settings are in
                   an invalid state, and invalid settings were fixed on sites affected by this
                   problem.

    TL-12316       Fixed missing include in Hierarchy unit tests covering the moving of custom fields


Release 9.2.1 (22nd December 2016):
===================================


Bug fixes:

    TL-12309       Fixed the display of aggregated questions within Appraisals

                   This was a regression from TL-11000, included in the 2.9.14 and 9.2
                   releases.
                   The code in that fix used functionality first introduced in PHP 5.6, and
                   which is not compatible with PHP5.5.
                   The effect of the resulting bug was purely visual.
                   We've now re-fixed this code in order to ensure it is compatible with all
                   supported versions of PHP.


Release 9.2 (21st December 2016):
=================================


Important:

    TL-11333       Fixes from Moodle 3.0.7 have been included in this release

                   Information on the issues included from this Moodle release can be found
                   further on in this changelog.

    TL-11369       Date related form elements exportValue() methods were fixed to return non array data by default

                   All custom code using MoodleQuickForm_date_time_selector::exportValue() or
                   \MoodleQuickForm_date_selector::exportValue() must be reviewed and fixed if
                   necessary.

Security issues:

    TL-5254        Improved user verification within the Quick Links block
    TL-11133       Fixed Seminar activities allowing sign up even when restricted access conditions are not met
    TL-11194       Fixed get_users_by_capability() when prohibit permissions used
    TL-11335       MDL-56065: Fixed the update_users web service function
    TL-11336       MDL-53744: Fixed question file access checks
    TL-11338       MDL-56268: Format backtrace to avoid displaying private data within web services

Improvements:

    TL-7221        Added time selectors to Before and After date criteria in dynamic audience rules
    TL-10952       Links that should be styled as buttons now look like buttons in Basis & Roots themes
    TL-10971       Improved Feedback activity export formatting

                   The following improvements were made to the exported responses for feedback
                   activities:
                   * Newlines in Long Text responses are no longer replaced with the html
                   <br/> tag
                   * The text wrap attribute is set for all response cells
                   * Long text, Short text and Information responses are no longer exported in
                   bold

    TL-11054       Only the available regions are shown when configuring a block's position on the current page

                   Previously, when configuring blocks, all possible regions were shown when
                   setting the region for a block on the current page. This setting now only
                   has the options that exist on the page

    TL-11056       Added phpunit support for third party modules that use "coursecreator" role
    TL-11075       Improved inline help for Seminar's "Manager and Administrative approval" option
    TL-11117       Removed unused, redundant, legacy hierarchy code
    TL-11145       Newly created learning plans now include competencies from all of a user's job assignments
    TL-11261       Converted folder and arrow icon in file form control to flex icons
    TL-11273       Removed an unnecessary fieldset surrounding admin options
    TL-11289       Dropping a file onto the course while editing now has alternative text

                   This also converts the image icon to a flex icon.

Bug fixes:

    TL-4912        Fixed the missing archive completion option in course administration menu
    TL-7666        Images used in hierarchy custom fields are now displayed correctly when viewing or reporting on the hierarchy
    TL-9500        Fixed "View full report" link for embedded reports in the Report table block
    TL-9988        Fixed moving hierarchy custom fields when multiple frameworks and custom fields exist
    TL-10054       Ensured that the display of file custom fields in hierarchies link to the file to download
    TL-10101       Removed unnecessary permission checks when accessing hierarchies
    TL-10744       Fixed footer navigation column stacking in the Roots and Basis themes
    TL-10915       Ensured that courses are displayed correctly within the Current Learning block when added via a Certification
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
    TL-11072       Administrative approver can do final approval of seminar bookings in two stage approvals prior to manager
    TL-11076       Fixed the display of the attendee name for Seminar approval requests in the Task/Alert report
    TL-11110       Added validation warning when creating management loops in job assignments

                   Previously, if you tried to assign a manger which would result in a
                   circular management structure, it would fail and show an error message. Now
                   it shows a validation warning explaining the problem.

    TL-11124       Treeview controls in dialogs now display correctly in RTL languages
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
    TL-11191       Ensured the calendar block controls are displayed correctly in RTL languages
    TL-11200       Fixed the program enrolment plugin which was not working for certifications when programs had been disabled
    TL-11203       Allowed access to courses via completed programs consistently

                   Previously if a user was complete with a due date they could not access any
                   courses added to the program after completion, but users without a due date
                   could access the new courses. Now any user with a valid program assignment
                   can access the courses regardless of their completion state.

    TL-11208       Fixed unnecessary comma appearing after user's name in Seminar attendee picker

                   When only "ID Number" is selected in the showuseridentity setting and a
                   user does not have an ID number an extra comma was displayed after the
                   user's name in the user picker when adding / removing Seminar attendees.

    TL-11209       Fixed errors in some reports when using report caching and audience visibility
    TL-11213       Fixed undefined index warnings while updating a Seminar event without dates
    TL-11216       Fixed incorrect use of userid when logging a program view from required learning
    TL-11217       Flex icons now use the title attribute correctly
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
    TL-11246       Added default sort order of attendees on the Seminar sign-in sheet

                   The sort order was the order in which the attendees was added. This patch
                   adds a default sort order to the embedded report so that users are listed
                   in alphabetical order. Note: for existing sites the sign-in sheet embedded
                   report will need to be reset on the manage reports page (doing this will
                   reset any customisations to this report)

    TL-11263       Loosened cleaning on Program and Certification summary field making it consistent with course summary
    TL-11272       Fixed inaccessible files when viewing locked appraisal questions
    TL-11309       HR Import now converts mixed case usernames to lower case

                   Now when you import a username with mixed case you will receive a warning,
                   the username will be converted to lower case and the user will be
                   imported.
                   This patch brings the behaviour in Totara 9 in line with Totara 2.9.

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
    TL-12248       Fixed layout of Totara forms when using RTL languages

API changes:

    TL-8423        Changed course completion to only trigger processing of related programs

                   Previously, course completion caused completion of all of a user's programs
                   and certifications to be re-processed. Now, only programs which contain
                   that course are processed.

    TL-11225       \totara_form\model::get_current_data(null) now returns all current form data

Miscellaneous Moodle fixes:

    TL-11337       MDL-51347: View notes capability is now checked using the course context
    TL-11339       MDL-55777: We now check libcurl version during installation
    TL-11342       MDL-55632: Tidy up forum post messages
    TL-11343       MDL-55820: Use correct displayattempt default options in SCORM settings
    TL-11344       MDL-55610: Improved cache clearing
    TL-11345       MDL-42041: Added "Turn Editing On" to page body to Book module
    TL-11346       MDL-55874: Fixed html markup in participation report
    TL-11347       MDL-55862: The database module now uses the correct name function for display
    TL-11348       MDL-55505: Fixed editing of previous attempt in Assignment module
    TL-11349       MDL-53893: Fixed awarding of badges with the same criteria
    TL-11351       MDL-55654: Added multilang support for custom profile field names and categories
    TL-11352       MDL-55626: Added desktop-first-column to legacy themes
    TL-11353       MDL-29332: Fixed unique index issue in calculated questions when using MySQL with case insensitive collation
    TL-11358       MDL-55957: Fixed the embedded files serving in Workshop module
    TL-11359       MDL-55987: Prevent some memory related problems when updating final grades in gradebook
    TL-11360       MDL-55988: Prevent autocomplete elements triggering warning on form submission
    TL-11361       MDL-55602: Added redis session handler with locking support
    TL-11362       MDL-56019: Fixed text formatting issue in web services
    TL-11363       MDL-55776: Fixed group related performance regression
    TL-11364       MDL-55876: Invalid low level front page course updates are now prevented
    TL-11368       MDL-55911: Improved Quiz module accessibility
    TL-11371       MDL-56069: Fixed scrolling to questions in Quiz module
    TL-11372       MDL-56136: Improved error handling of file operations during restore
    TL-11373       MDL-56181: Updated short country names
    TL-11374       MDL-56127: Fixed a regression in form element dependencies
    TL-11376       MDL-55861: Fixed displaying of activity names during drag & drop operations
    TL-11379       MDL-52317: Fixed visual issues when inserting oversized images
    TL-11384       MDL-55597: Fixed support for templates in subdirectories
    TL-11385       MDL-51633: Restyled ADD BLOCK to remove max-width in legacy themes
    TL-11386       MDL-51584: Improved performance when re-grading
    TL-11387       MDL-56319: Fixed the handling of default blocks when an empty string is used to specify there should be no default blocks
    TL-11388       MDL-52051: Correct code that relies on the expires_in optional setting within OAuth
    TL-11389       MDL-56050: Fixed missing context warning on the maintenance page
    TL-11390       MDL-36611: Fixed missing context warning when editing outcomes
    TL-11392       MDL-51401: Improved the ordering of roles on the enrolled users screen
    TL-11393       MDL-55345: Fixed links to IP lookup in user profiles
    TL-11394       MDL-56062: Standardised display of grade decimals in Assignment module
    TL-11395       MDL-56345: Fixed alt text for PDF editing in Assignment module
    TL-11396       MDL-56439: Added missing include in course format code
    TL-11397       MDL-56328: Improved activity indentation on the course page in legacy themes
    TL-11398       MDL-56368: Fixed Restrict Access layout issue in legacy themes
    TL-11399       MDL-43796: Fixed Reveal identities issue during restore
    TL-11400       MDL-56131: Added checks to prevent the Choice module becoming locked for a long periods of time
    TL-11401       MDL-55143: Fixed detection of version bumps in phpunit
    TL-11402       MDL-29774: Group membership summaries are now updated on AJAX calls
    TL-11403       MDL-55456: Fixed context warning when assigning roles
    TL-11404       MDL-56275: Removed repository options when adding external blog
    TL-11405       MDL-55858: Removed subscription links when not relevant in Forum module
    TL-11406       MDL-56250: mforms now support multiple validation calls
    TL-11407       MDL-53098: Fixed form validation issue when displaying confirmation
    TL-11408       MDL-56341: Fixed Quote and Str helpers collisions in JS Mustache rendering
    TL-11411       MDL-48350: Fixed action icons placement in docked blocks in legacy themes
    TL-11412       MDL-56347: Added diagnostic output for alt cache store problems in phpunit
    TL-11414       MDL-56354: All debugging calls now fail phpunit execution
    TL-11415       MDL-54112: Fixed Required grading filtering
    TL-11416       MDL-56615: Fixed PHP 7.0.9 warning in Portfolio
    TL-11417       MDL-56673: Fixed minor problems in template library tool
    TL-11418       MDL-47500: Improved SCORM height calculation

                   Please note that Totara already contained a similar patch. This change
                   added minor changes from upstream only.

    TL-11419       MDL-55249: Fixed status in feedback activity reports
    TL-11420       MDL-55883: Fixed calendar events for Lesson module
    TL-11421       MDL-56634: Improved rendering of WS api descriptions
    TL-11423       MDL-54986: Disabled add button for quizzes with existing attempts
    TL-11426       MDL-56748: Fixed a memory leak when resetting MUC
    TL-11427       MDL-56731: Fixed breadcrumb when returning to groups/index.php
    TL-11428       MDL-56765: User preferences are reloaded in new WS sessions
    TL-11429       MDL-53718: Do not show course badges when disabled
    TL-11430       MDL-54916: Improved the performance of empty ZIP file creation
    TL-11431       MDL-56120: Calendar events belonging to disabled modules are now hidden
    TL-11432       MDL-56755: Improved documentation of assign::get_grade_item()
    TL-11433       MDL-56133: Caches are now purged after automatic language pack updates
    TL-11434       MDL-53481: Fixed sql errors within availability restrictions
    TL-11435       MDL-56753: Fixed separate group mode errors
    TL-11436       MDL-56417: Fixed ignore_timeout_hook logic in auth subsystem
    TL-11437       MDL-56623: Added a new lang string for 'addressedto'
    TL-11438       MDL-55994: Fixed warning in RSS feed generation
    TL-11439       MDL-52216: Prevented invalid view modes in Lesson module

Contributions:

    * Russell England at Kineo USA - TL-11239


Release 9.1 (22nd November 2016):
=================================


Important:

    TL-10252       Non-date picker uses of date picker strings changed to langconfig strings

                   Code unrelated to date pickers has been updated to use strings from the
                   langconfig language pack. Date picker strings should only be used in
                   relation to date pickers. Code now using the langconfig strings will
                   benefit from customisations made to those strings.

                   Additionally, the lang string customfieldtextdateformat was added in
                   totara_customfield. If you have customised the lang string
                   datepickerlongyearregexphp then after upgrading you should change
                   customfieldtextdateformat to your custom regular expression.

    TL-11112       The default encoding is now consistently set to UTF-8

                   Totara now sets UTF-8 as default encoding for PHP scripts to prevent hard
                   to detect problems on sites with non-standard php.ini settings. There are
                   no known problems in Totara, but this change may help with compatibility in
                   external libraries and 3rd party plugins.

    TL-11114       Incompatible plugin updates and installer code was removed

                   Totara LMS does not include an add-on installer, all additional plugins
                   must be installed manually by server administrators.

                   Before installing any additional plugins please make sure the code was
                   tested with Totara LMS, is secure, is maintained by authors and contains
                   phpunit and behat tests.

                   Totara Learning Solutions support does not cover plugins that are not
                   included in the standard distribution.

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

    TL-8849        Improved validation when managing Seminar custom fields

                   Previously it was possible to view custom fields from areas outside of
                   Seminars through the Seminar custom field management page.
                   This page now properly verifies that the custom fields being requested
                   belong to a Seminar area.

    TL-10752       Implemented additional checks within the Appraisal review ajax script

Improvements:

    TL-9325        Moved the add event link within Seminar above the upcoming events display
    TL-10038       Added a warning entry into the HR Import import log if data contains a user that has their "HR import" setting disabled
    TL-10097       Removed whitespace when editing individual feedback 360 requests
    TL-10203       Improved efficiency when importing users that include dropdown menu profile field data

                   A significant performance gain has been made when importing users through
                   HR Import on sites that use drop down menu profile custom fields.
                   The import process should now run much faster than before.

    TL-10292       Added a legend when exporting and importing questions by category or context from within the question bank
    TL-10627       Improved appraisal snapshot PDF rendering
    TL-10654       Improved display of username when viewing as another role
    TL-10681       Added an environment test for mbstring.func_overload to ensure it is not set

                   Multibyte function overloading is not compatible with Totara.

    TL-10705       Improved the help text within Seminar when uploading attendees by CSV file
    TL-10731       Added setting to allow limiting of feedback reminders sent out

                   A new setting has been added, 'reminder_maxtimesincecompletion', which can
                   be used to limit the number of days after course completion in which
                   feedback activity reminders will be sent. This may be used to prevent
                   reminders being sent for historic course completions after they are
                   imported via upload.

    TL-10782       Seminar direct enrolment instances within a course can now be manually removed when no longer wanted
    TL-10793       Improved support of RTL languages within Report builder reports in the new themes
    TL-10909       Improved wording of course activity reports visibility setting help
    TL-10917       Improved the performance of admin settings for PDF fonts
    TL-10947       Removed duplicated link in the My team block
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

    TL-11026       Improved move left and move right functionality when editing a course
    TL-11041       Site level administrative approvers setting in Seminars has been relocated to Seminars > Global settings
    TL-11045       Seminar upcoming and previous headings are now the correct level
    TL-11051       The Seminar event "Add approver" button is now disabled when it is not relevant
    TL-11052       Changed text when removing users from a seminar event

Bug fixes:

    TL-7752        Fixed problems with program enrolment messages

                   Program enrolment and unenrolment messages are now resent each time a user
                   is assigned or unassigned, rather than just the first time either of those
                   events occur.
                   All program messages are now covered by automated tests.

    TL-9301        Fixed Seminar event functionality when the cancellationnote default custom field has been deleted
    TL-9846        Removed reference to deprecated variable when in a chat activity
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

    TL-10244       Removed unnecessary italic format from the my team block
    TL-10273       Removed unnecessary fieldset around forum search
    TL-10311       Controls in the element library now link to the same page
    TL-10320       Corrected the accessibility link between the Seminar event export label and it's select input
    TL-10331       Ensured URL custom fields are cleaned using PARAM_URL when uploaded via HR Import
    TL-10332       Added default behaviour of do not open in new window for URL custom fields when added or updated via HR Import
    TL-10360       Competency completion calculations now correctly look at previously completed courses

                   Courses completed before the last time a competency is modified are now
                   correctly considered for competency assignment

    TL-10687       Dock action icons now use the same colour as block actions in basis
    TL-10766       Fixed colour of legends and help icons in Kiwifruit responsive
    TL-10787       Fixed a php notice generated when a competency is added to a learning plan with optional courses
    TL-10819       Added code to re-run an upgrade step to delete report data for deleted users

                   The issue was caused by TL-8711 and fixed by TL-10804

    TL-10837       Added workaround for iOS 10 bug causing problems with video playback
    TL-10853       Ensured consistent spacing around the login info within the Basis theme footer
    TL-10891       Fixed overactive validation of Seminar cutoff against dates

                   Previously when editing a Seminar event in which the current date was
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

    TL-10905       Stopped a duplicate error message from being displayed on the login screen when the session has expired
    TL-10910       Fixed required permissions for appraisals aggregate questions
    TL-10916       Fixed a debug error within the Current Learning block when images are added to the summary of a program or certification
    TL-10946       Removed false deprecation message for the viewmyteam string
    TL-10955       Fixed database error when generating a report with search columns
    TL-10956       Fixed  the display of the marking guide editing interface

                   Missing selectors from Totara's new themes have been added to now catch
                   each type of advanced grading form; marking guide & Rubric.

                   As themes continue to prefer CSS applied without the use of the 'style'
                   attribute, the maximum grade form input has also had its explicit width
                   removed.

                   The Javascript calculation of textarea widths inside the form have also
                   been simplified, with height now being the only value calculated & set.

    TL-10963       Added tabs to the seminar events and session report pages and ensured bookmarking of both pages can be achieved
    TL-10972       Deleting a Seminar now correctly removes orphaned notification records
    TL-10979       Ensured certification messages can be resent on subsequent recertifications

                   This patch ensures that all applicable certification messages are reset
                   when a user's recertification window opens, allowing them to be triggered
                   again for that user.

    TL-10998       Removed inaccessible options in Program Administration block
    TL-11009       Fixed the display of learning plan courses within the Current Learning block after being enrolled in a course
    TL-11010       Fixed emails being sent to declined users when an event is closed
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
    TL-11060       Fixed a php notice generated within HR Sync when using the organisation or position elements
    TL-11087       Ensured that IE9 chunked stylesheet paths are correctly generated
    TL-11102       Fixed a timing issue in totara_core_webservice PHPUnit tests
    TL-11138       Provided an IE9 compatible fallback for the loading icon

API changes:

    TL-9726        Added the system requirements for upgrades to Totara 10dev

Contributions:

    * Davo Smith at Synergy Learning - TL-10917
    * Jo Jones at Kineo - TL-11157


Release 9.0 (19th October 2016):
================================


New features:

    TL-6874        Introduction of Font Icons

                   Font Icons have been made available through a new "flex icon" API.
                   Additionally the majority of icons used within Totara have been converted to use font icons.

                   Font icons bring exciting new possibilities when styling an icon.
                   With them you can use any standard CSS font styling to customise the icon, including changing its colour and size.
                   Additionally the font icons are fully scalable, and are not designed at a specific size.
                   Totara's font icon implementation is based upon the Font Awesome toolkit.

                   Information on using font icons, as well as how to work with them can be found in the developer documentation.

    TL-9031        Introduction of Roots, a new base theme for Totara

                   A new base theme has been created for Totara.
                   Called Roots it utilises Bootstrap 3 to produce a clean new look.

                   We strongly recommend that all new themes utilise the roots theme as a parent.

                   This theme is based on the Bootstrap theme (https://github.com/bmbrands/theme_bootstrap) produced and maintained by Bas
                   Brands, David Scotson, and other contributors.

                   For more information on the Totara 9 approach to theme management please refer to our developer documentation
                   https://help.totaralearning.com/display/DEV/Totara+LMS+9+approach+to+theme+management

    TL-7468        Introduction of Basis, the new default theme for Totara

                   Basis is a new theme added to Totara, and now set as the default theme for all new installations.

                   Designed in house by our UX experts it brings a brand new look to Totara out of the box.

                   For more information on the Totara 9 approach to theme management please refer to our developer documentation
                   https://help.totaralearning.com/display/DEV/Totara+LMS+9+approach+to+theme+management

    TL-9493        Repurpose the base theme

                   The base theme in Totara is no longer a stand alone theme.
                   It now contains only essential styles for the Font Icons.

                   It is strongly recommended that all themes use the base theme as a parent.
                   This will ensure that the new font icons will work out of the box for your theme.

                   Making your theme use the base theme is as simple adding "base" to the end of the parents array in your theme config.
                   For instance you should have something like the following:

                   $THEME->parents = array('roots', 'base');

    TL-7494        New Totara forms library

                   There is a new modern forms library available in Totara that can be used as an alternative to the current forms library.
                   The new library provides additional functionality not available in the Quick Forms based mform library.

                   This new forms library will be our library of choice for all future work.
                   It is designed to overcome the limitations of the current forms library and lend itself more towards use within the
                   current technologies embraced within Totara, including fully template driven, deliverable and translatable via
                   JavaScript and AJAX, easy introduction of new custom elements, full testable, more intuitive PHP API, and modular
                   extendable JavaScript API.

                   See the developer documentation for more details https://help.totaralearning.com/display/DEV/Totara+forms+library

    TL-8208        Added support for hooks

                   Hooks were designed to improve interactions of plugins and simplify customisation. This release contains the hook
                   infrastructure and conversion of one sample area - the course editing form. We are planning to add hook support to more
                   areas in the future and welcome code contributions inserting new hooks where required.

                   See the developer documentation for more information
                   https://help.totaralearning.com/display/DEV/Hooks+developer+documentation

    TL-8513        Added Current Learning block

                   The Current learning block is a new block which displays all learning that is active for the viewing user. This includes
                   courses, programs and certifications in a unified view, but excludes completed learning.

                   A new user_learning API has been introduced to provide a standard way to access learning data, currently four components
                   are supported:

                   *  Course
                   *  Program
                   *  Certification
                   *  Learning Plan

                   Other components and plugins can extend the user_learning classes if they wish to integrate with the current learning
                   block. Look in code for examples.

    TL-8514        Added Last Course Accessed block

                   The Last Course accessed block can be added to any page that supports blocks. It provides quick access to the course the
                   user last accessed.

    TL-7456        Added a new Location custom field

                   A new Location custom field has been added to Totara.
                   This can now be used anywhere custom fields can be used.
                   It allows a address to be entered and optionally displays a map with a pinned location that can be either the same as
                   the address or a secondary (dropped) location.

                   Please note that this location field uses the Google Map API in order to present a visual interactive map.
                   In order to use the visual map you will need to generate and provide Totara with a Google Maps API key.

    TL-7466        Added an optional regular expression validation for the "Text Input" custom field type.

                   Text Input custom fields can now be restricted to match particular format using PHP PCRE regular expressions.

    TL-7983        Moodle 3.0

                   This release of Totara contains new features and improvements introduced upstream in Moodle 3.0.0
                   Additionally fixes made in Moodle 3.0.0 through to and including 3.0.6 are included in this release.
                   For details please refer to the Moodle release notes.



New features - Seminar:

    TL-7461        The Face-to-face activity has been renamed to Seminar

                   All strings used in the interface that contained 'Face-to-face' have been updated.

                   Previously, a 'Face-to-face' activity contained a number of 'Sessions', which each contained some amount of 'Session
                   dates'.
                   'Sessions' have been renamed as 'Events' and 'Session dates' are now called 'Sessions'.
                   Strings containing these terms have been updated.

                   Face-to-face report sources and embedded reports have also had their names updated to reflect these changes, this is
                   only a display change and should not effect any existing reports.

                   There will be some language strings which still contain the old terms. These should not be used anywhere on the
                   interface. They are used to identify old notification messages and placeholders in Seminar notifications.

    TL-7453        New interface for bulk add/remove of users in Seminar events

                   Users in Seminar events are now added or removed in two steps: preparing a list of users and confirmation.

                   Custom fields have been added to the Sign-ups report source. Also, sign-up custom fields can be populated in a CSV file
                   upload for each user that will be added to the session.

                   The Attendees tab on the attendees page now also uses an editable embedded report for displaying data.

    TL-7455        Added sign-in sheet to download for Seminar events

                   A sign-in sheet can now be downloaded from the attendees tab in a Seminar event, which contains a list of learners
                   booked for that sessions along with spaces for signatures. Details such as start/end times and location are included in
                   the header of the sign-in sheet.

    TL-7458        Sign-up periods added to Seminar events

                   Sign-up start and end times can be added to a Seminar event. These restrict when a user can book into an event and when
                   managers can approve sign-up requests. If the user views the event outside of the sign-up period, dates will be shown
                   for when they will be able to sign up.

                   Setting these dates is optional. If just the end is set, then users will be able to sign up any time prior to that time.
                   If just the start is set, then users will be able to sign up any time from then until the start of the first session.

                   These settings will not prevent a Seminar admin from adding or removing users via the attendees page.

    TL-7463        Added session assets and room improvements

                   Seminar sessions can have assets associated with them. These may represent equipment or other items that are used during
                   the course of the session, such as a laptop or projector. Assets can be available globally or created and used for one
                   event. Multiple assets can be assigned to each session (formerly 'session date', of which there may be one or more in an
                   event).

                   Rooms may now be assigned per session rather than per event. During upgrade, any rooms assigned to an event will be
                   assigned to all sessions within that event.

                   The 'datetimeknown' field has been dropped as part of this process. Whether or not the date/times are known for an event
                   is determined in code by whether there are session records (in the 'facetoface_session_dates' table) associated with
                   that event. If an event had the 'datetimeknown' field set to unknown (value of 0), then any data associated with that
                   event in the 'facetoface_session_dates' table will be dropped during upgrade.

                   Custom fields have been added for rooms and assets. The fields for a room's 'building' and 'address' have also been
                   converted to default custom fields. The fields for these values have been dropped from the 'facetoface_room' table. Any
                   data in these fields will be transferred to the newly generated room custom fields during upgrade.

                   Report sources have been added for rooms, room assignments, assets and asset assignments.

                   As a result of the change from rooms being per-event to per-session, a system has been added for looping through
                   sessions within an event when substituting placeholders in a notification email.
                   Within a notification the placeholders [#sessions] and [/sessions] respectively indicate the start and end of a repeated
                   section.
                   That section of the email will be repeated for each session. Within each repeat, placeholders that are associated with
                   per-session data will be substituted with data for a given session. See our help documentation for further explanation.

                   The placeholders [alldates], [session:room], [session:venue] and [session:location] have been deprecated. These will
                   only be removed from the message when an email is generated, but no data will be substituted in. Placeholders such as
                   [session:room] can be replaced using the system above and new placeholders added for this purpose, for this case, it
                   would be [session:room:name]. In place of [alldates], a loop will need to be created which defines session start/end
                   times and other information specifically. The full list of placeholders relevant to these loops are available in the
                   help documentation.

                   Existing placeholders in notifications: If a template or specific notification exactly matches the default template for
                   that message in 2.9, it will be replaced with the new default. Otherwise, existing templates will have any [alldates]
                   placeholders replaced with an equivalent loop during upgrade, meaning the same message details should appear in
                   notifications. However, placeholders for room, location and venue cannot be replaced automatically as their relation to
                   each Seminar event is changing. If you have any notifications using room placeholders (and they are not exactly the same
                   as the 2.9 default template), you will need to update the notification to use the new tags for looping through sessions.

    TL-7465        Added new approval options to Seminars

                   Previously the approval options were limited to "No approval required", "Approval required", or in a separate setting
                   "Self approval" with terms and conditions. These have all been grouped into one setting, "Approval required" has been
                   renamed to "Manager Approval", and two new approval options "Role Approval" and "Manager and Administrative Approval"
                   have been added.

                   Role Approval:
                   To enable role approval you first need to make the desired role assignable at the event level by selecting it in the
                   "Event roles" setting on the site administration > Seminars > Global settings page. When the role is assignable a
                   corresponding option will be displayed under the available approval options setting on the same page, you may have to
                   save the page before it shows up. When a Seminar is using a role approval option, approval requests will be sent to
                   everyone assigned with the specified role in the seminar event. Any of those same users can then go to the seminars
                   pending requests tab and approve or deny the requests.

                   Manager and Administrative Approval:
                   This is a 2 step approval option. First the request must be approved by their manager, the same as "manager approval".
                   Then they must be approved by a "Seminar Administrator". Seminar administrators are a combination of site level
                   administrators and seminar (activity) level administrators. Site level administrators are users with the
                   "mod/facetoface:approveanyrequest" capability at site level, who have been selected in the "Site level administrative
                   approvers" setting on the site administration > Seminars > Global settings page. Seminar level administrators are any
                   user who has been selected with the "add approver" dialogue on the edit seminars page underneath the "admin approval"
                   option. When a user requests signup with this setting their manager and all of the administrators are sent the request,
                   the manager must first approve the request, then any one of the admins must approve it.

                   Another new setting on the site administration > Seminars > Global settings page, is the "Users select manager" setting.
                   This setting is for when manager approval is required but manager assignment data is not available. It prompts the user
                   to select their manager from a dialogue when they attempt to sign up, the selected user is then treated as their manager
                   for the rest of the approval process.

    TL-7944        Added seminar minimum bookings status and notification

                   This utilises a minimum booking setting that allows the user managing a Seminar activity to specify the minimum number
                   of attendees required for the event.
                   The value of this setting is then used when reporting on the status of the event, and can be used to trigger
                   notifications.

    TL-8187        Seminar events can now be cancelled

                   Seminar events can be cancelled by a Seminar admin. The event's information will remain, allowing it to be viewed in
                   reports. However, users will not be able to book into the event and the event's details can not be updated.

    TL-9675        Any one of a user's managers can approve their Seminar signup

                   The setting 'Select job assignment on sign-up' replaces 'Select position on sign-up'. With this setting on, a learner
                   with multiple job assignments will need to choose which job assignment they are signing up under.

                   Where learners have multiple managers (by having multiple job assignments), there are several possible scenarios for
                   sending out Seminar notifications to managers  (such as approval requests):

                   1: If the setting 'Select job assignment on sign-up' is on, notifications will go to the manager related to job
                      assignment chosen by the user.
                   2: If the setting 'Select job assignment on sign-up' is off, notifications will go to all of the user's managers.
                   3: If the 'Users select manager' site setting is turned on, then regardless of whether a job assignment was selected,
                      manager notifications will go to the manager chosen during sign-up. For more information on the 'User selects manager'
                      setting, see change log entry TL-7465.



New features - Multiple jobs:

    TL-2082        Multiple job assignments

                   Multiple job assignments can be defined for each user. Existing data in primary and secondary positions was converted to
                   job assignments. See the related changelog entries for specific uses of this feature.

    TL-9513        Added "Allow multiple job assignments" setting

                   By default, multiple job assignments are enabled.
                   By disabling this setting, only one job assignment can be created for each user.
                   HR Import will also prevent uploading multiple job assignments for a single user when disabled.
                   Any existing multiple jobs within the system when the setting is disabled will still remain, they will not be
                   automatically removed. They will continue to function until they are manually removed.

    TL-8946        HR Import now supports Job Assignments

                   The HR Import User source has been changed to import into job assignments, rather than the deprecated position
                   assignments:

                   *  The option "Link job assignments" has been added to the User source. The effects of this option are detailed below.
                   *  The column "Job assignment ID Number" has been added. This field relates to the ID Number field in the user's job
                      assignments. If "Link job assignments" is set "to the user's first job assignment" when you import user records, the
                      imported record will be linked to the user's "first" job assignment, and if the "Job assignment ID Number" field is
                      included then it will be updated with the specified value. If "Link job assignments" is "using the user's job
                      assignment ID number" when you import user records, the imported records will be linked to the user's existing job
                      assignment records using the ID Number.
                   *  If job assignment data is specified in the import and the matching job assignment record does not already exist, it
                      will be created.
                   *  The column "Manager's job assignment ID Number" has been added. This is required when "using the user's job
                      assignment ID number". If specified, the specific job belonging to the manager will be linked to the user's job
                      assignment. If "Link job assignments" is set "to the user's first job assignment", the manager's "first" job
                      assignment will be linked to the user's job assignment.
                   *  The column "Position title" has been renamed "Job assignment full name".
                   *  The columns "Position start date" and "Position end date" were renamed "Job assignment start date" and "Job
                      assignment end date".

                   Special care should be taken by sites upgrading to Totara 9.0 who used HR Import to populate the old "primary position",
                   and who wish to import multiple job assignments. During upgrade, all existing position assignments were made into job
                   assignments. Their ID Numbers were set to their location in the users' job assignment list. It is important to note that
                   after upgrading, the location in the list of job assignments is not connected to the ID Number - if a user's first and
                   second just assignments are swapped, it would result in the first job assignment having ID Number "2" and the second
                   would have "1". Therefore, after upgrading, before the site goes live, if your HR management system already has a system
                   of ID Numbers used to identify job assignments, and you want to use this value rather than the default "1" and "2", then
                   you should perform the following steps:

                   1. Upgrade to Totara 9.0 or later.
                   2. With "Link job assignments" set "to the user's first job assignment" and column "Job assignment ID Number" enabled,
                      import the ID Numbers from your HR management system into the "first" job assignments (previously primary position
                      assignments). Note that your import should only contain the "primary" job assignment for each user at this stage.
                   3. At this stage, your "first" job assignments have the ID Number from your HR management system.
                   4. If you had previously set up old "secondary position assignments", they will now exist as "second" job assignments
                      with ID number "2". You now need to manually update the ID Number in each of them to the ID number from your HR
                      management system.
                   5. Finally, set "Link job assignments" to "using the user's job assignment ID number". After the first import using this
                      setting, it will not be possible to change it back (the setting will be removed from the interface). The "Job
                      assignment ID Number" column will be required when importing job assignment data. Any number of job assignments, each
                      identified by the "Job assignment ID Number" field, can now be imported in a single upload.

    TL-8947        Updated the embedded "My team" report to work with multiple job assignments

                   The embedded "My Team" report has been updated to show all of the manager's staff members across all of their job
                   assignments.

    TL-8948        Updated the dynamic audience rules to work with multiple job assignments

                   Dynamic audience rules based on position assignments previously only applied to users primary positions, these have been
                   replaced with new job assignment rules that apply across all of a user's job assignments. This means that for sites
                   currently only using primary and not secondary position assignments, existing audience membership will not change on
                   upgrade. However sites currently using secondary position assignments might see audience memberships change on upgrade.
                   If you do not want secondary position data to be considered when reviewing dynamic audience memberships then you will
                   need to remove secondary position data from your users prior to upgrade.

                   The position and organisation audience rules have been combined under a new "All Job Assignments" header, the affected
                   rules are:

                   *  Titles (New - The job assignment full name)
                   *  Start dates (Previously: Position Start Date)
                   *  End dates (Previously: Position End Date)
                   *  Positions
                   *  Position Names
                   *  Position ID Numbers
                   *  Position Assignment Dates
                   *  Position Types
                   *  Position Custom Fields
                   *  Organisations
                   *  Organisation Names
                   *  Organisation ID Numbers
                   *  Organisation Types
                   *  Organisation Custom Fields
                   *  Managers
                   *  Has Direct Reports

    TL-8949        Updated the report builder columns, filters, and content options to work with multiple job assignments

                   Report builder columns, filters and content options based on a users position assignment previously only displayed or
                   checked the users primary positions.
                   These have been replaced with new job assignment columns, filters and content options that apply across all of a users
                   job assignments. The new columns are concatenated and display one job assignment field per line, with a spacer "-"
                   displayed for empty fields. The new filters now check whether any of the users job assignments match the specified
                   constraints, and if so will display all the data. These columns have been moved from the "User" header and are all now
                   located under a new "All User's Job Assignments" header to make them easier to find. The User, Organisation, and
                   Position content options are now applied across all of the users job assignments. This means if a report has a content
                   option set to "Show user's direct reports" and a user has three job assignments, all three managers would see the user
                   when viewing the report.

                   Note: Concatenated columns can not be sorted, and there are no concatenated text area columns or filters since there is
                   no way to display them inline. Any existing columns or filters for position and organisation text area custom fields, or
                   framework descriptions, will be lost on upgrade.

    TL-9524        Managers are selected via their job assignment

                   When assigning a manager to a staff member, both the manager and the job related to that manager-staff relationship can
                   be selected.

                   If the user has the 'totara/hierarchy:assignuserposition' capability in the manager's context or in the system context,
                   they will be able to create an empty job assignment for the manager within the selection dialog and assign that as the
                   job that the manager has this staff member under.

                   As part of this change, the selector dialog for choosing a manager or temporary manager will now return a job assignment
                   id as well as a manager id. The job assignment id can only be empty when the user has the capability described above, in
                   which case the new job assignment will be created.

                   If a user selecting a manager does not have permission to create the empty job assignment, and the manager currently has
                   no job assignments, a new job assignment must be created by someone with permission to do so before that manager can be
                   assigned a staff member.

    TL-9531        Updated program relative due dates to work with multiple job assignments

                   The relative to 'position start date' rule has been renamed to 'job assignment start date' and, along with the relative
                   to 'position assigned date' rule, has been changed to work across all of a users job assignments. When a user has the
                   same position in several job assignments, the maximum due date will be selected.

    TL-9677        Appraisals have been modified to work with the new job assignments feature.

                   Take note that the workflow for appraisals has changed:
                   1. Create appraisal; assign appraisees; fill in the content as per normal
                   2. The following has changed when an appraisal is activated:
                       *  Previously the system warned against missing roles and disallowed activation if it was a static appraisal. In 9.0,
                          there is no longer a check here for missing roles since that is tied in with the job assignment an appraisee links
                          to his appraisal.
                       *  The job assignment is linked only when the appraisee sees the appraisal for the very first time
                       *  Now, as long as the appraisal has assigned appraisees and questions, it can be activated, even if the appraisal is
                          static.
                   3. The following has changed when an appraisee opens their appraisal:
                       *  If the appraisee has multiple existing job assignments then they must now select a job assignment to link to the
                          appraisal. They cannot proceed with the appraisal until a job assignment is selected.
                       *  If the appraisee has only one job assignment then it is automatically linked to the appraisal.
                       *  If the appraisee doesn't have an existing job assignment then a "default" one is created and automatically linked to
                          the appraisal.
                       *  Only now does the system register the managers and appraisers based on the job assignment the appraisee selects.
                   4. The rest of the appraisal workflow is the same as in previous versions.

    TL-9468        Converted the user aspirational position into a user profile field

                   On upgrade to 9, aspirational position data will be migrated into the new profile field. This is because it behaves
                   differently to existing job assignments and serves a different purpose (a target position, rather than current one).

                   At present, aspirational position data is informational only, however in the future we may add gap analysis
                   functionality that makes use of this value.



Improvements:

    TL-5143        Added job assignment title as a filter for dynamic audiences

                   This was originally contributed by Aldo Paradiso to allow dynamic audiences based on primary position titles.  However,
                   with the new job assignments feature in 9.0, this has been reworked to use job assignment titles instead - specifically
                   job assignment full names.

    TL-5946        Certification block now uses its own duedate string

                   Previously the certification block was using strings belonging to programs.
                   It now has its own versions of these strings, allowing them to be translated with specific reference to the block.

    TL-6243        Added the ability to automatically create learning plans when new members are added to an audience

                   This improvement extends the manual learning plan creation within an audience by saving the creation configuration
                   settings and dynamically creating learning plans when new members are added to the audience, based on the saved
                   configuration.

    TL-6578        Fixed dynamic audiences with user profile custom fields so default field values work

                   It is possible to use checkbox, menu and text custom fields within dynamic audience rules for user profiles. However,
                   the default values for these fields have never been considered when computing the membership of the dynamic audience.
                   This fix allows the system to do so.
                   Take note: this fix may cause audience changes upon upgrading.

    TL-6643        SCORM serving scripts now have explicit HTTP headers to consistently prevent caching problems
    TL-6879        Changed HR import scheduling to use scheduled tasks scheduling

                   Scheduling now uses the core scheduled task scheduling, allowing for more fine grained control of scheduling. The UI on
                   within HR Import hasn't changed but will be disabled if the schedule is too complex to be displayed. A complex schedule
                   can be created using the scheduled task scheduler.

    TL-7060        Made appraisals CSS classes more specific
    TL-7332        Removed appraisal question options for disabled functionality

                   Previously you could add "course from plan" review questions to your appraisal even if learning plans had been disabled
                   for your site, these types of question should now only be available if the associated feature(s) are enabled.

    TL-7386        Improved the run time of report builder column unit tests
    TL-7452        Improved Seminar navigation

                   The Seminar administration menu has been moved from the plugins menu (Site administration > Plugins > Activity modules >
                   Seminar) to the Site administration root (Site administration > Seminar).
                   Additionally the settings have been regrouped depending upon the scope of their influence, for example the "Available
                   approval options" setting is located under "Global settings" as it determines the available options for all Seminars.

    TL-7459        Added links to further information on each room when signing up
    TL-7460        Improved Seminar summary report source

                   Seminar summary report has a number of new columns and filters that allows the user to get more detailed information
                   about Seminar events.

    TL-7621        Seminar add/remove attendees selectors now use the user identity setting
    TL-7795        Improved the handling of signup requests when switching approval types

                   Fixed user's status code from REQUESTED to BOOKED/WAITLISTED depending from session capacity when Seminar is changed
                   from approval required to not

    TL-7914        Removed report builder filter HTML when no filters are displayed
    TL-7963        Added the ability to use custom fields within evidence

                   On upgrade the description, evidencelink, institution and datecompleted database dp_plan_evidence fields will be deleted
                   with their data being transferred into new evidence custom fields.

    TL-8023        Added a new option "All courses are optional" to program coursesets

                   You can now define program course sets that don't require any of the courses to be complete. Note that the course set
                   will automatically be marked as complete when it is reached and will potentially contribute towards the progress in the
                   program. If you use this option with OR or THEN operators, you might find that portions of the program are automatically
                   completed before the user is required to do anything.

    TL-8043        Improved capability handling on the Tag Management pages
    TL-8116        Extended the file input custom field to support multiple files
    TL-8118        Added the ability to import evidence custom field data in the completion Import tool

                   Evidence Custom field data can be included in the import by adding columns to the CSV file named with the prefix
                   'customfield_' followed by the custom field shortname.

                   Custom fields of type 'file' and 'multiselect' can not be uploaded.

                   On install or upgrade, an evidence 'Date Completed' custom field is created. This field will be used to store the
                   completiondate value from the CSV upload file.

                   Also, on install or upgrade,  an evidence 'Description' field is created. If this field is specified in the CSV upload
                   file, the content from the CSV upload file will be used. If the content is empty, the default value set in the custom
                   'Description' field will be used. If the 'Description'  field is not specified in the CSV upload file, an auto-generated
                   description will be created, based on the course completion data and stored in the 'Description' field.

    TL-8168        Improved method of sending Seminar calendar events

                   Previously the events were sent as ics file attachments, this practice is not recommended any more for security reasons.
                   The calendar event is now embedded in the email.

    TL-8181        Improved alignment in the add/edit scheduled report form
    TL-8182        Re-implemented manual course completion archiving

                   You can manually archive all completions for a course by going to Course administration > completions archive page, this
                   has been restricted to courses that are not part of programs or certifications to avoid flow on effects.

    TL-8186        Added user first_name string as placeholder for new user welcome email
    TL-8272        Dashboards can now be cloned

                   The ability to clone dashboards has been added to Totara 9.0
                   Cloning a dashboard creates a copy of the original dashboard, the blocks that have been added to it and any audience
                   assignments that have been made.
                   Cloning does not copy any user customisations to the dashboard.

    TL-8383        Activity completion reaggregated during scheduled task after unlock and delete

                   Previously, when activity completion was unlocked and deleted, completions were recalculated immediately (based on
                   grades already attained for example). However this led to performance problems given it was being recalculated for each
                   user.

                   Activity completions will now be set to incomplete (rather than deleted) immediately and will then be flagged for
                   reaggregation. The reaggregation will happen on the following run of the \core\task\completion_regular_task which, by
                   default, is run every minute.

                   With completions being set to incomplete rather than deleted, this means the record remains (where it was previously
                   just deleted). This has the advantage of retaining whether an activity was viewed by a user (if that was recorded).  If
                   an activity was manually completed, this information is still not retained.

                   When the state is set to incomplete, the timecompleted field is also set to null (if it wasn't already).

                   A new field, named 'reaggregate', has been added to the course_modules_completion table. This is an integer field for
                   storing the UTC timestamp of when it is set.

    TL-8413        Enabled URL custom fields to display as working links on view Hierarchy details page
    TL-8459        Added landing page for Session Attendees to allow user to select Seminar event attendees and then view a report.
    TL-8465        Added 'Sign-up Period' column to Seminar Events and Sessions reports
    TL-8469        Added Seminar Event registration expired notification
    TL-8497        The language menu is now displayed on the login page if multiple languages are installed
    TL-8515        Reorganised the main menu navigation defaults and dashboard default blocks

                   The main menu has been reorganised with new naming and simplified content. On upgrade the existing content will be left
                   but the new menu can be selected by clicking on "reset menu to default configuration" in "Appearance > Main menu".

                   The My Learning page has been removed. The contents has been moved into a "Legacy My Learning" Totara dashboard which is
                   hidden by default. There is a new My Learning dashboard shown to all logged in users which has new default content. You
                   can switch back to the old My learning by changing the visibility settings on "Appearance > Dashboards".

                   The defaulthomepage options have been simplified.

    TL-8516        Added "Visible to all users" option to Dashboards which makes it accessible to every logged in user
    TL-8569        Applied correct style to "Join Waitlist" link for a Seminar event.
    TL-8578        Changed Seminar signup page to show overlapping signup warning
    TL-8636        Added pagination when reviewing attendees to be added or removed from a Seminar session
    TL-8637        Removed "Face-to-face" block

                   This block's functionality is superseded by current Seminar Reportbuilder sources.

    TL-8647        Added option to specify behaviour of empty strings in HR Import

                   When importing data from CSV files, there is a new option to determine the behaviour of empty strings. Option "Empty
                   strings are ignored" means that any empty field in the import will be ignored and existing data will be unchanged, while
                   option "Empty strings erase existing data" means that any empty field in the import will cause the existing data in that
                   field to be erased.

                   External database sources are unaffected by this change. Fields set to null will leave the existing data unchanged while
                   empty strings will cause the existing data to be erased.

    TL-8652        Improved URL references in the template library

                   Previously URL references in the template library were largely dependant on the developers setup. With this change, URLs
                   are adjusted to suit whatever the setup is of the person viewing the template library (assuming the mustache template
                   uses __WWWROOT__ and __THEME__ placeholders, as created when using
                   \tool_templatelibrary\exampledata_formatter::to_json())

    TL-8713        Improved diagnostic messages in scheduled report task execution
    TL-8724        Improved the user workflow when completing actions within a Seminar activity
    TL-8767        Rephrased help text for room scheduling conflict option
    TL-8779        Removed non-standard 'back to' navigation links from Learning Plans and Record of Learning
    TL-8786        Refactored Seminar asset and room "allow conflicts" data storage
    TL-8814        Made Seminar Room name a required field
    TL-8853        Improved a number of JavaScript modules to meet current coding standards

                   The following JS modules have been improved to meet current coding standards:

                   *  Category manager
                   *  Acceptance testing JS
                   *  Reportbuilder graphical reporting

    TL-8863        Introduced a new API for adding custom role access restrictions in Reportbuilder

                   Previously the role access restrictions were implemented in Report builder lib.php file. This meant that plugin
                   developers could not add custom restrictions without modifying the report builder.

                   All access restriction code was refactored to to use new rb\access class namespace and a new discovery mechanism was
                   added to allow any plugin to define new access restriction classes for all other reports. See
                   totara/reportbuilder/classes/rb/access/base.php file for more information.

                   All pre-existing role access code customisations need to be updated to use this new API.

    TL-8871        Improved category expander JavaScript to meet current coding standards
    TL-8886        Moved Seminar Default minimum bookings setting to Event defaults
    TL-8892        Fixed sign-up time column option in Seminar events report builder
    TL-8895        Added Events report source and embedded report
    TL-8900        Added "Description of regular expression validation format" option to text input custom field
    TL-8915        Created a "booking options" section when adding Seminar event attendees
    TL-8940        Improved display of capacity for waitlisted users of waitlisted events
    TL-8950        Learning plan approval requests are now sent to all managers

                   With the ability to have several managers by having multiple job assignments, approval requests and other learning plan
                   notifications will now go to all of the learner's managers. All of the learner's managers will have the ability to
                   approve, decline or delete a user's learning plan or its components.

    TL-8968        Converted course edit form customisations to proper hooks

                   This change introduced three new hooks:

                   1. \core_course\hook\edit_form_definition_complete
                      Gets called at the end of the course_edit_form definition.
                      Through this watcher we can make any adjustments to the form definition we want, including adding Totara specific
                      elements.

                   2. \core_course\hook\edit_form_save_changes
                      Gets called after the form has been submitted and the initial saving has been done, before the user is redirected.
                      Through this watcher we can save any custom element data we need to.

                   3. \core_course\hook\edit_form_display
                      Gets called immediately before the form is displayed and is used to initialise any required JS.

    TL-9123        Added rel="noreferrer" to links being displayed by URL custom fields
    TL-9127        Added Grunt tasks for working with Less

                   Less may be used to author plugin styles by providing a less directory containing a styles.less file e.g.
                   local/myplugin/less/styles.less. Theme less files if found are compiled and a RTL equivalent generated.

    TL-9141        Added back-end validation methods to date/time custom fields.
    TL-9156        Removed the setting "Users can enter requests when signing up" from the Seminar module

                   The setting "Users can enter requests when signing up" previously governed whether the ability to add text (a 'sign-up
                   note') was available to the user when they signed up. Options like this are now based on whether there are event custom
                   fields enabled.

    TL-9237        Added support for unique index on nullable column
    TL-9256        Improved directory removal to prevent race conditions from file and directory removal
    TL-9259        Theme RTL stylesheets are now served separately

                   This update allows developers to provide a RTL equivalent for any theme stylesheet by providing a file with an -rtl
                   suffix e.g. totara.css and totara-rtl.css. Where found the RTL theme sheet will be cached and served instead for RTL
                   languages.

    TL-9267        Improved display of SCORM in popup windows
    TL-9274        Review and clean up of all old Totara update code

                   All Totara upgrade code has been reviewed and tidied up to greatly reduce the likelihood of upgrade conflicts being
                   encountered.

                   The following changes have been made:

                   *  The totara_core upgrade is now executed immediately after core upgrade and install before any other plugins
                   *  There are only two Totara pre upgrade scripts - one executed once before migration from Moodle and other gets
                      executed before every Totara upgrade
                   *  Totara blocks are using standard install.php scripts
                   *  All language packs are updated only once
                   *  The upgrade sections were renamed so that the first heading is "Totara", then "System", then  "totara_core" and then
                      the rest of plugins
                   *  Custom field capabilities were cleaned up
                   *  Migration of badge capabilities from old installations was improved
                   *  Added detection and automatic fixing of capabilities migrated from plugins to core
                   *  Removed unnecessary upgrade flags from totara_core and totara_cohort
                   *  Fixed totara_cohort upgrade.php to use $oldversion instead of previous release hack
                   *  Removed code duplication from totara_upgrade_mod_savepoint()
                   *  Moved totara_completionimport upgrade code to totara_completionimport
                   *  Fixed incorrect include in Totara menu upgrade

    TL-9322        Provided Less compilation --themedir option

                   Allows a developer compiling Less via Grunt to pass an optional parameter 'themedir' to add a custom theme directory (as
                   per $CFG->themedir) to the Less import search path.

    TL-9353        Increased the Seminars CALENDAR_MAX_NAME_LENGTH constant

                   Previously seminar names were being truncated to 32 characters while creating calendar events, this has been changed to
                   256 characters. And can now be overridden in config.php using "define('CALENDAR_MAX_NAME_LENGTH', 32);" if you want to
                   continue shortening the name.
                   Note: this will not change any existing calendar events, but can be updated by editing the associated seminar.

    TL-9430        Added the ability to include partials dynamically into a Mustache template

                   This allows a list of items to be displayed differently depending on the type of an item using partials within a
                   Mustache template.

    TL-9514        Report builder scheduled report email message string 'scheduledreportmessage' now supports markdown format including html tags
    TL-9539        Embedded reports and sources may override exported report headers

                   Developers may now customise headers in exported reports by adding new method get_custom_export_header() to the embedded
                   report or source class. Non-null value overrides the standard report header in exported reports.

    TL-9676        Updated the programs extension requests to work with multiple job assignments

                   When a user requests an extension, messages will now be sent to all of the user's managers, across all of their job
                   assignments. Any of the managers can then go and approve or deny the extension request.

    TL-9727        Manager's job assignment is selected when assigning program via management hierarchy

                   When assigning management hierarchies to a program, one or more of the manager's job assignments must be selected. Only
                   staff managed under the selected job assignments will be assigned to the program via this method.

                   Any custom code using assignment via management hierarchy must use the ASSIGNTYPE_MANAGERJA constant (which has a value
                   of 6) rather than the ASSIGNTYPE_MANAGER constant (which as a value of 4). You will need to ensure that any calls to
                   Totara functions using this assignment type use the manager's job assignment id in place of where the manager's user id
                   was being used. Data in the prog_assignment table will updated during upgrade.

    TL-9765        Improved template handling in situations where the active theme no longer exists
    TL-9774        Use system fonts

                   This change updates the Roots theme to use the appropriate sans-serif system font for the user's operating system. This
                   ensures that the font is clear, readable and available, and that languages such as Hebrew and Arabic will be displayed
                   correctly.

    TL-9791        Converted the main menu to use use renderers

                   The main menu can now be overridden using Mustache templates improving the ease of customisation

    TL-9792        Visual improvements to the admin notification page to bring it inline with the new themes
    TL-9805        Changed the default theme to Basis
    TL-9809        Updated PHP doc for has_config() method
    TL-9817        Improved displaying of custom field dates
    TL-9839        Deprecated broad CSS in Appraisals
    TL-9866        Standard Totara Responsive now aligns form labels to the left
    TL-9944        URL type custom fields can now be imported through HR Import
    TL-9979        Fixed the constructor of the QuickForm custom renderer class for PHP7
    TL-9995        Reportbuilder titles are now filtered for multilingual content
    TL-10019       Fixed visual display of SCORM reports
    TL-10032       Assignments to programs and certifications take into account all of a user's job assignments

                   With the addition of the multiple job assignments functionality, assignments to a program or certification via position,
                   organisation or management hierarchy will include users with the relevant settings (such as organisation) in any of
                   their job assignments.

                   When upgrading, be aware that any positions, organisations and manager assignments in a users second job assignment
                   (formerly called secondary position assignment) may lead to users being assigned to additional programs and
                   certifications.

    TL-10115       Improved the display of the miniature calendar used in the calendar block and calendar interface

                   Calendar plugins can now specify an abbreviated calendar name within the structure::get_weekdays method. This should be
                   the shortest possible abbreviation of the day, and is used predominantly within the calendar when displayed as a block
                   in a small space.

    TL-10130       Re-approval for a previously declined Seminar event no longer results in a debugging message
    TL-10137       A warning about deleted fields has been added to user source configuration in HR Import

                   HR Import will allow you to add job assignments via a User source. The deleted field contained in this source only
                   applies to deleting users and should not be used for attempting to delete job assignment data.

                   HR Import currently only allows job assignment records to be added or updated. It does not allow deletion of job
                   assignment records. However that can be achieved via a user's profile.

                   A warning regarding this has been added to the user source configuration in HR Import. However, anyone who may edit HR
                   Import data sources should also be made aware of this information.

    TL-10182       Fixed a number of layout and appearance issues on the course management page.
    TL-10402       Removed calendar icons from frozen form elements
    TL-10429       'Process messages and alerts' task was split in to 'Dismiss alerts and tasks after 30 days' and 'Cleanup messaging related data'
    TL-10452       Minimum required browser version were increased

                   It is recommended to use only browser that are supported by their manufacturer, minimum versions for Totara are:

                   *  recent Chrome
                   *  recent Firefox
                   *  Safari 9
                   *  Internet Explorer 9

    TL-10597       Improved program and certification progress calculation to exclude 'Optional' courses
    TL-10655       Hyphen and full-stop characters are now valid within the Reportbuilder scheduled report export path setting
    TL-10704       Ensured hierarchy AJAX is disabled when hierarchies have been disabled in Advanced features
    TL-10908       All current Bootstrap 2 themes have been deprecated

                   With the arrival of the new Bootstrap 3 themes we have deprecated the current Bootstrap 2 themes.
                   This includes the following themes:

                   *  Bootstrap Base
                   *  Standard Totara Responsive
                   *  Custom Totara Responsive
                   *  Kiwifruit Responsive

                   Deprecation is the first step towards eventual removal. These themes will remain in core for one more major release and
                   may then be moved out of Totara core.
                   They will still be made available for those who want to use them, but will not longer receive regular updates.


Accessibility improvements:

    TL-6292        Improved the HTML markup used when viewing Reportbuilder reports

                   Previously Reportbuilder reports had all the controls inside an HTML table, and when there were no results, it was
                   displayed as a table with one cell. This improvement moves the controls outside of the report table, and replaces the
                   table when there are no results. Themes may have to have their CSS changed to accommodate this.

    TL-7740        The add/remove site administrators user interface no longer uses HTML tables for layout
    TL-7742        The lanugage pack importer user interface no longer uses HTML tables for layout
    TL-7837        The add/remove role user interface no longer uses HTML tables for layout
    TL-7840        The user interface for assigning user's to audiences no longer uses HTML tables for layout
    TL-7843        The user interface for managing group membership no longer uses HTML tables for layout
    TL-7861        The Seminar event allocation user interface no longer uses HTML tables for layout
    TL-7863        The Seminar add/remove attendees user interface no longer uses HTML tables for layout
    TL-7875        The user interface for managing groupings within a course no longer uses HTML tables for layout
    TL-7877        The Feedback activity add/remove user's user interface no longer uses HTML tables for layout
    TL-7880        The user selector used for enrolling users into a course no longer uses HTML tables for layout
    TL-7882        The user interface for managing webservice user's no longer uses HTML tables for layout
    TL-7885        The user interface for awarding badges no longer uses an HTML table for layout
    TL-7890        The user interface for managing Forum subscribers no longer uses HTML tables for layout
    TL-7924        The audience rules user interface no longer uses HTML tables for layout
    TL-7996        Removed duplicate HTML id's when adding linked competencies
    TL-8530        Removed tables for layout when viewing items from a learning plan
    TL-8598        Improved Aria accessibility experience within Seminar
    TL-8913        Improved accessibility when removing users from a feedback360 request
    TL-9164        Linked custom URL field text to their appropriate input elements
    TL-10241       Added alternative text to badge images
    TL-10259       Improved accessibility when using multi check filters in report builder reports
    TL-10262       Improved the HTML markup on the My Badges page
    TL-10263       Added a legend when searching your badges
    TL-10264       Linked reason input field in a learning plan to a label
    TL-10265       Added a legend to the report builder export functionality
    TL-10269       The completion status interface within the course completion status block no longer uses an HTML table for layout
    TL-10271       Improved accessibility around creating a calendar event
    TL-10272       Added a label to the move forum post dropdown
    TL-10277       Removed labels that were not required when displaying date fields
    TL-10279       Improved accessibility when adding groups of users to a goal
    TL-10283       Replaced unnecessary headings in badges.
    TL-10309       Added accessible label when viewing custom checkboxes on a users profile


Technology conversions:

    TL-7947        Converted totara_program_completion/block.js to an AMD module

                   The M.block_totara_program_completion JavaScript code has been converted from a statically loaded JS file to an AMD
                   module.
                   This allows the JS to be loaded dynamically with much greater ease and unlocks the benefits AMD brings such as
                   minification and organisation.

                   This change removes the block/totara_program_completion/block.js file.

    TL-7950        The JavaScript loaded by the report table block has been converted to an AMD module and is only loaded when required
    TL-7951        The JavaScript loaded by element library has been converted to an AMD module
    TL-7959        The instance filter as used in the report builder been converted to an AMD module
    TL-7961        The cache now JavaScript as used in the report builder been converted to an AMD module
    TL-7987        Converted block rendering to use templates
    TL-8009        Converted totara_print_active_users to templates
    TL-8011        Converted is_registered method to use templates
    TL-8012        Converted print_totara_search method to use templates
    TL-8013        Converted print_totara_notifications method to use templates
    TL-8014        Converted print_totara_progressbar method to use templates
    TL-8015        Converted comment_template method to use templates
    TL-8016        Converted print_toolbars method to use templates
    TL-8017        Converted print_totara_menu method to use templates
    TL-8018        Converted totara_print_errorlog_link method to use templates
    TL-8019        Converted display_course_progress_icon to use templates.
    TL-8031        Converted print_my_team_nav to use templates.
    TL-8034        Converted print_report_manager to use templates
    TL-8035        Converted print_scheduled_reports to use templates.
    TL-8036        Converted print_icons_list to use templates.
    TL-8084        Converted html_table and html_writer::table to use templates
    TL-8200        Converted the My Reports page to use templates
    TL-8304        Converted Manage types pages to Mustache templates
    TL-8305        Converted manage frameworks to Mustache templates
    TL-8330        Converted print_competency_view_evidence to use a Mustache template
    TL-8331        Converted competency_view_related to use a template
    TL-8332        Converted goal assignment to Mustache templates
    TL-8334        Converted assigned goals to use a Mustache template
    TL-8335        Converted mygoals_company_table to use Mustache templates
    TL-8337        Converted mygoals_personal_table to use a Mustache template
    TL-8448        Converted render_single_button function to use templates
    TL-9070        Converted filter_dialogs to an AMD module
    TL-9192        Converted competency JavaScript functionality to AMD modules


API changes:

    TL-7473        Custom menu has been deprecated

                   The custom menu that could be edited through Site Administration > Appearance > Themes > Theme settings has been
                   deprecated. You may need to convert this into the functionality as provided by Site Administration > Appearance > Main
                   menu

    TL-7817        Added support for AMD modules to the jQuery DataTables plugin

                   In Totara 2.9, a small customisation was introduced to the jQuery DataTables library removing AMD support. This
                   customisation has now been removed (and updated the library to 1.10.11 in the process) as we have updated our JavaScript
                   to AMD modules.

                   To convert JavaScript functionality to support jQuery DataTables, ensure your JavaScript is written as an AMD module and
                   require both 'jquery' and 'totara_core/datatables' in your module.

    TL-7981        Removed unused preferred_width function from blocks
    TL-7982        Removed unnecessary calls to local_js

                   Previously (in Totara 2.7 and below) making a call to local_js would load jQuery into the page. As of Totara 2.9, jQuery
                   is loaded by default on every page so this call is no longer needed if no jQuery plugins are required. This fix removed
                   all requests to local_js with no parameters.

    TL-8004        Separated Totara specific CSS from bootstrap bases theme
    TL-8027        Function function_or_method_exists() was deprecated
    TL-8055        Removed references to some deprecated functions
    TL-8057        Added element id to static and hard-frozen form field containers

                   This allows them to be addressed in JavaScript and by CSS, the same as non-static and non-hard-frozen field containers.

    TL-8071        Moved Hierarchy JavaScript from inline to an AMD module

                   Previously there was a large amount of JavaScript that was written in the HTML (instead of being in an external
                   JavaScript file) in Hierarchies. These have been replaced with 2 AMD modules

    TL-8188        Totara LMS 9 now supports PHP7

                   Note that support for PHP7 has not been backported to the stable release.
                   Any stable releases still have a maximum version of PHP5.6.

    TL-8369        html_writer should implement templatable
    TL-8405        JQuery updated to 2.2.0
    TL-8537        Fixed a redirection issue when signing up to a Seminar event
    TL-8562        Created export_for_template method on single_button class
    TL-8958        Added new API for lookup of classes in namespace
    TL-8998        Imported latest SVGGraph library
    TL-9101        Deprecated the totara_message_accept_reject_action function

                   This function is not used within core Totara code and has been deprecated. It will be removed in Totara 10.0

    TL-9826        Created new 'I switch to "tab" tab' behat step


API change details:

    TL-6292        *  totara_table::print_extended_headers() has been removed
    TL-6874        *  New \core\output\flex_icon class to allow use of font icons.
                   *  New pluginname/pix/flex_icons.php file to define and alias icons.
    TL-6879        *  admin/tool/totara_sync/lib.php tool_totara_sync_run first argument has been removed
    TL-7060        *  CSS class "stagelist" has been renamed "appraisal-stagelist"
                   *  CSS class "stagetitle" has been renamed "appraisal-stagetitle"
                   *  CSS class "stageinfo" has been renamed "appraisal-stageinfo"
                   *  CSS class "stageactions" has been renamed "appraisal-stageactions"
    TL-7452        *  Removed table facetoface_notice
                   *  Removed table facetoface_notice_data
                   *  Removed unused function facetoface_get_manageremailformat
                   *  Removed unused function facetoface_check_manageremail
                   *  Removed unused class mod_facetoface_sitenotice_form
    TL-7453        *  rb_param_option::$type is deprecated and will be removed in future versions
                   *  facetoface_print_session function added sixth argument $class
    TL-7455        *  rb_source_facetoface_summary::rb_display_link_f2f method is removed
                   *  rb_source_facetoface_summary::rb_display_link_f2f_session is removed
                   *  rb_source_facetoface_sessions::rb_display_facetoface_status method is removed
                   *  rb_source_facetoface_sessions::rb_display_link_f2f_session method is removed
    TL-7458        *  facetoface_sessions.registrationtimestart added a new database field registrationtimestart to facetoface_sessions
                   *  facetoface_sessions.registrationtimefinish added a new database field registrationtimefinish to facetoface_sessions
    TL-7461        *  Capability 'mod/facetoface:editsessions' was renamed to 'mod/facetoface:editevents'
                   *  Capability 'mod/facetoface:overbook' was renamed to 'mod/facetoface:signupwaitlist'
    TL-7463        *  rb_base_source::add_custom_fields_for added seventh argument $useshortname
                   *  $session->datetimeknown property of session objects fetched with facetoface_get_session() is removed.
                      $session->cntdates should be used instead
                   *  mod_facetoface_session_form class is removed
                   *  mod_facetoface_renderer::print_session_list_table added sixth and seventh optional arguments $currenturl and
                      $minimal
                   *  facetoface_save_session_room function is removed
                   *  facetoface_get_session_room function is replaced with facetoface_get_session_rooms
                   *  facetoface_room_html added second argument $backurl
                   *  facetoface_room_info_field added a new database table
                   *  facetoface_room_info_data added a new database table
                   *  facetoface_room_info_data_param added a new database table
                   *  facetoface_room.address removed a database field address from facetoface_room
                   *  facetoface_room.building removed a database field building from facetoface_room
                   *  facetoface_room.usercreated added a new database field usercreated to facetoface_room
                   *  facetoface_room.usermodified added a new database field usermodified to facetoface_room
                   *  facetoface_room.hidden added a new database field hidden to facetoface_room
                   *  facetoface_sessions_dates.roomid added a new database roomid hidden to facetoface_sessions_dates
                   *  facetoface_sessions.roomid removed a database field roomid from facetoface_sessions
                   *  facetoface_sessions.datetimeknown removed a database field datetimeknown from facetoface_sessions
                   *  facetoface_asset added a new database table
                   *  facetoface_asset_info_field added a new database table
                   *  facetoface_asset_info_data added a new database table
                   *  facetoface_asset_info_data_param added a new database table
                   *  facetoface_asset_dates added a new database table
                   *  mod_facetoface\rb\display\link_f2f_session class removed
                   *  mod_facetoface\rb\display\link_f2f class removed
                   *  mod_facetoface\rb\display\f2f_status class removed
    TL-7465        *  facetoface.approvaltype added a new database field approvaltype to facetoface
                   *  facetoface.approvalrole added a new database field approvalrole to facetoface
                   *  facetoface.approvalterms added a new database field approvalterms to facetoface
                   *  facetoface.approvaladmins added a new database field approvaladmins to facetoface
                   *  facetoface_signups.managerid added a new database field managerid to facetoface_signups
                   *  MDL_F2F_CONDITION_BOOKING_REQUEST this global constant has been replaced by
                      MDL_F2F_CONDITION_BOOKING_REQUEST_MANAGER
                   *  facetoface_message_substitutions added a 7th argument $approvalrole
                   *  facetoface_approval_required argument two has been removed and is no longer used
                   *  facetoface_user_signup there have been several changes to the function signature. Any uses in custom code should be
                      reviewed to ensure they are still compatible
    TL-7473        *  class custom_menu_item has been deprecated and will be removed in a future version
                   *  class custom_menu has been deprecated and will be removed in a future version
                   *  core_renderer::custom_menu() has been deprecated and will be removed in a future version
                   *  core_renderer::render_custom_menu() has been deprecated and will be removed in a future version
                   *  core_renderer::render_custom_menu_item() has been deprecated and will be removed in a future version
    TL-7944        *  facetoface_sessions.sendcapacityemail added a new database field sendcapacityemail to facetoface_sessions
                   *  facetoface_sessions.mincapacity field must be not null with default value 0
    TL-7963        *  dp_plan_evidence.description removed the database field description in dp_plan_evidence
                   *  dp_plan_evidence.evidencelink removed the database field evidencelink in dp_plan_evidence
                   *  dp_plan_evidence.institution removed the database field institution in dp_plan_evidence
                   *  dp_plan_evidence.datecompleted removed the database field datecompleted in dp_plan_evidence
    TL-7981        *  block_totara_alerts::preferred_width() has been removed.
                   *  block_totara_quicklinks::preferred_width() has been removed
                   *  block_totara_stats::preferred_width() has been removed
                   *  block_totara_tasks::preferred_width() has been removed
    TL-7982        *  local_js() with no arguments now shows a debugging message.
    TL-8009        *  totara_core_renderer::totara_print_active_users has been deprecated and will be removed in a future version
    TL-8012        *  totara_core_renderer::print_totara_search has been deprecated and will be removed in a future version
    TL-8013        *  totara_core_renderer::print_totara_notifications has been deprecated and will be removed in a future version
    TL-8014        *  totara_core_renderer::print_totara_progressbar has been deprecated and will be removed in a future version
    TL-8016        *  totara_core_renderer::print_toolbars has been deprecated and will be removed in a future version
    TL-8017        *  totara_core_renderer::print_totara_menu has been deprecated and will be removed in a future version
    TL-8018        *  totara_core_renderer::totara_print_errorlog_link has been deprecated and will be removed in a future version
    TL-8019        *  totara_core_renderer::display_course_progress_icon has been deprecated and will be removed in a future version
    TL-8023        *  Global constant COMPLETIONTYPE_OPTIONAL added for setting program course sets as optional
    TL-8031        *  totara_core_renderer::print_my_team_nav has been deprecated and will be removed in a future version
    TL-8034        *  totara_core_renderer::print_report_manager has been deprecated and will be removed in a future release
    TL-8035        *  totara_core_renderer::print_scheduled_reports has been deprecated and will be removed in a future release
    TL-8036        *  totara_core_renderer::print_icons_list has been deprecated and will be removed in a future release
    TL-8084        *  html_writer::table has been deprecated and will be removed in a future release
                   *  html_table::$tablealign has been deprecated and will be removed in a future release
                   *  html_table::$captionhide has been deprecated and will be removed in a future release
    TL-8118        *  totara_compl_import_course.customfields added a new database field customfields to totara_compl_import_course
                   *  totara_compl_import_course.evidenceid added a new database field evidenceid to totara_compl_import_course
                   *  totara_compl_import_cert.customfields added a new database field customfields to totara_compl_import_cert
                   *  totara_compl_import_cert.evidenceid added a new database field evidenceid to totara_compl_import_cert
                   *  totara/completionimport/lib.php check_fields_exist added a third argument $customfields
                   *  totara/completionimport/lib.php import_csv added a third argument $customfields
    TL-8187        *  facetoface_sessions.cancelledstatus added a new database field cancelledstatus to facetoface_sessions
                   *  facetoface_sessioncancel_info_data added a new database table
                   *  facetoface_sessioncancel_info_data_param added a new database table
                   *  facetoface_sessioncancel_info_field added a new database table
    TL-8330        *  totara_hierarchy_renderer::print_competency_view_evidence has been deprecated and will be removed in a future
                      release
    TL-8331        *  totara_hierarchy_renderer::print_competency_view_related has been deprecated and will be removed in a future release
    TL-8332        *  totara_hierarchy_renderer::print_goal_view_assignments has been deprecated and will be removed in a future release
    TL-8334        *  totara_hierarchy_renderer::print_assigned_goals has been deprecated and will be removed in a future version
    TL-8383        *  course_modules_completion.reaggregate added a new database field reaggregate to course_modules_completion
    TL-8513        *  a new user_learning API has been introduced, see changelog description for details
                   *  totara/program/lib.php prog_get_all_programs added a ninth argument $onlycertifications
    TL-8537        *  session_options_signup_link has a new fourth argument, true by default, if true the user is redirected to the view
                      all sessions page after completing a signup or cancellation action.
                   *  print_session_list_table has a new eighth argument, true by default, if true the user is redirected to the view all
                      sessions page after completing a signup or cancellation action.
    TL-8636        *  addconfirm_form::get_user_list($userlist, $offset, $limit) added new function
                   *  removeconfirm_form::get_user_list($userlist, $offset, $limit) added new function
    TL-8779        *  dp_base_component::display_back_to_index_link deprecated.
    TL-8786        *  facetoface_asset renamed database field "type" to "allowconflicts" and changed data type from char(10) to int(1)
                   *  facetoface_room renamed database field "type" to "allowconflicts" and changed data type from char(10) to int(1)
    TL-8892        *  rb_source_facetoface_sessions::define_columnoptions time of signup value is changed
    TL-8895        *  rb_facetoface_base_source::add_facetoface_common_to_columns added a second argument $joinsessions
                   *  rb_facetoface_base_source::add_session_status_to_joinlist second argument $sessionidfield was split into two
                      arguments $join and $field
    TL-2082        *  After upgrade, Primary position assignment records will be the users' first job assignment (sortorder = 1) and
                      Secondary records will be the users' second job assignment (sortorder = 2). Aspirational records are moved to the
                      new gap_aspirational table.
                   *  Strings relating to position assignments were deprecated from totara_hierarchy and totara_core and added into the
                      totara_job language file.
                   *  Moved config variables allowsignupposition, allowsignuporganisation and allowsignupmanager from totara_hierarchy to
                      the totara_job plugin.
                   *  The position_updated and position_viewed events have been changed to job_assignment_updated and
                      job_assignment_viewed. The create_from_instance functions now take a job_assignment object, the object id is
                      available to event observers in $data['objectid'], the userid is available in $data['relateduserid'].
                   *  Several behat data generators and steps relating to old position assignments have been removed. "the following job
                      assignments exist" should now be used to define manager, position, organisation, temp manager, etc.
                   *  The create_primary_position phpunit test generator was renamed to create_job_assignments.
                   *  The positionsenabled totara_hierarchy setting is deprecated and no longer used.

                   *  totara_is_manager has been deprecated but maintains a level of backwards compatibility and will be removed in a
                      future version. Instead, use job_assignment::is_managing.
                   *  totara_get_staff has been deprecated but maintains a level of backwards compatibility and will be removed in a
                      future version. Instead, use job_assignment::get_staff() or job_assignment::get_staff_userids(), depending on the
                      situation.
                   *  totara_get_manager has been deprecated but maintains a level of backwards compatibility and will be removed in a
                      future version. Code using this function should be redesigned to either use job_assignment::get_all() with the
                      $managerreqd parameter set to true, or use job_assignment::get_all_manager_userids(), depending on the situation. It
                      is also possible to use job_assignment::get_first(), but this is discouraged, because job assignment order can
                      easily be changed, so this is effectively an arbitrary selection.
                   *  totara_get_most_primary_manager has been deprecated but maintains a level of backwards compatibility and will be
                      removed in a future version. Instead, use job_assignment::get_first(), or, preferably, redesign your code to work
                      with multiple managers and call job_assignment::get_all() with the $managerreqd parameter set to true, or
                      job_assignment::get_all_manager_userids().
                   *  totara_update_temporary_manager has been deprecated but maintains a level of backwards compatibility and will be
                      removed in a future version. Instead, use job_assignment::update_temporary_managers().
                   *  totara_unassign_temporary_manager has been deprecated but maintains a level of backwards compatibility and will be
                      removed in a future version. This functionality is now part of the job_assignment class - load the job_assignment
                      object and call update() with an empty tempmanagerjaid.
                   *  totara_get_teamleader has been deprecated but maintains a level of backwards compatibility and will be removed in a
                      future version. Instead, load the job_assignment object and access the teamleaderid property.
                   *  totara_get_appraiser has been deprecated but maintains a level of backwards compatibility and will be removed in a
                      future version. Instead, load the job_assignment object and access the appraiserid property.
                   *  totara_update_temporary_managers has been deprecated but maintains a level of backwards compatibility and will be
                      removed in a future version
                   *  build_nojs_positionpicker has been deprecated but maintains a level of backwards compatibility and will be removed
                      in a future version
                   *  development_plan::get_manager has been deprecated but maintains a level of backwards compatibility and will be
                      removed in a future version
                   *  development_plan::send_alert has been deprecated but maintains a level of backwards compatibility and will be
                      removed in a future version
                   *  rb_source_facetoface_sessions::rb_filter_position_types_list has been deprecated but maintains a level of backwards
                      compatibility and will be removed in a future version
                   *  rb_source_facetoface_sessions::rb_display_position_type has been deprecated but maintains a level of backwards
                      compatibility and will be removed in a future version
                   *  appraisal::get_live_role_assignments has been deprecated but maintains a level of backwards compatibility and will
                      be removed in a future version
                   *  The file totara/hierarchy/prefix/position/assign/manager.php has been deprecated but maintains a level of backwards
                      compatibility and will be removed in a future version. Instead use totara/job/dialog/assign_manager_html.php.
                   *  The file totara/hierarchy/prefix/position/assign/tempmanager.php has been deprecated but maintains a level of
                      backwards compatibility and will be removed in a future version. Instead use
                      totara/job/dialog/assign_tempmanager_html.php.
                   *  pos_can_edit_position_assignment has been deprecated but maintains a level of backwards compatibility and will be
                      removed in a future version. Instead, use totara_job_can_edit_job_assignments.
                   *  pos_get_current_position_data has been deprecated but maintains a level of backwards compatibility and will be
                      removed in a future version. Instead, load the relevant job_assignment object and access the positionid and
                      organisationid properties.
                   *  pos_get_most_primary_position_assignment has been deprecated but maintains a level of backwards compatibility and
                      will be removed in a future version. Instead, it is possible to use job_assignment::get_first(), but this is
                      discouraged, because job assignment order can easily be changed, so this is effectively an arbitrary selection.
                   *  get_position_assignments has been deprecated but maintains a level of backwards compatibility and will be removed in
                      a future version. Instead, use job_assignment::get_all().
                   *  totara_core\task\update_temporary_managers_task has been deprecated but maintains a level of backwards compatibility
                      and will be removed in a future version. Instead, use totara_job\task\update_temporary_managers_task.
                   *  user/positions.php has been deprecated but maintains a level of backwards compatibility and will be removed in a
                      future version. This functionality is now provided in totara/job/jobassignment.php.

                   *  assign_user_position has been deprecated and throws an exception if called. This functionality is now being
                      performed when a job_assignment is created or updated.
                   *  appraisal::get_missingrole_users has been deprecated and throws an exception if called
                   *  appraisal::get_changedrole_users has been deprecated and throws an exception if called
                   *  position::position_label has been deprecated and throws an exception if called. Instead, use
                      position::job_position_label which returns a string formatted " ()".
                   *  The position_assignment class no longer contains any methods and an exception will be thrown from its constructor if
                      instantiated. Instead, job assignments should be managed through the job_assignment class interfaces.
                   *  prog_store_position_assignment has been deprecated and throws an exception if called. Now, when a user's position
                      changes in one of their job assignments, the positionassignmentdate is automatically updated.

                   *  appraisal::validate_roles argument one has been deprecated and is no longer used. Behaviour is now to compare
                      required appraisal roles against corresponding roles in appraisees' job assignments.
                   *  totara_appraisal_renderer::display_appraisal_header argument three has been deprecated and is no longer used. Role
                      assignments are now loaded within this method.
                   *  profile_signup_manager argument three has been changed - this now must be a manager's job assignment id, rather than
                      a manager id.

                   *  pos_add_node_positions_links has been removed. This functionality is now part of totara_job_myprofile_navigation.
                   *  totara_hierarchy_myprofile_navigation has been removed. Instead, use totara_job_myprofile_navigation.
                   *  totara_program_observer::position_updated has been removed. Instead use job_assignment_updated, which is called when
                      the new job_assignment_updated event is triggered.
                   *  mod_facetoface_signup_form::add_position_selection_formelem has been removed
                   *  The JavaScript function rb_load_hierarchy_multi_filters has been removed
                   *  totara/core/js/position.user.js has been removed
                   *  totara/core/classes/event/position_updated.php has been removed, including the the class
                      totara_core\event\position_updated
                   *  totara/core/classes/event/position_viewed.php has been removed, including the the class
                      totara_core\event\position_viewed
                   *  mod/facetoface/classes/event/attendee_position_updated.php has been removed, including the class
                      mod_facetoface\event\attendee_position_updated
                   *  mod/facetoface/attendee_position_form.php has been removed, including the class attendee_position_form
                   *  totara/hierarchy/prefix/position/forms.php has been removed, including the class position_settings_form
                   *  totara/hierarchy/prefix/position/settings.php has been removed, including the function update_pos_settings
                   *  user/positions_form.php has been removed, including the class user_position_assignment_form

                   *  The pos_assignment table was renamed to job_assignment. This maintains record ids, so if you have a table which
                      referenced pos_assignment.id, e.g. "posassignid", then that field does not need to be updated (although you
                      probably want to change the field name and fix the foreign key specification). Several other changes were made to
                      this table, including making idnumber user-unique and cannot be null (and it is editable in the user interface),
                      added fields for temporary manager, added a field for position assignment date (indicates when position was last
                      changed), timevalidfrom and timevalidto renamed to startdate and enddate, managerid and managerpath changed to
                      managerjaid and managerjapath (note that these new fields store job assignment data, not manager ids), and fullname
                      can now be empty (if accessed through the job_assignment class, a default string containing the user-unique idnumber
                      will be returned).
                   *  The pos_assignment_history table was unused so was removed, but only if it contained no data (in case it was in use
                      by some customisation).
                   *  The temporary_manager table has been removed. Temporary manager data was moved into the job_assignment table.
                   *  The prog_pos_assignment table has been removed. The data in this table, which indicates on which date a user was
                      last assigned to their current position, has been moved into the job_assignment table, in the positionassignmentdate
                      field.

                   *  Global constants POSITION_TYPE_PRIMARY, POSITION_TYPE_SECONDARY and POSITION_TYPE_ASPIRATIONAL have all been
                      deprecated, as have the global variables $POSITION_TYPES and $POSITION_CODES.
                   *  The global constant ASSIGNTYPE_MANAGER has been deprecated. This was used for program assignments via manager
                      hierarchy. Custom code using this assignment type will need to be updated to use the managers' job assignments. The
                      new constant used for those assignments is ASSIGNTYPE_MANAGERJA.
    TL-8949        *  Any custom report sources using the old position functions will need to be updated to use the corresponding job
                      assignment function, also any custom columns or filters using the position assignment joins or fields will need to
                      be updated. The add_basic_user_content_options() function is an easy way to add the "show by user", "show by users
                      current position", and "show by users current organisation" content options to a report, just call it inside the
                      sources define_contentoptions() function (see totara/reportbuilder/rb_sources/rb_source_user.php for an example)
                   *  Function add_basic_user_content_options() added to the base source
                   *  Function add_job_assignment_tables_to_joinlist() added to the base source
                   *  Function add_job_assignments_fields_to_columns() added to the base source
                   *  Function add_job_assignments_fields_to_filters() added to the base source
                   *  Function add_position_tables_to_joinlist() has been deprecated, please use  add_job_assignment_tables_to_joinlist()
                      instead
                   *  Function add_position_fields_to_columns() has been deprecated, please use  add_job_assignment_fields_to_columns()
                      instead
                   *  Function add_position_fields_to_filters() has been deprecated, please use  add_job_assignment_fields_to_filters()
                      instead
    TL-9141        *  customfield_datetime::edit_validate_field new method to override parent
                   *  customfield_datetime::edit_save_data new method to override parent
    TL-9156        *  facetoface.allowsignupnotedefault removed unused database field allowsignupnotedefault from facetoface
                   *  facetoface_sessions.availablesignupnote removed unused database field availablesignupnote from facetoface_sessions
    TL-9677        *  totara_appraisal_renderer::display_appraisal_header: argument 3 has been deprecated
                   *  appraisal::validate_roles: argument has been deprecated
                   *  appraisal::get_missingrole_users: has been deprecated and will be removed in a future version
                   *  appraisal::get_changedrole_users: has been deprecated and will be removed in a future version
                   *  appraisal::get_live_role_assignments: has been deprecated and will be removed in a future version
                   *  mdl_appraisal_userassignment.jobassignmentid: new database field to track a linked job assignment to an appraisal
                   *  mdl_appraisal_userassignment.jobassignmentlastmodified: new database field to track if a linked job assignment has
                      be updated
    TL-10597       *  prog_content::get_courseset_groups added a second argument $trimoptional


Database changes:

    TL-6243        Added the ability to automatically create learning plans when new members are added to an audience
    TL-7452        Improved Seminar navigation
    TL-7458        Sign-up periods added to Seminar events
    TL-7459        Added links to further information on each room when signing up
    TL-7463        Added session assets and room improvements
    TL-7465        Added new approval options to Seminars
    TL-7944        Added seminar minimum bookings status and notification
    TL-7963        Added the ability to use custom fields within evidence
    TL-8118        Added the ability to import evidence custom field data in the completion Import tool
    TL-8187        Seminar events can now be cancelled
    TL-8383        Activity completion reaggregated during scheduled task after unlock and delete
    TL-8786        Refactored Seminar asset and room "allow conflicts" data storage
    TL-9156        Removed the setting "Users can enter requests when signing up" from the Seminar module


Deleted files:

    TL-7452        * mod/facetoface/sitenotice.php
                   * mod/facetoface/sitenotice_form.php
                   * mod/facetoface/sitenotices.php
    TL-7453        * mod/facetoface/bulkadd_attendees.php
                   * mod/facetoface/editattendees.html
                   * mod/facetoface/editattendees.php
    TL-7459        * mod/facetoface/amd/build/addpdroom.min.js
                   * mod/facetoface/amd/src/addpdroom.js
                   * mod/facetoface/classes/rb/display/link_f2f_session.php
                   * mod/facetoface/room/ajax/room_save.php
    TL-7465        * mod/facetoface/tests/behat/managerapproval_facetoface.feature
                   * mod/facetoface/tests/behat/selfapproval_facetoface.feature
    TL-7817        * totara/core/js/lib/build/jquery.dataTables.min.js
    TL-7947        * blocks/totara_program_completion/block.js
    TL-7950        * blocks/totara_report_table/module.js
    TL-7951        * elementlibrary/js/competency.item.js
    TL-7959        * totara/reportbuilder/js/instantfilter.js
    TL-7961        * totara/reportbuilder/js/cachenow.js
    TL-7963        * totara/plan/tests/behat/evidence_link.feature
    TL-8188        * totara/core/lib/phpseclib/System/SSH_Agent.php
    TL-8405        * lib/jquery/jquery-1.11.3.min.js
                   * lib/jquery/jquery-2.1.4.min.js
                   * lib/jquery/jquery-migrate-1.2.1.min.js
    TL-8515        * totara/core/classes/totara/menu/mylearning.php
                   * totara/dashboard/tests/behat/dashboard_disable.feature
                   * totara/dashboard/tests/behat/dashboard_home.feature
    TL-8637        * blocks/facetoface/ChangeLog.txt
                   * blocks/facetoface/README.txt
                   * blocks/facetoface/block_facetoface.php
                   * blocks/facetoface/bookinghistory.php
                   * blocks/facetoface/calendar.php
                   * blocks/facetoface/db/access.php
                   * blocks/facetoface/export_form.php
                   * blocks/facetoface/index.php
                   * blocks/facetoface/lang/en/block_facetoface.php
                   * blocks/facetoface/lib.php
                   * blocks/facetoface/mysessions.php
                   * blocks/facetoface/mysignups.php
                   * blocks/facetoface/renderer.php
                   * blocks/facetoface/sessionfilter_form.php
                   * blocks/facetoface/styles.css
                   * blocks/facetoface/tabs.php
                   * blocks/facetoface/tests/behat/mysessions.feature
                   * blocks/facetoface/version.php
    TL-9070        * totara/reportbuilder/filter_dialogs.js
    TL-9192        * totara/core/js/competency.add.js
                   * totara/core/js/competency.item.js
                   * totara/core/js/competency.template.js
    TL-9274        * totara/core/db/pre_any_upgrade.php
    TL-9493        * theme/base/layout/embedded.php
                   * theme/base/layout/frontpage.php
                   * theme/base/layout/report.php
                   * theme/base/pix/fp/add_file.png
                   * theme/base/pix/fp/add_file.svg
                   * theme/base/pix/fp/alias.png
                   * theme/base/pix/fp/alias_sm.png
                   * theme/base/pix/fp/check.png
                   * theme/base/pix/fp/create_folder.png
                   * theme/base/pix/fp/create_folder.svg
                   * theme/base/pix/fp/cross.png
                   * theme/base/pix/fp/dnd_arrow.gif
                   * theme/base/pix/fp/download_all.png
                   * theme/base/pix/fp/download_all.svg
                   * theme/base/pix/fp/help.png
                   * theme/base/pix/fp/help.svg
                   * theme/base/pix/fp/link.png
                   * theme/base/pix/fp/link_sm.png
                   * theme/base/pix/fp/logout.png
                   * theme/base/pix/fp/logout.svg
                   * theme/base/pix/fp/path_folder.png
                   * theme/base/pix/fp/path_folder_rtl.png
                   * theme/base/pix/fp/refresh.png
                   * theme/base/pix/fp/refresh.svg
                   * theme/base/pix/fp/search.png
                   * theme/base/pix/fp/search.svg
                   * theme/base/pix/fp/setting.png
                   * theme/base/pix/fp/setting.svg
                   * theme/base/pix/fp/view_icon_active.png
                   * theme/base/pix/fp/view_icon_active.svg
                   * theme/base/pix/fp/view_list_active.png
                   * theme/base/pix/fp/view_list_active.svg
                   * theme/base/pix/fp/view_tree_active.png
                   * theme/base/pix/fp/view_tree_active.svg
                   * theme/base/pix/horizontal-menu-submenu-indicator.png
                   * theme/base/pix/progress.gif
                   * theme/base/pix/sprite.png
                   * theme/base/pix/vertical-menu-submenu-indicator.png
                   * theme/base/pix/yui2-treeview-sprite-rtl.gif
                   * theme/base/style/admin.css
                   * theme/base/style/autocomplete.css
                   * theme/base/style/blocks.css
                   * theme/base/style/calendar.css
                   * theme/base/style/core.css
                   * theme/base/style/course.css
                   * theme/base/style/dock.css
                   * theme/base/style/editor.css
                   * theme/base/style/filemanager.css
                   * theme/base/style/grade.css
                   * theme/base/style/group.css
                   * theme/base/style/message.css
                   * theme/base/style/pagelayout.css
                   * theme/base/style/question.css
                   * theme/base/style/tabs.css
                   * theme/base/style/templates.css
                   * theme/base/style/totara.css
                   * theme/base/style/totara_jquery_datatables.css
                   * theme/base/style/totara_jquery_treeview.css
                   * theme/base/style/totara_jquery_ui_dialog.css
                   * theme/base/style/user.css
                   * theme/base/templates/core/notification_message.mustache
                   * theme/base/templates/core/notification_problem.mustache
                   * theme/base/templates/core/notification_redirect.mustache
                   * theme/base/templates/core/notification_success.mustache
                   * theme/bootstrapbase/style/font-totara.css


Contributions:

    * Aldo Paradiso at MultaMedio - TL-5143
    * Anthony McLaughlin at Learning Pool - TL-7944
    * Chris Wharton at Catalyst EU - TL-6243
    * Eugene Venter at Catalyst NZ - TL-8023
    * Keelin Devenney at Learning Pool - TL-7458, TL-8187
    * Learning Pool - TL-7466
    * Lee Campbell at Learning Pool - TL-7455
    * Ryan Adams at Learning Pool - TL-7456, TL-7459


*/
