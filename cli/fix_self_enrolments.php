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
 * Script to delete any 'self' enrolment methods created for activities and move them to 'self_parents'.
 *
 * @package    local_activity_center
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(dirname(dirname(dirname(__DIR__))) . '/config.php');

$activitycenter = new \local_activity_center\ActivityCenter();

define('STUDENT_ROLE_ID', 5);
define('PARENT_ROLE_ID', 12);

function green($string) {
    return "\033[0;32m" . $string . "\033[0m";
}

function red($string) {
    return "\033[0;31m" . $string . "\033[0m";
}

// Get all activity courses.
$sql = "SELECT
    crs.id,
    crs.fullname
FROM
    {course} crs
JOIN
    {course_categories} cat
    ON
        cat.id = crs.category
WHERE
    cat.path like ?";
$params[] = "/1/%";
$activities = $DB->get_records_sql($sql, $params);

$selfplugin = enrol_get_plugin('self');
$selfparentsplugin = enrol_get_plugin('self_parents');

// Begin log file.
$unenrollog = fopen('unlucky-students-' . time() . '.csv', 'w+');

// Write headers to log.
$logline = array(
    'Activity ID',
    'Activity Name',
    'Student User ID',
    'Student Username',
    'Student Email',
    'Student ID Number',
    'Student First Name',
    'Student Last Name',
    'Number',
    'Max',
    'Enroled At'
);
fputcsv($unenrollog, $logline, ',', '"');

foreach ($activities as $activity) {

    echo $activity->id . "\t" . $activity->fullname . PHP_EOL;

    // Get enrolment methods for activity.
    $enrolrows = $DB->get_records('enrol', array('courseid' => $activity->id), 'id ASC');

    // Organise by method type.
    $enrols = array();
    foreach ($enrolrows as $enrol) {
        $enrols[$enrol->enrol][] = $enrol;
    }

    if (!empty($enrols['self']) && !empty($enrols['self_parents'])) {

        $selfparentsid = $enrols['self_parents'][0]->id;

        foreach ($enrols['self'] as $enrolid => $enrol) {

            echo "\tChanging enrolid {$enrol->id} to {$selfparentsid} in user_enrolments" . PHP_EOL;

            // Move everybody over to the self_parents method.
            // (Have to check they're not already there because there's a unique index on enrolid-userid).
            $sql = "
            UPDATE
                {user_enrolments} ue
            SET
                enrolid = {$selfparentsid}
            WHERE
                enrolid = {$enrol->id}
                AND
                userid NOT IN (
                    SELECT userid FROM {user_enrolments} WHERE enrolid = {$selfparentsid}
                )
            ";
            $result = $DB->execute($sql);

            // Now everybody has switched over to the self_parents method, delete the self one.
            echo "\tDelete self enrolment method {$enrol->id}" . PHP_EOL;
            $selfplugin->delete_instance($enrol);
        }
    }

    // Now we need to change parents to have the parent role if they enroled as a student.
    // Get parents who have the student role.
    $parentswithstudentrole = $DB->get_records_sql("
        SELECT
            ra.id,
            ra.roleid,
            ra.userid,
            crs.id AS courseid,
            ctx.id AS contextid,
            role.name,
            usr.username,
            usr.email
        FROM
            {role_assignments} ra
        JOIN
            {context} ctx ON ctx.id = ra.contextid
        JOIN
            {course} crs ON crs.id = ctx.instanceid
        JOIN
            {role} role ON role.id = ra.roleid
        JOIN
            {user} usr ON usr.id = ra.userid
        WHERE
            crs.id = '{$activity->id}'
            AND
            role.name = 'Student'
            AND
            usr.email NOT LIKE '%@student.ssis-suzhou.net'
    ");

    foreach ($parentswithstudentrole as $userrole) {
        echo "\tAssigning role "
            . PARENT_ROLE_ID
            . " to user {$userrole->userid} in course {$userrole->courseid}" . PHP_EOL;
        role_assign(PARENT_ROLE_ID, $userrole->userid, $userrole->contextid);

        echo "\tRemoving role "
            . STUDENT_ROLE_ID
            . " from user {$userrole->userid} in course {$userrole->courseid}" . PHP_EOL;
        role_unassign(STUDENT_ROLE_ID, $userrole->userid, $userrole->contextid);
    }

    // Finally, we might have too many students enroled now (we don't limit parents)

    if (isset($enrols['self_parents'][0])) {
        $selfparentsenrol = $enrols['self_parents'][0];

        $maxstudents = $selfparentsenrol->customint3;
        if ($maxstudents) {
            // Would have to do something for if parents are included in the limit, but we don't use that on any course.
            echo "\tLimiting to {$maxstudents} students" . PHP_EOL;

            // Count enroled students only.
            $q = 'select usr.*, ue.timecreated as enroledat
            from {user_enrolments} ue
            join {enrol} enrl on enrl.id = ue.enrolid
            join {context} ctx on ctx.instanceid = enrl.courseid and ctx.contextlevel = 50
            join {role_assignments} ra on ra.userid = ue.userid and ra.contextid = ctx.id
            join {user} usr on usr.id = ue.userid
            where
                ue.enrolid = ?
                and
                ra.roleid != ?
            order by enroledat ASC
            ';
            $students = $DB->get_records_sql(
                $q,
                array(
                    $selfparentsenrol->id,
                    $selfparentsenrol->customchar1 // Parent role id.
                ));

            $i = 0;
            foreach ($students as $student) {
                ++$i;
                $enroledat = date('Y-m-d H:i:s e', $student->enroledat);
                $line = $i . "\t" . $student->id . "\t" . $student->username . "\t" . $student->enroledat . "\t" . $enroledat;

                if ($i <= $maxstudents) {
                    echo "\t\t" . green($line) . PHP_EOL;
                } else {
                    echo "\t\t" . red($line) . PHP_EOL;

                    // Sorry bro.
                    $logline = array(
                        $activity->id,
                        $activity->fullname,
                        $student->id,
                        $student->username,
                        $student->email,
                        $student->idnumber,
                        $student->firstname,
                        $student->lastname,
                        $i,
                        $maxstudents,
                        $enroledat
                    );
                    fputcsv($unenrollog, $logline, ',', '"');
                    $selfparentsplugin->unenrol_user($selfparentsenrol, $student->id);
                }
            }
        }
    }


}
fclose($unenrollog);

