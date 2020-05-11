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
 * Defines classes used for updates.
 *
 * @package    core
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core\update;

use html_writer, coding_exception, core_component;

defined('MOODLE_INTERNAL') || die();

/**
 * Singleton class that handles checking for available updates
 */
class checker {

    /** @var \core\update\checker holds the singleton instance */
    protected static $singletoninstance;

    /**
     * Direct initiation not allowed, use the factory method {@link self::instance()}
     */
    protected function __construct() {
    }

    /**
     * Sorry, this is singleton
     */
    protected function __clone() {
    }

    /**
     * Factory method for this class
     *
     * @return \core\update\checker the singleton instance
     */
    public static function instance() {
        if (is_null(self::$singletoninstance)) {
            self::$singletoninstance = new self();
        }
        return self::$singletoninstance;
    }

    /**
     * Reset any caches
     * @param bool $phpunitreset
     */
    public static function reset_caches($phpunitreset = false) {
        if ($phpunitreset) {
            self::$singletoninstance = null;
        }
    }

    /**
     * Is checking for available updates enabled?
     *
     * The feature is enabled unless it is prohibited via config.php.
     * If enabled, the button for manual checking for available updates is
     * displayed at admin screens. To perform scheduled checks for updates
     * automatically, the admin setting $CFG->updateautocheck has to be enabled.
     *
     * @return bool
     */
    public function enabled() {
        return false;
    }

    /**
     * Returns the timestamp of the last execution of {@link fetch()}
     *
     * @return int|null null if it has never been executed or we don't known
     */
    public function get_last_timefetched() {
        return null;
    }

    /**
     * Fetches the available update status from the remote site
     *
     * @throws checker_exception
     */
    public function fetch() {
        return;
    }

    /**
     * Returns the available update information for the given component
     *
     * This method returns null if the most recent response does not contain any information
     * about it. The returned structure is an array of available updates for the given
     * component. Each update info is an object with at least one property called
     * 'version'. Other possible properties are 'release', 'maturity', 'url' and 'downloadurl'.
     *
     * For the 'core' component, the method returns real updates only (those with higher version).
     * For all other components, the list of all known remote updates is returned and the caller
     * (usually the {@link core_plugin_manager}) is supposed to make the actual comparison of versions.
     *
     * @param string $component frankenstyle
     * @param array $options with supported keys 'minmaturity' and/or 'notifybuilds'
     * @return null|array null or array of \core\update\info objects
     */
    public function get_update_info($component, array $options = array()) {
        return null;
    }

    /**
     * The method being run via cron.php
     */
    public function cron() {
        return;
    }

    /* === End of public API === */
}
