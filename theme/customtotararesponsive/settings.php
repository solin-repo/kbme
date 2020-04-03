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
 * @package theme
 * @subpackage customtotararesponsive
 */

/**
 * Settings for the customtotararesponsive theme
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Favicon file setting.
    $name = 'theme_customtotararesponsive/favicon';
    $title = new lang_string('favicon', 'theme_customtotararesponsive');
    $description = new lang_string('favicondesc', 'theme_customtotararesponsive');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon', 0, array('accepted_types' => '.ico'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Logo file setting.
    $name = 'theme_customtotararesponsive/logo';
    $title = new lang_string('logo', 'theme_customtotararesponsive');
    $description = new lang_string('logodesc', 'theme_customtotararesponsive');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Logo alt text.
    $name = 'theme_customtotararesponsive/alttext';
    $title = new lang_string('alttext', 'theme_customtotararesponsive');
    $description = new lang_string('alttextdesc', 'theme_customtotararesponsive');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Site text color
    $name = 'theme_customtotararesponsive/textcolor';
    $title = get_string('textcolor', 'theme_customtotararesponsive');
    $description = get_string('textcolor_desc', 'theme_customtotararesponsive');
    $default = '#333366';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Link colour setting.
    $name = 'theme_customtotararesponsive/linkcolor';
    $title = new lang_string('linkcolor', 'theme_customtotararesponsive');
    $description = new lang_string('linkcolordesc', 'theme_customtotararesponsive');
    $default = '#087BB1';
    $previewconfig = array('selector' => 'a', 'style' => 'color');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    //Link visited colour setting.
    $name = 'theme_customtotararesponsive/linkvisitedcolor';
    $title = new lang_string('linkvisitedcolor', 'theme_customtotararesponsive');
    $description = new lang_string('linkvisitedcolordesc', 'theme_customtotararesponsive');
    $default = '#087BB1';
    $previewconfig = array('selector' => 'a:visited', 'style' => 'color');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Button colour setting.
    $name = 'theme_customtotararesponsive/buttoncolor';
    $title = new lang_string('buttoncolor','theme_customtotararesponsive');
    $description = new lang_string('buttoncolordesc', 'theme_customtotararesponsive');
    $default = '#E6E6E6';
    $previewconfig = array('selector'=>'input[\'type=submit\']]', 'style'=>'background-color');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Site content background color.
    $name = 'theme_customtotararesponsive/bodybackground';
    $title = get_string('bodybackground', 'theme_customtotararesponsive');
    $description = get_string('bodybackground_desc', 'theme_customtotararesponsive');
    $default = '#FFFFFF';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Background image setting.
    $name = 'theme_customtotararesponsive/backgroundimage';
    $title = get_string('backgroundimage', 'theme_customtotararesponsive');
    $description = get_string('backgroundimage_desc', 'theme_customtotararesponsive');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'backgroundimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Background repeat setting.
    $name = 'theme_customtotararesponsive/backgroundrepeat';
    $title = get_string('backgroundrepeat', 'theme_customtotararesponsive');
    $description = get_string('backgroundrepeat_desc', 'theme_customtotararesponsive');;
    $default = 'repeat';
    $choices = array(
        '0' => get_string('default'),
        'repeat' => get_string('backgroundrepeatrepeat', 'theme_customtotararesponsive'),
        'repeat-x' => get_string('backgroundrepeatrepeatx', 'theme_customtotararesponsive'),
        'repeat-y' => get_string('backgroundrepeatrepeaty', 'theme_customtotararesponsive'),
        'no-repeat' => get_string('backgroundrepeatnorepeat', 'theme_customtotararesponsive'),
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Background position setting.
    $name = 'theme_customtotararesponsive/backgroundposition';
    $title = get_string('backgroundposition', 'theme_customtotararesponsive');
    $description = get_string('backgroundposition_desc', 'theme_customtotararesponsive');
    $default = '0';
    $choices = array(
        '0' => get_string('default'),
        'left_top' => get_string('backgroundpositionlefttop', 'theme_customtotararesponsive'),
        'left_center' => get_string('backgroundpositionleftcenter', 'theme_customtotararesponsive'),
        'left_bottom' => get_string('backgroundpositionleftbottom', 'theme_customtotararesponsive'),
        'right_top' => get_string('backgroundpositionrighttop', 'theme_customtotararesponsive'),
        'right_center' => get_string('backgroundpositionrightcenter', 'theme_customtotararesponsive'),
        'right_bottom' => get_string('backgroundpositionrightbottom', 'theme_customtotararesponsive'),
        'center_top' => get_string('backgroundpositioncentertop', 'theme_customtotararesponsive'),
        'center_center' => get_string('backgroundpositioncentercenter', 'theme_customtotararesponsive'),
        'center_bottom' => get_string('backgroundpositioncenterbottom', 'theme_customtotararesponsive'),
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Background fixed setting.
    $name = 'theme_customtotararesponsive/backgroundfixed';
    $title = get_string('backgroundfixed', 'theme_customtotararesponsive');
    $description = get_string('backgroundfixed_desc', 'theme_customtotararesponsive');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Main content background color.
    $name = 'theme_customtotararesponsive/contentbackground';
    $title = get_string('contentbackground', 'theme_customtotararesponsive');
    $description = get_string('contentbackground_desc', 'theme_customtotararesponsive');
    $default = '#FFFFFF';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Page header background colour setting.
    $name = 'theme_customtotararesponsive/headerbgc';
    $title = new lang_string('headerbgc', 'theme_customtotararesponsive');
    $description = new lang_string('headerbgcdesc', 'theme_customtotararesponsive');
    $default = '#F5F5F5';
    $previewconfig = array('selector' => '#page-header', 'style' => 'backgroundColor');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Footnote setting.
    $name = 'theme_customtotararesponsive/footnote';
    $title = get_string('footnote', 'theme_customtotararesponsive');
    $description = get_string('footnotedesc', 'theme_customtotararesponsive');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Custom CSS file.
    $name = 'theme_customtotararesponsive/customcss';
    $title = new lang_string('customcss','theme_customtotararesponsive');
    $description = new lang_string('customcssdesc', 'theme_customtotararesponsive');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
