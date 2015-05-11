<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Show form for creating a new activity
 */

echo $OUTPUT->sign('plus-sign', 'Create A New Activity', 'This page allows you to create a new activity offered to students.');

define('FORMACTION', 'add');

require dirname(dirname(__DIR__)) . '/include/newactivityform.php';


