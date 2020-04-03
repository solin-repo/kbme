// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A notify function for the Atto editor.
 *
 * @module     moodle-editor_atto-notify
 * @submodule  notify
 * @package    editor_atto
 * @copyright  2014 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var LOGNAME_NOTIFY = 'moodle-editor_atto-editor-notify',
    NOTIFY_INFO = 'info',
    NOTIFY_WARNING = 'warning',
    ALERT_SELECTOR = '.editor_atto_alert';

function EditorNotify() {}

EditorNotify.ATTRS= {
};

EditorNotify.prototype = {

    /**
     * A single Y.Node for the page containing editors with draft content. There is only ever one, and will only ever appear once.
     *
     * @property alertOverlay
     * @type {Node}
     */
    alertOverlay: null,

    /**
     * A single Y.Node for the form containing this editor. There is only ever one - it is replaced if a new message comes in.
     *
     * @property messageOverlay
     * @type {Node}
     */
    messageOverlay: null,

    /**
     * A single timer object that can be used to cancel the hiding behaviour.
     *
     * @property hideTimer
     * @type {timer}
     */
    hideTimer: null,

    /**
     * Initialize the notifications.
     *
     * @method setupNotifications
     * @chainable
     */
    setupNotifications: function() {

        var preload1 = new Image(),
            preload2 = new Image(),
            preload3 = new Image();

        preload1.src = M.util.image_url('i/warning', 'moodle');
        preload2.src = M.util.image_url('i/info', 'moodle');
        preload3.src = M.util.image_url('t/delete', 'moodle');

        return this;
    },

    /**
     * Creates and shows an alert to notify the User of drafted content, intended to display
     * as though at a page level. Multiple instances of Editor may try and create an alert
     * but only one will ever be used.
     *
     * TODO: abstract /course/dndupload.js and this implementation of growl notifications
     *
     * @method showAlert
     * @param {String} message The translated message (use get_string)
     * @param {String} type Must be either "info", "warning" or "danger"
     */
     showAlert: function(message, type) {

        this.alertOverlay = Y.one(ALERT_SELECTOR);

        // We should only ever see one global notification, do nothing if the Node already exists
        if (this.alertOverlay === null) {

            var alertContainer = Y.one('#page-content');
            if (alertContainer === null) {
                Y.log('Atto could not find a suitable page level Node to append an alert to!', 'debug', LOGNAME_NOTIFY);
                return;
            }

            this.alertOverlay = Y.Node.create('<div class="editor_atto_alert alert alert-' + type + " " +
                                      'role="alert" aria-live="assertive">' +
                                        '<span class="icon">' +
                                            '<img src="' + M.util.image_url('t/delete', 'moodle') +
                                                '" alt="' + M.util.get_string('closebuttontitle', 'moodle') + '"/>' +
                                        '</span>' +
                                        message + '</div>');

            alertContainer.prepend(this.alertOverlay);

            this.alertOverlay.once('click', this.hideAlert, this);

            // Growl-style notification, positioned similarly to course page growls to
            // catch the eye above main navigation (defaulting to 0 depending on themed
            // position value) and fixed in place until dismissed.
            var styletop,
                styletopunit;

            styletop = this.alertOverlay.getStyle('top') || '0';
            styletopunit = styletop.replace(/^\d+/, '');
            styletop = parseInt(styletop.replace(/\D*$/, ''), 10);

            YUI().use('anim', function (Y) {

                var fadein = new Y.Anim({
                    node: ALERT_SELECTOR,
                    from: {
                        opacity: 0.0,
                        top: (styletop - 30).toString() + styletopunit
                    },

                    to: {
                        opacity: 1.0,
                        top: styletop.toString() + styletopunit
                    },
                    duration: 0.5
                });
                fadein.run();
            });

        }
     },

    /**
     * Hide the currently displayed notification alert.
     *
     * @method hideAlert
     */
    hideAlert: function () {

        var styletop,
            styletopunit;

        // Reverse the fixed top positioning values, defaulting to 0 depending
        // on themed position value (resulting in no apparent vertical movement)
        styletop = this.alertOverlay.getStyle('top') || '0';
        styletopunit = styletop.replace(/^\d+/, '');
        styletop = parseInt(styletop.replace(/\D*$/, ''), 10);

        YUI().use('anim', function (Y) {

            var fadeout = new Y.Anim({
                node: ALERT_SELECTOR,
                from: {
                    opacity: 1.0,
                    top: styletop.toString() + styletopunit
                },

                to: {
                    opacity: 0.0,
                    top: (styletop - 30).toString() + styletopunit
                },
                duration: 0.5
            });

            fadeout.run();

            fadeout.on('end', function() {
                Y.one(ALERT_SELECTOR).remove(true);
            });

        });
    },

    /**
     * Show a notification in a floaty overlay somewhere in the atto editor text area.
     *
     * @method showMessage
     * @param {String} message The translated message (use get_string)
     * @param {String} type Must be either "info" or "warning"
     * @param {Number} [timeout] Optional time in milliseconds to show this message for.
     * @chainable
     */
    showMessage: function(message, type, timeout) {

        var messageTypeIcon = '',
            intTimeout,
            bodyContent;

        // Create a message container if there is not one already.
        if (this.messageOverlay === null) {
            this.messageOverlay = Y.Node.create('<div class="editor_atto_notification"></div>');

            this.messageOverlay.hide(true);
            this.textarea.get('parentNode').append(this.messageOverlay);

            this.messageOverlay.on('click', this.hideMessage, this);
        }

        // Populate message contents, the icon may/not be delayed in its rendering.
        if (type === NOTIFY_WARNING) {
            messageTypeIcon = '<img src="' +
                              M.util.image_url('i/warning', 'moodle') +
                              '" alt="' + M.util.get_string('warning', 'moodle') + '"/>';
        } else if (type === NOTIFY_INFO) {
            messageTypeIcon = '<img src="' +
                              M.util.image_url('i/info', 'moodle') +
                              '" alt="' + M.util.get_string('info', 'moodle') + '"/>';
        } else {
            Y.log('Invalid message type specified: ' + type + '. Must be either "info" or "warning".', 'debug', LOGNAME_NOTIFY);
        }

        // Convert class to atto_info (for example).
        bodyContent = Y.Node.create('<div class="atto_' + type + ' alert alert-' + type + '" role="alert" aria-live="assertive">' +
                                        messageTypeIcon + ' ' +
                                        Y.Escape.html(message) +
                                        '</div>');

        // Replace current message content with new.
        this.messageOverlay.empty();
        this.messageOverlay.append(bodyContent);
        this.messageOverlay.show(true);

        // Tidy up previous autohide timer to avoid hide collisions.
        if (this.hideTimer !== null) {
            this.hideTimer.cancel();
        }

        // reset hide timout, if applicable
        if (timeout > 0) {

            // Parse the timeout value.
            intTimeout = parseInt(timeout, 10);
            if (intTimeout <= 0) {
                intTimeout = 60000;
            }

            // Create a new timer for autohide.
            this.hideTimer = Y.later(intTimeout, this, function() {
                Y.log('Hide Atto notification.', 'debug', LOGNAME_NOTIFY);
                this.hideMessage();
            });
        }

        return this;
    },

    /**
     * Hide the currently displayed notification message.
     *
     * @method hideMessage
     * @chainable
     */
    hideMessage: function() {

        if (this.hideTimer !== null) {
            this.hideTimer.cancel();
            this.hideTimer = null;
        }

        Y.log('Hide Atto notification.', 'debug', LOGNAME_NOTIFY);
        this.messageOverlay.hide(true);

        return this;
    }
};

Y.Base.mix(Y.M.editor_atto.Editor, [EditorNotify]);
