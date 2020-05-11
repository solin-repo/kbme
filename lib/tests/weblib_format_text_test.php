<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for format_text defined in weblib.php.
 *
 * @package   core
 * @category  test
 * @copyright 2015 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Unit tests for format_text defined in weblib.php.
 *
 * @copyright 2015 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class core_weblib_format_text_testcase extends advanced_testcase {

    /**
     * This function is used to fix inconsistent behaviour of format_text across operating systems when cleaning markup with
     * multiple newlines.
     *
     * In OSX multiple continuous newlines are preserved. In Linux they are not.
     *
     * @param string $text
     * @return string
     */
    private function fix_newlines(string $text): string {
        // We don't replace with another character here, the regex problem means multiple newlines are left in OSX and reduced in
        // linux, the character replacement is therefor inconsistent.
        $text = str_replace("\n", '', $text);
        return $text;
    }

    public function test_option_filter_at_system_context() {
        global $CFG;

        $CFG->filter_censor_badwords = 'one,red';
        filter_set_global_state('censor', TEXTFILTER_ON);

        $text = 'I have one red balloon';
        $censored_html = 'I have <span class="censoredtext" title="one">**</span> <span class="censoredtext" title="red">**</span> balloon';
        $censored_plain = $text; // Plain text is NEVER filtered!
        $censored_markdown = "<p>I have <span class=\"censoredtext\" title=\"one\">**</span> <span class=\"censoredtext\" title=\"red\">**</span> balloon</p>\n";
        $censored_moodle = '<div class="text_to_html">I have <span class="censoredtext" title="one">**</span> <span class="censoredtext" title="red">**</span> balloon</div>';

        // Filters turned on.
        $options = ['filter' => true];
        self::assertSame($censored_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($censored_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($censored_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($censored_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Filters turned off.
        $options = ['filter' => false];
        self::assertSame($text, format_text($text, FORMAT_HTML, $options));
        self::assertSame($text, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame('<p>' . $text . "</p>\n", format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame('<div class="text_to_html">' . $text . '</div>', format_text($text, FORMAT_MOODLE, $options));

        // Filters default (on).
        $options = [];
        self::assertSame($censored_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($censored_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($censored_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($censored_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_option_filter_at_course_context_filter() {
        global $CFG;

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $CFG->filter_censor_badwords = 'one,red';
        filter_set_local_state('censor', $context->id, TEXTFILTER_ON);

        $text = 'I have one red balloon';

        $expected_html = 'I have one red balloon';
        $expected_plain = 'I have one red balloon';
        $expected_markdown = "<p>I have one red balloon</p>\n";
        $expected_moodle = '<div class="text_to_html">I have one red balloon</div>';

        $censored_html = 'I have <span class="censoredtext" title="one">**</span> <span class="censoredtext" title="red">**</span> balloon';
        $censored_plain = $text; // Plain text is NEVER filtered!
        $censored_markdown = "<p>I have <span class=\"censoredtext\" title=\"one\">**</span> <span class=\"censoredtext\" title=\"red\">**</span> balloon</p>\n";
        $censored_moodle = '<div class="text_to_html">I have <span class="censoredtext" title="one">**</span> <span class="censoredtext" title="red">**</span> balloon</div>';

        // Filters turned on.
        $options = ['filter' => true, 'context' => \context_system::instance()];
        self::assertSame($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        $options = ['filter' => true, 'context' => $context];
        self::assertSame($censored_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($censored_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($censored_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($censored_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Filters turned off.
        $options = ['filter' => false, 'context' => \context_system::instance()];
        self::assertSame($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        $options = ['filter' => false, 'context' => $context];
        self::assertSame($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Filters default (on).
        $options = ['context' => \context_system::instance()];
        self::assertSame($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        $options = ['context' => $context];
        self::assertSame($censored_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($censored_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($censored_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($censored_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Finally test with the legacy courseid context arg.
        $options = [];
        self::assertSame($censored_html, format_text($text, FORMAT_HTML, $options, $course->id));
        self::assertSame($censored_plain, format_text($text, FORMAT_PLAIN, $options, $course->id));
        self::assertSame($censored_markdown, format_text($text, FORMAT_MARKDOWN, $options, $course->id));
        self::assertSame($censored_moodle, format_text($text, FORMAT_MOODLE, $options, $course->id));
    }

    public function test_utf8_thai() {
        filter_set_global_state('censor', TEXTFILTER_ON);

        $text = 'ฉันนักพัฒนาที่ Totara ขอให้คุณดี';

        $expected_html = $text;
        $expected_plain = $text;
        $expected_markdown = '<p>' . $text . "</p>\n";
        $expected_moodle = '<div class="text_to_html">' . $text . '</div>';

        self::assertSame($expected_html, format_text($text, FORMAT_HTML));
        self::assertSame($expected_plain, format_text($text, FORMAT_PLAIN));
        self::assertSame($expected_markdown, format_text($text, FORMAT_MARKDOWN));
        self::assertSame($expected_moodle, format_text($text, FORMAT_MOODLE));
    }

    public function test_ut8_anglo_saxan() {
        filter_set_global_state('censor', TEXTFILTER_ON);

        $chars = 'ᚠᛁᛋᚳ᛫ᚠᛚᚩᛞᚢ᛫ᚪᚻᚩᚠᚩᚾᚠᛖᚱᚷ ᛖᚾᛒᛖᚱᛁᚷ ᚹᚪᚱᚦᚷᚪ᛬ᛋᚱᛁᚳᚷᚱᚩᚱᚾᚦᚫᚱᚻᛖᚩᚾᚷᚱᛖᚢᛏᚷᛁᛋᚹᚩᛗ ᚻ' .
            'ᚱᚩᚾᚫᛋᛒᚪᚾ ᛗᚫᚷᛁᚠᛁᛋᚳ᛫ᚠᛚᚩᛞᚢ᛫ᚪᚻᚩᚠᚩᚾᚠᛖᚱᚷ ᛖᚾᛒᛖᚱᛁᚷ ᚹᚪᚱᚦᚷᚪ᛬ᛋᚱᛁᚳᚷᚱᚩᚱᚾᚦᚫᚱᚻᛖᚩᚾᚷᚱᛖᚢᛏᚷᛁᛋᚹᚩᛗ ᚻᚱᚩᚾᚫᛋᛒᚪᚾ ᛗᚫᚷᛁ';
        $text = '<a href="https://en.wikipedia.org/wiki/Franks_Casket#ᛖᚾᛒᛖᚱᛁᚷ">'.$chars.'</a>';
        $options = ['newlines' => true, 'para' => true, 'blanktarget' => true];

        $expected_html = '<a href="https://en.wikipedia.org/wiki/Franks_Casket#%E1%9B%96%E1%9A%BE%E1%9B%92%E1%9B%96%E1%9A%B1%E1%9B%81%E1%9A%B7" target="_blank" rel="noreferrer">' . $chars . '</a>';
        $expected_plain = '<p>&lt;a href="https://en.wikipedia.org/wiki/Franks_Casket#ᛖᚾᛒᛖᚱᛁᚷ"&gt;' . $chars . '&lt;/a&gt;</p>';
        $expected_markdown = '<p><a href="https://en.wikipedia.org/wiki/Franks_Casket#%E1%9B%96%E1%9A%BE%E1%9B%92%E1%9B%96%E1%9A%B1%E1%9B%81%E1%9A%B7" target="_blank" rel="noreferrer">' . $chars . '</a></p>';
        $expected_moodle = '<div class="text_to_html"><a href="https://en.wikipedia.org/wiki/Franks_Casket#%E1%9B%96%E1%9A%BE%E1%9B%92%E1%9B%96%E1%9A%B1%E1%9B%81%E1%9A%B7" target="_blank" rel="noreferrer">' . $chars . '</a></div>';

        self::assertSame($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_utf8_thors_map_ruins() {
        filter_set_global_state('censor', TEXTFILTER_ON);

        $text = 'ᛋᛏᚫᚾᛞ ᛒᚣ ᚦᛖ ᚷᚱᛖᚣ ᛋᛏᚩᚾᛖ ᚻᚹᛁᛚᛖ ᚦᛖ ᚦᚱᚢᛋᚻ ᚾᚩᚳᛋ ᚫᚾᛞ ᚦᛖ ᛋᛖᛏᛏᛁᚾᚷ ᛋᚢᚾ ᚹᛁᚦ ᚦᛖ ᛚᚫᛋᛏ ᛚᛁᚷᚻᛏ ᚩᚠ ᛞᚢᚱᛁᚾᛋ ᛞᚫᚣ ᚹᛁᛚᛚ ᛋᚻᛁᚾᛖ ᚢᛈᚩᚾ ᚦᛖ ᚳᛖᚣᚻᚩᛚᛖ';
        $options = ['newlines' => true, 'para' => true, 'blanktarget' => true];

        $expected_html = '<p>' . $text . '</p>';
        $expected_plain = '<p>' . $text . '</p>';
        $expected_markdown = '<p>' . $text . '</p>';
        $expected_moodle = '<div class="text_to_html">' . $text . '</div>';

        self::assertSame($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertSame($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertSame($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertSame($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_empty_values() {
        self::assertSame('', format_text(''));
        self::assertSame('', format_text('', FORMAT_HTML));
        self::assertSame('', format_text('', FORMAT_PLAIN));
        self::assertSame('', format_text('', FORMAT_MARKDOWN));
        self::assertSame('', format_text('', FORMAT_MOODLE));

        self::assertSame('', format_text(null));
        self::assertSame('', format_text(null, FORMAT_HTML));
        self::assertSame('', format_text(null, FORMAT_PLAIN));
        self::assertSame('', format_text(null, FORMAT_MARKDOWN));
        self::assertSame('', format_text(null, FORMAT_MOODLE));

        self::assertSame('<div class="text_to_html">0</div>', format_text(0));
        self::assertSame('0', format_text(0, FORMAT_HTML));
        self::assertSame('0', format_text(0, FORMAT_PLAIN));
        self::assertSame("<p>0</p>\n", format_text(0, FORMAT_MARKDOWN));
        self::assertSame('<div class="text_to_html">0</div>', format_text(0, FORMAT_MOODLE));

        self::assertSame('<div class="text_to_html">0</div>', format_text('0'));
        self::assertSame('0', format_text('0', FORMAT_HTML));
        self::assertSame('0', format_text('0', FORMAT_PLAIN));
        self::assertSame("<p>0</p>\n", format_text('0', FORMAT_MARKDOWN));
        self::assertSame('<div class="text_to_html">0</div>', format_text('0', FORMAT_MOODLE));
    }

    public function test_removal_of_onclick_alert() {
        $text = 'I\'m the needle<a onclick="alert(1)">Hack</a>';
        $expected_html = 'I\'m the needle<a>Hack</a>';
        $expected_plain = 'I&#039;m the needle&lt;a onclick=&quot;alert(1)&quot;&gt;Hack&lt;/a&gt;';
        $expected_markdown = "<p>I'm the needle<a>Hack</a></p>\n";
        $expected_moodle = '<div class="text_to_html">I\'m the needle<a>Hack</a></div>';

        self::assertEquals($expected_moodle, format_text($text));
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE));
        self::assertEquals($expected_moodle, format_text($text, 'sam')); // Fake format.
    }

    public function test_wiki() {
        $text = 'I\'m the needle<a onclick="alert(1)">Hack</a>';
        $filtered = format_text($text, FORMAT_WIKI);
        self::assertContains(s($text), $filtered);
        self::assertNotContains($text, $filtered);
        self::assertNotContains('<a', $filtered);
        self::assertContains('NOTICE: Wiki-like formatting has been removed from Moodle', $filtered);
    }

    public function test_option_none() {
        $text = 'I\'m the needle<a onclick="alert(1)">Hack</a>';
        $expected_html = 'I\'m the needle<a>Hack</a>';
        $expected_plain = 'I&#039;m the needle&lt;a onclick=&quot;alert(1)&quot;&gt;Hack&lt;/a&gt;';
        $expected_markdown = "<p>I'm the needle<a>Hack</a></p>\n";
        $expected_moodle = '<div class="text_to_html">I\'m the needle<a>Hack</a></div>';

        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, []));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, []));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, []));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, []));
        self::assertEquals($expected_moodle, format_text($text, 'sam', [])); // Fake format.

        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, (object)[]));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, (object)[]));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, (object)[]));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, (object)[]));
        self::assertEquals($expected_moodle, format_text($text, 'sam', (object)[])); // Fake format.

        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, 0));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, 0));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, 0));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, 0));
        self::assertEquals($expected_moodle, format_text($text, 'sam', 0)); // Fake format.

        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, '0'));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, '0'));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, '0'));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, '0'));
        self::assertEquals($expected_moodle, format_text($text, 'sam', '0')); // Fake format.
    }

    public function test_legacy_option_noclean() {
        global $CFG;

        // Test it when off
        $text = 'Check out <img src="#" onerror="alert(1)" />';
        $options = ['noclean' => false];
        $expected_html = 'Check out <img src="#" alt="#" />';
        $expected_plain = 'Check out &lt;img src=&quot;#&quot; onerror=&quot;alert(1)&quot; /&gt;';
        $expected_markdown = "<p>Check out <img src=\"#\" alt=\"#\" /></p>\n";
        $expected_moodle = '<div class="text_to_html">Check out <img src="#" alt="#" /></div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test it when on
        $text = 'Check out <img src="#" onerror="alert(1)" />';
        $options = ['noclean' => true];
        $expected_html = 'Check out <img src="#" onerror="alert(1)" />';
        $expected_plain = 'Check out &lt;img src=&quot;#&quot; onerror=&quot;alert(1)&quot; /&gt;';
        $expected_markdown = "<p>Check out <img src=\"#\" onerror=\"alert(1)\" /></p>\n";
        $expected_moodle = '<div class="text_to_html">Check out <img src="#" onerror="alert(1)" /></div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_legacy_option_trusttext() {
        global $CFG;

        self::assertEmpty($CFG->enabletrusttext);

        $CFG->enabletrusttext = 1;

        // Test it when off
        $text = 'Check out <img src="#" onerror="alert(1)" />';
        $options = ['trusted' => false];
        $expected_html = 'Check out <img src="#" alt="#" />';
        $expected_plain = 'Check out &lt;img src=&quot;#&quot; onerror=&quot;alert(1)&quot; /&gt;';
        $expected_markdown = "<p>Check out <img src=\"#\" alt=\"#\" /></p>\n";
        $expected_moodle = '<div class="text_to_html">Check out <img src="#" alt="#" /></div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test it when on
        $text = 'Check out <img src="#" onerror="alert(1)" />';
        $options = ['trusted' => true];
        $expected_html = 'Check out <img src="#" onerror="alert(1)" />';
        $expected_plain = 'Check out &lt;img src=&quot;#&quot; onerror=&quot;alert(1)&quot; /&gt;';
        $expected_markdown = "<p>Check out <img src=\"#\" onerror=\"alert(1)\" /></p>\n";
        $expected_moodle = '<div class="text_to_html">Check out <img src="#" onerror="alert(1)" /></div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        $CFG->enabletrusttext = 0;

        // Test it when on and legacy cleaning is on but trusttext is disabled.
        $text = 'Check out <img src="#" onerror="alert(1)" />';
        $options = ['trusted' => true];
        $expected_html = 'Check out <img src="#" alt="#" />';
        $expected_plain = 'Check out &lt;img src=&quot;#&quot; onerror=&quot;alert(1)&quot; /&gt;';
        $expected_markdown = "<p>Check out <img src=\"#\" alt=\"#\" /></p>\n";
        $expected_moodle = '<div class="text_to_html">Check out <img src="#" alt="#" /></div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_option_nocache() {
        // Test it when off
        $text = 'Check out my <a href="favourite">favourite course</a> today';
        $options = ['nocache' => false];
        $expected_html = 'Check out my <a href="favourite">favourite course</a> today';
        $expected_plain = 'Check out my &lt;a href=&quot;favourite&quot;&gt;favourite course&lt;/a&gt; today';
        $expected_markdown = "<p>Check out my <a href=\"favourite\">favourite course</a> today</p>\n";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test it when on
        $text = 'Check out my <a href="favourite">favourite course</a> today';
        $options = ['nocache' => true];
        $expected_html = 'Check out my <a href="favourite">favourite course</a> today';
        $expected_plain = 'Check out my &lt;a href=&quot;favourite&quot;&gt;favourite course&lt;/a&gt; today';
        $expected_markdown = "<p>Check out my <a href=\"favourite\">favourite course</a> today</p>\n";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test default is off
        $text = 'Check out my <a href="favourite">favourite course</a> today';
        $options = ['nocache' => true];
        $expected_html = 'Check out my <a href="favourite">favourite course</a> today';
        $expected_plain = 'Check out my &lt;a href=&quot;favourite&quot;&gt;favourite course&lt;/a&gt; today';
        $expected_markdown = "<p>Check out my <a href=\"favourite\">favourite course</a> today</p>\n";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_option_para() {
        // Test it when off
        $text = 'Check out my <a href="favourite">favourite course</a> today';
        $options = ['para' => false];
        $expected_html = 'Check out my <a href="favourite">favourite course</a> today';
        $expected_plain = 'Check out my &lt;a href=&quot;favourite&quot;&gt;favourite course&lt;/a&gt; today';
        $expected_markdown = "<p>Check out my <a href=\"favourite\">favourite course</a> today</p>\n";
        $expected_moodle = 'Check out my <a href="favourite">favourite course</a> today';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test it when on
        $text = 'Check out my <a href="favourite">favourite course</a> today';
        $options = ['para' => true];
        $expected_html = 'Check out my <a href="favourite">favourite course</a> today';
        $expected_plain = 'Check out my &lt;a href=&quot;favourite&quot;&gt;favourite course&lt;/a&gt; today';
        $expected_markdown = "<p>Check out my <a href=\"favourite\">favourite course</a> today</p>\n";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test the default is on.
        $text = 'Check out my <a href="favourite">favourite course</a> today';
        $options = [];
        $expected_html = 'Check out my <a href="favourite">favourite course</a> today';
        $expected_plain = 'Check out my &lt;a href=&quot;favourite&quot;&gt;favourite course&lt;/a&gt; today';
        $expected_markdown = "<p>Check out my <a href=\"favourite\">favourite course</a> today</p>\n";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_option_newlines() {
        // Test it when off
        $text = "My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?";
        $options = ['newlines' => false];
        $expected_html = "My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?";
        $expected_plain = "My favourite fruit:<br />\n\t# Banana&#039;s<br />\n\t# Apples <br />\n\t#\tOranges<br />\n<br />\nWhat are your favourite?";
        $expected_markdown = "<p>My favourite fruit:\n    # Banana's\n    # Apples \n    #   Oranges</p>\n\n<p>What are your favourite?</p>\n";
        $expected_moodle = "<div class=\"text_to_html\">My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?</div>";
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test it when on
        $text = "My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?";
        $options = ['newlines' => true];
        $expected_html = "My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?";
        $expected_plain = "My favourite fruit:<br />\n\t# Banana&#039;s<br />\n\t# Apples <br />\n\t#\tOranges<br />\n<br />\nWhat are your favourite?";
        $expected_markdown = "<p>My favourite fruit:\n    # Banana's\n    # Apples \n    #   Oranges</p>\n\n<p>What are your favourite?</p>\n";
        // We use nl2br because its result differs between OSX and Linux.... I know, I know.
        $expected_moodle = nl2br("<div class=\"text_to_html\">My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?</div>");
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals(
            $this->fix_newlines($expected_moodle),
            $this->fix_newlines(format_text($text, FORMAT_MOODLE, $options))
        );

        // Test default (on)
        $text = "My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?";
        $options = [];
        $expected_html = "My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?";
        $expected_plain = "My favourite fruit:<br />\n\t# Banana&#039;s<br />\n\t# Apples <br />\n\t#\tOranges<br />\n<br />\nWhat are your favourite?";
        $expected_markdown = "<p>My favourite fruit:\n    # Banana's\n    # Apples \n    #   Oranges</p>\n\n<p>What are your favourite?</p>\n";
        // We use nl2br because its result differs between OSX and Linux.... I know, I know.
        $expected_moodle = nl2br("<div class=\"text_to_html\">My favourite fruit:\n\t# Banana's\n\t# Apples \n\t#\tOranges\n\nWhat are your favourite?</div>");
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals(
            $this->fix_newlines($expected_moodle),
            $this->fix_newlines(format_text($text, FORMAT_MOODLE, $options))
        );
    }

    public function test_option_overflowdiv() {
        $text = 'A test of the overflow div system';

        // Test on.
        $options = ['overflowdiv' => true];
        $expected_html = '<div class="no-overflow">A test of the overflow div system</div>';
        $expected_plain = '<div class="no-overflow">A test of the overflow div system</div>';
        $expected_markdown = "<div class=\"no-overflow\"><p>A test of the overflow div system</p>\n</div>";
        $expected_moodle = '<div class="no-overflow"><div class="text_to_html">A test of the overflow div system</div></div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test off.
        $options = ['overflowdiv' => false];
        $expected_html = 'A test of the overflow div system';
        $expected_plain = 'A test of the overflow div system';
        $expected_markdown = "<p>A test of the overflow div system</p>\n";
        $expected_moodle = '<div class="text_to_html">A test of the overflow div system</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test the default is off.
        $options = [];
        $expected_html = 'A test of the overflow div system';
        $expected_plain = 'A test of the overflow div system';
        $expected_markdown = "<p>A test of the overflow div system</p>\n";
        $expected_moodle = '<div class="text_to_html">A test of the overflow div system</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_option_allowid() {
        $text = 'The <span id="frank">allowid</span> option';

        // Test on.
        $options = ['allowid' => true];
        $expected_html = 'The <span id="frank">allowid</span> option';
        $expected_plain = 'The &lt;span id=&quot;frank&quot;&gt;allowid&lt;/span&gt; option';
        $expected_markdown = "<p>The <span id=\"frank\">allowid</span> option</p>\n";
        $expected_moodle = '<div class="text_to_html">The <span id="frank">allowid</span> option</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test off.
        $options = ['allowid' => false];
        $expected_html = 'The <span>allowid</span> option';
        $expected_plain = 'The &lt;span id=&quot;frank&quot;&gt;allowid&lt;/span&gt; option';
        $expected_markdown = "<p>The <span>allowid</span> option</p>\n";
        $expected_moodle = '<div class="text_to_html">The <span>allowid</span> option</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test the default is off.
        $options = [];
        $expected_html = 'The <span>allowid</span> option';
        $expected_plain = 'The &lt;span id=&quot;frank&quot;&gt;allowid&lt;/span&gt; option';
        $expected_markdown = "<p>The <span>allowid</span> option</p>\n";
        $expected_moodle = '<div class="text_to_html">The <span>allowid</span> option</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_option_blanktarget() {
        // Test on, basic text
        $text = 'Check out my <a href="favourite">favourite course</a> today';
        $options = ['blanktarget' => true];
        $expected_html = '<p>Check out my <a href="favourite" target="_blank" rel="noreferrer">favourite course</a> today</p>';
        $expected_plain = '<p>Check out my &lt;a href="favourite"&gt;favourite course&lt;/a&gt; today</p>';
        $expected_markdown = "<p>Check out my <a href=\"favourite\" target=\"_blank\" rel=\"noreferrer\">favourite course</a> today</p>";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite" target="_blank" rel="noreferrer">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test on, many properties
        $text = 'Check out my <a href="favourite" title="Basic 101" tabindex=0>favourite course</a> today';
        $options = ['blanktarget' => true];
        $expected_html = '<p>Check out my <a href="favourite" title="Basic 101" target="_blank" rel="noreferrer">favourite course</a> today</p>';
        $expected_plain = '<p>Check out my &lt;a href="favourite" title="Basic 101" tabindex=0&gt;favourite course&lt;/a&gt; today</p>';
        $expected_markdown = "<p>Check out my <a href=\"favourite\" title=\"Basic 101\" target=\"_blank\" rel=\"noreferrer\">favourite course</a> today</p>";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite" title="Basic 101" target="_blank" rel="noreferrer">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test on, already defined but to parent - it changes them to blank :(
        $text = 'Check out my <a href="favourite" target="_parent" rel="nofollow">favourite course</a> today';
        $options = ['blanktarget' => true];
        $expected_html = '<p>Check out my <a href="favourite" target="_blank" rel="noreferrer">favourite course</a> today</p>';
        $expected_plain = '<p>Check out my &lt;a href="favourite" target="_parent" rel="nofollow"&gt;favourite course&lt;/a&gt; today</p>';
        $expected_markdown = "<p>Check out my <a href=\"favourite\" target=\"_blank\" rel=\"noreferrer\">favourite course</a> today</p>";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite" target="_blank" rel="noreferrer">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test on, already defined to blank
        $text = 'Check out my <a href="favourite" target="_blank" rel="nofollow">favourite course</a> today';
        $options = ['blanktarget' => true];
        $expected_html = '<p>Check out my <a href="favourite" target="_blank" rel="noreferrer noopener">favourite course</a> today</p>';
        $expected_plain = '<p>Check out my &lt;a href="favourite" target="_blank" rel="nofollow"&gt;favourite course&lt;/a&gt; today</p>';
        $expected_markdown = "<p>Check out my <a href=\"favourite\" target=\"_blank\" rel=\"noreferrer noopener\">favourite course</a> today</p>";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite" target="_blank" rel="noreferrer noopener">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test on, already defined to _parent and noclean which avoids cleaning.
        $text = 'Check out my <a href="favourite" target="_parent" rel="nofollow">favourite course</a> today';
        $options = ['blanktarget' => true, 'noclean' => true];
        $expected_html = '<p>Check out my <a href="favourite" target="_parent" rel="nofollow">favourite course</a> today</p>';
        $expected_plain = '<p>Check out my &lt;a href="favourite" target="_parent" rel="nofollow"&gt;favourite course&lt;/a&gt; today</p>';
        $expected_markdown = "<p>Check out my <a href=\"favourite\" target=\"_parent\" rel=\"nofollow\">favourite course</a> today</p>";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite" target="_parent" rel="nofollow">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test on, defined only with a rel
        $text = 'Check out my <a href="favourite" rel="nofollow">favourite course</a> today';
        $options = ['blanktarget' => true];
        $expected_html = '<p>Check out my <a href="favourite" target="_blank" rel="noreferrer">favourite course</a> today</p>';
        $expected_plain = '<p>Check out my &lt;a href="favourite" rel="nofollow"&gt;favourite course&lt;/a&gt; today</p>';
        $expected_markdown = "<p>Check out my <a href=\"favourite\" target=\"_blank\" rel=\"noreferrer\">favourite course</a> today</p>";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite" target="_blank" rel="noreferrer">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test on, defined only with a rel and with noclean which avoids cleaning.
        $text = 'Check out my <a href="favourite" rel="nofollow">favourite course</a> today';
        $options = ['blanktarget' => true, 'noclean' => true];
        $expected_html = '<p>Check out my <a href="favourite" rel="nofollow noreferrer" target="_blank">favourite course</a> today</p>';
        $expected_plain = '<p>Check out my &lt;a href="favourite" rel="nofollow"&gt;favourite course&lt;/a&gt; today</p>';
        $expected_markdown = "<p>Check out my <a href=\"favourite\" rel=\"nofollow noreferrer\" target=\"_blank\">favourite course</a> today</p>";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite" rel="nofollow noreferrer" target="_blank">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));

        // Test it when off
        $text = 'Check out my <a href="favourite" target="_parent" rel="nofollow">favourite course</a> today';
        $options = ['blanktarget' => false];
        $expected_html = 'Check out my <a href="favourite">favourite course</a> today';
        $expected_plain = 'Check out my &lt;a href=&quot;favourite&quot; target=&quot;_parent&quot; rel=&quot;nofollow&quot;&gt;favourite course&lt;/a&gt; today';
        $expected_markdown = "<p>Check out my <a href=\"favourite\">favourite course</a> today</p>\n";
        $expected_moodle = '<div class="text_to_html">Check out my <a href="favourite">favourite course</a> today</div>';
        self::assertEquals($expected_html, format_text($text, FORMAT_HTML, $options));
        self::assertEquals($expected_plain, format_text($text, FORMAT_PLAIN, $options));
        self::assertEquals($expected_markdown, format_text($text, FORMAT_MARKDOWN, $options));
        self::assertEquals($expected_moodle, format_text($text, FORMAT_MOODLE, $options));
    }

    public function test_option_allowxss() {
        // Hahaha, this option does not exist in 12! It was introduced in 13.
        $text = 'I\'m the needle<a onclick="alert(1)">Hack</a>';
        self::assertSame('I\'m the needle<a>Hack</a>', format_text($text, FORMAT_HTML, ['allowxss' => true]));
    }

    public function test_low_version_formatting() {
        global $CFG;
        $CFG->version = 2013051400 - 1;
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $text = '<p>:-)</p>';
        self::assertSame($text, format_text($text, FORMAT_HTML, ['filter' => true]));
    }

    public function test_pluginfile_debugging() {
        global $CFG;
        $text = '<a href="@@PLUGINFILE@@/test.jpg">Test</a>';
        $expected = '<a href="@@PLUGINFILE@@/test.jpg">Test</a>';

        $CFG->debugdeveloper = 0;
        self::assertSame($expected, format_text($text, FORMAT_HTML));

        $CFG->debugdeveloper = 1;
        self::assertSame($expected, format_text($text, FORMAT_HTML));
        $this->assertDebuggingCalled('Before calling format_text(), the content must be processed with file_rewrite_pluginfile_urls()');
    }

    public function test_script_tag_persistence() {
        $text = '<div>Test</div><script>alert(1);</script>';

        self::assertSame('<div>Test</div>', format_text($text, FORMAT_HTML));
        self::assertSame(
            $this->fix_newlines('<div>Test</div>'),
            $this->fix_newlines(format_text($text, FORMAT_MARKDOWN))
        );
        self::assertSame('&lt;div&gt;Test&lt;/div&gt;&lt;script&gt;alert(1);&lt;/script&gt;', format_text($text, FORMAT_PLAIN));
        self::assertSame('<div class="text_to_html"><div>Test</div></div>', format_text($text, FORMAT_MOODLE));

        $text = '<div>Test</div><script type="text/javascript">alert(1);</script>';

        self::assertSame('<div>Test</div>', format_text($text, FORMAT_HTML));
        self::assertSame(
            $this->fix_newlines('<div>Test</div>'),
            $this->fix_newlines(format_text($text, FORMAT_MARKDOWN))
        );
        self::assertSame('&lt;div&gt;Test&lt;/div&gt;&lt;script type=&quot;text/javascript&quot;&gt;alert(1);&lt;/script&gt;', format_text($text, FORMAT_PLAIN));
        self::assertSame('<div class="text_to_html"><div>Test</div></div>', format_text($text, FORMAT_MOODLE));

        $text = '<script>alert(1);</script><div>Test</div>';

        self::assertSame('<div>Test</div>', format_text($text, FORMAT_HTML));
        self::assertSame(
            $this->fix_newlines('<div>Test</div>'),
            $this->fix_newlines(format_text($text, FORMAT_MARKDOWN))
        );
        self::assertSame('&lt;script&gt;alert(1);&lt;/script&gt;&lt;div&gt;Test&lt;/div&gt;', format_text($text, FORMAT_PLAIN));
        self::assertSame('<div class="text_to_html"><div>Test</div></div>', format_text($text, FORMAT_MOODLE));

        $text = '<div>Te<script>alert(1);</script>st</div>';

        self::assertSame('<div>Test</div>', format_text($text, FORMAT_HTML));
        self::assertSame(
            $this->fix_newlines('<div>Test</div>'),
            $this->fix_newlines(format_text($text, FORMAT_MARKDOWN))
        );
        self::assertSame('&lt;div&gt;Te&lt;script&gt;alert(1);&lt;/script&gt;st&lt;/div&gt;', format_text($text, FORMAT_PLAIN));
        self::assertSame('<div class="text_to_html"><div>Test</div></div>', format_text($text, FORMAT_MOODLE));
    }

    public function test_style_tag_persistence() {
        $text = '<div>Test</div><style>background-color:red;</style>';

        self::assertSame('<div>Test</div>', format_text($text, FORMAT_HTML));
        self::assertSame(
            $this->fix_newlines('<div>Test</div>'),
            $this->fix_newlines(format_text($text, FORMAT_MARKDOWN))
        );
        self::assertSame('&lt;div&gt;Test&lt;/div&gt;&lt;style&gt;background-color:red;&lt;/style&gt;', format_text($text, FORMAT_PLAIN));
        self::assertSame('<div class="text_to_html"><div>Test</div></div>', format_text($text, FORMAT_MOODLE));

        $text = '<div>Test</div><script type="text/css">alert(1);</script>';

        self::assertSame('<div>Test</div>', format_text($text, FORMAT_HTML));
        self::assertSame(
            $this->fix_newlines('<div>Test</div>'),
            $this->fix_newlines(format_text($text, FORMAT_MARKDOWN))
        );
        self::assertSame('&lt;div&gt;Test&lt;/div&gt;&lt;script type=&quot;text/css&quot;&gt;alert(1);&lt;/script&gt;', format_text($text, FORMAT_PLAIN));
        self::assertSame('<div class="text_to_html"><div>Test</div></div>', format_text($text, FORMAT_MOODLE));

        $text = '<style>background-color:red;</style><div>Test</div>';

        self::assertSame('<div>Test</div>', format_text($text, FORMAT_HTML));
        self::assertSame(
            $this->fix_newlines('<div>Test</div>'),
            $this->fix_newlines(format_text($text, FORMAT_MARKDOWN))
        );
        self::assertSame('&lt;style&gt;background-color:red;&lt;/style&gt;&lt;div&gt;Test&lt;/div&gt;', format_text($text, FORMAT_PLAIN));
        self::assertSame('<div class="text_to_html"><div>Test</div></div>', format_text($text, FORMAT_MOODLE));

        $text = '<div>Te<style>background-color:red;</style>st</div>';

        self::assertSame('<div>Test</div>', format_text($text, FORMAT_HTML));
        self::assertSame(
            $this->fix_newlines('<div>Test</div>'),
            $this->fix_newlines(format_text($text, FORMAT_MARKDOWN))
        );
        self::assertSame('&lt;div&gt;Te&lt;style&gt;background-color:red;&lt;/style&gt;st&lt;/div&gt;', format_text($text, FORMAT_PLAIN));
        self::assertSame('<div class="text_to_html"><div>Test</div></div>', format_text($text, FORMAT_MOODLE));
    }
}
