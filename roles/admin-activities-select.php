<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

echo '<div class="row-fluid">';
    echo '<div class="span6">';
        output_act_form("Enter the name of an activity here", "activities", SELECT);
    echo '</div>';
    echo '<div class="span6">';
        output_act_cat_form("Enter the category name here", "activity_categories", SELECT);
    echo '</div>';
echo '</div>';

$courseid = optional_param('courseid', '', PARAM_RAW);
$remove = optional_param('remove', '', PARAM_RAW);
if (!empty($courseid)) {
   if ($remove == "YES") {
        $ref = array_search($courseid, $SESSION->dnet_activity_center_activities);
        if (!($ref === false)) {
            unset($SESSION->dnet_activity_center_activities[$ref]);
        }
   } else {
        add_activity_to_sesssion($courseid);
   }
}

$catid = optional_param('catid', '', PARAM_RAW);
if (!empty($catid)) {
    // get every course that is under that category, and add it to the session
    $where = "category = ?";
    $params = array($catid);
    $sort = 'fullname';
    $fields = 'fullname, id';
    $courses = $DB->get_records_select("course", $where, $params, $sort, $fields);
    foreach ($courses as $course) {
        add_activity_to_sesssion($course->id);
    }
}

if (!empty($SESSION->dnet_activity_center_activities)) {
    foreach ($SESSION->dnet_activity_center_activities as $activity) {
        $activity = $DB->get_record("course", array("id" => $activity));
        if (!$activity) {
            // Could get here if something strange happened ...
            continue;
        }

        activity_box($activity, $remove = true);

    }

    ?>
    <ul class="buttons">
    <a class="btn" href="?mode=<?= CLEAR ?>"><i class="fa fa-times"></i> <?= CLEAR ?></a>
    </ul>
    <?php
}

