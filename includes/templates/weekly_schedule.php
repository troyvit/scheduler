<div id="schedule" style="width:3000px">
<?php
foreach($week as $current_day_j => $current_day_number) {
    $cal_date=date('Y-m-d', mktime(0,0,0,$this_month, $current_day_number, $this_year)); 
    $day_name=date('l', mktime(0,0,0,$this_month, $current_day_number, $this_year));
    $date_stamp = date('U', mktime(0,0,0,$this_month, $current_day_number, $this_year));

?>
    <table border="0" class="day_container"><tr><td colspan="2" class="day_name_container" id="day_name_<?php echo $day_name; ?>">
    <?php 
        echo $day_name;
    ?> 
    </td>
    </tr>
    <?php
    $current_time_hour = $daily_start_hour;
    $current_time_minute = $daily_start_minute;
    $current_unix_time = date('U', mktime($daily_start_hour, $daily_start_minute, 0, $this_month, $current_day_number, $this_year));
    $end_unix_time =  date('U', mktime($daily_end_hour, $daily_end_minute, 0, $this_month, $current_day_number, $this_year));
    $first_time_class=" top_row ";
    while($current_unix_time < $end_unix_time) {
        $compare_minute=date('i', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year));
        $time=date('g:ia', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year));
        if(in_array( $compare_minute, $show_time_triggers)) {
            $extra_td_styles=' show_time '; 
            $extra_span_styles=' show_time '; 
            $show_time='show';
            $time_display=$time;
        } else {
            $extra_td_styles=' hide_time ';
            $extra_span_styles=' hide_time ';
            // $time='&nbsp;'; // time is the time you use for classes, so you actually gotta show it sometimes
            $show_time='hidden';
            $time_display='&nbsp;';
        }
        $extra_td_styles.=$first_time_class;
        $first_time_class='';
        $current_time_stamp=date('w_H:i:00', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year)); // daynumber_military hour:minute:00 seconds
        $check_event=date('Y-m-d H:i:00', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year)); // daynumber_military hour:minute:00 seconds
        $date_stamp=date('U', mktime($current_time_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year)); // daynumber_military hour:minute:00 seconds
        echo '<tr class="time_row" id="'.$date_stamp.'">';
        if($count_rs==true) {
            $rscounter++;
        }
        if(is_array($e[$check_event])) {
            $longest_duration=0;
            // this goes through each item and makes sure I have the longest duration for simultaneous events
            // with the longest duration established I can figure out the rowspan I need for the events.
            foreach($e[$check_event] as $event_val) {
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
            $rhstyle='style="height: '.$rowheight.'px;"';
            $count_rs=true;
        }  else {
            $rowspan='';
            $rhstyle='';
        }
        echo '<td '.$rhstyle.' class="'. $extra_td_styles.' time_cell"><span class="'.$extra_span_styles.'">'.$time_display.'</span>';
        if(is_array($e[$check_event])) {
            $rscounter=1;
                $extra_event_cell_class=' active ';
                $event_increment=0;
                foreach($e[$check_event] as $val) {
                    extract($val);
                    if($event_increment > 0) {
                        $extra_event_class = ' event_daily_display_mult ';
                    } else {
                        $extra_event_class = '';
                    }
                    // debug // $display_events.= '<div class="event_daily_display '.$extra_event_class.'" id="'.$id.'" >'.$time.': '.$et_name.' ('.$duration.' min - '.$id.') at '.$location.' with '.$fname.'</div>&nbsp;';
                    $display_events.= '<div class="event_daily_display '.$extra_event_class.'" id="'.$id.'" >'.$time.': '.$et_name.' ('.$duration.' min) at '.$location.' with '.$fname.'</div>&nbsp;';
                    $event_increment++;
                }
        }
        echo '</td>';
        if(strlen($display_events) > 0) {
            echo ' <td class="event '.$extra_event_cell_class.' " '.$rowspan.' id="'.$current_time_stamp.'">'.$display_events.'</td>';
        }
        if(($rscounter > $class_time_increment_rowspan[$longest_duration]) || $longest_duration=='') {
            // echo '<td>'.$rscounter.' vs '.$class_time_increment_rowspan[$longest_duration].'</td>';
            echo ' <td class="event '.$extra_event_cell_class.' " id="'.$current_time_stamp.'">&nbsp;</td>';
        }
        echo '</tr>';
        $current_unix_time=date('U', mktime($daily_start_hour, $current_time_minute, 0, $this_month, $current_day_number, $this_year));
        $current_time_minute=$current_time_minute+$time_constraint;
        $display_events='';
        $extra_event_cell_class='';
        $extra_td_styles='';
        // $longest_duration='';
    }
?>
<?php 
}  ?>
</div>
