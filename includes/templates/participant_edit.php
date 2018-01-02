<?php
if($participant_num == 1) {
?>
<table id="participant_edit">
    <tr>
        <td>
        <label class="participant_label" for="fname"><?php echo $sl->gp('First Name'); ?></label>
        </td>
        <td>
        <label class="participant_label" for="fname"><?php echo $sl->gp('Last Name'); ?></label>
        </td>
        <td>
        <label class="participant_label" for="fname"><?php echo $sl->gp('Date of Birth'); ?></label>
        </td>
        <td>
        <label class="participant_label" for="participant_status"><?php echo $sl->gp('Registration type'); ?></label>
        </td>
        <td>
        <label class="participant_label" for="registration"><?php echo $sl->gp('Registration Fee'); ?></label>
        </td>
    </tr>
<?php
}
?>
    <tr>
        <td>
            <input type="text" class="<?php echo $participant_class; ?>" id="fname<?php echo $id_append; ?>" name="fname" value="<?php echo $fname; ?>">
        </td>
        <td>
            <input type="text" class="<?php echo $participant_class; ?>" id="lname<?php echo $id_append; ?>" name="lname" value="<?php echo $lname; ?>">
        </td>
        <td>
            <input type="text" class="<?php echo $dob_extra; ?> <?php echo $participant_class; ?>" id="dob<?php echo $id_append; ?>" name="dob" value="<?php echo $dob; ?>">
        </td>
        <td class="waiver_status">
            <!--
            <?php echo $waiver_status; ?>
            -->
            <select id="ps_group_<?php echo $participant_id; ?>" class="participant_section_group" name="ps_group_<?php echo $participant_id; ?>">
            <?php echo $sg_sel; ?>
            </select> 
        </td>
        <td class="waiver_amount_due">
            <?php echo $amount_due; ?>
        </td>
        <td class="part_controls">
            <?php echo $part_controls; ?>
        </td>
    </tr>
<?php 
if($participant_num == $num_participants) { ?>
</table>
<?php } ?>
