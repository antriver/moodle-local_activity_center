<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Export all the goals teachers entered for PD to a CSV
 */

require_once '../../../config.php';
require_once '../sharedlib.php';

require_login();

if (!(is_admin() or is_activities_head())) {
	die("Only admins may do that.");
}

$activityCenter = new \local_activity_center\ActivityCenter();

$data = $activityCenter->data->getAllUsersGoals();

header('Content-type: text/csv');
header('Content-Disposition: attachment; filename=pdgoals.csv');
header('Pragma: no-cache');
header('Expires: 0');

$headers = array();

foreach ($data as &$row) {

	$userHasPD = false;
	$userHasGoals = false;

	foreach ($row as $key => $value) {

		if ($key == 'goals' || $key == 'pd') {

			if ($key == 'goals' && !empty($value)) {
				$userHasGoals = true;
			} else if ($key == 'pd' && !empty($value)) {
				$userHasPD = true;
			}

			$json = json_decode($value);

			unset($row->$key);

			foreach ($json as $subkey => $subvalue) {
				$row->{$key . '.' . $subkey} = $subvalue;
			}

		}

	}

	if ($userHasPD && $userHasGoals && empty($headers)) {
		$headers = array_keys(get_object_vars($row));
	}

}

unset($row); //unset the pointer from the last foreach loop

$output = fopen('php://output', 'a+');

fputcsv($output, $headers);

foreach ($data as $row) {
	fputcsv($output, get_object_vars($row));
}

fclose($output);
