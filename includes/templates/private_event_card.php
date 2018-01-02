<?php
if(is_array($priv_event)) {
?>
    <table>
    <tr>
    <tr>
        <td class="pecard_header">Time</td>
        <td class="pecard_header">Name</td>
        <td class="pecard_header">Age</td>
        <td class="pecard_header">Parent</td>
        <td class="pecard_header">Instructor</td>
        <td class="pecard_header">Week</td>
    </tr>
<?php
    foreach($priv_event as $pe_arr) { 
        $start_time              = $pe_arr['start_time'];
        $participant_fullname    = $pe_arr['participant_fullname'];
        $dob                     = $pe_arr['dob'];
        $participant_age         = $pe_arr['participant_age'];
        $login_fullname          = $pe_arr['login_fullname'];
        $leader                  = $pe_arr['leader'];
        $week                    = $pe_arr['week'];
        if($participant_fullname == '') {
            // No participant assigned to this slot yet
            $participant_fullname='Open';
        }
        ?>
        <tr>
        <td><?php echo $start_time; ?></td>
        <td><?php echo $participant_fullname; ?></td>
        <td><?php echo $participant_age; ?></td>
        <td><?php echo $login_fullname; ?></td>
        <td><?php echo $leader; ?></td>
        <td><?php echo $week; ?></td>
        </tr>
    <?php } ?>
    </table>
    </div>
<?php } ?>
