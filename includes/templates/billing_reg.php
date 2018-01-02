<div id="payment">
    <?php
    if(is_array($atns)) {
        echo '<br/>'; // screw you it's late
        echo '<div id="payment_buttons">';
        foreach($atns as $var => $val) {
            // echo $pipe.'<a href="#payment" class="choose_address '.$val['address_type_name'].'" id="atn_'.$var.'">Use '.$val['address_type'].'</a>';
            // echo '<button class="ui-button choose_address '.$val['address_type_name'].'" id="atn_'.$var.'">Use '.$val['address_type'].'</button>';
            // echo '<button class="choose_address" id="atn_'.$var.'">Use '.$val['address_type'].'</button>';
            echo '<a class="choose_address" id="atn_'.$var.'">Use '.$val['address_type'].'</a>';
        }
        echo '</div>';
    }?>
    <div class="payment_form">
        <div class="reg_contact">
            <div class="reg_name">
                <label for="fname"><?php echo $sl->gp('Name'); ?></label>
                <input required type="text" id="fname" name="first_name" value="<?php echo $first_name; ?>">
                <input required type="text" id="lname" name="last_name" value="<?php echo $last_name; ?>">
            </div>

            <div class="reg_email">
                <label for="email"><?php echo $sl->gp('Email'); ?></label>
                <input required type="text" id="email" name="email" value="<?php echo $email; ?>">
            </div>
            <div class="reg_phone">
                <label for="phone"><?php echo $sl->gp('Phone'); ?></label>
                <input required type="text" id="phone" name="phone" value="<?php echo $phone; ?>">
            </div>
        </div>
        <br /> <?php /* you can make it later better */ 
        /* it must be better, because I made it later */
        ?>
        <div class="reg_addresses">
            <div class="reg_address">
                <label for="address_1"><?php echo $sl->gp('Address'); ?></label>
                <input required type="text" id="address_1" name="address_1" value="<?php echo $address; ?>">
            </div>

            <div class="reg_address">
                <label for="city"><?php echo $sl->gp('City'); ?></label>
                <input required type="text" id="city" name="city" value="<?php echo $city; ?>">
            </div>

            <div class="reg_address">
                <label for="state"><?php echo $sl->gp('State'); ?></label>
                <input required type="text" id="state" name="state" value="<?php echo $state_us; ?>">
            </div>
            <div class="reg_address">
                <label for="postal_code"><?php echo $sl->gp('Zip'); ?></label>
                <input required type="text" id="zip" name="postal_code" value="<?php echo $postal_code; ?>">
            </div>

            <div class="reg_address">
                <label for="country"><?php echo $sl->gp('Country'); ?></label>
                <input required type="text" id="country" name="country" value="<?php echo $country; ?>">
            </div>
        </div> <!-- end reg_addresses -->
    </div><!-- end payment_form -->

<div class="clearfix" style="margin-bottom: 2em;" ></div><!-- I swore I'd never need this -->
<h3>Visa, Mastercard and Discover accepted. Unfortunately we cannot accept American Express.</h3>

<div class="payment_doc">
<?php echo $payment_doc; ?>
</div>

<table id="participant_billing_invoice">
    <tr>
        <th><?php echo $sl->gp('Participant'); ?></th>
        <th><?php echo $sl->gp('Registration Fee'); ?></th>
    </tr>
<?php
    // $data['return_link']='http://www.swiftscheduler.com/schedule/registration.php?login_id='.$enc_login_id.'&ty=1';
    $data['return_link']=$reg_process_link;
    $data['enc_login_id']=$enc_login_id;
    foreach($names as $participant_id => $billed_name) {
        if($statii[$participant_id] !==2) {
            $total_amount_due+=$amounts_due[$participant_id];
            $data['pd_arr'][$participant_id]='Registration for '.$billed_name;
            $data['pay'][$participant_id]=$amounts_due[$participant_id];
            $participant_ids[]=$participant_id;
        } else {
            $amounts_due[$participant_id]=$c.'0.00';
        }
?>
        <tr>
            <td><?php echo $billed_name; ?></td>
            <td class="money">$<?php echo $amounts_due[$participant_id]; ?></td>
        </tr>
<?php
    }
    $participant_ids=implode(',',$participant_ids);
?>
    <tr>
        <td class="total_row"><?php echo $sl->gp('Total'); ?></td>
        <td class="money"><?php echo  $c.number_format($total_amount_due, 2); ?></td>
    </tr>
</table>

<?php if($view_mode == 'edit') { ?>
    <div id="sig_registration_pay" class="sigPad">
        <label for="registration_signature_<?php echo $login_id.'_'.$reg_id; ?>"><?php echo $sl->gp('Please enter your name'); ?></label>
        <input class="editable" name="reg_login|registration_signed_name|lreg|<?php echo $login_id.','.$reg_id; ?>" id="registration_signed_name_<?php echo $login_id.'_'.$reg_id; ?>" type="text" value="<?php echo $registration_signed_name; ?>" required>
    </div>
    <?php /* <input id="payplease" type="button" name="topay" value="Pay"> */ ?>
    <input id="payplease" type="submit" name="topay" value="Pay">
    <input type="hidden" name="login_id" value="<?php echo $login_id; ?>">
    <input type="hidden" name="participant_ids" value="<?php echo $participant_ids; ?>">
    <input type="hidden" name="reg_id" value="<?php echo $reg_id; ?>">
<?php } elseif ($view_mode == 'read') { ?>

    <h3><?php echo $registration_signed_name; ?></h3>

<?php } ?>

</div>
<?php
    // this really belongs in rpc but THAT freaks me out
    // where it really belongs is in its own template, which would be interesting and cool
$demo=false;
if($_REQUEST['demo']=="demo" || $demo==true) {
    $data['key_id']   = $payment[$payment_method]['demo']['key_id'];
    $data['username'] = $payment[$payment_method]['demo']['username'];
} else {
    $data['key_id']   = $payment[$payment_method]['key_id'];
    $data['username'] = $payment[$payment_method]['username'];
}

$data['key'] = $payment[$payment_method]['merchantKeyText'];
$data['order_description']="Swim Float Swim Registration";
$data['pay']=$amounts_due;
$data['amount']=$total_amount_due;
generate_payment('intrix', $data, false);
?>
