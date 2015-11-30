<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$confirm = optional_param('confirm', '', PARAM_RAW);
if ($confirm == "YES") {

    $rows = array();

    $sql = "
    SELECT
        usr.id as userid,  crs.id as courseid
    FROM
        {user} usr
    JOIN
        {user_enrolments} usrenrl
    ON
        usrenrl.userid = usr.id
    JOIN
        {enrol} enrl
    ON
        enrl.id = usrenrl.enrolid
    JOIN
        {course} crs
    ON
        crs.id = enrl.courseid
    JOIN
        {course_categories} ccat
    ON
        ccat.id = crs.category
    WHERE
        usr.id = ? and
        ccat.path like ?
    ";

    // first go through the individuals that have been built in the list part

    $enrolplugin = enrol_get_plugin(\local_activity_center\ActivityCenter::ENROL_PLUGIN);

    foreach ($SESSION->dnet_activity_center_individuals as $individual) {
        $user = $DB->get_record("user", array("idnumber" => $individual));
        if (!$user) {
            // Could get here if something strange happened ...
            continue;
        }

        $params = array($user->id, '/1/%');
        foreach ($DB->get_records_sql($sql, $params) as $row) {
            $enrolment_instances = enrol_get_instances($activity_id, true);
            foreach ($enrolment_instances as $instance) {
                if ($instance->enrol == \local_activity_center\ActivityCenter::ENROL_PLUGIN) {
                    $enrolplugin->unenrol_user($instance, $user->id);
                }
            }
        }
    }

} else {
    sign("info-sign", "Bulk de-enrollment",
        "Click to clear selected users from all activities.");
    echo '<br ?>';
    $href = '?confirm=YES&mode='.DEENROL;
    echo '<ul class="buttons"><a class="btn" href="'.$href.'">Bulk Unenrol</a></ul>';
}
