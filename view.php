<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Displays a view from the views directory
 */

require '../../config.php';

$activityCenter = new \local_activity_center\ActivityCenter();

$view = optional_param('view', false, PARAM_RAW);

if (!$activityCenter->isValidView($view)) {
	$view = false;
}

if (!$view) {
    $mode = $activityCenter->getCurrentMode();
	$view = $view ? $view : $activityCenter->defaultViewForMode($mode);
	redirect("view.php?view={$view}");
	exit();
}

$mode = $activityCenter->getCurrentMode();

$PAGE->set_title('Activity Center');
$PAGE->set_heading('Activity Center');

echo $OUTPUT->header();

// Show mode switching tabs
echo $activityCenter->display->showTabs($mode, $view);

include "./views/{$mode}/{$view}.php";

echo $OUTPUT->footer();
