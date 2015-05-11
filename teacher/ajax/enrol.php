<?php

/**
 * Enrol the current user as a manager into an activity
 */

require_once '../../../../config.php';

require_login();

// FIXME: No permission checks here!

$courseID = required_param('courseid', PARAM_RAW);
$action = required_param('action', PARAM_RAW);

$activityCenter = new \local_activity_center\ActivityCenter();

switch ($action) {
	case 'enrol':

		$success = $activityCenter->addManager($courseID, $activityCenter->getUserID());

		break;
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode(array('success' => $success));
