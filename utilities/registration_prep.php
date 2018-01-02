<?php
ob_start();
require('../includes/config.php');
require('../includes/functions.php');
require('../includes/language.php');
/* I call bollocks that I'm not just embedding this into the web form. 
 * Maybe eventually I can roll it up as an include. 
 * */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<?php

$amount             = $_REQUEST['total']; 
$pay                = $_REQUEST['pay']; 
$full_name          = $_REQUEST['full_name']; 
// $payment_method     = $_REQUEST['method']; // held in config for now
$line_items         = $_REQUEST['line_item']; // an array
$first_name         = $_REQUEST['first_name']; 
$last_name          = $_REQUEST['last_name']; 
$email              = $_REQUEST['email']; 
$demo               = $_REQUEST['demo']; 

// $demo = "demo";
/*
echo '<pre>';
print_r($_REQUEST);
*/

if($_REQUEST['demo']=="demo") {
    $key_id   = $payment[$payment_method]['demo']['key_id'];
    $username = $payment[$payment_method]['demo']['username'];
} else {
    $key_id   = $payment[$payment_method]['key_id'];
    $username = $payment[$payment_method]['username'];
}

$key = $payment[$payment_method]['merchantKeyText'];

$order_description=$_REQUEST['order_description'];

// moved into functions and wrap it in the appropriate class
/*
function gw_printField($key, $name, $value = "") {
    $gw_merchantKeyText=$key;
    static $fields;
    // Generate the hash
    if($name == "hash") {
        $stringToHash = implode('|', array_values($fields)) .
            "|" . $gw_merchantKeyText;
        $value = implode("|", array_keys($fields)) . "|" . md5($stringToHash);
    } else {
        $fields[$name] = $value;
    }
    echo '<input type="hidden" name="'.$name.'" value="'.$value.'">'."\n";
}
 */

foreach($_REQUEST as $var => $val) {
    if(!is_array($val)) {
        $m.="$var : $val \n";
    } else {
        $m.="$var : \n";
        $m.=print_r($val, true);
    }
}
        $from_email = "admin@infantaquatics.com";
        $headers    = "From: $from_email\n";
        $subject    = "New Transaction";
        $to_email   = "troy@troyvit.com";
        $body       = $m;
        // debug // 
        mail($to_email, $subject, $body, $headers, '-f'.$from_email);
?>
<html>
<head>
<title>Swim Float Swim Gateway</title>
<script type="text/javascript">
function submitform() {
    document.forms["frm"].submit();
}
</script>

</head>
<body>
<?php
/*
echo '<pre>';
echo $payment_method;
print_r($payment);
print_r($payment[$payment_method]);
print_r($_REQUEST);
echo '</pre>';
*/
?>
<form action="<?php echo $payment[$payment_method]['cart']; ?>" method="POST" id="frm" name ="frm">
<?php 
foreach($line_items as $pb_id => $line_item) { // ok this is intrix-specific. I'm sorry future-Troy 
    if($pay[$pb_id]=='' || $pay[$pb_id]*1==0) {
        $pay[$pb_id]=0;
    }
    $li[$pb_id]=array('line_item'=>$line_item,'pay'=>$pay[$pb_id], 'full_name'=>$full_name[$pb_id]);
    gw_printField($key, "product_description_".$pb_id, $line_item);
    gw_printField($key, "product_amount_".$pb_id, number_format($pay[$pb_id], 2,'.',''));
}
$line_item_field=base64_encode(json_encode($li));
// setcookie("TestCookie", $value);

setcookie("line_item_field", $line_item_field, time()+3600);
ob_end_flush();

?>
<input type="hidden" name="key_id" value="<?php echo $key_id; ?>" />
<input type="hidden" name="username" value="<?php echo $username; ?>" />
<input type="hidden" name="language" value="en" />
<input type="hidden" name="first_name" value="<?php echo $first_name; ?>" />
<input type="hidden" name="last_name" value="<?php echo $last_name; ?>" />
<input type="hidden" name="email" value="<?php echo $email; ?>" />
<input type="hidden" name="line_item_field" value="<?php echo $line_item_field; ?>" />
<input type="hidden" name="return_method" value="redirect" />
<input type="hidden" name="customer_receipt" value="false" />
<?php
/* <input type="hidden" name="country" value="US" /> <!-- you don't need this man --> */
?>
<input type="hidden" name="address_1" value="<?php echo $address_1; ?>" />
<input type="hidden" name="address_2" value="<?php echo $address_2; ?>" />
<input type="hidden" name="city" value="<?php echo $city; ?>" />
<input type="hidden" name="state_us" value="CO" />
<input type="hidden" name="state" value="CO" />
<input type="hidden" name="postal_code" value="<?php echo $postal_code; ?>" />
<input type="hidden" name="phone" value="<?php echo $phone; ?>" />
<input type="hidden" name="return_link" value="<?php echo $default_return_link; ?>" />
<input type="hidden" name="customer_receipt" value="false" />

<? gw_printField($key, "action", "process_fixed"); ?>
<? gw_printField($key, "order_description", $order_description ); ?>
<? gw_printField($key, "amount",number_format($amount, 2,'.','') ); ?>
<? gw_printField($key, "surcharge", '0' ); ?>
<? gw_printField($key, "hash", ''); ?>

<h2 style="width: 100%; text-align: center;"><!--<img src="../images/waiting.gif">--></h2>
    <p style="width: 100%; text-align: center;"><?php echo $sl->gp('Processing ... If nothing happens after a little while you can click the button below to finish registration.'); ?><br><br>
<input style="text-transform: capitalize;" type="submit" name="internetexplorercanthandlethewordsubmit" value="Continue" /></p>
</form>
<script language="javaScript">
submitform();
</script>
</body>
</html>
