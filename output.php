<?php

/**
 * @package    local_activity_center
 * @copyright  Adam Morris <www.mistermorris.com> and Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once 'portables.php';
require_once 'activities.php';

use \local_activity_center\ActivityCenter;

// Some display stuff
function output_begin_table($message) {
    echo '<div>$message</div><br />';
    echo '<table class="userinfotable htmltable" width="100%"><thead></thead><tbody>';
}

function output_end_table() {
    echo '</tbody></table>';
}

function output_tabs($kind, $tabs, $mode_name="mode") {

    if (count($tabs) < 1) {
        return '';
    }

    $t = '<div class="tabs text-center">';
    $t .= '<div class="btn-group">';

    foreach ($tabs as $label) {

        if ($label == START_AGAIN && (is_admin() or is_activities_head())) {
            $href = derive_plugin_path_from("session_mod.php?submode=&value=NO");
        } else {
            $href = derive_plugin_path_from("index.php?".$mode_name."={$label}");
        }

        $icon = '';
        if ($icon_defined = array_search($label, get_defined_constants(), true)) {
            if ($which_icon = constant($icon_defined.'_ICON')) {
                $icon = '<i class="fa fa-'.$which_icon.'"></i> ';
            }
        }

        $t .= '<a class="btn btn-sm btn-small ' . ($label == $kind ? ' active': '') . '" href="' . $href . '">';
        $t .= $icon . $label;
        $t .= '</a>';
    }

    $t .= '</div></div>';

    echo $t;
}

function activity_box($activity, $remove=false) {
    global $OUTPUT;
    global $DB;
    global $CFG;

    $table = new html_table();
    $table->attributes['class'] = 'userinfobox htmltable';
    //$table->attributes['style'] = "width:45%;";   // how to make it show in rows?

    $row = new html_table_row();

    $row->cells[0] = new html_table_cell();
    $row->cells[0]->attributes['class'] = 'left side';

    /*$icon = $DB->get_record('course_ssis_metadata', array("courseid" => $activity->id));
    if (!empty($icon)) {
        $row->cells[0]->text = '<i style="margin-left:20px;" class="fa fa-'.$icon->value.' fa-4x"></i>';
    } else {
        $row->cells[0]->text = "";
    }*/


    $row->cells[0]->text = ""; // Course icon used to be shown here.

    $row->cells[1] = new html_table_cell();
    $row->cells[1]->attributes['class'] = 'content';
    $category = $DB->get_record("course_categories", array("id" => $activity->category));
    if (empty($category)) {
        $cat_text = "Can't find category!";
    } else {
        $cat_text = 'in '.$category->name;
    }
    $dialog = '<div id="dialog_'.$activity->id.'" title="Rename" style="display:none"> Enter the new name for this activity:
    <form id="dialog_rename_'.$activity->id.'" action="'.derive_plugin_path_from('activity_mods.php').'">
    <input name="activity_id" type="hidden" value="'.$activity->id.'" />
    <input id="dialog_rename_input_'.$activity->id.'" style="width:100%;margin-top:5px;" name="new_name" autofocus="autofocus" size="100" type="text" value="'.$activity->fullname.'" />
    <br /><br />Enter the description for this activity (<b>including html</b>):
    <textarea id="dialog_summary_input_'.$activity->id.'" style="width:100%;margin-top:5px;" rows=10 name="new_name" autofocus="autofocus" size="100" type="text" />'.$activity->summary.'</textarea>
    </form>
    .</div>';
    $script = "<script>

    $('#dialog_rename_".$activity->id."').on(\"submit\", function (e) {
        e.preventDefault();
        var formURL = \"".derive_plugin_path_from('activity_mods.php') . "\";
        var formData = {
            \"activity_id\": \"".$activity->id."\",
            \"new_name\": $('#dialog_rename_input_".$activity->id."').val(),
            \"new_summary\": $('#dialog_summary_input_".$activity->id."').val()
        };
        $.ajax(
        {
            url : formURL,
            data: formData,
            async: true,
            type: \"GET\",
            success: function(data, textStatus, jqXHR)
            {
                $('#dialog_".$activity->id."').dialog('close');
                window.location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert('Could not change the name for some reason... you will have to do it manually (boo!)');
            }
        });
    });

    $('#rename_".$activity->id."').on(\"click\", function(e) {
        e.preventDefault();
        $(\"#dialog_".$activity->id."\").dialog({
            minWidth: 450,
            draggable: false,
            modal: true,
            show: { effect: \"drop\", duration: 400 },
            buttons: [
                {
                    id: 'ok_button_".$activity->id."',
                    text: \"OK\",
                    click: function() {
                        $('#dialog_rename_".$activity->id."').submit();
                    }
                },
            ],
            open: function () {
                $('#ok_button_".$activity->id."').focus();
        }

        });

    });
    </script>";
    $edit_name = '&nbsp;&nbsp;<a id="rename_'.$activity->id.'"   href="#"><i class="fa fa-cog"></i></a>&nbsp;&nbsp;';
    $row->cells[1]->text = '<div class="username">'.$activity->fullname.$edit_name.' ('. $cat_text.')</div>';
    $row->cells[1]->text .= $dialog.$script;
    $row->cells[1]->text .= '<table class="userinfotable">';

    $instance = get_activity_selfenrol_instance($activity->id);

    if ($remove) {
        $row->cells[1]->text .= '<tr>
            <td style="width:220px;">Remove from list:</td>
            <td><a href="?mode='.SELECT.'&courseid='.$activity->id.'&remove=YES"><i class="fa fa-times"></i></a></td>
        </tr>';

        $allow_new_enrollments = $instance ? YESno($instance->customint6) : '';

        // Visibility
        $icon = $activity->visible ? 'fa-check-square-o' : 'fa-square-o';
        $visibility = YESno($activity->visible);
        $row->cells[1]->text .= '<tr>
            <td>'."Visible (in user's menus)".'</td>
            <td><a id="'.$activity->id.'_toggle_vis" href=""><i class="fa '.$icon.'"></i></a></td>
            <script>
            $("#'.$activity->id.'_toggle_vis").on("click", function (e) {
                e.preventDefault();
                formURL = "'.derive_plugin_path_from('activity_mods.php').'";
                formData = {
                    "activity_id": "'.$activity->id.'",
                    "toggle_visibility": "YES"
                };
                $.ajax(
                {
                    url : formURL,
                    data: formData,
                    async: true,
                    type:  "GET",
                    success: function(data, textStatus, jqXHR)
                    {
                        window.location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown)
                    {
                        console.log(textStatus);
                        console.log(errorThrown);
                        alert("fail with error \'" + textStatus + "\'");
                    }
                });
            });
            </script>
        </tr>';

        // Allow new enrollments
        $icon = $allow_new_enrollments == "YES" ? 'fa-check-square-o' : 'fa-square-o';
        $row->cells[1]->text .= '<tr>
            <td>'."Allow new enrollments".'</td>
            <td><a id="'.$activity->id.'_toggle_ne" href=""><i class="fa '.$icon.'"></i></a></td>
        </tr>
        <script>
        $("#'.$activity->id.'_toggle_ne").on("click", function (e) {
            e.preventDefault();
            formURL = "'.derive_plugin_path_from('activity_mods.php').'";
            formData = {
                "activity_id": "'.$activity->id.'",
                "toggle_enrollments": "YES"
            };
            $.ajax(
            {
                url : formURL,
                data: formData,
                async: true,
                type:  "GET",
                success: function(data, textStatus, jqXHR)
                {
                    window.location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    console.log(textStatus);
                    console.log(errorThrown);
                    alert("fail with error \'" + textStatus + "\'");
                }
            });
        });
        </script>
