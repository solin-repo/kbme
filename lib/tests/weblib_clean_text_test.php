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

defined('MOODLE_INTERNAL') || die();

class core_weblib_clean_text_testcase extends advanced_testcase {

    private const NOT_ALLOWED = false;
    private const ALLOWED = true;

    public function test_clean_text_applet() {
        $text = "lala <applet>xx</applet>";
        $this->assertSame($text, clean_text($text, FORMAT_PLAIN));
        $this->assertSame('lala xx', clean_text($text, FORMAT_MARKDOWN));
        $this->assertSame('lala xx', clean_text($text, FORMAT_MOODLE));
        $this->assertSame('lala xx', clean_text($text, FORMAT_HTML));
    }

    public function test_clean_text_xss() {
        $text = "lala <a onclick='alert(1)'>xx</a>";
        $this->assertSame($text, clean_text($text, FORMAT_PLAIN));
        $this->assertSame('lala <a>xx</a>', clean_text($text, FORMAT_MARKDOWN));
        $this->assertSame('lala <a>xx</a>', clean_text($text, FORMAT_MOODLE));
        $this->assertSame('lala <a>xx</a>', clean_text($text, FORMAT_HTML));

        $text = "lala <img src='#' onerror='alert(1)' /> xx";
        $this->assertSame($text, clean_text($text, FORMAT_PLAIN));
        $this->assertSame('lala <img src="#" alt="#" /> xx', clean_text($text, FORMAT_MARKDOWN));
        $this->assertSame('lala <img src="#" alt="#" /> xx', clean_text($text, FORMAT_MOODLE));
        $this->assertSame('lala <img src="#" alt="#" /> xx', clean_text($text, FORMAT_HTML));
    }

    public function test_tag_a() {
        $attributes = [
            'hreflang' => [
                '' => self::NOT_ALLOWED,
                'en' => self::NOT_ALLOWED,
                'gb' => self::NOT_ALLOWED
            ],
            'download' => [
                '' => self::NOT_ALLOWED,
                'download' => self::NOT_ALLOWED
            ],
            'target' => [
                '' => self::NOT_ALLOWED,
                '_blank' => '<a target="_blank" rel="noreferrer noopener">Test</a>',
                '_parent' => self::NOT_ALLOWED,
                '_self' => self::NOT_ALLOWED,
                '_top' => self::NOT_ALLOWED,
                'my_frame_name' => self::NOT_ALLOWED
            ],
            'title' => [
                '' => self::ALLOWED,
                'my_title' => self::ALLOWED
            ],
            'href' => [
                '' => self::ALLOWED,
                '#' => self::ALLOWED,
                '/test.php' => self::ALLOWED,
                'test.php' => self::ALLOWED
            ],
            'name' => [
                '' => self::NOT_ALLOWED,
                'one' => self::NOT_ALLOWED
            ],
            'ping' => [
                '' => self::NOT_ALLOWED,
                '#' => self::NOT_ALLOWED,
                '# /' => self::NOT_ALLOWED
            ],
            'referrerpolicy' => [
                '' => self::NOT_ALLOWED,
                'no-referrer' => self::NOT_ALLOWED,
                'no-referrer-when-downgrade' => self::NOT_ALLOWED,
                'origin' => self::NOT_ALLOWED,
                'origin-when-cross-origin' => self::NOT_ALLOWED,
                'strict-origin-when-cross-origin' => self::NOT_ALLOWED,
                'unsafe-url' => self::NOT_ALLOWED
            ],
            'rel' => [
                '' => self::NOT_ALLOWED,
                'alternate' => self::NOT_ALLOWED,
                'author' => self::NOT_ALLOWED,
                'bookmark' => self::NOT_ALLOWED,
                'external' => self::NOT_ALLOWED,
                'help' => self::NOT_ALLOWED
            ],
            'rev' => [
                '' => self::NOT_ALLOWED,
                'test' => self::NOT_ALLOWED
            ],
            'shape' => [
                'default' => self::NOT_ALLOWED,
                'rect' => self::NOT_ALLOWED,
                'circle' => self::NOT_ALLOWED,
                'poly' => self::NOT_ALLOWED
            ],
            'type' => [
                '' => self::NOT_ALLOWED,
                'application' => self::NOT_ALLOWED,
                'video' => self::NOT_ALLOWED,
                'model' => self::NOT_ALLOWED
            ],

            // Aria tags.
            'role' => [
                'progressbar' => self::NOT_ALLOWED
            ],
            'aria-valuemin' => [
                '0' => self::NOT_ALLOWED
            ],
            'aria-valuemax' => [
                '100' => self::NOT_ALLOWED
            ],
            'aria-valuenow' => [
                '75' => self::NOT_ALLOWED
            ],
        ];

        $this->check_tag_outcome('a', $attributes);
    }

    private function check_tag_outcome($tag, $attributes) {
        $notexpected = "<{$tag}>Test</{$tag}>";
        foreach ($attributes as $attribute => $values) {
            foreach ($values as $value => $expected) {
                $text = "<{$tag} {$attribute}=\"{$value}\">Test</{$tag}>";
                $outcome = clean_text($text, FORMAT_HTML);
                $message = "Testing tag[{$tag}] attribute[{$attribute}] with value[{$value}]";
                if ($expected === true) {
                    self::assertSame($text, $outcome, $message);
                } else if ($expected === false) {
                    self::assertSame($notexpected, $outcome, $message);
                } else {
                    self::assertSame($expected, $outcome, $message);
                }
            }
        }
    }

}