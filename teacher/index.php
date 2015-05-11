<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Display all activities so a teacher can pick which ones they want to manage
 */

include '../roles/common_top.php';

require_login();

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'overview');

sign('ok-sign', 'Activity & PD Overview', 'These are the activities you currently supervise and the PD you have signed up for.');

$pdoutput = $activityCenter->data->getUserPDSelection($activityCenter->getUserID());
$goal = $activityCenter->data->getUserGoal($activityCenter->getUserID());

$managedActivities = $activityCenter->data->getActivitiesManaged(false, $activityCenter->getUserID());

// echo $activityCenter->display->displayPDFramework($pdoutput);
echo $activityCenter->display->overview($goal, $managedActivities, $pdoutput);

include '../roles/common_end.php';
