<?php
/**
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package totara_core
 */

namespace totara_core;

/**
 * This class exposes the URL validation as it was improved in Totara 12.
 * It is opt in, and can be used in place of clean_param($param, PARAM_URL)
 * The functionality differs significantly from the current PARAM_URL so use
 * this only where required.
 *
 * @since Totara 11.25, 10.31, and 9.42
 * @deprecated NOTE: This does not exist in Totara 12.
 */
final class url_validator {

    /**
     * Cleans a URL in the same way it would be cleaned in Totara 12.
     * @param string $param
     * @param string $type
     * @return string
     */
    public static function clean_param($param, $type) {
        global $CFG;

        if ($type !== PARAM_URL) {
            throw new \coding_exception('This version of clean param only handles PARAM_URL');
        }

        $param = fix_utf8($param);
        if ($param === '') {
            return '';
        }
        if (substr($param, 0, 1) === ':') {
            // '://wwww.example.com/' urls were never allowed.
            return '';
        }
        $param = self::preprocess_param_url($param);
        if (preg_match('/^[a-z]+:/i', $param)) {
            // Totara: the validateUrlSyntax() does not support extended characters,
            //         that means we can use native PHP url validation without risk of regressions
            //         to improve security, but only for full URLs.
            $param = filter_var($param, FILTER_VALIDATE_URL);
            if ($param === false) {
                return '';
            }
        } else {
            // Totara: Colons are not needed in relative URLs.
            $param = str_replace(':', '%3A', $param);
        }
        include_once($CFG->dirroot . '/lib/validateurlsyntax.php');
        // Totara: mailto never worked here because of 'u-', instead of fixing it was removed.
        if (!empty($param) && validateUrlSyntax($param, 's?H?S?F?E-u-P-a?I?p?f?q?r?')) {
            // All is ok, param is respected.
        } else {
            // Not really ok.
            $param = '';
        }
        return $param;
    }

    /**
     * Fix some common issues that make URLs incompatible with PARAM_URL cleaning.
     *
     * @internal intended to be used from clean_param() above.
     *
     * @param string $url
     * @return string
     */
    private static function preprocess_param_url($url) {
        $url = (string)$url;
        if ($url === '') {
            return '';
        }
        if (substr($url, 0, 1) === ':') {
            // Invalid, nothing to fix anything, it will not pass URL cleaning.
            return $url;
        }
        if (substr($url, 0, 2) === '//') {
            // Fix protocol relative URLs, we know what this site is using.
            if (is_https()) {
                $url = 'https:' . $url;
            } else {
                $url = 'http:' . $url;
            }
        }

        // Encode dangerous and incompatible characters.
        $url = str_replace(
            ['"',   "'",   '[',   ']',   ' ',   "\n",  "\t",  '{',   '}',   '<',   '>'],
            ['%22', '%27', '%5B', '%5D', '%20', '%0A', '%09', '%7B', '%7D', '%3C', '%3E'],
            $url);

        // NOTE: in the future we may add encoding of non-unicode characters in URL right here.

        if (preg_match('/^[a-z]+:/i', $url)) {
            $filtered = filter_var($url, FILTER_VALIDATE_URL);
            if ($filtered !== false) {
                // PHP docs do not specify if the returned value is ever changed, but let's assume it might in the future.
                $url = $filtered;
            }
        }

        return $url;
    }

}
