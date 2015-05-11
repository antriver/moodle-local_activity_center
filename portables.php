<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function derive_plugin_path_from($stem) {
    // Moodle really really should be providing a standard way to do this
    return "/local/activity_center/{$stem}";
}

function setup_page() {
    global $PAGE;
    global $OUTPUT;

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url(derive_plugin_path_from('index.php'));
    $PAGE->set_title("Activities Hub");
    $PAGE->set_heading("Activities Hub");

    echo $OUTPUT->header();
}
