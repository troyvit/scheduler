<?php
require('../includes/config.php');
require('../includes/functions.php');
// intrix ... why a GET???
$ret.=print_r($_REQUEST, true);

$sp_reg = new S_participant_reg;
$sp_reg -> db = $db;

$s_login = new S_login; // to make a hash I know. Nice going past-troy
$s_login -> db = $db;

$s_login_reg = new S_login_reg;
$s_login_reg -> db = $db;

/*
echo '<h3>post</h3>';
print_r($_POST);

echo '<h3>get</h3><pre>';
print_r($_GET);

*/

// pull the request and get the URL I sent the cc processor
$zeropay = $_POST['zeropay']; // why is this a post if intrix uses a get? I don't know, this is something completely different.
// I think it's what happens if somebody has a free registration
if($zeropay == 'process') {
    /*
    echo "<pre>";
    print_r($_POST);
     */

    $login_id = $_POST['login_id'];
    $enc_login_id= bin2hex(base64_encode($login_id));
    $reg_id   = $_POST['reg_id'];

    $participant_ids=$_POST['participant_ids'];
    $participant_ids=explode(',', $participant_ids);

} else {
    // echo "we are past the zeropay thing <br>";
    // this is coming from a payment processor
    $rurl=$_REQUEST['referrer_url'];
    $rurl_arr = parse_url($rurl);
    // debug // print_r($rurl_arr);
    // this is halfass but also kinda rock solid
    $truefalse = parse_str($rurl_arr['query'], $output);
    /*
    echo "truefalse is $truefalse <br>";
    print_r($output);
    echo "<br>";
     */
    extract($output);
    $enc_login_id=$login_id;
    $login_id = base64_decode(pack("H*" , $login_id));

    // echo "ok login_id is $login_id <br>";
    // OK I added those fields to payment. I think they'll filter right through.
    /*
    echo '<pre>';
    print_r($_SERVER);
    */

    if($_REQUEST['responsetext']=='SUCCESS' || $_REQUEST['responsetext']=='Approval' || $_POST['zeropay']=='process') {
        // ROCK solid.
        $login_id = $_REQUEST['login_id'];
        $reg_id   = $_REQUEST['reg_id'];

        $participant_ids=$_REQUEST['participant_ids'];
        $participant_ids=explode(',', $participant_ids);
        $ret = print_r($_REQUEST, true);
        $ret.="\n\nLogin id: $login_id\nreg_id: $reg_id\n\n";
        mail('troy@troyvit.com', 'made it from the cc processor', $ret, $headers, '-f troy@troyvit.com');
	$client_email="";
        // update any participants' waiver that needs updating 
    } else {
        // echo "Oops there was a problem ";
        $headers = "From: troy@troyvit.com";
        $ret.=print_r($_SERVER, true);
        $ret.="here is request:\n\n";
        $ret.=print_r($_REQUEST, true);
        $ret.="\n\nand specifically response is ".$_REQUEST['responsetext']."\n";
        mail('troy@troyvit.com', 'registration is broken somewhere', $ret, $headers, '-f troy@troyvit.com');
        $participant_ids=$_REQUEST['participant_ids'];
        $participant_ids=explode(',', $participant_ids);
        // for NOW we will still allow the process to finish.
        // die();
    }
}

foreach($participant_ids as $participant_id) {
    $sp_reg -> change_participant_waiver_status ($participant_id, $reg_id, 2);
    $pd='product_description_'.$participant_id;
    $pa='product_amount_'.$participant_id;
    $grand_total += $_REQUEST[$pa];
    $line_item_amount=number_format($_REQUEST[$pa], 2);
    $line_items.=$_REQUEST[$pd]." : $line_item_amount\n";
}

$line_items.="\nTotal: $c".number_format($grand_total, 2);

// and update the registration form
$hash = $s_login -> make_hash();
$hash.='|'.date(U); // add the date to the sigs

$s_login_reg -> update_reg_login_item ($login_id, $reg_id, 'reg_status', 2);
$s_login_reg -> update_reg_login_item ($login_id, $reg_id, 'agreement_sig_hash', $hash);
$s_login_reg -> update_reg_login_item ($login_id, $reg_id, 'registration_sig_hash', $hash);
// echo "enc_login is $enc_login_id <br>";
// header to the default domain and registration page. so add that to the config.

// send a thankyou email

$login_info = $s_login ->get_login_from_id($login_id);
$login_data = $login_info ->fetch_assoc();
$login_fname=$login_data['fname'];
$login_lname=$login_data['lname'];
$login_email=$login_data['email'];


$email="Dear $login_fname $login_lname,

Thank you for your registration. It is now complete. Below is a summary.\n\n".$line_items."
    
Best Regards,
    
Swim Float Swim";

// debug 
/* 
echo '<pre>'.$email.'';

print_r($_REQUEST);
echo "mail to ";
echo $_REQUEST['email'];
*/
$emailto=$_REQUEST['email'];
$headers = "From: info@swimfloatswim.com";
mail($emailto, 'Swim Float Swim Registration Receipt', $email, $headers, '-f info@swimfloatswim.com');
mail('troy@troyvit.com', 'Swim Float Swim Registration Receipt', $email, $headers, '-f info@swimfloatswim.com');
mail('judy@infantaquatics.com', 'Swim Float Swim Registration Receipt', $email, $headers, '-f info@swimfloatswim.com');

header("Location:../registration.php?login_id=$enc_login_id&ty=true");
?>
