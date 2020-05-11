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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package repository_url
 */

defined('MOODLE_INTERNAL') || die;

use totara_core\url_validator;

class repository_url_url_validator_testcase extends advanced_testcase {

    protected function setUp() {
        global $CFG;
        require_once($CFG->dirroot . '/repository/url/lib.php');
        parent::setUp();
    }

    public function test_preprocess_param_url() {
        global $CFG;

        $this->resetAfterTest(true);

        $method = new ReflectionMethod('\totara_core\url_validator', 'preprocess_param_url');
        $method->setAccessible(true);

        // Make sure the special characters are encoded properly.
        $url = "http://www.example.com/?whatever='\" \t\n&bbb={1,2}&amp;c=<br>";
        $result = $method->invoke(null, $url);
        $this->assertSame('http://www.example.com/?whatever=%27%22%20%09%0A&bbb=%7B1,2%7D&amp;c=%3Cbr%3E', $result);
        $this->assertSame($url, urldecode($result));

        $this->assertSame('', $method->invoke(null, ''));
        $this->assertSame('ssh://username@hostname:/path', $method->invoke(null, 'ssh://username@hostname:/path'));
        $this->assertSame('http://username@hostname:/path', $method->invoke(null, 'http://username@hostname:/path'));

        // Invalid data passes through without changes.
        $this->assertSame('://', $method->invoke(null, '://'));
        $this->assertSame('aa/bb/:xx', $method->invoke(null, 'aa/bb/:xx'));

        // Leading double slash is fixed.
        $this->assertSame('http://example.com/', $method->invoke(null, '//example.com/'));
        $this->assertSame('http://example.com/', $method->invoke(null, '//example.com/'));
    }

    /**
     * Test encoding of dangerous and incompatible characters in URLs.
     */
    public function test_clean_param_url() {
        // Make sure the special characters are encoded properly.
        $url = "http://www.example.com/?whatever='\" \t\n&bbb={1,2}&amp;c=<br>";
        $result = url_validator::clean_param($url, PARAM_URL);
        $this->assertSame('http://www.example.com/?whatever=%27%22%20%09%0A&bbb=%7B1,2%7D&amp;c=%3Cbr%3E', $result);
        $this->assertSame($url, urldecode($result));

        // Only these 3 protocols are supported.
        $this->assertSame('http://www.example.com/course/view.php?id=1', url_validator::clean_param('http://www.example.com/course/view.php?id=1', PARAM_URL));
        $this->assertSame('https://www.example.com/course/view.php?id=:1', url_validator::clean_param('https://www.example.com/course/view.php?id=:1', PARAM_URL));
        $this->assertSame('ftp://www.example.com/index.html', url_validator::clean_param('ftp://www.example.com/index.html', PARAM_URL));
        $this->assertSame('', url_validator::clean_param('gpher://www.example.com/index.html', PARAM_URL));

        // Protocol case is not important.
        $this->assertSame('HttP://www.example.com/course/view.php?id=1', url_validator::clean_param('HttP://www.example.com/course/view.php?id=1', PARAM_URL));

        $this->assertSame('http://www.example.com/course/view.php?id=1', url_validator::clean_param('//www.example.com/course/view.php?id=1', PARAM_URL));
        $this->assertSame('', url_validator::clean_param('://www.example.com/course/view.php?id=1', PARAM_URL));
        $this->assertSame('www.example.com/course/view.php?id=1', url_validator::clean_param('www.example.com/course/view.php?id=1', PARAM_URL));

        // Ports are allowed.
        $this->assertSame('http://www.example.com:8080/course/view.php?id=1', url_validator::clean_param('http://www.example.com:8080/course/view.php?id=1', PARAM_URL));
        $this->assertSame('https://www.example.com:443/course/view.php?id=1', url_validator::clean_param('https://www.example.com:443/course/view.php?id=1', PARAM_URL));

        // Incomplete URLs should pass.
        $this->assertSame('/course/view.php?id=1', url_validator::clean_param('/course/view.php?id=1', PARAM_URL));
        $this->assertSame('course/view.php?id=1', url_validator::clean_param('course/view.php?id=1', PARAM_URL));

        // Various arguments should be ok, some of them may be URL encoded
        $this->assertSame('http://www.example.com/course/view.php?id=13#test', url_validator::clean_param('http://www.example.com/course/view.php?id=13#test', PARAM_URL));
        $this->assertSame('http://www.example.com/?whatever%5B%5D=abc', url_validator::clean_param('http://www.example.com/?whatever[]=abc', PARAM_URL));
        $this->assertSame('http://www.example.com/?whatever%5B0%5D=abc&%5B1%5D=def', url_validator::clean_param('http://www.example.com/?whatever[0]=abc&[1]=def', PARAM_URL));
        $this->assertSame('/?whatever%5B%5D=abc', url_validator::clean_param('/?whatever[]=abc', PARAM_URL));
        $this->assertSame('/course/view.php?id=%3A1', url_validator::clean_param('/course/view.php?id=:1', PARAM_URL));
        $this->assertSame('course/view.php?id=%3A1', url_validator::clean_param('course/view.php?id=:1', PARAM_URL));

        // mailto: never worked and never will
        $this->assertSame('', url_validator::clean_param('mailto:someone@example.com', PARAM_URL));

        // Non-ascii characters never worked.
        $this->assertSame('', url_validator::clean_param('http://www.example.com/course/view.php?id=škoďák', PARAM_URL));
        $this->assertSame('', url_validator::clean_param('http://www.example.com/course/škoďák.php', PARAM_URL));
        $this->assertSame('', url_validator::clean_param('http://www.example.com/course/view.php#škoďák', PARAM_URL));

        // Broken URLs.
        $this->assertSame('', url_validator::clean_param('://www.example.com/course/view.php?id=1', PARAM_URL));
        $this->assertSame('', url_validator::clean_param(' http://www.example.com/course/view.php?id=1', PARAM_URL));
        $this->assertSame('', url_validator::clean_param('http://', PARAM_URL));
        $this->assertSame('', url_validator::clean_param(' ', PARAM_URL));
        $this->assertSame('', url_validator::clean_param('whatever[]=abc', PARAM_URL));
        $this->assertSame('', url_validator::clean_param('[]', PARAM_URL));
        $this->assertSame('', url_validator::clean_param('{}', PARAM_URL));
    }

