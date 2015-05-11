<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once '../../../config.php';
require_once '../portables.php';
require_once '../output.php';
require_once '../sharedlib.php';

$activityCenter = new \local_activity_center\ActivityCenter();

$mode = optional_param('mode', '', PARAM_RAW);

setup_page();
