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
 * @author Paul Walker <paul.walker@catalyst-eu.net>
 * @author Brian Barnes <brian.barnes@totaralms.com>
 * @author Onno Schuit <onno@solin.nl>
 * @package totara
 * @subpackage theme
 */

$THEME->name = 'kiwibasis';
$THEME->parents = array('basis', 'roots', 'base');
$THEME->sheets = array('kiwibasis', 'settings-noprocess');
//$THEME->sheets = array('settings-noprocess');
$THEME->csspostprocess = 'theme_kiwibasis_process_css';

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->parents_exclude_sheets = array(
    'roots' => array('totara', 'totara-rtl'),
    'base' => array('flexible-icons'),
);
