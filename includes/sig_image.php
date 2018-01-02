<?php
require('config.php');
require('functions.php');

if(ca($action) == 'show_signature') {
    $sig_type = $_REQUEST['sig_type'];
    if($sig_type == 'registration') {
        $s_login_reg = new S_login_reg;
        $s_login_reg -> db = $db; # god i need to fix this
        $login_id               = $_REQUEST['login_id'];
        $reg_id                 = $_REQUEST['reg_id'];
        $res                    = $s_login_reg -> get_reg_login ($login_id, $reg_id);
        $arr                    = $res -> fetch_assoc();
        $signature              = $arr['registration_signature'];
        $options['imageSize']=array($sig_width['registration'], $sig_height['registration']);
    }
    if($sig_type == 'agreement') {

        $s_login_reg = new S_login_reg;
        $s_login_reg -> db = $db; # god i need to fix this
        $login_id               = $_REQUEST['login_id'];
        $reg_id                 = $_REQUEST['reg_id'];
        $res                    = $s_login_reg -> get_reg_login ($login_id, $reg_id);
        $arr                    = $res -> fetch_assoc();
        $signature              = $arr['agreement_signature'];
        $options['imageSize']=array($sig_width['agreement'], $sig_height['agreement']);
    }
    if($sig_type == 'waiver') {
        $s_participant_reg = new S_participant_reg;
        $s_participant_reg -> db = $db; # god i need to fix this
        $participant_id         = $_REQUEST['participant_id'];
        $reg_id                 = $_REQUEST['reg_id'];
        $res                    = $s_participant_reg -> waiver_by_participant ($participant_id, $reg_id);
        $arr                    = $res -> fetch_assoc();
        $signature              = $arr['signature'];
        $options['imageSize']=array($sig_width['waiver'], $sig_height['waiver']);
    }
    $signature=sigJsonToImage($signature, $options);
    header('Content-Type: image/png');
    imagepng($signature);
}
