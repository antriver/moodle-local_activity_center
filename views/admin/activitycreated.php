<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Success page after creating an activity
 */

$courseID = optional_param('id', '', PARAM_INT);


$signText = 'Your activity has been successfuly created.';
if ($courseID) {
	$signText .= '<p><a class="btn" href="/course/view.php?id=' . $courseID . '">Go to activity</a></p>';
}

sign('ok-sign', 'Activity Created', $signText);
