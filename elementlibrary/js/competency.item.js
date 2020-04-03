/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2015 onwards Totara Learning Solutions LTD
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
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package elementlibrary
 */
 M.totara_elementlibmultiselect = M.totara_elementlibmultiselect || {

    Y: null,
    // optional php params and defaults defined here, args passed to init method
    // below will override these values
    config: {
                id:0
            },

    /**
     * module initialisation method called by php js_init_call()
     *
     * @param object    YUI instance
     * @param string    args supplied in JSON format
     */
    init: function(Y, args){
        // save a reference to the Y instance (all of its dependencies included)
        this.Y = Y;

        // if defined, parse args into this module's config object
        if (args) {
            var jargs = Y.JSON.parse(args);
            for (var a in jargs) {
                if (Y.Object.owns(jargs, a)) {
                    this.config[a] = jargs[a];
                }
            }
        }

        // check jQuery dependency is available
        if (typeof $ === 'undefined') {
            throw new Error('M.totara_elementlibmultiselect.init()-> jQuery dependency required for this module to function.');
        }

        ///
        /// Add related competency dialog
        ///
        (function() {
            var url = M.cfg.wwwroot+'/totara/hierarchy/prefix/competency/related/';

            var handler = new totaraDialog_handler_treeview_multiselect();

            var buttonObj = {};
            buttonObj[M.util.get_string('save', 'totara_core')] = function() { handler._dialog.hide(); };
            buttonObj[M.util.get_string('cancel', 'moodle')] = function() { handler._cancel() };

            totaraDialogs[name] = new totaraDialog(
                'related',
                'show-related-dialog',
                {
                    buttons: buttonObj,
                    title: '<h2>'+M.util.get_string('assignrelatedcompetencies', 'totara_hierarchy')+'</h2>'
                },
                url+'find.php?id='+M.totara_elementlibmultiselect.config.id,
                handler
            );
        })();
    }
};
