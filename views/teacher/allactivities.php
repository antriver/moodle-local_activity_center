<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

echo $OUTPUT->sign('rocket', 'All Activities', 'This page shows all the activities available. Click on an Activity you would like to supervise.');

$activities = $activityCenter->data->getActivities();
echo $activityCenter->display->activityList($activities, false, 'becomeActivityManagerList');