';

    }

    $row->cells[1]->text .= '<tr>
        <td>Convenient Links:</td>
        <td>
        <a target="_blank" href="'.$CFG->wwwroot.'/course/view.php?id='.$activity->id.'"><i class="fa fa-rocket"></i> Activity Page</a>&nbsp;&nbsp;&nbsp;
        <a target="_blank" href="'.$CFG->wwwroot.'/course/edit.php?id='.$activity->id.'"><i class="fa fa-cogs"></i> Course Settings</a>&nbsp;&nbsp;&nbsp;
        <a target="_blank" href="'.$CFG->wwwroot.'/enrol/' . ActivityCenter::ENROL_PLUGIN . '/edit.php?courseid='.$activity->id.'&id='. ($instance ? $instance->id : '') .'"><i class="fa fa-tachometer"></i> Enrolment Settings</a>&nbsp;&nbsp;&nbsp;
        <a target="_blank" href="'.$CFG->wwwroot.'/enrol/users.php?id='.$activity->id.'"><i class="fa fa-user"></i> Enrolled Users</a>&nbsp;&nbsp;&nbsp;
        </td>
    </tr>';

    # output some basic stats about the activity

    $manager_role = MANAGER_ROLE_ID;
    $editor_role = TEACHER_ROLE_ID;
    $participant_role = STUDENT_ROLE_ID;

    $role_info = array(
        array( "id" => $manager_role, "name" => "Supervisors:" ),
        array( "id" => $editor_role, "name" => "Editors:" ),
        array( "id" => $participant_role, "name" => "# Participants (students only):")
        );

    $context = context_course::instance($activity->id);

    foreach ($role_info as $role) {
        $role_id = $role["id"];
        $users = get_role_users($role_id, $context);
        $count = count($users);
        $value = '';
        if (substr($role["name"], 0, 1) == "#") {
            $value = $count;
        } else {
            if ($count > 10) {
                $value = "> 10";
            } else {
                $i = 1;
                foreach ($users as $user) {
                    $value .= $user->firstname . ' ' . $user->lastname;
                    if ($i < $count) {
                        $value .= '&nbsp;&nbsp;<b>&amp;</b>&nbsp;&nbsp;';
                    }
                    $i += 1;
                }
            }
        }
        if (empty($value)) {
            $value = '0';
        }
        $row->cells[1]->text .= '<tr>
            <td>'.$role["name"].'</td>
            <td>'.$value.'</td>
        </tr>';

    }

    $max_participants = $DB->get_field('enrol', 'customint3', array('courseid' => $activity->id, 'enrol' => ActivityCenter::ENROL_PLUGIN));

    $dialog = '<div id="dialog_adjust_max_participants_'.$activity->id.'" title="Edit Max Participants" style="display:none"> Enter the maximum number of participants:
    <form id="dialog_adjust_max_participants_'.$activity->id.'" action="'.derive_plugin_path_from('activity_mods.php').'">
    <input name="activity_id" type="hidden" value="'.$activity->id.'" />
    <input id="dialog_adjust_max_participants_input_'.$activity->id.'" style="width:100%;margin-top:5px;" name="new_name" autofocus="autofocus" size="100" onclick="this.select()" type="text" value="'.$max_participants.'" />
    </form>
    .</div>';
    $script = "<script>

    $('#dialog_adjust_max_participants_".$activity->id."').on(\"submit\", function (e) {
        e.preventDefault();
        var formURL = \"".derive_plugin_path_from('activity_mods.php') . "\";
        var formData = {
            \"activity_id\": \"".$activity->id."\",
            \"max_participants\": $('#dialog_adjust_max_participants_input_".$activity->id."').val()
        };
        $.ajax(
        {
            url : formURL,
            data: formData,
            async: true,
            type: \"GET\",
            success: function(data, textStatus, jqXHR)
            {
                $('#dialog_max_participants_".$activity->id."').dialog('close');
                window.location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert('Could not change the name for some reason... you will have to do it manually (boo!)');
            }
        });
    });

    $('#adjust_max_participants_".$activity->id."').on(\"click\", function(e) {
        e.preventDefault();
        $(\"#dialog_adjust_max_participants_".$activity->id."\").dialog({
            minWidth: 450,
            draggable: false,
            modal: true,
            show: { effect: \"drop\", duration: 400 },
            buttons: [
                {
                    id: 'ok_button_".$activity->id."',
                    text: \"OK\",
                    click: function() {
                        $('#dialog_adjust_max_participants_".$activity->id."').submit();
                    }
                },
            ],
            open: function () {
                $('#ok_button_".$activity->id."').focus();
        }

        });

    });
    </script>";
    $edit_name = '&nbsp;&nbsp;<a id="adjust_max_participants_'.$activity->id.'"   href="#"><i class="fa fa-cog"></i></a>&nbsp;&nbsp;';
    $row->cells[1]->text .= '<tr><td>'.'# Max participants'.'</td>';
    $row->cells[1]->text .= '<td>'.$max_participants.$edit_name.'</td></tr>';
    $row->cells[1]->text .= $dialog;
    $row->cells[1]->text .= $script;

    $metadata = ActivityCenter::getCourseMetadata($activity->id);
    $max_supervisors = $metadata ? $metadata->activitysupervisors : null;

    $dialog = '<div id="dialog_adjust_max_supervisors_'.$activity->id.'" title="Edit Max Supervisors" style="display:none"> Enter the maximum number of supervisors:
    <form id="dialog_adjust_max_supervisors_'.$activity->id.'" action="'.derive_plugin_path_from('activity_mods.php').'">
    <input name="activity_id" type="hidden" value="'.$activity->id.'" />
    <input id="dialog_adjust_max_supervisors_input_'.$activity->id.'" style="width:100%;margin-top:5px;" name="new_name" autofocus="autofocus" size="100" onclick="this.select()" type="text" value="'.$max_supervisors.'" />
    </form>
    </div>';
    $script = "<script>

    $('#dialog_adjust_max_supervisors_".$activity->id."').on(\"submit\", function (e) {
        e.preventDefault();
        var formURL = \"".derive_plugin_path_from('activity_mods.php') . "\";
        var formData = {
            \"activity_id\": \"".$activity->id."\",
            \"max_supervisors\": $('#dialog_adjust_max_supervisors_input_".$activity->id."').val()
        };
        $.ajax(
        {
            url : formURL,
            data: formData,
            async: true,
            type: \"GET\",
            success: function(data, textStatus, jqXHR)
            {
                $('#dialog_max_supervisors_".$activity->id."').dialog('close');
                window.location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert('Could not change the name for some reason... you will have to do it manually (boo!)');
            }
        });
    });

    $('#adjust_max_supervisors_".$activity->id."').on(\"click\", function(e) {
        e.preventDefault();
        $(\"#dialog_adjust_max_supervisors_".$activity->id."\").dialog({
            minWidth: 450,
            draggable: false,
            modal: true,
            show: { effect: \"drop\", duration: 400 },
            buttons: [
                {
                    id: 'ok_button_".$activity->id."',
                    text: \"OK\",
                    click: function() {
                        $('#dialog_adjust_max_supervisors_".$activity->id."').submit();
                    }
                },
            ],
            open: function () {
                $('#ok_button_".$activity->id."').focus();
        }

        });

    });
    </script>";
    $edit_name = '&nbsp;&nbsp;<a id="adjust_max_supervisors_'.$activity->id.'"   href="#"><i class="fa fa-cog"></i></a>&nbsp;&nbsp;';

    $row->cells[1]->text .= '<tr><td>'.'# Max supervisors'.'</td>';

    if ($max_supervisors) {
        $value = $max_supervisors;
    } else {
        $value = 'undefined';
    }

    $row->cells[1]->text .= '<td>'.$value.$edit_name.'</td></tr>';
    $row->cells[1]->text .= $dialog;
    $row->cells[1]->text .= $script;

    $row->cells[1]->text .= '</table>';

    $table->data = array($row);
    echo html_writer::table($table);
}

