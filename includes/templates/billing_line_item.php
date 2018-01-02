<?php
/*
if($u_login_email !=='katevitullo@gmail.com') {
    die();
}
 */
    if($require_deposit == true) {
        $order_description = 'Swim Float Swim Deposit';
    } else {
        $order_description = 'Swim Float Swim Payment';
    }
    if($_SESSION['recent_payment']=='true') {
        echo '<h3>'.$sl->gp('Thanks for your recent payment.').'</h3>';
        $paid_extra_class=" paid ";
    }
?>
    <form target="_parent" method="post" action="utilities/registration_prep.php">
    <input type="hidden" name="order_description" id="order_description" value="<?php echo $order_description; ?>">
    <input type="hidden" name="first_name" value="<?php echo $u_login_fname; ?>">
    <input type="hidden" name="last_name" value="<?php echo $u_login_lname; ?>">
    <input type="hidden" name="email" value="<?php echo $u_login_email; ?>">
<?php /*
    <input type="text" name="demo" value="demo">
 */
?>
<p><?php echo $sl->gp('Below are the students associated with your account and the amount due.'); ?></p>
<p><?php echo $sl->gp('Indicate the amount you wish to pay and click the update button.'); ?></p>
    <table id="participant_billing_invoice" border="1">
    <tr>
        <th><?php echo $sl->gp('Name'); ?></th>
        <th><?php echo $sl->gp('Session'); ?></th>
        <th><?php echo $sl->gp('Class Fee'); ?></th>
        <th><?php echo $sl->gp('Payments Applied'); ?></th>
        <?php if($require_deposit == true) { 
        ?>
            <th><?php echo $sl->gp('Deposit Due'); ?></th>
        <?php } ?>
        <th><?php echo $sl->gp("Today's Payment"); ?></th>
    </tr>
<?php
    foreach($login_part_arr as $val) {
        $fname          = $val['fname'];
        $lname          = $val['lname'];
        $participant_id = $val['id'];
        // what are these doing in a template
        // they're stuck in a foreach
        /*
         */
        $event_name = $en_arr['event_name'];
	// debug // echo "checking event_billing by $participant_id <br>";
        $pb_res = $s_billing->event_billing_by_participant($participant_id);
        while($pb_arr = $pb_res->fetch_assoc()) {
            $pb_id                 = $pb_arr['id'];
            $event_line_item_id    = $pb_arr['event_line_item_id'];
            $event_participant_id  = $pb_arr['event_participant_id']; // you can get the class from this
            $en_res = $s_event -> event_name_by_event_participant_id($event_participant_id);
            $en_arr = $en_res -> fetch_assoc();
            /*
            echo '<pre>';
            print_r($en_arr);
             */
            $class_name            = $s_billing ->class_name_from_event_participant_billing_id($pb_id);
            // $event_line_item       = $pb_arr['event_line_item'];
            $event_line_item       = $class_name.', '.$en_arr['et_name'];
            $amount_due            = $pb_arr['amount_due'];
            $amount_paid           = $pb_arr['amount_paid'];
            $total_amount_paid[]   = $pb_arr['amount_paid'];
            $amount_owed           = $amount_due-$amount_paid;
            /*
            $li_result = $s_billing -> get_event_line_item ($event_line_item_id);
            $li_row = $li_result -> fetch_assoc();
            $line_item = $li_row['line_item'];
             */
            $full_name = $fname.' '.$lname;
            echo '<input type="hidden" name="line_item['.$pb_id.']" value="'.$event_line_item.'">';
            echo '<input type="hidden" name="full_name['.$pb_id.']" value="'.$full_name.'">';
            // echo $pb_arr['amount_due'];
            $total_amount_owed+=$amount_owed;
            $total_amount_due+=$amount_due;
            // echo "so total amount is $total_amount_owed because of $amount_owed";
?>
            <tr>
            <td><?php echo $fname.' '.$lname; ?><!--(<?php echo $event_participant_id; ?>)--></td>
            <td><?php echo $class_name ?></td>
            <td class="money"><?php echo $c.$amount_due ?></td>
            <td class="money <?php echo $paid_extra_class; ?> "><?php echo $c.$amount_paid ?></td>
<?php
            if($require_deposit==true) {
                // Sorry for this mess Future-Troy ... you should just rewrite it
                if($amount_paid < $deposit_amount) {
                    // wtf // $c_amount_owed = $amount_owed - $deposit_amount;
                    $c_amount_owed = $deposit_amount - $amount_paid;
                    $total_deposit_owed+=$c_amount_owed;
                    $text_amount_owed=number_format($c_amount_owed, 2);
                    $c_amount_owed = $c.number_format($c_amount_owed, 2);
                } else {
                    $c_amount_owed = '0';
                }
                echo '<td class="money">'.$c_amount_owed.'</td>';
            } else {
                    $c_amount_owed = $amount_owed;
                    $text_amount_owed=number_format($c_amount_owed, 2);
                    $c_amount_owed = $c.number_format($c_amount_owed, 2);
            }
            ?>
                <td class="money"><?php echo $c; ?>
                    <input class="input_money" type="text" size="7" name="pay[<?php echo $pb_id;?>]" value="<?php echo $text_amount_owed; ?>">
                    <?php // echo $text_amount_owed; ?>
                    <?php /* <input class="input_money" type="text" size="6" name="pay[<?php echo $pb_id;?>]" value="<?php echo $text_amount_owed; ?>"> */ ?>
                </td>
        </tr>
<?php
        }
    }
    if($require_deposit == true) {
        $colspan=6;
        $summary_colspan=2;
        $total=$total_deposit_owed;
    } else {
        $colspan=5;
        $summary_colspan=2;
        $total=$total_amount_due;
    }
    ?>
        <tr> <td class="total_row" colspan="<?php echo $summary_colspan; ?>"><?php echo $sl->gp('Totals'); ?></td>
        <td class="money">
            <div><?php echo $c.number_format($total, 2); ?></div>
            <input type="hidden" name="total" id="total" value="<?php echo $total_amount_owed; ?>">
        </td>
        <td class="money">
            <div><?php echo $c.number_format(array_sum($total_amount_paid), 2); ?></div>
        </td>
        <td class="money">
            <span><?php echo $c; ?></span><span id="calc_amount"><?php echo number_format($total_amount_owed, 2); ?></span>
        </td>
    <?php if($require_deposit == true) { ?>
        <td class="money">&nbsp;</td>
    <?php } ?>
        </tr>
        <tr>
            <td colspan="<?php echo $colspan; ?>"><input type="button" name="Update" value="Update"><input type="submit" id="pay_button" value="Pay" name="pay_button"></td>
        </tr>
        
        </table>
    </form>
