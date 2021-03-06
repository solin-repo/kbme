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
 * This file contains the activity badge award criteria type class
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/completionlib.php');

/**
 * Badge award criteria -- award on activity completion
 *
 */
class award_criteria_activity extends award_criteria {

    /* @var int Criteria [BADGE_CRITERIA_TYPE_ACTIVITY] */
    public $criteriatype = BADGE_CRITERIA_TYPE_ACTIVITY;

    private $courseid;
    private $course;

    public $required_param = 'module';
    public $optional_params = array('bydate');

    public function __construct($record) {
        global $DB;
        parent::__construct($record);

        $this->course = $DB->get_record_sql('SELECT c.id, c.enablecompletion, c.cacherev, c.startdate
                        FROM {badge} b INNER JOIN {course} c ON b.courseid = c.id
                        WHERE b.id = :badgeid ', array('badgeid' => $this->badgeid));
        $this->courseid = $this->course->id;
    }

    /**
     * Gets the module instance from the database and returns it.
     * If no module instance exists this function returns false.
     *
     * @return stdClass|bool
     */
    private function get_mod_instance($cmid) {
        global $DB;
        $rec = $DB->get_record_sql("SELECT md.name
                               FROM {course_modules} cm,
                                    {modules} md
                               WHERE cm.id = ? AND
                                     md.id = cm.module", array($cmid));

        if ($rec) {
            return get_coursemodule_from_id($rec->name, $cmid);
        } else {
            return null;
        }
    }

    /**
     * Get criteria description for displaying to users
     *
     * @return string
     */
    public function get_details($short = '') {
        global $DB, $OUTPUT;
        $output = array();
        foreach ($this->params as $p) {
            $mod = self::get_mod_instance($p['module']);
            if (!$mod) {
                $str = $OUTPUT->error_text(get_string('error:nosuchmod', 'badges'));
            } else {
                $str = html_writer::tag('b', '"' . get_string('modulename', $mod->modname) . ' - ' . $mod->name . '"');
                if (isset($p['bydate'])) {
                    $str .= get_string('criteria_descr_bydate', 'badges', userdate($p['bydate'], get_string('strftimedate', 'core_langconfig')));
                }
            }
            $output[] = $str;
        }

        if ($short) {
            return implode(', ', $output);
        } else {
            return html_writer::alist($output, array(), 'ul');
        }
    }

    /**
     * Add appropriate new criteria options to the form
     *
     */
    public function get_options(&$mform) {
        $none = true;
        $existing = array();
        $missing = array();

        $course = $this->course;
        $info = new completion_info($course);
        $mods = $info->get_activities();
        $mids = array_keys($mods);

        if ($this->id !== 0) {
            $existing = array_keys($this->params);
            $missing = array_diff($existing, $mids);
        }

        if (!empty($missing)) {
            $mform->addElement('header', 'category_errors', get_string('criterror', 'badges'));
            $mform->addHelpButton('category_errors', 'criterror', 'badges');
            foreach ($missing as $m) {
                $this->config_options($mform, array('id' => $m, 'checked' => true,
                        'name' => get_string('error:nosuchmod', 'badges'), 'error' => true));
                $none = false;
            }
        }

        if (!empty($mods)) {
            $mform->addElement('header', 'first_header', $this->get_title());
            foreach ($mods as $mod) {
                $checked = false;
                if (in_array($mod->id, $existing)) {
                    $checked = true;
                }
                $param = array('id' => $mod->id,
                        'checked' => $checked,
                        'name' => get_string('modulename', $mod->modname) . ' - ' . $mod->name,
                        'error' => false
                        );

                if ($this->id !== 0 && isset($this->params[$mod->id]['bydate'])) {
                    $param['bydate'] = $this->params[$mod->id]['bydate'];
                }

                if ($this->id !== 0 && isset($this->params[$mod->id]['grade'])) {
                    $param['grade'] = $this->params[$mod->id]['grade'];
                }

                $this->config_options($mform, $param);
                $none = false;
            }
        }

        // Add aggregation.
        if (!$none) {
            $mform->addElement('header', 'aggregation', get_string('method', 'badges'));
            $agg = array();
            $agg[] =& $mform->createElement('radio', 'agg', '', get_string('allmethodactivity', 'badges'), 1);
            $agg[] =& $mform->createElement('radio', 'agg', '', get_string('anymethodactivity', 'badges'), 2);
            $mform->addGroup($agg, 'methodgr', '', array('<br/>'), false);
            if ($this->id !== 0) {
                $mform->setDefault('agg', $this->method);
            } else {
                $mform->setDefault('agg', BADGE_CRITERIA_AGGREGATION_ANY);
            }
        }

        return array($none, get_string('error:noactivities', 'badges'));
    }

    /**
     * Review this criteria and decide if it has been completed
     *
     * @param int $userid User whose criteria completion needs to be reviewed.
     * @param bool $filtered An additional parameter indicating that user list
     *        has been reduced and some expensive checks can be skipped.
     *
     * @return bool Whether criteria is complete
     */
    public function review($userid, $filtered = false) {
        $completionstates = array(
            COMPLETION_COMPLETE,
            COMPLETION_COMPLETE_PASS,
            COMPLETION_COMPLETE_FAIL
        );

        if ($this->course->startdate > time()) {
            return false;
        }

        $info = new completion_info($this->course);

        $overall = false;
        foreach ($this->params as $param) {
            $cm = new stdClass();
            $cm->id = $param['module'];

            $data = $info->get_data($cm, false, $userid);
            $check_date = true;

            if (isset($param['bydate'])) {
                $date = $data->timemodified;
                $check_date = ($date <= $param['bydate']);
            }

            if ($this->method == BADGE_CRITERIA_AGGREGATION_ALL) {
                if (in_array($data->completionstate, $completionstates) && $check_date) {
                    $overall = true;
                    continue;
                } else {
                    return false;
                }
            } else {
                if (in_array($data->completionstate, $completionstates) && $check_date) {
                    return true;
                } else {
                    $overall = false;
                    continue;
                }
            }
        }

        return $overall;
    }

    /**
     * Returns array with sql code and parameters returning all ids
     * of users who meet this particular criterion.
     *
     * @return array list($join, $where, $params)
     */
    public function get_completed_criteria_sql() {
        global $DB;
        $join = '';
        $where = '';
        $params = array();

        if ($this->method == BADGE_CRITERIA_AGGREGATION_ANY) {
            foreach ($this->params as $param) {
                // Generate a unique param.
                $parammodule = $DB->get_unique_param('completedmodule');
                // Generate the SQL.
                $moduledata[] = " cmc.coursemoduleid = :{$parammodule} ";
                // Add the param.
                $params[$parammodule] = $param['module'];
            }
            if (!empty($moduledata)) {
                // Generate unique params.
                $paramcomplete = $DB->get_unique_param('completioncomplete');
                $parampass = $DB->get_unique_param('completionpass');
                $paramfail = $DB->get_unique_param('completionfail');

                // Generate the SQL.
                $extraon = implode(' OR ', $moduledata);
                $join = " JOIN {course_modules_completion} cmc ON cmc.userid = u.id AND (
                                cmc.completionstate = :{$paramcomplete} OR
                                cmc.completionstate = :{$parampass} OR
                                cmc.completionstate = :{$paramfail}
                         ) AND ({$extraon})";

                // Add the params.
                $params[$paramcomplete] = COMPLETION_COMPLETE;
                $params[$parampass] = COMPLETION_COMPLETE_PASS;
                $params[$paramfail] = COMPLETION_COMPLETE_FAIL;
            }
        } else {
            foreach ($this->params as $param) {
                // Generate unique params.
                $prefix = 'cmc'.$param['module'];
                $parammodule = $DB->get_unique_param('completedmodule');
                $paramcomplete = $DB->get_unique_param('completionpass');
                $parampass = $DB->get_unique_param('completionfail');
                $paramfail = $DB->get_unique_param('completioncomplete');

                // Generate the SQL.
                $join .= " LEFT JOIN {course_modules_completion} {$prefix} ON
                                     {$prefix}.userid = u.id AND
                                     {$prefix}.coursemoduleid = :{$parammodule} AND (
                                        {$prefix}.completionstate = :{$paramcomplete} OR
                                        {$prefix}.completionstate = :{$parampass} OR
                                        {$prefix}.completionstate = :{$paramfail} )";
                $where .= " AND {$prefix}.coursemoduleid IS NOT NULL ";

                // Add the params.
                $params[$parammodule] = $param['module'];
                $params[$paramcomplete] = COMPLETION_COMPLETE;
                $params[$parampass] = COMPLETION_COMPLETE_PASS;
                $params[$paramfail] = COMPLETION_COMPLETE_FAIL;
            }
        }
        return array($join, $where, $params);
    }

    /**
     * Checks criteria for any major problems.
     *
     * @return array A list containing status and an error message (if any).
     */
    public function validate() {
        global $DB;
        $params = array_keys($this->params);
        $method = ($this->method == BADGE_CRITERIA_AGGREGATION_ALL);
        $singleparam = (count($params) == 1);

        foreach ($params as $param) {
            // Perform check if there only one parameter with any type of aggregation,
            // Or there are more than one parameter with aggregation ALL.
            if (($singleparam || $method) && !$DB->record_exists('course_modules', array('id' => $param))) {
                return array(false, get_string('error:invalidparamactivity', 'badges'));
            }
        }

        return array(true, '');
    }
}
