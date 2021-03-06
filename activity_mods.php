<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('../../enrol/locallib.php');

use \local_activity_center\ActivityCenter;

require_login();

$activity_id = required_param('activity_id', PARAM_RAW);
$new_name = optional_param('new_name', '', PARAM_RAW);
$new_summary = optional_param('new_summary', '', PARAM_RAW);
$enrol = optional_param('enrol', '', PARAM_RAW);
$toggle_visibility = optional_param('toggle_visibility', '', PARAM_RAW);
$toggle_enrollments = optional_param('toggle_enrollments', '', PARAM_RAW);
$max_participants = optional_param('max_participants', '', PARAM_INT);
$max_supervisors = optional_param('max_supervisors', '', PARAM_INT);

if ($enrol == "BULKENROL") {
    $selfenrolment_plugin = enrol_get_plugin(ActivityCenter::ENROL_PLUGIN);

    foreach ($SESSION->dnet_activity_center_individuals as $individual) {
        $user = $DB->get_record("user", array("idnumber" => $individual));
        if (!$user) {
            // Could get here if something strange happened ...
            continue;
        }
        $enrolment_instances = enrol_get_instances($activity_id, true);
        foreach ($enrolment_instances as $instance) {
            if ($instance->enrol == ActivityCenter::ENROL_PLUGIN) {
                $selfenrolment_plugin->enrol_user($instance, $user->id, 5);
            }
        }
    }

} else if ($enrol == "DEENROL") {
    $user_id = required_param('user_id', PARAM_RAW);
    $activity_id = required_param('activity_id', PARAM_RAW);

    if (!$context = context_course::instance($activity_id)) {
        //return "-1 Could not get context for course ".$short_name." with ID ".$activity_id;
    }

    $course = $DB->get_record('course', array('id' => $activity_id));
    $user = $DB->get_record('user', array('id' => $user_id));

    $selfenrolment_plugin = enrol_get_plugin(ActivityCenter::ENROL_PLUGIN);
    $enrolment_instances = enrol_get_instances($activity_id, true);
    foreach ($enrolment_instances as $instance) {
        if ($instance->enrol == ActivityCenter::ENROL_PLUGIN) {
            $selfenrolment_plugin->unenrol_user($instance, $user->id);
        }
    }
}

if (!empty($new_name)) {
    $DB->update_record('course', array(
        "id" => $activity_id,
        "fullname" => $new_name
        ));
}

if (!empty($new_summary)) {
    $DB->update_record('course', array(
        "id" => $activity_id,
        "summary" => $new_summary
        ));
}

if (!empty($toggle_visibility)) {
    $course = $DB->get_record('course', array('id' => $activity_id));
    $value = $course->visible == 1 ? 0 : 1;
    $DB->update_record('course', array(
        'id' => $activity_id,
        'visible' => $value
        ));
}

if (!empty($toggle_enrollments)) {
    $enrolment_instances = enrol_get_instances($activity_id, true);
    foreach ($enrolment_instances as $instance) {
        if ($instance->enrol == ActivityCenter::ENROL_PLUGIN) {
            $value = $instance->customint6 == 1 ? 0 : 1;
            $DB->update_record('enrol', array(
                'id' => $instance->id,
                'customint6' => $value
                ));
        }
    }
}

if (!empty($max_supervisors) or $max_supervisors === 0) {

    $metadata = ActivityCenter::getCourseMetadata($activity_id);
    if ($metadata) {
        $DB->set_field(ActivityCenter::TABLE, 'activitysupervisors', $max_supervisors, array('courseid' => $activity_id));
    } else {
        $row = new stdClass();
        $row->courseid = $activity_id;
        $row->activitysupervisors = $max_supervisors;
        $DB->insert_record(ActivityCenter::TABLE, $row);
    }
}

if (!empty($max_participants)) {
    $DB->set_field('enrol', 'customint3', $max_participants, array('courseid' => $activity_id, 'enrol' => ActivityCenter::ENROL_PLUGIN));
}
