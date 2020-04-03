/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totaralearning.com>
 * @package totara_program
 */

define(['jquery', 'core/str', 'core/config'], function($, mdlstrings, mdlcfg) {
    var check_completion = {

        config: {},
        /**
        * module initialisation method called by php js_call_amd()
        *
        * @param string    args supplied in JSON format
        */
        init : function(args) {

            if (args) {
                check_completion.config = $.parseJSON(args);
            }

            $('.problemaggregation a').on('click', function(e) {
                modalConfirm($(this).attr('href'), 'fixconfirmsome');
                return false;
            });

            $('.problemsolution a').on('click', function(e) {
                modalConfirm($(this).attr('href'), 'fixconfirmone');
                return false;
            });
        }
    };

    function modalConfirm(url, scope) {
        var dialogue = new M.core.dialogue({
            headerContent: M.util.get_string('fixconfirmtitle', 'totara_program'),
            bodyContent  : M.util.get_string(scope, 'totara_program'),
            width        : 500,
            centered     : true,
            modal        : true,
            visible      : true,
            render       : true
        });
        dialogue.addButton({
            label: M.util.get_string('yes', 'moodle'),
            section: Y.WidgetStdMod.FOOTER,
            action : function (e) {
                window.location.href = url;
                dialogue.destroy(true);
            }
        });

        dialogue.addButton({
            label: M.util.get_string('no','moodle'),
            section: Y.WidgetStdMod.FOOTER,
            action : function (e) {
                e.preventDefault();
                dialogue.destroy(true);
            }
        });
        $('.moodle-dialogue-ft button').removeClass('yui3-button');
    }

    return check_completion;
});
