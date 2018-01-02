<?php
require('config.php');
require('functions.php');

/* This grabs all the emails from the logins of participants belonging to a given class id */

$class_id=5;
    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $ap_res=$s_participant -> participants_in_class($class_id);
    $ap=result_as_array(new serialized_Render(), $ap_res, 'participant_id');
    $email_arr = array();
    foreach($ap as $var => $val) {
        // print_r( $val);
        extract($val);
        $part_res = $s_participant -> get_logins_by_participant($participant_id);
        $login_part_arr=result_as_array(new serialized_Render(), $part_res, 'id');
        // print_r($login_part_arr);
        foreach($login_part_arr as $login_arr) {
            extract($login_arr);
            $email=trim($email);
            if(strlen($email) > 0) {
                if(!in_array($email, $email_arr)) {
                    $email_arr[] = $email;
                }
            }
        }
    }
    foreach($email_arr as $email) {
        echo $email."\n";
    }
?>
