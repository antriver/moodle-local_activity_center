<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require '../../config.php';

use local_activity_center\ActivityCenter;

$activityCenter = new ActivityCenter();

$mode = required_param('mode', PARAM_RAW);

if ($activityCenter->setCurrentMode($mode)) {
	redirect(ActivityCenter::PATH);

} else {

	die('Invalid mode.');

}
