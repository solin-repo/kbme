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
 * @author Eugene Venter <eugene@catalyst.net.nz>
 *
 * @package block
 * @subpackage totara_program_completion
 */

M.block_totara_program_completion = M.block_totara_program_completion || {
    Y: null,

    // Optional php params and defaults defined here, args passed to init method
    // below will override these values.
    config: {},

    init: function(Y, args) {
        // Save a reference to the Y instance (all of its dependencies included).
        this.Y = Y;

        // If defined, parse args into this module's config object.
        if (args) {
            var jargs = Y.JSON.parse(args);
            for (var a in jargs) {
                if (Y.Object.owns(jargs, a)) {
                    this.config[a] = jargs[a];
                }
            }
        }

        var instanceid = this.config.instanceid;

        // Check jQuery dependency is available.
        if (typeof $ === 'undefined') {
            throw new Error('M.block_totara_totara_completion.init()-> jQuery dependency required for this module to function.');
        }

        $('.block-totara-prog-completion-morelink'+instanceid).on('click', function (e) {
            e.preventDefault();
            $('.block-prog-completions-list .more'+instanceid).toggle();
            if ($(this).text() == M.util.get_string('more', 'block_totara_program_completion')) {
                $(this).text(M.util.get_string('less', 'block_totara_program_completion'));
            } else {
                $(this).text(M.util.get_string('more', 'block_totara_program_completion'));
            }
        });
    }
}
