<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('portables.php');
require_once('sharedlib.php');
require_once('../../cohort/lib.php');

use \local_activity_center\ActivityCenter;

require_login();

function as_teacher() {
    redirect(derive_plugin_path_from('roles/teachers.php') . '?' . http_build_query($_GET));
}

if (is_admin() or is_activities_head()) {

    if (isset($SESSION->dnet_activity_center_submode) && $SESSION->dnet_activity_center_submode == "becometeacher") {
        as_teacher();
    } else {
        redirect(derive_plugin_path_from('roles/admin.php')  . '?' . http_build_query($_GET));
    }

} else if (is_teacher()) {

    redirect('teacher/index.php');

} else if (is_student()) {

    redirect('student/index.php');

}
