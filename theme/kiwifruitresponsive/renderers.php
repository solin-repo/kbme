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
 * @author Brian Barnes <brian.barnes@totaralms.com>
 * @package totara
 * @subpackage theme
 */

/**
 * Overriding core rendering functions for kiwifruitresponsive
 */
class theme_kiwifruitresponsive_core_renderer extends theme_standardtotararesponsive_core_renderer {
    public function kiwifruit_header() {
        global $OUTPUT, $PAGE, $CFG, $SITE;
        $output = '';
        $output .= html_writer::start_tag('header');
        $output .= html_writer::start_tag('div', array('id' => 'main-menu'));

        // Small responsive button.
        $output .= $this->responsive_button();

        // Find the logo.
        if (!empty($PAGE->theme->settings->frontpagelogo)) {
            $logourl = $PAGE->theme->setting_file_url('frontpagelogo', 'frontpagelogo');
            $logoalt = get_string('logoalt', 'theme_kiwifruitresponsive', $SITE->fullname);
        } else if (!empty($PAGE->theme->settings->logo)) {
            $logourl = $PAGE->theme->setting_file_url('logo', 'logo');
            $logoalt = get_string('logoalt', 'theme_kiwifruitresponsive', $SITE->fullname);
        } else {
            $logourl = $OUTPUT->pix_url('logo', 'theme');
            $logoalt = get_string('totaralogo', 'theme_standardtotararesponsive');
        }

        if (!empty($PAGE->theme->settings->alttext)) {
            $logoalt = format_string($PAGE->theme->settings->alttext);
        }

        if ($logourl) {
            $logo = html_writer::empty_tag('img', array('src' => $logourl, 'alt' => $logoalt));
            $output .= html_writer::tag('a', $logo, array('href' => $CFG->wwwroot, 'class' => 'logo'));
        }

        // The menu.
        $output .= html_writer::start_tag('div', array('id' => 'totaramenu', 'class' => 'nav-collapse'));
        if (empty($PAGE->layout_options['nocustommenu'])) {
            $custommenu = $OUTPUT->custom_menu();
            if ($custommenu) {
                $output .= $custommenu;
            } else {
                $menudata = totara_build_menu();
                $totara_core_renderer = $PAGE->get_renderer('totara_core');
                $totaramenu = $totara_core_renderer->print_totara_menu($menudata);
                $output .= $totaramenu;
            }
        }

        // Profile Menu.
        $output.= $OUTPUT->user_menu();

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('header');
        return $output;
    }

    public function responsive_button() {
        $attrs = array(
            'class' => 'btn btn-navbar',
            'data-toggle' => 'collapse',
            'data-target' => '.nav-collapse, .langmenu',
            'href' => '#'
        );
        $output = html_writer::start_tag('a', $attrs);
        $output .= html_writer::tag('span', '', array('class' => 'icon-bar')); // Chrome doesn't like self closing spans.
        $output .= html_writer::tag('span', '', array('class' => 'icon-bar'));
        $output .= html_writer::tag('span', '', array('class' => 'icon-bar'));
        $output .= html_writer::tag('span', get_string('expand'), array('class' => 'accesshide'));
        $output .= html_writer::end_tag('a');

        return $output;
    }

    /**
     * Gets HTML for the page heading.
     *
     * @since Moodle 2.5.1 2.6
     * @param string $tag The tag to encase the heading in. h1 by default.
     * @return string HTML.
     */
    public function page_heading($tag = 'h1') {
        return html_writer::tag($tag, $this->page->heading, array('id' => 'pageheading'));
    }
}
