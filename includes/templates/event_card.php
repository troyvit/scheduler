<?php
// echo 'admin is '.$is_admin;
if(is_admin() == 1) {
    $show_shelf=true;
}
if(is_array($active_participant)) {
       $pointer=current($active_participant); 
} else {
    $event_extra_classes.=' emtpy_event ';
}
if(!is_array($active_participant) || count($active_participant) == 0) {
    $event_extra_classes.=' emtpy_event ';
}    
?>
<div class = "event_card_holder located_in_<?php echo $location_id; ?> led_by_<?php echo $leader_id; ?> <?php echo $event_extra_classes; ?>" id="event_holder_<?php echo $event_id; ?>">
    <div class="event_card" id="event_card_<? echo $event_id; ?>">
        <div class="table_event_card">
            <div class="event_meta">
                <!-- <div class="event_name"><h3><? echo $event_id.' '.$et_name; ?></h3></div> -->
                <div class="event_name"><h3><? echo $et_name; ?></h3></div>
                <?php if(strlen($et_desc) > 0) { ?>
                    <div class="event_desc"><h3><? echo $et_desc; ?></h3></div>
                <?php } ?>
                <div class="event_day"> <h3><? echo $event_day; ?></h3></div>
                <div class="event_time"><h3><? echo $event_time; ?></h3></div>
                <div class="event_leader"><h3>Instructor: <? echo $event_leader; ?></h3></div>
                <!-- <div><? echo $number_participants.' and '.$i; ?></div> -->
               </div><!-- end event_meta -->
     
            <?php
            while($i < $number_participants) {
                $extra_style           = '';
                $value                 = '';
                $participant_id        = '';
                $status_id             = '';
                $event_participant_id  = '';
                $extra_style           = '';
     
                if(is_array($active_participant)) {
                    if(current($active_participant) != false) {
                        $extra_style           = ' event_slot_taken ';
                        $value                 = $pointer['fname'].' '.$pointer['lname'];
                        $participant_id        = $pointer['participant_id'];
                        $status_id             = $pointer['status_id'];
                        $event_participant_id  = $pointer['event_participant_id'];
                        $pay_status            = $pointer['pay_status']; // this takes precedence over event_participant_status' fully_paid because that really shouldn't be there.
                        $extra_style          .= ' '.str_replace(' ','_',strtolower($status_arr[$status_id])).' '.$pay_status.' ';
                        if(is_array($login_part_arr)) {
                            if(is_array($login_part_arr[$participant_id])) {
                                $show_config=true;
                                $extra_style .= ' editable ';
                            } else {
                                $show_config=false;
                            }
                        } else {
                            // debug // echo "it just isn't an array";
                        }
                        $show_ep_config=true;
                        $div_id = 'conf_'.$event_participant_id;
                        $shelf='<div class="ep_config" id="e_shelf_'.$event_participant_id.'">';
                        $shelf.='<div class="status_buttons">';
                        if(is_array($status_arr)) {
                            foreach($status_arr as $svar => $sval) {
                                if($svar == $status_id) {
                                    $c=' checked = "checked" ';
                                } else {
                                    $c = '';
                                }
                                $shelf.='<input class="status_radio '.$event_id.' " '.$c.' type="radio" name="status_'.$event_participant_id.'" id="stat_'.$event_participant_id.'_'.$svar.'" value="'.$svar.'">
                                    <label for ="stat_'.$event_participant_id.'_'.$svar.'">'.$sval.'</label>';
                            }
                        }
                        $shelf.='
                            <input style="float: left;" type="radio" class="participant_remove '.$event_id.'" name="remove_'.$event_participant_id.'" id="remove_'.$event_participant_id.'">
                            <label class="label_remove" for = "remove_'.$event_participant_id.'">'.$sl->gp('remove').'</label>
                            <input style="float: left;" type="radio" class="participant_orphan '.$event_id.'" name="orphan_'.$event_participant_id.'" id="orphan_'.$event_participant_id.'">
                            <label class="label_orphan" for = "orphan_'.$event_participant_id.'">'.$sl->gp('orphan').'</label>
                            
                            ';
                        $shelf.='</div>';
                        $shelf.='</div>';
                    } else {
                        // debug // echo "here event_id is $event_id <br>";
                        $div_id=$event_id.'_'.$i;
                        // debug // echo "id is supposed to be $div_id <br>";
                        // $shelf='<h3>no shelf here<h3>';
                        $shelf='';
                        $extra_style = ' event_slot ';
                        // debug // $value=$event_id.": BOpen";
                        $value=$sl->gp("Open");
                        $show_ep_config=false;
                    }
                } else {
                    $extra_style = ' event_slot ';
                    $value=$sl->gp("Open");
                    $div_id=$event_id.'_'.$i;
                }
                if($event_participant_id * 1 > 0 && $is_admin === true) {
                    $extra_style .=' editable ';
                    $show_config = true;
                }
    ?>
        <div id="e_<? echo $div_id; ?>" class="ep_holder <?php echo $extra_style; ?> droppable">
<?php /* echo "admin is $is_admin and id is $event_participant_id <br>"; */ ?>
        <?php echo $value; ?>
                </div>
        
                <?php 
                if($show_shelf == false) {
                    $show_config = false;
                }
                    if($show_config == true) {
                        echo $shelf;
                    } else {
                        // debug // echo "showconfig is false";
                    } ?>
    <?php if($show_ep_config == true) { ?>

    <?php }

                if(is_array($active_participant)) {
                    // debug // echo "moving on!";
                    $pointer = next($active_participant); 
                }
                $i++;
            } 
            // unload this array here instead of multiple times in rpc.
            $active_participant = array();
?>
        </div>
    </div>
</div>
<?php
$event_extra_classes='';
?>
