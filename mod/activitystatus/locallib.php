<?php

/**
 *
 * @package
 * @subpackage
 * @copyright   2019 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @author      Olumuyiwa Taiwo {@link https://moodle.org/user/view.php?id=416594}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function activitystatus_set_image_files($data) {
    $course = new stdClass();
    $course->id = $data->course;
    $cmid = $data->coursemodule;
    $modcontext = context_module::instance($cmid);
    $elements = activitystatus_background_elements();
    foreach ($elements as $el) {
        file_save_draft_area_files($data->$el, $modcontext->id, 'mod_activitystatus', $el, 0, ['subdirs' => false]);
    }

    $completiontypes_courses = activitystatus_get_completion_types_courses();
    $linkedcourses = activitystatus_get_linked_courses($data);
    foreach ($linkedcourses as $linkedcourse) {
        $tracked = new stdClass();
        $tracked->courseormodid = $linkedcourse->id;
        $tracked->type = 'course';
        foreach ($completiontypes_courses as $key => $type) {
            $el = "imagefile_$linkedcourse->id" . "_$key";
            $tracked->$el = $data->$el;
            file_save_draft_area_files($data->$el, $modcontext->id, 'mod_activitystatus', 'statusimages', $linkedcourse->id . $key, ['subdirs' => false]);
        }
    }

    $completiontypes_mods = activitystatus_get_completion_types_mods();
    $trackedmodules = activitystatus_get_tracked_coursemodules($data);
    foreach ($trackedmodules as $cm) {
        $tracked = new stdClass();
        $tracked->courseormodid = $cm->id;
        $tracked->type = 'mod';
        foreach ($completiontypes_mods as $key => $type) {
            $el = 'imagefile_' . $cm->id . '_' . $key;
            $tracked->$el = $data->$el;
            file_save_draft_area_files($data->$el, $modcontext->id, 'mod_activitystatus', 'statusimages', $cm->id . $key, ['subdirs' => false]);
        }
    }
}

function activitystatus_get_tracked_coursemodules($data) {
    $course = new stdClass();
    $course->id = $data->course;
    $trackedmodules = [];
    try {
        $modinfo = get_fast_modinfo($course);
    } catch (\moodle_exception $e) {
        // Course may have been deleted.
    }
    foreach ($modinfo->cms as $trackedcm) {
        if ($trackedcm->completion != COMPLETION_TRACKING_NONE) {
            $trackedmodules[] = $trackedcm;
        }
    }
    return $trackedmodules;
}

function activitystatus_get_linked_courses($data) {
    $course = new stdClass();
    $course->id = $data->course;

    $courses = [];
    $completion = new completion_info($course);
    foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_COURSE) as $criterion) {
        try {
            $linkedcourse = get_fast_modinfo($criterion->courseinstance)->get_course();
        } catch (\moodle_exception $e) {
            // Course may have been deleted.
        }
        $courses[] = $linkedcourse; // get_fast_modinfo($criterion->courseinstance)->get_course();
    }
//    var_dump(array_column(json_decode(json_encode($courses), true), 'id'));
    return $courses;
}

function activitystatus_save_displayorder($data, $trackedmodsorcourses, $type) {
    global $DB;
    foreach ($trackedmodsorcourses as $cm) {
        $params = [
            'modid' => (int)($data->coursemodule),
            'modinstanceid' => (int)($data->instance),
            'courseormodid' => (int)($cm->id),
            'itemtype' => $type,
        ];
        if (false !== $pos = $DB->get_field('activitystatus_displayorder', 'displayorder', $params)) {
            // Update.
            $DB->set_field('activitystatus_displayorder', 'displayorder', $data->{"displayorder_" . $type . "_$cm->id"}, $params);
        } else {
            // Insert.
            $record = new \stdClass();
            $record->modid = $data->coursemodule;
            $record->modinstanceid = $data->instance;
            $record->courseormodid = $cm->id;
            $record->itemtype = $type;
            $record->displayorder = $data->{"displayorder_" . $type . "_$cm->id"};
            $DB->insert_record('activitystatus_displayorder', $record);
        }
    }
    // Delete courses that are no longer linked
    $linkedcourses = activitystatus_get_linked_courses($data);
    $linkedcourseids = array_column(json_decode(json_encode($linkedcourses), true), 'id');
    $displaycourses = $DB->get_records('activitystatus_displayorder', ['modid' => (int) ($data->coursemodule), 'modinstanceid' => (int)($data->instance), 'itemtype' => 'course']);
    $displaycourseids = array_column(json_decode(json_encode($displaycourses), true), 'courseormodid');
    $orphans = array_diff($displaycourseids, $linkedcourseids);
    if (!empty($orphans)) {
        list($in_sql, $del_params) = $DB->get_in_or_equal($orphans);
        $sql = "DELETE FROM {activitystatus_displayorder} WHERE itemtype = 'course' AND courseormodid {$in_sql}";
        $DB->execute($sql, $del_params);
    }
}

function activitystatus_load_displayorder($cm_data) {
    global $DB;
    return $DB->get_records('activitystatus_displayorder', ['modid' => (int)($cm_data->id), 'modinstanceid' => (int)($cm_data->instance)]);
}

function activitystatus_get_completion_types_mods() {
    $completiontypes = [
        0 => 'incomplete',
        1 => 'complete',
        2 => 'complete but failed',
        3 => 'restricted',
    ];

    return $completiontypes;
}

function activitystatus_get_completion_types_courses() {
    $completiontypes = array(
        // Using core course completion status because they're convenient.
        COMPLETION_STATUS_NOTYETSTARTED => 'not yet started',
        COMPLETION_STATUS_INPROGRESS => 'in progress',
        COMPLETION_STATUS_COMPLETE => 'complete',
        COMPLETION_STATUS_COMPLETEVIARPL => 'complete via rpl',
    );
    return $completiontypes;
}

function activitystatus_background_elements() {
    $elements = ['default_background',
        'default_status',
    ];
    return $elements;
}

function activitystatus_get_displayorder($array, $type, $id) {
    foreach ($array as $item) {
        if ($item->itemtype == $type && $item->courseormodid == $id) {
            return $item->displayorder;
        }
    }
    return 0;
}

function activitystatus_icons_order($displayorder) {
    usort($displayorder, function($a, $b) {
        if ((int) $a->displayorder == (int) $b->displayorder) {
            return 0;
        } else if ((int) $a->displayorder < (int) $b->displayorder) {
            return -1;
        } else {
            return 1;
        }
    }
    );
    return $displayorder;
}

function activitystatus_get_module_with_id($objects, $id) {
    $item = array_filter($objects, function($e) use ($id) {
        return $e->id == $id;
    }); // Array containing 1 object
    return array_shift($item);
}

function activitystatus_get_course_with_id($objects, $id) {
    $item = array_filter($objects, function($e) use ($id) {
        return $e->id == $id;
    }); // Array containing 1 object
    return array_shift($item);
}
