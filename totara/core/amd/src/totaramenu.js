/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2016 onwards Totara Learning Solutions LTD
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
 * @author Brian Barnes <brian.barnes@totaralearning.com>
 * @author Joby Harding <joby.harding@totaralearning.com>
 * @package totara_core
 */

define(['jquery'], function($) {

    /**
     * Set focus when tabbing through menu.
     *
     * @param {jQuery|String} $menu Element or element collection or query selector.
     */
    var _setFocus = function($menu) {
        if (typeof $menu === 'string') {
            $menu = $($menu);
        }

        $menu.on('focus', '> ul > li > a', function() {
            var $focusedElement = $(this);
            $focusedElement.closest('ul')
                .find('ul')
                .removeAttr('style');
            $focusedElement.siblings('ul')
                .show();
        });
    };

    return {
        setFocus: _setFocus
    };

});