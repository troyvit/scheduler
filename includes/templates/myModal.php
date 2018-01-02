<div id="myModal" class="reveal-modal">
    <form name="classManipulator" id="classManipulator">
        <input type="hidden" id="dateTime" name="date_time" value="<?php echo $date_time; ?>">
    <ul>
        <li><a href="#privateClass"><?php echo $sl->gp('Private Class'); ?></a></li>
        <li><a href="#groupClass"><?php echo $sl->gp('Group Class'); ?></a></li>
    </ul>
    <div id="privateClass">
        <input type="hidden" id="privateEventId" name="private_event_id" value="<?php echo $private_event_id; ?>">
        <table style="float: left;" class="class_edit">
            <tr><td colspan="2" style="text-align: center; font-weight: bold; "><?php echo $event_time; ?></td></tr>
            <!--<tr>
                <td colspan="2" style="text-align: center; font-weight: bold; ">
                    <?php echo $private_event_day; ?>
                </td>
                </tr>-->
                <tr><td colspan="2" style="text-align: center; font-weight: bold; "><?php echo $private_event_time; ?></td></tr>
                </tr>
            <tr>
            <td><?php echo $sl->gp('student'); ?></td>
                <td><input type="text" id="private_participant" name="private_participant" value="<?php echo $private_participant; ?>">
                <input type="hidden" id="private_participant_id" name="private_participant_id" value="<?php echo $private_participant_id; ?>">
            </tr>
            <tr>
                <td><?php echo $sl->gp('start date'); ?></td>
                <td><input type="text" class="private_dates" id="private_start" name="private_start" value="<?php echo $private_start; ?>">
            </tr>
            <tr>
                <td><?php echo $sl->gp('end date'); ?></td>
                <td><input type="text" class="private_dates" id="private_end" name="private_end" value="<?php echo $private_end; ?>">
            </tr>
            <tr>
            <td><?php echo $sl->gp('location') ?></td>
                <td>
                    <select name="private_location_id" id="private_location_id"><?php echo $location_list; ?></select>
                </td>
            </tr>
            <tr>
            <td><?php echo $sl->gp('duration'); ?></td>
                <td>
                    <select name="private_duration" id="private_duration">
                    <?php
                    foreach($class_time_increment_choices as $val) {
                        echo '<option value="'.$val.'"';
                        if($private_duration) {
                            if($val==$private_duration) {
                                echo ' selected="selected" ';
                            }
                        }
                            echo '>'.$val.'</option>';
                    }
                    ?>
                    </select>
            </td>
            </tr>
            <tr>
            <td><?php echo $sl->gp('instructor'); ?></td>
                <td>
                    <select name="private_leader_id" id="private_leader_id"><?php echo $leader_list; ?></select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><!-- I must have been very angry when I built this screen -->
                    <div class="ped_meta_box" style="clear:both; width:100%">
                        <h4>Notes (applied to all class days)</h4>
                        <textarea name="ped_meta" id="ped_meta" style="width:100%"><?php echo $ped_meta; ?></textarea>
                <input type="button" id="privateNoteEdit" value="Save note edit">
                        <input type="hidden" id="ped_meta_id" name="ped_meta_id" value="<?php echo $ped_meta_id; ?>">
                    </div>
                <input class="classManipulatorButton" type="button" id="add_private_event" value="Add Class">
                <?php if(isset($private_event_id)) { ?>
                    <input class="classManipulatorButton" type="button" id="delete_private_event" value="Remove Class">
                    <!-- <input class="classManipulatorButton" type="button" id="edit_private_event" value="Save Edit to Class"> -->
                <?php } ?>
                </td>
            </tr>
        </table>
        <div id="day_boxes" style="width:20%; float: left; margin-top: 27px; margin-left: 18px;">
        <?php
        foreach($day_list as $dvar => $dval) {
            echo '<input type="checkbox" name="selected_days['.$dvar.']" id="day_'.$dvar.'"';
            if(in_array($dvar, $default_selected_privates)) {
                echo ' CHECKED ';
            }
            echo ' value="'.$dvar.'"><label for="day_'.$dvar.'">'.$dval.'</label>';
        }
?>
        </div>
     
    </div>
    <div id="groupClass">
        <h1 id="className"></h1>
        <input type="hidden" id="action" name="action" value="">
        <input type="hidden" id="eventId" name="event_id" value="<?php echo $event_id; ?>">
        <table class="class_edit">
            <tr><td colspan="2" style="text-align: center; font-weight: bold; "><?php echo $event_day; ?></td></tr>
            <tr><td colspan="2" style="text-align: center; font-weight: bold; "><?php echo $event_time; ?></td></tr>
        <?php if($class_dropdown==false) { ?>
            <input type="hidden" id="classId" name="class_id" value="<?php echo $class_id; ?>">
        <?php } else { ?>
        <tr><td><?php echo $sl->gp('select a class'); ?></td>
        <td>
            <select id="classId" name="class_id"> 
                <?php echo $class_list; ?>
            </select>
        </td>
        <?php } ?>

            <tr>
            <td><?php echo $sl->gp('event type'); ?></td><td>
    <select name="event_type" id="event_type">
    <?php echo $event_list; ?>
    </select></td>
            </tr>
    <tr><td><?php echo $sl->gp('location'); ?></td><td><select name="location_id" id="location_id"><?php echo $location_list; ?></select></td>
            </tr>
            <tr>
            <td><?php echo $sl->gp('duration'); ?></td>
                <td>
                    <select name="duration" id="duration">
                    <?php
                    foreach($class_time_increment_choices as $val) {
                        echo '<option value="'.$val.'"';
                        if($duration) {
                            if($val==$duration) {
                                echo ' selected="selected" ';
                            }
                        }
                            echo '>'.$val.'</option>';
                    }
                    ?>
                    </select>
            </td>
            </tr>
            <tr>
            <td><?php echo $sl->gp('occurance rate'); ?></td><td><select name="occurance_rate" id="occurance_rate"><option value="weekly" SELECTED ><?php echo $sl->gp('Weekly'); ?> (<?php echo $sl->gp('default'); ?>)</option></select></td>
            </tr>
            <tr>
            <td><?php echo $sl->gp('number of students'); ?></td><td><input type="text" class="small_number_text" name="number_participants" id="number_participants" value="<?php echo $number_participants; ?>" ></td>
            </tr>
            <tr>
                <td><?php echo $sl->gp('instructor'); ?></td><td><select name="leader_id" id="leader_id"><?php echo $leader_list; ?></select></td>
            </tr>
            <tr>
                <td colspan="2" id="tmpdata"><h4>Notes for today's class</h4>
                    <textarea name="edt_meta" id="edt_meta<?php echo $etd_id; ?>" style="width:100%"><?php echo $edt_meta; ?></textarea>
</td>
            </tr>
            <tr>
                <td colspan="2">
                <input type="hidden" name="edt_id" id="edt_id" value="<?php echo $edt_id; ?>">
                <?php if(isset($event_id)) { ?>
                    <input class="classManipulatorButton" type="button" id="edit_event" value="Save edits to existing class">
                    <input class="classManipulatorButton" type="button" id="delete_event" value="Remove existing class">
                    <input class="classManipulatorButton" type="button" id="add_class" value="Save info as additional class">
                <?php }  else { ?>
                    <input class="classManipulatorButton" type="button" id="add_class" value="Save info as additional class">
                <?php } ?>
                </td>
            </tr>
        </table>
    </div>
        </form>
     <a class="close-reveal-modal">&#215;</a>
</div>