function user_box($user, $remove=false) {
    global $OUTPUT;

    $table = new html_table();
    $table->attributes['class'] = 'userinfobox';
    //$table->attributes['style'] = "width:45%;";   // how to make it show in rows?

    $row = new html_table_row();

    $row->cells[0] = new html_table_cell();
    $row->cells[0]->attributes['class'] = 'left side';
    $row->cells[0]->text = $OUTPUT->user_picture($user, array('size' => 100, 'courseid' => 1));

    $row->cells[1] = new html_table_cell();
    $row->cells[1]->attributes['class'] = 'content';
    $row->cells[1]->text = '<div class="username">'.$user->firstname. ' '. $user->lastname .' ('. $user->department.')</div>';
    $row->cells[1]->text .= '<table class="userinfotable">';

    if ($remove) {
        $row->cells[1]->text .= '<tr>
            <td>Remove from list:</td>
            <td><a href="?mode='.SELECT.'&powerschool='.$user->idnumber.'&remove=YES"><i class="fa fa-times"></i></a></td>
        </tr>';
    }

    $activities = get_user_activity_enrollments($user->idnumber);
    if (empty($activities)) {
        $row->cells[1]->text .= '<tr>
            <td>Activities:</td>
            <td>None</td>
        </tr>';
    }
    foreach ($activities as $activity) {
        $deenrol_button = "";
        if ($remove == "YES") {
            $deenrol_click = "deenrol_".$activity->course_id.'_'.$user->id;
            $deenrol_button = ' <a id="'.$deenrol_click.'" href=""><i class="fa fa-minus-circle"></i></a>';
            $deenrol_button .= '<script>
                $("#'.$deenrol_click.'").on("click", function (e) {
                    e.preventDefault();
                    var formURL = "'.derive_plugin_path_from("activity_mods.php").'";
                    var formData = {
                        "enrol": "DEENROL",
                        "user_id": "'.$user->id.'",
                        "activity_id": "'.$activity->course_id.'"
                    };
                    $.ajax(
                    {
                        url : formURL,
                        data: formData,
                        async: true,
                        type:  "GET",
                        success: function(data, textStatus, jqXHR)
                        {
                            alert("Successfully unenrolled!");
                            window.location.reload();
                        },
                        error: function(jqXHR, textStatus, errorThrown)
                        {
                            console.log(textStatus);
                            console.log(errorThrown);
                            alert("fail with error \'" + textStatus + "\'");
                        }
                    });
                return false;
                });
            </script>';
        }
        $row->cells[1]->text .= '<tr>
            <td>Activity:</td>
            <td>'.$activity->fullname.$deenrol_button.'</td>
        </tr>';
    }

    $row->cells[1]->text .= '</table>';

    $table->data = array($row);
    echo html_writer::table($table);
}


