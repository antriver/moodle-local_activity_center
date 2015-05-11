<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


include 'common_top.php';

if (empty($mode)) {
    $mode = "Browse";
}
output_tabs($mode, array("Browse", "Approve"));



include 'common_end.php';
