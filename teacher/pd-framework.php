<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Allow teachers to sign up for PD
 */

include '../roles/common_top.php';

$activityCenter->setCurrentMode('teacher');

echo $activityCenter->display->showTabs('teacher', 'pdframework');

sign('ok-sign', 'Choose PD Strand', 'Please read through Section 2:  Supporting Professional Growth in the <strong> <a target="_new" href="https://dragonnet.ssis-suzhou.net/pluginfile.php/74998/mod_resource/content/0/Goal%20Setting%20Guidance%202014-15.pdf">Goal Setting Guidance</a></strong> and select the appropriate PD strand. Your PD selection should be aligned with the goals you have set.');

echo $activityCenter->display->displayPDFrameworkChoices($activityCenter->getUserID());

include '../roles/common_end.php';
