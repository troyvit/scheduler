<?php
require('./includes/config.php');
require('./includes/functions.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<?php
$allowed_fields = array(); // not used
extract($_GET); // what could possibly go wrong?
if($amount == '') {
    $amount = '80.00'; // what could possibly go wrong?
}

$key_id = (strlen($_GET['key_id']) > 0 ? $_GET['key_id'] : '3785894'); // default to demo
$username = (strlen($_GET['username']) > 0 ? $_GET['username'] : 'demo'); // default to demo
$order_description='Swim Float Swim Registration';

function gw_printField($name, $value = "") {
    $gw_merchantKeyText='SXEV38r62k3U2593tp3247p6KgXZQYt8';
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

$participant_fname=$_GET['participant_fname'];
$participant_lname=$_GET['participant_lname'];

foreach($_GET as $var => $val) {
    $m.="$var : $val \n";
}
        $from_email = "admin@infantaquatics.com";
        $headers    = "From: $from_email\n";
        $subject    = "New Registrant";
        $to_email   = "troy@troyvit.com";
        $body       = $m;
        // mail($to_email, $subject, $body, $headers, '-f'.$from_email);
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
<body >
<form action="https://secure.velocitypaymentsgateway.com/cart/cart.php" method="POST" id="frm" name ="frm">
<input type="hidden" name="key_id" value="3734671" />
<? php /* <input type="hidden" name="key_id" value="3785894" /> */ ?>
<input type="hidden" name="username" value="heumann" />
<?php /* total guess /* <input type="hidden" name="username" value="demo" /> */ ?>
<input type="hidden" name="action" value="process_fixed" />
<input type="hidden" name="amount" value="<?php echo $amount; ?>" />
<input type="hidden" name="order_description" value="<?php echo $order_description; ?>" />
<input type="hidden" name="merchant_defined_field_1" value="<?php echo $order_description; ?>" />
<input type="hidden" name="language" value="en" />
<input type="hidden" name="first_name" value="<?php echo $first_name; ?>" />
<input type="hidden" name="last_name" value="<?php echo $last_name; ?>" />
<input type="hidden" name="country" value="US" /> <!-- you don't need this man -->
<input type="hidden" name="address_1" value="<?php echo $address_1; ?>" />
<?php 
foreach($participant_fname as $key => $fname) {
    // stupid filemaker
    $pd=$participant_dob[$key];
    $pdarr=explode('/',$pd);
    // string str_pad ( string $input , int $pad_length [, string $pad_string = " " [, int $pad_type = STR_PAD_RIGHT ]] )
    $pdarr[0]=str_pad($pdarr[0], 2, 0, STR_PAD_LEFT);
    $pdarr[1]=str_pad($pdarr[1], 2, 0, STR_PAD_LEFT);
    $pdarr[2]=str_pad($pdarr[2], 4, 20, STR_PAD_LEFT);
    $npd=$pdarr[2].'-'.$pdarr[0].'-'.$pdarr[1];
    $participant_dob[$key]=$npd;
    // aaaand stupid intrix
    $participant[$key]=array( 
        'fname' => $participant_fname[$key],
        'lname' => $participant_lname[$key],
        'dob' => $participant_dob[$key]);
    $participant_json=base64_encode(json_encode($participant));
}
?>
<input type="hidden" name="participants" value="<?php echo $participant_json; ?>">
<input type="hidden" name="address_2" value="<?php echo $address_2; ?>" />
<input type="hidden" name="city" value="<?php echo $city; ?>" />
<input type="hidden" name="state_us" value="CO" />
<input type="hidden" name="state" value="CO" />
<input type="hidden" name="postal_code" value="<?php echo $postal_code; ?>" />
<input type="hidden" name="phone" value="<?php echo $phone; ?>" />
<input type="hidden" name="email" value="<?php echo $email; ?>" />
<input type="hidden" name="url_finish" value="http://www.infantaquatics.com/judyheumann.htm" />
<input type="hidden" name="url_finish" value="http://www.infantaquatics.com/schedule/process.php" />
<input type="hidden" name="customer_receipt" value="true" />

<? gw_printField("action", "process_fixed"); ?>
<? gw_printField("order_description", $order_description ); ?>
<? gw_printField("amount", $amount); ?>
<? gw_printField("hash"); ?>

<h2 style="width: 100%; text-align: center;"><!--<img src="../images/waiting.gif">--></h2>
<p style="width: 100%; text-align: center;">Processing ... If nothing happens after a little while you can click the button below to finish registration.<br><br>
<input type="submit" name="internetexplorercanthandlethewordsubmit" value="Register Now" /></p>
</form>
<script language="javaScript">
submitform();  
</script>
</body>
</html>
