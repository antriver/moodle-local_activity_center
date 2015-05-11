<?php

function death($message) {
    echo($message);
    global $OUTPUT;
    echo $OUTPUT->footer();
    die();
}

// This stuff basically manages the permissions and redirecting.
function is_admin() {
    if (has_capability('moodle/site:config', context_system::instance())) {
        return true;
    }
}

function cohort_is_member_by_idnumber($cohortidnumber, $userid) {
    global $DB;
    if ($cohort = $DB->get_record('cohort', array('idnumber' => $cohortidnumber))) {
        return $DB->record_exists('cohort_members', array('cohortid' => $cohort->id, 'userid' => $userid));
    }
    return false;
}

function is_activities_head() {
    global $USER;
    return cohort_is_member_by_idnumber('activitiesHEAD', $USER->id);
}

function is_secretary() {
    global $USER;
    if (is_admin()) {
        return true;
    }
    return cohort_is_member_by_idnumber('secretariesALL', $USER->id);
}

function is_teacher() {
    global $USER;
    if (is_admin()) {
        return true;
    }
    return cohort_is_member_by_idnumber('teachersALL', $USER->id);
}

function is_student() {
    global $USER;
    if (is_admin()) {
        return true;
    }
    return cohort_is_member_by_idnumber('studentsALL', $USER->id);
}

function is_parent() {
    global $USER;
    if (is_admin()) {
        return true;
    }
    return cohort_is_member_by_idnumber('parentsALL', $USER->id);
}

function sign($icon, $bigtext, $littletext) {
    echo '<div class="local-alert">';
        echo '<i class="fa fa-4x fa-' . $icon . ' pull-left"></i>';
        echo '<p style="font-size:18px;font-weight:bold;">' . $bigtext  .'</p>';
        echo $littletext;
    echo '</div>';
}
