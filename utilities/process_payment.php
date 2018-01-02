<?php
// this processes payments from the web site. process.php processes them from FMP for the registration fee.
// soon they will be integrated into one

require('../includes/config.php');
require('../includes/functions.php');

$s_Login = new s_Login;
$s_Login -> db = $db;

$s_billing = new S_billing;
$s_billing -> db = $db;

$log                     = array(); 
$log['transactionid']=$_GET['transactionid'];
$amount = $_REQUEST['amount'];
$show_amount = $c.number_format($amount, 2);
$log['amount']=$amount;
$log['GWUSID']=$_GET['GWUSID'];

if($_REQUEST['responsetext']=='SUCCESS') {
    $_SESSION['recent_payment']='true';
}
$line_item_field=$_REQUEST['line_item_field'];
if(strlen($line_item_field)==0) {
    // damn you intrix
    $line_item_field=$_COOKIE['line_item_field'];
    /*
    echo "used the cookie and got:<br>
        $line_item_field <br>
     ";   
     */

}
$line_items_json = base64_decode($line_item_field); // I don't know just in case
$line_items=json_decode(base64_decode($line_item_field), true);

/*
echo '<pre>';

print_r($_REQUEST);
echo "an dcookied <br>";

print_r($_COOKIE);
die();
 */
// don't save til you're ready

$s_Login -> db_logout_params = $logout_params['increment'].' '.$logout_params['time'];
$login_res = $s_Login -> login_by_session(session_id(), $_SESSION['login_hash']);
if($login_res -> num_rows != 0) {
    $login_data = $login_res ->fetch_assoc();
    $u_login_id              = $login_data['id'];
    $u_login_fname           = $login_data['fname'];
    $u_login_lname           = $login_data['lname'];
} else {
    $reason[]="somebody without a session tried to process a payment for $pb_id";
    foreach($_SESSION as $sess_var => $sess_val) {
        $reason[].=$sess_var.' : '.$sess_val;
    }
    foreach($_REQUEST as $req_var => $req_val) {
        $reason[].=$req_var.' : '.$req_val;
    }
    $log['error']=$reason;
}

if(is_array($line_items)) {
    foreach($line_items as $pb_id => $pb_arr) {
        if(!is_array($pb_arr)) {
            $b=base64_decode($line_item_field);
            $h= "From: troy@troyvit.com";
            mail('troy@troyvit.com', 'broken pb_arr', $b, $headers, '-f troy@troyvit.com');
        }
        $pay       = $pb_arr['pay'];
        $show_pay  = $c.number_format($pb_arr['pay'], 2);
        $full_name = $pb_arr['full_name'];
        $line_item = $pb_arr['line_item'];
        $billing_up = $s_billing -> update_event_billing ($pb_id, $pay, '+');
        $li.="\n$full_name: $line_item: $show_pay";
        if( $billing_up == false ) {
            $error=true;
            $reason[]="failed to update payment for $pb_id";
            $log['error']=$reason;
        }
    }
} else {
    $from_email='info@swimfloatswim.com';
    $headers = "From: $from_email\n";
    $subject = "No line items";
    $to='troy@troyvit.com';
    $body  = print_r($_REQUEST, true);
    $body .= $_REQUEST['line_item_field'];
    $body .= print_r($login_data, true);
    mail($to, $subject, $body, $headers, '-f'.$from_email);
}

$email=$_REQUEST['email'];

$from_email='info@swimfloatswim.com';
$headers = "From: $from_email";
$headers .= "\nBcc: troy@troyvit.com\n";
$subject = "Swim Float Swim transaction receipt";
$to=$email;
if(strlen($u_login_fname) > 0) {
    // wtf right?
    $body="Dear $u_login_fname $u_login_lname,";
} 
$body.="
Thanks for your payment. Here are the details.
$li

Grand Total: $amount

You can find a record of your payment by logging into your account at:

http://www.swimfloatswim.com/longmont-baby-swimming-lessons-boulder/make-payments/

If you have any problems please contact us at $from_email.

Thanks,

Swim Float Swim";
// debug // echo '<pre>'.$body;
        mail($to, $subject, $body, $headers, '-f'.$from_email);
        mail('troy@troyvit.com', "admin: ".$subject, $body, $headers, '-f'.$from_email);
        // mail('info@swimfloatswim.com', "admin: ".$subject, $body, $headers, '-f'.$from_email);
        mail('judy@infantaquatics.com', "admin: ".$subject, $body, $headers, '-f'.$from_email);


$log['info']=$line_items_json;
$log_file="../logs/web_log.csv";
$log_file_contents=file_get_contents($log_file);
$log_json=json_encode($log);
$full_log=$log_file_contents."\n".$log_json;
file_put_contents($log_file, $full_log);
// header("Location: $default_class_page");
header("Location: /schedule/registration_complete.php");
?>