    public function test_clean_param_url_preexisting() {
        global $CFG;
        include_once($CFG->dirroot . '/lib/validateurlsyntax.php');

        $oldclean = function ($url) {
            if (validateUrlSyntax($url, 's?H?S?F?E?u-P-a?I?p?f?q?r?')) {
                return $url;
            }
            return '';
        };

        $url = "http://www.example.com/?whatever='\" \t\n&bbb={1,2}&amp;c=<br>";
        $this->assertSame('', $oldclean($url)); // Fixed in new cleaning

        // Only these 3 protocols are supported.
        $this->assertSame('http://www.example.com/course/view.php?id=1', $oldclean('http://www.example.com/course/view.php?id=1'));
        $this->assertSame('https://www.example.com/course/view.php?id=:1', $oldclean('https://www.example.com/course/view.php?id=:1'));
        $this->assertSame('ftp://www.example.com/index.html', $oldclean('ftp://www.example.com/index.html'));
        $this->assertSame('', $oldclean('gpher://www.example.com/index.html'));

        // Protocol case is not important.
        $this->assertSame('HttP://www.example.com/course/view.php?id=1', $oldclean('HttP://www.example.com/course/view.php?id=1'));

        $this->assertSame('', $oldclean('//www.example.com/course/view.php?id=1')); // Fixed in new cleaning
        $this->assertSame('', $oldclean('://www.example.com/course/view.php?id=1'));
        $this->assertSame('www.example.com/course/view.php?id=1', $oldclean('www.example.com/course/view.php?id=1'));

        // Incomplete URLs should pass.
        $this->assertSame('/course/view.php?id=1', $oldclean('/course/view.php?id=1'));
        $this->assertSame('course/view.php?id=1', $oldclean('course/view.php?id=1'));

        // Ports are allowed.
        $this->assertSame('http://www.example.com:8080/course/view.php?id=1', $oldclean('http://www.example.com:8080/course/view.php?id=1'));
        $this->assertSame('https://www.example.com:443/course/view.php?id=1', $oldclean('https://www.example.com:443/course/view.php?id=1'));

        // Various arguments should be ok, some of them may be URL encoded
        $this->assertSame('http://www.example.com/course/view.php?id=13#test', $oldclean('http://www.example.com/course/view.php?id=13#test'));
        $this->assertSame('', $oldclean('http://www.example.com/?whatever[]=abc')); // Fixed in new cleaning
        $this->assertSame('', $oldclean('http://www.example.com/?whatever[0]=abc&[1]=def')); // Fixed in new cleaning
        $this->assertSame('', $oldclean('/?whatever[]=abc')); // Fixed in new cleaning
        $this->assertSame('/course/view.php?id=:1', $oldclean('/course/view.php?id=:1')); // Changed in new cleaning
        $this->assertSame('course/view.php?id=:1', $oldclean('course/view.php?id=:1')); // Changed in new cleaning

        // mailto: never worked and never will
        $this->assertSame('', $oldclean('mailto:someone@example.com'));

        // Non-ascii characters never worked.
        $this->assertSame('', $oldclean('http://www.example.com/course/view.php?id=škoďák'));
        $this->assertSame('', $oldclean('http://www.example.com/course/škoďák.php'));
        $this->assertSame('', $oldclean('http://www.example.com/course/view.php#škoďák'));

        // Broken URLs.
        $this->assertSame('', $oldclean('://www.example.com/course/view.php?id=1'));
        $this->assertSame('', $oldclean(' http://www.example.com/course/view.php?id=1'));
        $this->assertSame('http://', $oldclean('http://')); // Fixed in new cleaning
        $this->assertSame('', $oldclean(' '));
        $this->assertSame('', $oldclean('whatever[]=abc'));
        $this->assertSame('', $oldclean('[]'));
        $this->assertSame('', $oldclean('{}'));
    }

}