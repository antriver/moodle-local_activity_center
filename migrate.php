<?php

/**
 * Import activity data form old course_ssis_metadata table
 */

define('CLI_SCRIPT', 1);
require dirname(dirname(__DIR__)) . '/config.php';

$rows = $DB->get_records_sql(
    'SELECT CONCAT(courseid, field), * FROM {course_ssis_metadata} WHERE field = ? OR field = ?',
    [
        'activityseason',
        'activitysupervisors'
    ]);

$courses = [];

foreach ($rows as $row) {
    @$courses[$row->courseid][$row->field] = $row->value;
}

print_r($courses);

foreach ($courses as $courseid => $course) {
    $course['courseid'] = $courseid;
    $DB->insert_record('local_activity_center_crs', (object)$course);
}
