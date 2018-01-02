<?php

require('../includes/config.php');
require('../includes/functions.php');

// pull the request and get the URL I sent the cc processor
$rurl=$_REQUEST['referrer_url'];
$rurl_arr = parse_url($rurl);

// pull the login info from the referring url
// for the record I could have pulled the participants from here too
// would have saved around an hour
$truefalse = parse_str($rurl_arr['query'], $output);
extract($output);

/*
echo '<pre>';
print_r($_REQUEST);
print_r($output);
echo '</pre>';
*/
extract($_GET); // you need to hire some indians
// and when you do they'll create an array of elements that are allowed to be extracted
// and then they'll loop thru and extract them
// why *is* this a get anyway I wonder
// echo '<pre>';
// print_r($_GET);
$login_fname = $db -> real_escape_string($login_fname);
$login_lname = $db -> real_escape_string($login_lname);
$login_email = $db -> real_escape_string($login_email);

// pull in classes
$s_participant = new S_participant;
$s_participant -> db = $db;
$s_login = new S_login;
$s_login -> db = $db;

// set up some variables
$existing_participants   = array();
$existing_login          = false;
$error                   = false;
$log                     = array(); 

$log['transactionid']=$_GET['transactionid'];
$log['amount']=$_GET['amount'];
$log['GWUSID']=$_GET['GWUSID'];

// make sure this registration isn't a new one
$login_id = $s_login -> test_for_login($login_fname, $login_lname, $login_email);

if( $login_id == false) {
    $login_id = $s_login -> insert_login ($login_fname, $login_lname, $login_email);
} else {
    $existing_login = true;
    $ep_res = $s_participant -> get_participants_by_login ( $login_id );
    while( $ep_arr = $ep_res -> fetch_assoc()) {
        extract($ep_arr);
        $existing_participants[$id] = array('fname' => $fname, 'lname' => $lname);
    }
}

if($login_id == false) {
    $error=true;
    $reason[]="failed to create a login_id (login: $login_id, participant: $participant_id, existing_login = $existing_login)";
    $log['error']=$reason;
    die(); // hey better than nothing
}

$participant_arr = json_decode(base64_decode($participants), true);

// debug // print_r($participant_arr);


if($existing_login == true) {
    die('login is true');
    $log['warn'][]='login exists for '.$login_fname.' '.$login_lname.' '.$login_email.': '.$login_id;
    foreach($participant_arr as $key => $p_subarr) {
        extract($p_subarr);
        if($s_participant -> compare_participant($existing_participants, $fname, $lname) == false) {
            $log['info'][]='adding '.$fname.' '.$lname.' as a participant';
            $participant_id = $s_participant -> new_insert_participant ($fname, $lname, $dob);
            $log['participant_id'][]=$participant_id;
            $pl_id = $s_participant -> insert_login_participant($login_id, $participant_id);
            $log['login_participant'][]=$pl_id;
            if($pl_id==false) {
                $error=true;
                $reason[]="failed to insert login_participant (login: $login_id, participant: $participant_id)";
                $log['error']=$reason;
                die();
            }
        } else {
            $log['warn'][]='participant '.$fname.' '.$lname.' exists';
        }
    }
} else {
    foreach($participant_arr as $key => $p_subarr) {
        extract($p_subarr);
        $participant_id = $s_participant -> new_insert_participant ($fname, $lname, $dob);
        $log['participant_id'][]=$participant_id;
        $pl_id = $s_participant -> insert_login_participant($login_id, $participant_id);
        $log['login_participant'][]=$pl_id;
        if($pl_id==false) {
            $error=true;
            $reason[]="failed to insert login_participant (login: $login_id, participant: $participant_id)";
            $log['error']=$reason;
            die();
        }
    }
}
$log_file="../logs/fmp_log.csv";
$log_file_contents=file_get_contents($log_file);
$log_json=json_encode($log);
$full_log=$log_file_contents."\n".$log_json;
file_put_contents($log_file, $full_log);
// die('we will not go anywhere this afternoon');
header("Location: $default_front_page");
?>
