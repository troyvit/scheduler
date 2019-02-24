<div id="myModal" class="reveal-modal">
    <form name="classManipulator" id="classManipulator">
        <input type="hidden" id="dateTime" name="date_time" value="<?php echo $date_time; ?>">
    <div id="groupClass">
        <h1 id="className"></h1>
        <input type="hidden" id="action" name="action" value="">
        <input type="hidden" id="et_json" name="et_json" value='<?php echo $et_json; ?>'>
        <input type="hidden" id="bak_event_time" name="bak_event_time" value='<?php echo $event_time; ?>'>
        <input type="hidden" id="eventId" name="event_id" value="<?php echo $event_id; ?>">
        <input type="hidden" id="et_activity_level " name="et_activity_level" value="<?php echo $et_activity_level ; ?>">
        <table class="class_edit">
            <tr><td colspan="2" style="text-align: center; font-weight: bold; "><?php echo $now_dayname.' '.$now_date_display; ?></td></tr>
            <tr><td>Time</td><td>
                <select name="event_time" id="event_time">
                    <?php
                    foreach($dropdown_time as $time) {
                        if($time == $event_time) {
                            $sel = ' SELECTED ';
                        } else {
                            $sel = '';
                        }
                        echo '<option '.$sel.' value="'.$time.'">'.$time.'</option>';
                    }
                    ?>
                </select>
                <?php /* echo $event_time; */ ?>
            </td></tr>
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
        <?php if($et_activity_level == 2) { 
                // this is a private class and we are also editing a class
        ?>

            <tr class="private_display">
                <td><?php echo $sl->gp('start date (ignored if group class)'); echo $et_activity_level .'<br>';?></td>
                <td><input type="text" class="private_dates" id="private_start" name="private_start" value="<?php echo $private_start; ?>">
                    <input type="hidden" id="bak_private_start" name="bak_private_start" value="<?php echo $private_start; ?>">

            </tr>
            <tr class="private_display">
                <td><?php echo $sl->gp('end date (ignored if group class)'); ?></td>
                <td><input type="text" class="private_dates" id="private_end" name="private_end" value="<?php echo $private_end; ?>">
                    <input type="hidden" id="bak_private_end" name="bak_private_end" value="<?php echo $private_end; ?>">
            </tr>

            <tr>
            <td><?php echo $sl->gp('student (ignored if group class)'); ?></td>
                <td><input type="text" id="private_participant" name="private_participant" value="<?php echo $private_participant; ?>">
                <input type="hidden" id="private_participant_id" name="private_participant_id" value="<?php echo $private_participant_id; ?>">
                <input type="hidden" id="bak_private_participant_id" name="bak_private_participant_id" value="<?php echo $private_participant_id; ?>">
            </tr>
        <?php } // end private class / editing ?>


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
            <td>
            <?php echo $sl->gp('occurance rate'); ?></td><td><?php /* echo "it is $occurance_rate <br>"; */ ?><select name="occurance_rate" id="occurance_rate">
                <option value="weekly" <?php if($occurance_rate == 'weekly' || !isset($occurance_rate)) { echo ' SELECTED '; } ?> ><?php echo $sl->gp('Weekly'); ?> (<?php echo $sl->gp('default'); ?>)</option>
                <option value="daily"  <?php if($occurance_rate == 'daily')  { echo ' SELECTED '; } ?> ><?php echo $sl->gp('Daily');  ?> </option>
            </select>

        <div id="day_boxes" style="display: none; width:20%; float: left; margin-top: 27px; margin-left: 18px;">
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


</td>
            </tr>
            <?php if(isset($event_id)) { ?>
                <tr>
                <td><?php echo $sl->gp('week number'); ?></td><td><input type="text" class="small_number_text" name="attendance" id="attendance" value="<?php echo $weeks_plus_attendance; ?>" > / <?php echo $total_weeks; ?>
                    <input type="hidden" name="bak_attendance" id="bak_attendance" value="<?php echo $weeks_plus_attendance; ?>" >
                    <input type="hidden" name="week_no" id="week_no" value="<?php echo $week_no; ?>" > <!-- base week number before taking attendance into account -->
                    <?php if($attendance != 0) {
                        echo '<p>Attendance has been adjusted by '.$attendance.'.</p>';
                    } ?>

</td>
                </tr>
            <?php 
            
                    if($et_activity_level == 2) {
                        // private class; save the event_participant_id
                        echo '<input type="hidden" name="event_participant_id" id="event_participant_id" value="'.$event_participant_id.'" >';
                    }
            
} ?>
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
