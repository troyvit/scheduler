<div id="regAddressTabs">
<?php
// example $myname='p_reg_answer|answer|pqa|'.$participant_id.','.$question_id;

foreach($atns as $address_type_id => $atn_arr) {
    extract($atn_arr); // gives me address_type_name and address_type
    if(is_array($login_address[$address_type_id])) {
        $ati_arr=$login_address[$address_type_id];
    } else {
        // $ati_arr='';
        $ati_arr=array();
    }
?>
    <div id="<?php echo $address_type_name; ?>">
    <h3><?php echo $address_type; ?></h3>
    <div class="reg_name">
        <label for="fname_<?php echo $address_type_id; ?>">Name</label>
        <input type="text" class="editable" id="fname_<?php echo $address_type_id; ?>" name="login_address|fname|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['fname']; ?>">
        <input type="text" class="editable" id="lname_<?php echo $address_type_id; ?>" name="login_address|lname|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['lname']; ?>">
    </div>

    <div class="reg_email">
        <label for="email_<?php echo $address_type_id; ?>">Email</label>
        <input type="text" class="editable" id="email_<?php echo $address_type_id; ?>" name="login_address|email|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['email']; ?>">
    </div>
    <br /> <?php /* you can make it later better */ ?>
    
    <div class="reg_phones">
        <div class="reg_phone">
            <label for="phone_h_<?php echo $address_type_id; ?>">Phone (H)</label>
            <input type="text" class="editable" id="phone_h_<?php echo $address_type_id; ?>" name="login_address|phone_h|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['phone_h']; ?>">
        </div>
        <div class="reg_phone">
            <label for="phone_w_<?php echo $address_type_id; ?>">(W)</label>
            <input type="text" class="editable" id="phone_w_<?php echo $address_type_id; ?>" name="login_address|phone_w|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['phone_w']; ?>">
        </div>

        <div class="reg_phone">
            <label for="phone_c_<?php echo $address_type_id; ?>">(C)</label>
            <input type="text" class="editable" id="phone_c_<?php echo $address_type_id; ?>" name="login_address|phone_c|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['phone_c']; ?>">
        </div>
    </div>
    <br /> <?php /* you can make it later better */ ?>

    <div class="reg_addresses">
        <div class="reg_address">
            <label for="address_1_<?php echo $address_type_id; ?>">Address</label>
            <input type="text" class="editable" id="address_1_<?php echo $address_type_id; ?>" name="login_address|address_1|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['address_1']; ?>">
        </div>

        <div class="reg_address">
            <label for="city_<?php echo $address_type_id; ?>">City</label>
            <input type="text" class="editable" id="city_<?php echo $address_type_id; ?>" name="login_address|city|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['city']; ?>">
        </div>

        <div class="reg_address">
            <label for="state_<?php echo $address_type_id; ?>">State</label>
            <input type="text" class="editable" id="state_<?php echo $address_type_id; ?>" name="login_address|state|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['state']; ?>">
        </div>

        <div class="reg_address">
            <label for="zip_<?php echo $address_type_id; ?>">Zip</label>
            <input type="text" class="editable" id="zip_<?php echo $address_type_id; ?>" name="login_address|zip|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['zip']; ?>">
        </div>

        <div class="reg_address">
            <label for="country_<?php echo $address_type_id; ?>">Country</label>
            <input type="text" class="editable" id="country_<?php echo $address_type_id; ?>" name="login_address|country|login_ids|<?php echo $login_id.','.$address_type_id; ?>" value="<?php echo $ati_arr['country']; ?>">
        </div>
    </div><!-- end reg_addresses -->
    </div><!-- end <?php echo $address_type_name; ?> -->
<?php } ?>
</div>
<div class="clearfix"></div><!-- I swore I'd never need this -->
<?php 
    /* not used but so much work I hate to just throw it away */
    $fname_name='login_address|fname|login_ids|'.$login_id.','.$address_type_id;
    $lname_name='login_address|lname|login_ids|'.$login_id.','.$address_type_id;
    $email_name='login_address|email|login_ids|'.$login_id.','.$address_type_id;
    $phone_h_name='login_address|phone_h|login_ids|'.$login_id.','.$address_type_id;
    $phone_w_name='login_address|phone_w|login_ids|'.$login_id.','.$address_type_id;
    $phone_c_name='login_address|phone_c|login_ids|'.$login_id.','.$address_type_id;
    $address_1_name='login_address|address_1|login_ids|'.$login_id.','.$address_type_id;
    $address_2_name='login_address|address_2|login_ids|'.$login_id.','.$address_type_id;
    $city_name='login_address|city|login_ids|'.$login_id.','.$address_type_id;
    $state_name='login_address|state|login_ids|'.$login_id.','.$address_type_id;
    $country_name='login_address|country|login_ids|'.$login_id.','.$address_type_id;
    $zip_name='login_address|zip|login_ids|'.$login_id.','.$address_type_id;
?>
