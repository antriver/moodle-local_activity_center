<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Loops through selected activities
// and sets the visible flag as appropriate
// Doesn't actually 'toggle' despite the name

$on_or_off = optional_param('on_or_off', '', PARAM_RAW);

if (!empty($on_or_off)) {

    $enabled = $on_or_off == 'ON' ? true : false;

    foreach ($SESSION->dnet_activity_center_activities as $courseid) {
        $activityCenter->data->setSelfEnrolmentEnabled($courseid, $enabled);
    }

    sign('thumbs-up', "Done.", "All the selected activities have been changed.");

} else {

    sign('info-sign', 'Enable enrollments for selected activities.',
        'Use the buttons below to complete the action.');

    $buttons = '
    <ul class="buttons">
    <a class="btn" href="?mode='.TOGGLEENROLLMENTS.'&on_or_off=ON"><i class="fa fa-check-square-o"></i> Turn on enrollments for selected activities</a>
    <br />
    <a class="btn" href="?mode='.TOGGLEENROLLMENTS.'&on_or_off=OFF"><i class="fa fa-square-o"></i> Turn off enrollments for selected activities</a>
    </ul>
    ';
    echo $buttons;

}
