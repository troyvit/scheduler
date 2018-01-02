<div id="agreement">
<h3><?php echo $sl->gp('Agreement'); ?></h3>
<div class="agreement_contain"><?php echo $agreement_text; ?></div>

<?php if($view_mode == 'edit') { ?>
    <div id="sig_registration_agreement" class="sigPad">
        <label for="agreement_signature_<?php echo $login_id.'_'.$reg_id; ?>"><?php echo $sl->gp('Please enter your name'); ?></label>
       <?php /* <input class="editable" name="reg_login|agreement_signed_name|lreg|<?php echo $login_id.','.$reg_id; ?>" id="agreement_signed_name_<?php echo $login_id.'_'.$reg_id; ?>" type="text" name="agreement_signed_name" value="<?php echo $registration_signed_name; ?>"> */ ?>
       <input class="editable" name="reg_login|agreement_signed_name|lreg|<?php echo $login_id.','.$reg_id; ?>" id="agreement_signed_name_<?php echo $login_id.'_'.$reg_id; ?>" type="text" value="<?php echo $registration_signed_name; ?>"> <?php /* why is the value different from the name (aside from the name looking like it would be holy hell to write as a php variable) */ ?>
        </div>

<?php } elseif ($view_mode == 'read') { ?>

    <h3><?php echo $registration_signed_name; ?></h3>

<?php } ?>

<span class="signature_date" id="signature_date_<?php echo $participant_id.'_'.$reg_id; ?>">
<?php echo $agreement_signature_date; ?>
</span>
</div>