function output_submode_choice($kind, $tabs, $mode_name="mode") {

    if (count($tabs) < 1) {
        return '';
    }

    $t = '<div class="tabs text-center">';
    $t .= '<div class="btn-group">';


    foreach ($tabs as $label) {
        $icon = null;
        $href = null;
        $label_lower = str_replace(" ", "", strtolower($label));
        switch ($label_lower) {
            case "manageactivities":
                $icon = "rocket";
                break;
            case "manageindividuals":
                $icon = "user";
                break;
            case 'createnewactivity':
                $icon = "plus-circle";
                $href = '../view.php?view=newactivity';
                break;
            case 'exportpdchoices':
                $icon = "download";
                $href = '../teacher/export.php';
                break;
            case 'secsummaryreport':
                $icon = "download";
                $href = '../teacher/summary-sec.php';
                break;
            case 'elemsummaryreport':
                $icon = "download";
                $href = '../teacher/summary-elem.php';
                break;
            case "becometeacher":
                $icon = "magic";
                $href = '../teacher';
                break;
        }

        if (!$href) {
            $href = derive_plugin_path_from("session_mod.php?submode=".$label_lower."&value=YES");
        }

        $t .= '<a class="btn btn-sm btn-small ' . ($label == $kind ? ' active': '') . '" href="' . $href . '">';
        if ($icon) {
            $t .= '<i class="fa fa-' . $icon . '"></i> ';
        }
        $t .= $label;
        $t .= '</a>';
    }

    $t .= '</div></div>';

    echo $t;
}

