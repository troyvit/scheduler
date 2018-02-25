<div id="schedule" style="width:<?php echo $schedule_week_width; ?>px">
<?php
foreach($betterweek as $cal_date => $current_day_number) {
    /*
    if($last_day*1 > 0) {
        if($last_day > $current_day_number) {
            $this_month++;
        }
    }
     */
    $first_row_class=' time_top_row ';
    // $cal_date=date('Y-m-d', mktime(0,0,0,$this_month, $current_day_number, $this_year)); 
    $date_obj = new DateTime ($cal_date);
    $day_name = $date_obj -> format ('l');
    $old_day_name=date('l', mktime(0,0,0,$this_month, $current_day_number, $this_year));
    $date_stamp = $date_obj -> format('U');
    $old_date_stamp = date('U', mktime(0,0,0,$this_month, $current_day_number, $this_year));
    if($by_range==true) {
        $day_name_class = $day_name.' '.$cal_date;
        $day_name=$day_name; // wtf ever past-troy
    }
    $last_day=$current_day_number;
    if(in_array($day_name, $days_block)) {
        continue;
    }
?>
    <table border="0" class="day_container"><tr><td colspan="2" class="day_name_container" id="day_name_<?php echo $day_name_class; ?>">
    <?php echo $day_name.' '.$cal_date; ?> 
    <div class="day_control">
    <button class="print_day print_button" id="print_<?php echo $cal_date; ?>" class="print_day"><img style="width:16px; height: 16px;" src="images/printer.svg"></button>
    </div>
    </td>
    </tr>
    <td colspan="2">
    <?php
    $current_time_hour   = $daily_start_hour;
    $current_time_minute = $daily_start_minute;
    $current_unix_time   = date('U', mktime($daily_start_hour, $daily_start_minute, 0, $this_month, $current_day_number, $this_year));
    $end_unix_time       = date('U', mktime($daily_end_hour, $daily_end_minute, 0, $this_month, $current_day_number, $this_year));
    while($current_unix_time < $end_unix_time) {
        // initialize some variables
        $total_duration=0;

        $compare_minute=date('i', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year));
        $time=date('g:ia', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year));
        if(in_array( $compare_minute, $show_time_triggers)) {
            $extra_span_styles=' show_time '; 
            $show_time='show';
            $time_display=$time;
        } else {
            $extra_span_styles=' hide_time ';
            // $time='&nbsp;'; // time is the time you use for classes, so you actually gotta show it sometimes
            $show_time='hidden';
            $time_display='&nbsp;';
        }
        $current_time_stamp=date('w_H:i:00', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year)); // daynumber_military hour:minute:00 seconds
        // $check_event=date('Y-m-d H:i:00', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year)); // daynumber_military hour:minute:00 seconds
        $date_obj -> SetTime ($current_time_hour, $current_time_minute);
        $check_event = $date_obj -> format('Y-m-d H:i:00');
        // echo "checking against $check_event <br>";
       //  $date_stamp=date('U', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year)); // daynumber_military hour:minute:00 seconds
        $date_stamp = $date_obj -> format('U');
        if($count_rs==true) {
            $rscounter++;
        }
        if(is_array($e[$check_event])) {
            $longest_duration=0;
            // this goes through each item and makes sure I have the longest duration for simultaneous events
            // with the longest duration established I can figure out the rowspan I need for the events.
            foreach($e[$check_event] as $event_val) {
                $total_duration+=$event_val['duration'];
                if($event_val['duration'] > $longest_duration) {
                    $longest_duration=$event_val['duration'];
                }
            }
            $default_rowspan=$class_time_increment_rowspan[$longest_duration]; // an array from the config
            $rowspan_subtract=0;
            $rowspan=$default_rowspan-$rowspan_subtract;
            $rowspan='rowspan='.$rowspan; 
            // $rowspan='';
            $rowheight=ceil($time_row_height_multiplier * $longest_duration);
            // $rhstyle='style="height: '.$rowheight.'px;"';
            $count_rs=true;
        }  else {
            $rowspan='';
            $rhstyle='';
        }
        // $div_height=$total_duration+30;
        $div_height=30;
        $rhstyle='height: '.$div_height.'px; ';
        // echo '<div style="display: block; border-bottom: 1px solid #fabaca;" class="time_row '.$first_row_class.' " id="'.$date_stamp.'">'; // contains time and event
        echo '<div style="border-bottom: 1px solid #fabaca;" class="time_row '.$first_row_class.' " id="'.$date_stamp.'">'; // contains time and event
        echo '<div style="border:0px solid green; " class="time_cell">'; // time of day goes here
            echo '<span class="'.$extra_span_styles.'">'.$time_display.'</span>';
        if(is_array($e[$check_event])) {
            $rscounter=1;
                $extra_event_cell_class=' active ';
                $event_increment=0;
		$event_count = count($e[$check_event]);
                $each_event_width = floor(100/$event_count) -2;
		$extra_event_class = '';
                foreach($e[$check_event] as $val) {
                    /* extract($val); */
                    $daytime                   = $val['daytime'];
                    $et_name                   = $val['et_name'];
                    $edt_id                    = $val['edt_id'];
                    $location                  = $val['location'];
                    $location_class            = strtolower(str_replace(' ', '_', $location));
                    $privgroup                 = $val['privgroup'];
                    $leader                    = $val['leader'];
                    $fname                     = $val['fname'];
                    $id                        = $val['id'];
                    $participant_fullname      = $val['participant_fullname'];
                    $duration                  = $val['duration'];
                    $duration_height           = round(36*$duration/5);
                    $event_type                = $val['event_type'];
                    $private_event_daytime_id  = $val['private_event_daytime_id'];
                    if($event_increment > 0) {
                        // $extra_event_class = ' event_daily_display_mult ';
                        // $extra_event_style= 'margin-left: 48px; margin-bottom: -180px;';
                    } else {
                        // $extra_event_class = '';
                        // $extra_event_style= 'margin-left: 74px;';
                    }
                    // calculate the height of it
                    // check here
                    $tc=$time_constraint*1; // get the time constraint and clean it up
                    // get the duration / time_constraint
                    $duration_chunk=floor($duration/$tc);
                    // 30 minutes should give us 6 if the time constraint is 5 minutes
                    $new_height = $duration_chunk*$div_height;
		            $new_height='default';
		            // $new_height=$duration_height; // I see I've been here a few times
                    // $extra_event_style.=' height: '.$new_height.'px; ';
                    // $extra_event_style.=' position:relative; bottom: '.$div_height.'px; ';
                    $extra_event_style = ' width: '.$each_event_width.'%; height: '.$new_height.'px ';

                    if($privgroup=='private') {
                        // include student name
                        $extra_event_class.=" private_event ";
                        $display_events.= '<div style="'.$extra_event_style.'" class="event_daily_display '.$extra_event_class.' '.$location_class.'" id="'.$id.':'.$private_event_daytime_id.'" >'.$time.': '.$et_name.' ('.$duration.' min) for '.$participant_fullname.' at '.$location.' '.$sl->gp('with').' '.$fname.'</div>&nbsp;';

                    } else {
                        $display_events.= '<div style="'.$extra_event_style.'" class="event_daily_display 
'.$extra_event_class.'" id="'.$id.'" >'.$time.': '.$et_name.' ('.$duration.' min) 
at '.$location.' '.$sl->gp('with').' '.$fname.'<input type="hidden" class="cascade_edt_id" value="'.$edt_id.'"></div>&nbsp;';
                    }
                    $event_increment++;
                }
                // $extra_event_class=''; // remove?
        }
        echo '</div>';
        if(strlen($display_events) > 0) {
            echo ' <div class="event '.$extra_event_cell_class.' " '.$rowspan.' id="'.$current_time_stamp.'">'.$display_events.'</div>';
        }
        if(($rscounter > $class_time_increment_rowspan[$longest_duration]) || $longest_duration=='') {
            echo ' <div class="event '.$extra_event_cell_class.' " id="'.$current_time_stamp.'">&nbsp;</div>';
        }
        echo '</div>';
        // get the next time row ready and reset vars
        $current_unix_time=date('U', mktime($daily_start_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year));
        $current_time_minute=$current_time_minute+$time_constraint;
        $display_events='';
        $extra_event_cell_class='';
        $first_row_class='';
        // $longest_duration='';
    }
?>
    </td></tr></table>
<?php 
}  ?>
</div>
