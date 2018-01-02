<h3><?php echo $sl->gp('Waiver for').' '.$name; ?></h3>
<?php echo $waiver; ?>
<?php if($waiver_viewmode == 'edit') { ?>
    <div class="participantSigPad">
        <label style="margin-right: 2em;" for="signed_name_<?php echo $participant_id.'_'.$reg_id; ?>"><?php echo $sl->gp('Please enter your name'); ?></label>
        <input class="editable waivername_<?php echo $waiver_id; ?>" name="participant_waiver|signed_name|prid|<?php echo $participant_id.','.$reg_id; ?>" id="signed_name_<?php echo $participant_id.'_'.$reg_id; ?>" type="" value="<?php echo $signed_name; ?>" required> 
<input id="signplease_<?php echo $waiver_id; ?>" name="tosign" value="Sign" class="ui-button ui-widget ui-state-default ui-corner-all waiver_tosign" role="button" aria-disabled="false" type="submit">
        </div>

<?php } elseif ($waiver_viewmode == 'read') { ?>

<h3><?php echo $signed_name; ?></h3>
<label for="signature_date_<?php echo $participant_id.'_'.$reg_id; ?>"><?php echo $sl->gp('Date'); ?></label>
<span id="signature_date_<?php echo $participant_id.'_'.$reg_id; ?>">
<?php echo $signature_date; ?>
</span>

<?php } ?>