function output_act_form($placeholder="Type something, dude", $kind="activities", $mode="") {
    $path_to_index = "";
    $path_to_query = "/local/activity_center/query/{$kind}.php";

    ?>
<form id="activity_user_entry" action="" method="get">
<input name="" style="box-sizing:border-box; width:100%;" onclick="this.select()"
    type="text" autofocus="autofocus" id="activity" placeholder="<?= $placeholder ?>"/><br />
<input name="courseid" type="hidden" id="courseid" value=""/>
<input name="mode" type="hidden" id="select" value="<?= $mode ?>"/>
</form><br />
<script>
$("#activity").autocomplete({
            autoFocus: true,
            source: "<?= $path_to_query ?>",
            minLength: 3,
            select: function (event, ui) {
                event.preventDefault();
                $("#activity").val(ui.item.label);
                $("#courseid").val(ui.item.value);
                $("#activity_user_entry").submit();
            },
            change: function (event, ui) {   // TODO: determine if I really really need this
                if (ui != null) {
                    event.preventDefault();
                }
            },
            focus: function (event, ui) {
                event.preventDefault();
            },
        });
</script>
<?php
}

function output_act_cat_form($placeholder="Type something, dude", $kind="students", $mode="") {
    $path_to_index = "";
    $path_to_query = "/local/activity_center/query/{$kind}.php";

    ?>
<form id="cat_user_entry" action="" method="get">
<input name="" style="box-sizing:border-box; width:100%;" onclick="this.select()"
    type="text" id="activity_cat" placeholder="<?= $placeholder ?>"/><br />
<input name="catid" type="hidden" id="catid" value=""/>
<input name="mode" type="hidden" id="select" value="<?= $mode ?>"/>
</form><br />
<script>
$("#activity_cat").autocomplete({
            autoFocus: true,
            source: "<?= $path_to_query ?>",
            minLength: 3,
            select: function (event, ui) {
                event.preventDefault();
                $("#activity_cat").val(ui.item.label);
                $("#catid").val(ui.item.value);
                $("#cat_user_entry").submit();
            },
            change: function (event, ui) {   // TODO: determine if I really really need this
                if (ui != null) {
                    event.preventDefault();
                }
            },
            focus: function (event, ui) {
                event.preventDefault();
            },
        });
</script>
<?php
}

function output_forms($placeholder="Type something, dude", $kind="students", $mode="") {
    $path_to_index = "";
    $path_to_query = "/local/activity_center/query/{$kind}.php";

    ?>
<form id="user_entry" action="" method="get">
<input name="" autofocus="autofocus" size="100" onclick="this.select()"
    type="text" id="person" placeholder="<?= $placeholder ?>"/><br />
<input name="powerschool" type="hidden" id="powerschool" value=""/>
<input name="mode" type="hidden" id="select" value="<?= $mode ?>"/>
</form><br />
<script>
$("#person").autocomplete({
            autoFocus: true,
            source: "<?= $path_to_query ?>",
            minLength: 3,
            select: function (event, ui) {
                event.preventDefault();
                $("#person").val(ui.item.label);
                $("#powerschool").val(ui.item.value);
                $("#user_entry").submit();
            },
            change: function (event, ui) {   // TODO: determine if I really really need this
                if (ui != null) {
                    event.preventDefault();
                }
            },
            focus: function (event, ui) {
                event.preventDefault();
            },
        });
</script>
<?php
}
