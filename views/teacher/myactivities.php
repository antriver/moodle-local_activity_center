<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

echo $OUTPUT->sign('ok-sign', 'Your Activities', 'These are the activities you currently supervise.');

$managedActivities = $activityCenter->data->getActivitiesManaged(false, $activityCenter->getUserID());

echo $activityCenter->display->activityList($managedActivities);
