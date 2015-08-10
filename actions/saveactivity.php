<?php

/**
 * Saves changes to an activity / creates a new activity
 *
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require '../../../config.php';

$activityCenter = new \local_activity_center\ActivityCenter();

$action = required_param('action', PARAM_RAW);

switch ($action) {

    case 'add':

        if ($activityCenter->getCurrentMode() != 'admin') {
            die("You need to be an admin to create an activity.");
        }

        $name = required_param('name', PARAM_RAW);
        $categoryID = required_param('categoryid', PARAM_INT);
        $summary = required_param('summary', PARAM_RAW);
        $season = required_param('season', PARAM_RAW);
        $supervisors = optional_param('supervisors', 0, PARAM_INT);
        $maxEnrolledUsers = optional_param('maxEnrolledUsers', 0, PARAM_INT);
        $parentsCanEnrol = optional_param('parentsCanEnrol', 1, PARAM_INT);

        // Create the new course

        $seasonString = 'S' . implode(',S', $season);
        if ($seasonString === 'S1,S2,S3') {
            $seasonString = 'ALL';
        }

        $shortname = strtoupper($name);
        $shortname = str_replace(' ', '', $shortname);

        $courseData = new \stdClass();
        $courseData->fullname = '(' . $seasonString . ') ' . $name;
        $courseData->shortname = $shortname;
        $courseData->summary = $summary;
        $courseData->format = 'onetopic';
        $courseData->category = $categoryID;

        require_once $CFG->dirroot . '/course/lib.php';
        $course = create_course($courseData);

        if (!$course) {
            die("Unable to create the course.");
        }

        // Add enrolment instances to the new course
        $activityCenter->data->addEnrolmentInstances($course, $maxEnrolledUsers, $parentsCanEnrol);

        redirect($activityCenter->getPath() . 'view.php?view=activitycreated&id=' . $course->id);

        break;
}
