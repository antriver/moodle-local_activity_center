<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

use \local_activity_center\ActivityCenter;

function get_user_activity_enrollments($idnumber) {
    global $DB;

    $sql = "
    select
        crs.fullname, usr.idnumber, crs.id as course_id
    from
        {enrol} enrl
    join
        {user_enrolments} usrenrl
            on usrenrl.enrolid = enrl.id
    join
        {course} crs
            on enrl.courseid = crs.id
    join
        {user} usr
            on usrenrl.userid = usr.id
    where
        enrl.enrol = ? and
        usr.idnumber = ?
    order by
        crs.fullname";

    $params = array(ActivityCenter::ENROL_PLUGIN, $idnumber);
    return $DB->get_records_sql($sql, $params);
}


function get_user_roles_in_activity($userid, $courseid) {
    $context = context_course::instance($courseid);
    return get_user_roles($context, $userid, true);
}

function YESno($item) {
    return $item == 1 ? "YES" : "no";
}

function get_activity_selfenrol_instance($activity_id) {
    $enrolment_instances = enrol_get_instances($activity_id, true);
    $gotit = false;
    foreach ($enrolment_instances as $instance) {
        if ($instance->enrol == ActivityCenter::ENROL_PLUGIN) {
            $gotit = $instance;
        }
    }
    return $gotit;
}
