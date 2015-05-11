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

$activityCenter->setCurrentMode('admin');

echo $activityCenter->display->showTabs('admin', 'summary-sec');

echo $OUTPUT->sign('rocket', 'Secondary Activty Report', 'Lists what everyone chose for their PD and Activities');

$info = $activityCenter->data->getUsersSummary('teachersSEC');
echo $activityCenter->display->summaryList($info);

include '../roles/common_end.php';
