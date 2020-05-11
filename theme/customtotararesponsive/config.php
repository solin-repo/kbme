<?php
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
 * This theme has been deprecated.
 * We strongly recommend basing all new themes on roots and basis.
 * This theme will be removed from core in a future release at which point
 * it will no longer receive updates from Totara.
 *
 * @deprecated since Totara 9
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @author Brian Barnes <brian.barnes@totaralms.com>
 * @package totara
 * @subpackage theme
 */

$THEME->name = 'customtotararesponsive';
$THEME->parents = array('standardtotararesponsive', 'bootstrapbase', 'base');
$THEME->sheets = array(
    'core',     /** Must come first**/
    'navigation',
    'admin',
    'blocks',
    'calendar',
    'course',
    'user',
    'dock',
    'grade',
    'message',
    'modules',
    'question',
    'pagelayout',
    'settings'
);

$THEME->enable_dock = true;
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_customtotararesponsive_process_css';
