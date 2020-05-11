<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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

namespace totara_core\local\visibility;

defined('MOODLE_INTERNAL') || die();

/**
 * Base visibility resolver implementation.
 *
 * @internal
 */
abstract class base implements resolver {
    /**
     * The SQL separator.
     * This is used by report builder report caching.
     * It should not be used by anything else. Ever!
     * @var string
     */
    private $separator = '.';

    /**
     * Should we skip admin checks?
     * @var bool
     */
    private $skip_admin_checks = true;

    /**
     * Returns an SQL snippet that resolves whether the user has the required capability to view the item given its ID field.
     *
     * @param int $userid
     * @param string $field_id
     * @return array An array with two items, string:SQL, array:params
     */
    protected function sql_view_hidden(int $userid, string $field_id): array {
        $mapsql = $this->map()->sql_view_hidden_roles($userid);

        $sql = "EXISTS (
                SELECT 1
                FROM (
                    {$mapsql}
                HAVING COUNT(map.roleid) > 0
                ) vh_r WHERE vh_r.id = {$field_id}
            )";

        return [$sql, []];
    }

    /**
     * Returns an SQL snippet that resolves whether the item is currently available or not.
     *
     * @param int $userid
     * @param string $tablealias
     * @return array An array with two items, string:SQL, array:params OR null if there is no availability to calculate.
     */
    abstract protected function get_availability_sql(int $userid, string $tablealias): ?array;

    /**
     * Returns an SQL snippet restrict visibility.
     *
     * @param int $userid
     * @param string $field_id
     * @param string $field_visible
     * @return array An array with two items, string:SQL, array:params OR null if there is no availability to calculate.
     */
    abstract protected function get_visibility_sql(int $userid, string $field_id, string $field_visible): array;

    /**
     * @inheritDoc
     * @param int $userid
     * @param string $tablealias The item table alias, this is normally one of course, c, prog, p
     * @return array An array with two items, string:SQL, array:params
     */
    public function sql_where_visible(int $userid, string $tablealias) : array {

        if ($this->can_see_all($userid)) {
            return ['1=1', []];
        }

        $separator = $this->sql_separator();

        $field_id = $tablealias . $separator . 'id';
        $field_visible = $tablealias . $separator . $this->sql_field_visible();

        [$audiencesql, $audienceparams] = $this->get_visibility_sql($userid, $field_id, $field_visible);
        [$hiddensql, $hiddenparams] = $this->sql_view_hidden($userid, $field_id);

        $availability = $this->get_availability_sql($userid, $tablealias);

        if (is_null($availability)) {
            $sql = "({$hiddensql} OR {$audiencesql})";
            $params = array_merge($hiddenparams, $audienceparams);

            return [$sql, $params];
        }

        [$availabilitysql, $availabilityparams] = $availability;
        $sql = "({$hiddensql} OR ({$audiencesql} AND {$availabilitysql}))";
        $params = array_merge($hiddenparams, $audienceparams, $availabilityparams);

        return [$sql, $params];
    }

    /**
     * @inheritDoc
     */
    final public function sql_separator(): string {
        return $this->separator;
    }

    /**
     * @inheritDoc
     */
    final public function set_sql_separator(string $separator) {
        $this->separator = $separator;
    }

    /**
     * @inheritDoc
     */
    final public function set_skip_checks_for_admin(bool $value) {
        $this->skip_admin_checks = $value;
    }

    /**
     * @inheritDoc
     */
    final public function skip_checks_for_admin(): bool {
        return $this->skip_admin_checks;
    }

    /**
     * Returns true if the given user can see all items, regardless of visibility.
     *
     * @param int $userid
     * @return bool
     */
    protected function can_see_all(int $userid) {
        if (is_siteadmin($userid) && $this->skip_checks_for_admin()) {
            return true;
        }
        return false;
    }

}