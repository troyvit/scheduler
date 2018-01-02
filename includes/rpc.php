<?php
session_save_path('/var/tmp');
ini_set('session.gc_probability', 1);
session_start();
require('config.php');
require('functions.php');
// base vars
$log_level = false;

// for filemaker: email=fmp@infantaquatics.com&password=14mt0t4llys3cur3
//  Insert from URL is what you use to ping a url and insert a result.
//  you can use it for 2 things:
//  1. remote db id (id of record tied to fmp record)
//  2. last modified date so she can know if they're sync'd

// get language defaults

$_SESSION['lang']=$lang;

require('language.php');

// how to use the class to get a phrase echo $sl->gp('color key');

// how to use echo $ph_arr['color key']['phrase'];

$s_Login = new s_Login;
$s_Login -> db = $db;
$s_Login -> log_level=false;
$s_Login -> db_logout_params = $logout_params['increment'].' '.$logout_params['time'];

if(strlen($_SESSION['login_hash']) > 0) {
    $login_res = $s_Login -> login_by_session(session_id(), $_SESSION['login_hash']);
    if($login_res -> num_rows != 0) {
        $login_data = $login_res ->fetch_assoc();
        $u_login_id              = $login_data['id'];
        $u_login_fname           = $login_data['fname'];
        $u_login_lname           = $login_data['lname'];
        $u_login_email           = $login_data['email'];
        $u_login_log_level       = $login_data['log_level'];
        $s_Login -> log_level    = $login_data['log_level'];
        $_SESSION['log_level']   = $login_data['log_level'];
        // update last_log
        $update_log = $s_Login -> update_last_log();
        if($u_login_log_level==3) {
            $is_admin=true;
        } else {
            $is_admin=false;
        }
        // echo 'you have '.$update_log."<br>";
        // create a fucntion called has_admin() and have it be by the hash and have
        // that be what decides stuff like if the shelf is going to come out and such.
        /* get all the login's participants */
        $s_participant = new S_participant;
        $s_participant -> db = $db;
        // debug // print_r($login_data);
        $part_res = $s_participant -> get_participants_by_login($login_data['id']);
        $login_part_arr=result_as_array(new serialized_Render(), $part_res, 'id');
        // debug // echo '<pre>'; print_r($login_part_arr); echo '</pre>';
    } else {
        // clear out any old sessions
        $_SESSION = array();
        // delete the cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }
} else {
    // user is not logged in
    $_SESSION['log_level']=0;
}

// to test add an $action
if(ca($action) == 'login_address') {
    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db; # god i need to fix this

}

if(ca($action) == 'gen_register') {
    $s_reg = new S_reg;
    $s_reg -> db = $db; # god i need to fix this

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db;

    $login_id = $_REQUEST['login_id'];
    $reg_id   = $_REQUEST['reg_id'];
    if($reg_id*1==0) {
        $reg_id = $default_reg_id;
    }

    $part_res = $s_participant -> get_participants_by_login($login_id);
    $part_arr=result_as_array(new serialized_Render(), $part_res, 'id');
    $amount_due = $registration['orig'];
    
    $res = $s_login_reg -> get_reg_login ($login_id, $reg_id);
    if($res->num_rows == 0) {
        // insert login reg info
        $s_login_reg -> reg_login_insert($login_id, $reg_id);
        $reg_login_id=$db->insert_id;
        // nice to have but I don't use it. Someday I'll wish I did.
        // and that day is today.
    } else {
        $s_login_arr = $res -> fetch_assoc();
        $reg_login_id = $s_login_arr['id'];
    }

    foreach($part_arr as $participants) {
        $id = $participants['id'];
        $p_fname = trim($participants['fname']);
        $p_lname = trim($participants['lname']);
        $p_name=$p_fname.' '.$p_lname;
        $w_res = $s_participant_reg -> waiver_by_participant($id, $reg_id);
        $w_num = $w_res->num_rows;
        if($w_num == 0) {
            $s_participant_reg -> insert_participant_waiver_item($id, $reg_id, 'amount_due', $amount_due);
            $waiver_id=$db->insert_id;
            // you need to update with the waiver text
            $w_res = $s_reg -> get_registration_document ( 1 ); 
            $w_arr = $w_res -> fetch_assoc();
            // debug // echo "p_name is $p_name <br>";
            $waiver = sprintf($w_arr['document_text'], $p_name);
            $waiver = $s_participant_reg -> db ->real_escape_string($waiver);
            $s_participant_reg -> update_participant_waiver_item($waiver_id, 'waiver', $waiver);
        }
        // still change amount_due in case we're adding a sibling.
        $amount_due = $registration['sibling']; // much simpler
    }

    echo '{"reg_login_id":"'.$reg_login_id.'"}'; // can't keep me down baby
}

if(ca($action) == 'check_register_pay') {
    // check to see if money is owed for a login's registration.

    $s_reg = new S_reg;
    $s_reg -> db = $db; # god i need to fix this

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db;

    $login_id = $_REQUEST['login_id'];
    $reg_id   = $_REQUEST['reg_id'];

    if($reg_id=='') {
        $reg_id = $default_reg_id;
    }

    $part_res = $s_participant -> get_participants_by_login($login_id);
    $part_arr=result_as_array(new serialized_Render(), $part_res, 'id');
    $tot=0;
    foreach($part_arr as $participants) {
        $id = $participants['id'];
        $w_res = $s_participant_reg -> waiver_by_participant($id, $reg_id);
        $w_num = $w_res->num_rows;
        if($w_num != 0) {
           $waiver_arr = $w_res -> fetch_assoc();
           $tot.=$waiver_arr['amount_due'];
        }
    }

    if($tot > 0) {
        echo '{"register_pay":"true"}';
    } else {
        echo '{"register_pay":"false"}';
    }
}

if(ca($action) == 'new_register') {
    $reg_id   = $_REQUEST['reg_id'];
    $login_id = $_REQUEST['login_id'];

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    $s_reg = new S_reg;
    $s_reg -> db = $db; # god i need to fix this

    $radio_in_a_row=2;
    $text_in_a_row=100;

    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db; # god i need to fix this

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_section_group = new S_section_group;
    $s_section_group -> db = $db;

    // see if a reg_login exists yet

    if(strlen($login_id)==0) {
        echo '<div style="margin-left: 60px;">';
        echo '<h1>'.$sl->gp('There was an error').'</h1>';
        echo '<h3>'.$sl->gp('Please provide a log in').'</h3>';
        echo '</div>';
        die();
    }

    $enc_login_id = base64_decode(pack("H*" , $login_id));
    if(strlen($enc_login_id) != 0) {
        $login_id = $enc_login_id;
    }

    $rl_res = $s_login_reg -> get_reg_login($login_id, $reg_id);
    if($rl_res->num_rows == 0) {
        // insert a blank login record...?
        $s_login_reg -> reg_login_insert($login_id, $reg_id);
        $reg_login_id=$db->insert_id;
    } else {
        $view_mode = 'edit';  // view mode for the whole reg
        // doesn't take into account if you add a new participant.
        $rl_arr = $rl_res -> fetch_assoc();
        $reg_login_id              = $rl_arr['id'];
        $registration_signed_name  = $rl_arr['registration_signed_name'];
        $registration_signature    = $rl_arr['registration_signature'];
        $agreement_text            = $rl_arr['agreement_text'];
        $agreement_signature       = $rl_arr['agreement_signature'];
        $agreement_signed_name     = $rl_arr['agreement_signed_name'];
        $reg_status                = $rl_arr['reg_status'];
        // debug // echo "status is $reg_status for $reg_login_id<br>";
        if($reg_status == 2 && strlen($registration_signed_name) > 0) { // pain and sadness, suffering and woe
            $view_mode = 'read';
        }
    }

    // find out if we need to pay, as that will tell us where the form goes
    $part_res = $s_participant -> get_participants_by_login($login_id);
    $part_arr=result_as_array(new serialized_Render(), $part_res, 'id');
    $tot=0;
    foreach($part_arr as $participants) {
        $id = $participants['id'];
        $w_res = $s_participant_reg -> waiver_by_participant($id, $reg_id);
        $w_num = $w_res->num_rows;
        if($w_num != 0) {
           $waiver_arr = $w_res -> fetch_assoc();
           // print_r($waiver_arr);
           $tot+=$waiver_arr['amount_due'];
        }
    }
    $part_res='';
    $part_arr='';
    $participants='';
    $id='';
    $w_num='';
    $w_res='';
    $waiver_arr='';
    if($tot > 0) {
        $form_action=$payment[$payment_method]['cart'];
    } else {
        $form_action='/schedule/utilities/reg_process.php'; // needs to be a config option
        $zeropay='process'; // why did I name it this?
    }
    // debug // $form_action='./utilities/reg_process.php';
    // knock knock.
    // who's there?
    // fuck you jquery.
    // echo '<form id="reg" name="registration_form" method="post" action="'.$form_action.'">';
    // echo '<form id="reg" name="reg" method="post" action="registration.php">'; // fix
    echo '<form id="reg" name="reg">'; // fixed?

    $login_res = $s_Login -> get_login_from_id($login_id);
    $login_data = $login_res ->fetch_assoc();
    $login_fname=$login_data['fname'];
    $login_lname=$login_data['lname'];
    $login_email=$login_data['email'];

    $part_res = $s_participant -> get_participants_by_login($login_id);
    $part_arr=result_as_array(new serialized_Render(), $part_res, 'id');

    $at_res = $s_login_reg -> login_address_types();

    while($at_arr = $at_res->fetch_assoc()) {
        $address_type = $at_arr['address_type'];
        $address_type_id = $at_arr['id'];
        $address_type_name=strtolower(clean_string($address_type));
        $address_type_name=str_replace(' ','_', $address_type_name);
        $atns[$address_type_id]=array('address_type_name'=>$address_type_name,'address_type'=>$address_type); // save for your divs
        $address_tabs.='<li>
            <a href="#'.$address_type_name.'">'.$address_type.'</a>
            </li>';
        $address_res = $s_login_reg -> address_by_login_type($login_id, $address_type_id);
        $login_address[$address_type_id] = $address_res -> fetch_assoc();
    }

    $reg_res = $s_reg -> get_reg($reg_id);
    $reg_data = $reg_res->fetch_assoc();

    echo '<h1>'.$reg_data['reg_title'].'</h1>';

    // get login-level questions
    $ls_res = $s_reg->sections_by_reg($reg_id, 'login');
    while($login_sections = $ls_res -> fetch_assoc()) {
        $section_id   = $login_sections['id'];
        $section_name = $login_sections['section_name'];
        echo '<div class="reg_section reg_section_'.$section_id.'" id="reg_section_login_'.$login_id.'_'.$section_id.'">'."\n";
        echo '<h3>'.$section_name.'</h3>'."\n";
        // this is nicked straight from templates/participant_reg
        // which is bad
        $q_res = $s_reg -> questions_by_section($section_id);
        while($questions = $q_res -> fetch_assoc()) {
            $field_data=array();
            $question        = $questions['question'];
            $question_id     = $questions['id'];
            $question_name   = $questions['question_name'];
            $answer_group_id = $questions['answer_group_id'];
            $is_required     = $questions['is_required'];
            // load up any answers you have for this question
            $qa_res = $s_login_reg -> get_l_reg_answer($login_id, $question_id);
            if($qa_res -> num_rows != 0) {
                $qa_arr = $qa_res->fetch_assoc();
                $answer=htmlentities($qa_arr['answer']);
            } else {
                $answer='';
            }
            if($answer_group_id * 1 > 0) {
                // until I can rectify the login and participant templates 
                // we're just doing checkboxes and text boxes.
                if($g_res = $s_reg -> get_answer_group($answer_group_id, $question_name, $question_id)) {
                    $g_arr = result_as_array(new serialized_Render(), $g_res, 'id');
                    foreach($g_arr as $gvar => $gval) {
                        foreach($gval as $mygkey => $myname)  {
                            // echo "key is $mygkey and val is $myname<br>";
                            if($mygkey=='question_name') {
                                $g_arr[$gvar]['simple_q']=$myname;
                                $killme=explode('|', $myname);
                                $myname=$killme[0].'['.$login_id.']['.$killme[1].']';
                                $myname='l_reg_answer|answer|lqa|'.$login_id.','.$question_id;
                                $g_arr[$gvar][$mygkey]=$myname;
                            }
                            // so so sorry Future-Troy
                            // that's ok Past-Troy, I can still laugh at this
                            if($mygkey=='answer') {
                                $g_arr[$gvar][$mygkey]=$question;
                            }
                        }
                    }
                    $preload_res = $s_reg -> get_preload_data($answer_group_id);
                    $preload_arr = $preload_res -> fetch_assoc();
                    $answer_type = $preload_arr['answer_type'];
                    $preload_id  = $preload_arr['id'];
                    $field_data['name_name']   = 'question_name';
                    $field_data['id_name']     = 'field_id'; 
                    $field_data['label_name']  = 'answer';
                    $field_data['value_key']   = 'id';
                    $field_data['value_name']  = 'answer';
                    $field_data['input_class'] = 'editable';
                    $field_data['extra_attr']  = is_required( $is_required );  

                    /*
                    echo '<pre>';
                    print_r($g_arr);
                    echo '</pre>';
                    */
                    if($answer_type == 'checkbox') {
                        /* not sure this belongs here */
                        if(strlen($answer) > 0) {
                            $selected[]=$answer;
                        }
                        if(!is_array($selected)) {
                            // this should be fixed
                            $selected=array();
                        }
                        $preload_checkboxen=new_as_html_check_boxes(new html_Render(), $field_data, $g_arr, $selected);
                        unset($selected);
                        $q_line = '<div class="register_checkboxes">'.$preload_checkboxen.'</div>'."\n";
                    }
                    unset($preload_arr);
                    unset($field_data);
                }
            } else {
                $t++; // increment the number of text boxes we have
                $r=0; // reset the radio buttons
                if($t >= $text_in_a_row) {
                    $break_row=" break_form_line ";
                    $q_line.='<div class="break_row"></div>';
                    $t=0;
                } else {
                    $break_row="";
                }
                $extra_attr = is_required($is_required);
                $extra_class=" div_text $break_row";
                $question='<label for="'.$question_name.'">'.$question.'</label>';
                $myname='l_reg_answer|answer|lqa|'.$login_id.','.$question_id;
                $q_form = '<input class="editable text_'.$section_id.' text_'.$question_name.'" type="text" name="'.$myname.'" id="'.$question_name.'-'.$question_id.'" value="'.$answer.'" '.$extra_attr.' >';
                $q_line.= '<div class="div_'.$question_name.' reg_question question_'.$section_id.$extra_class.'"><span class="reg_question_text_container">'.$question .'</span>'. $q_form. '</div><!-- end question div -->';
            }
            echo $q_line;
            $q_line='';
        }
        unset($q_res);
        unset($g_arr);
        unset($qa_arr);
        unset($questions);
        echo '</div>';
    }
    echo '<div class="reg_section">';
    echo '<h3>'.$sl->gp('Contact Information').'</h3>';
    echo '<div id="addressTypeTabs"><ul>'.$address_tabs.'</ul>';
    require('templates/login_reg.php');
    echo '</div></div>';
    echo '<div class="reg_section">';
    echo '<div id="participantRegTabs"><ul>';
    foreach($part_arr as $participant_id => $participants) {
        $tab_name=$participants['fname'].' '.$participants['lname'];
        echo '<li><a href="#form_'.$participant_id.'">'.$tab_name.'</a></li>';
    }
    echo '</ul>';
    // reset($part_arr);
    foreach($part_arr as $participant_id => $participants) {
        $section_id_arr=array();
        $name=$participants['fname'].' '.$participants['lname'];
        $names[$participant_id]=$participants['fname'].' '.$participants['lname']; // for the billing
        $dob = $participants['dob'];
        echo '<!-- start form for participant -->
        <div class="reg_holder" id="form_'.$participant_id.'">';

        // get the section ids for the participant
        $sg_res = $s_section_group -> get_participant_sections($participant_id, $reg_login_id);
        while($sg_arr= $sg_res -> fetch_assoc()) {
            $section_id_arr[] = $sg_arr['section_id'];
        }
        if(count($section_id_arr) == 0) {
            echo '<h2>'.$sl->gp('No registration needed').'</h2>';
            echo '</div><!-- end participant form -->';
            continue;
        } else {
            // leave this here for debugging
        }
        $section_ids = implode(',',$section_id_arr);
        // then plug them in
        $s_res = $s_reg -> sections_by_ids($section_ids);
        require('templates/participant_reg.php');
        $w_res = $s_participant_reg -> waiver_by_participant($participant_id, $reg_id);
        $w_num = $w_res->num_rows;
        if($w_num > 0) {
            // debug // echo "we found reg for $participant_id <br>";
            $w_arr = $w_res -> fetch_assoc();
            // debug // echo '<pre>'; print_r($w_arr); echo '</pre>';
            $waiver = $w_arr['waiver'];
            $waiver_id = $w_arr['id'];
            $signature = $w_arr['signature'];
            $signed_name = $w_arr['signed_name'];
            $signature_date = $w_arr['signature_date'];
            $waiver_status = $w_arr['waiver_status'];
            if($waiver_status !=2) {
                $amounts_due[$participant_id] = $w_arr['amount_due']; // for the billing; assumes 1 waiver per participant which should be ok given reg_id
                $waiver_viewmode = 'edit'; // I guess?
            } else {
                $waiver_viewmode = 'read';
                $amounts_due[$participant_id] = "0";
            }
            $statii[$participant_id] = $w_arr['waiver_status']; // that's right, statii baby
        } else {
            $waiver_viewmode = 'edit'; // I guess?
            // welp we need to grab the default
            $w_res = $s_reg -> get_registration_document ( 1 ); // 1 is hardcoded to this document. Sorry future-Troy
            $w_arr = $w_res -> fetch_assoc();
            $waiver = sprintf($w_arr['document_text'], $name);
            $signature = '';
            $signature_date=date('Y-m-d');
        }
        require('templates/participant_waiver.php');
        echo '</div><!-- end participant form -->';
    }
    echo '</div><!-- close tabs container -->';
    echo '</div><!-- end reg_section for participants -->';
    $a_res = $s_reg -> get_registration_document ( 2 ); // 2 is hardcoded to this document. Sorry future-Troy
    $a_arr = $a_res -> fetch_assoc();
    $agreement_text = $a_arr['document_text'];
    $agreement_signature = ''; // good idea?
    $agreement_signature_date=date('Y-m-d');
    echo '<div class="reg_section"><h3>'.$sl->gp('Payment').'</h3>';
    echo '<div id="agreementPaymentTabs"><ul><li><a href="#agreement">'.$sl->gp('Agreement').'</a><li><li><a href="#payment">'.$sl->gp('Payment').'</a></li></ul>';
    require('templates/agreement.php');

    $p_res = $s_reg -> get_registration_document ( 3 ); // 3 is hardcoded to this document. Sorry future-Troy

    $p_arr = $p_res -> fetch_assoc();
    $name_count = count($names);
    $comma='';
    $i=0;
    foreach($names as $name) {
        $i++;
        if($i == $name_count) {
            $comma=' and ';
        }
        $payment_names.=$comma.$name;
        $comma=', ';
    }
    $payment_doc = sprintf($p_arr['document_text'], '<strong>'.$payment_names.'</strong>');
    echo '</form>'; // end reg form

    echo '<form id="billing_reg" name="billing_reg" method="post" action="'.$form_action.'">';

    $zeropay='process';
    if($zeropay == 'process') {
        echo '<input type="hidden" name="zeropay" value="process">';
    }
    // because of a bug where Intrix passes all form values through its system as a $%@$#_GET I need to be more careful about my signatures. None can go through Intrix. As a result the top form ends and the bottom form begins inside templates/billing_reg.
    
    require('templates/billing_reg.php');
    echo '</form>'; // end billing reg form
    echo '</div></div><!-- end reg section -->';
}

if(ca($action) == 'sign_waiver') {
    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db; # god i need to fix this
    $waiver_id = $_REQUEST['waiver_id'];
    $res = $s_participant_reg -> update_participant_waiver_item($waiver_id, 'waiver_status', '2');
    if($res == true) {
        echo '{"status":"success"}';
    } else {
        echo '{"status":"'.$res.'"}';
    }
}

if(ca($action) == 'submit_signature') {
    // this isn't used. Rather update_reg_login takes this on
    // because past-troy was a little cooler than I gave him 
    // credit for
    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    $data = $_POST['data'];

    $allowed_fieldnames=array('registration_signature', 'agreement_signature', 'signature');
    $allowed_tables = array ('reg_login', 'participant_waiver');
}

if(ca($action) == 'update_reg_login') {
    $lreg         = $_REQUEST['lreg'];
    $field_name   = $_REQUEST['field_name'];
    $field_val    = $_REQUEST['field_val'];

    $lreg_arr = explode(',',$lreg);
    print_r($lreg_arr);
    $login_id = $lreg_arr[0];
    $reg_id   = $lreg_arr[1];

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this
    // this has become a pretty big table.
    $allowed_fieldnames=array('registration_signature','registration_signed_name','registration_sig_date','registration_sig_hash','agreement_text','agreement_signature','agreement_signed_name','agreement_sig_date','agreement_sig_hash','reg_status');

    $field_val = $db ->real_escape_string($field_val);

    if(in_array($field_name, $allowed_fieldnames)) {
        echo "updating! \n";
        $s_login_reg -> update_reg_login_item($login_id, $reg_id, $field_name, $field_val);
    }
}

if(ca($action) == 'update_participant_waiver') {
    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db; # god i need to fix this

    $s_participant = new S_participant;
    $s_participant -> db = $db; 

    $s_reg = new S_reg;
    $s_reg -> db = $db; # god i need to fix this

    $prida=explode(',',$_REQUEST['prid']);
    $participant_id=$prida[0];
    $reg_id=$prida[1];
    $field_name=$_REQUEST['field_name'];
    $field_val=$_REQUEST['field_val'];

    $participant_res = $s_participant -> get_participant($participant_id);
    $participant_arr=$participant_res -> fetch_assoc();

    $p_fname=trim($participant_arr['fname']);
    $p_lname=trim($participant_arr['lname']);
    $p_name=$p_fname.' '.$p_lname;


    $allowed_fieldnames=array('signature', 'signed_name', 'signature_date','waiver');
    $field_val=$s_participant_reg -> db ->real_escape_string($field_val);

    $waiver_res = $s_participant_reg -> waiver_by_participant($participant_id, $reg_id);
    $num=$waiver_res->num_rows;
    if($num == 0) {
        $w_res = $s_reg -> get_registration_document ( 1 ); // 1 is hardcoded to this document. Sorry future-Troy
        // the way this will be done I guess is that when you build a form if you want
        // to insert some hefty amount of documentishnessitude you would pick it from a dropdown menu.
        $w_arr = $w_res -> fetch_assoc();
        $waiver = sprintf($w_arr['document_text'], $p_name);
        $waiver = $s_participant_reg -> db ->real_escape_string($waiver);

        $s_participant_reg -> insert_participant_waiver_item($participant_id, $reg_id, $field_name, $field_val);
        $waiver_id=$db->insert_id;

        $s_participant_reg -> update_participant_waiver_item($waiver_id, 'waiver', $waiver);
    } else {
        // I see no reason why we should update the waiver body
        $waiver_arr = $waiver_res -> fetch_assoc();
        $waiver = $waiver_arr['waiver'];
        $waiver_id = $waiver_arr['id'];
        if(in_array($field_name, $allowed_fieldnames)) {
            $s_participant_reg -> update_participant_waiver_item($waiver_id, $field_name, $field_val);
        }
    }
    // debug // echo "at the bottom of the update <br>";
    if($field_name == 'signed_name') {
        // user signed. update dignature date
        $waiver_date = date('Y-m-d G:i:00');
        $s_participant_reg -> update_participant_waiver_item($waiver_id, 'signature_date', $waiver_date);
    }

}

if(ca($action) == 'update_login_address') {
    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    $allowed_fieldnames=array('id','login_id','address_type_id','is_primary','fname','lname','email','phone_h','phone_w','phone_c','address_1','address_2','city','state','country','zip');

    if(strlen($_REQUEST['login_ids']) > 0) {
        $lia = explode ( ',', $_REQUEST['login_ids']);
        $field_name = $_REQUEST['field_name'];
        $field_val = $_REQUEST['field_val'];
        $login_id        = $lia[0];
        $address_type_id = $lia[1];
        if(in_array($field_name, $allowed_fieldnames)) {
            $test_res = $s_login_reg -> address_by_login_type($login_id, $address_type_id);
            $num=$test_res->num_rows;
            if($num > 0) {
                $result = $s_login_reg -> update_login_address_item($login_id, $address_type_id, $field_name, $field_val);
            } else {
                $result = $s_login_reg -> insert_login_address_item($login_id, $address_type_id, $field_name, $field_val);
            }
            echo "result is $result <br>";
        }
    } 
}

if(ca($action) == 'update_l_reg_answer') {
    $s_reg = new S_reg;
    $s_reg -> db = $db; # god i need to fix this

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; 

    $lqa=explode(',',$_REQUEST['lqa']);
    $login_id    = $lqa[0];
    $question_id = $lqa[1];

    $answer      = $_REQUEST['field_val'];
    $answer      = $s_login_reg -> db ->real_escape_string($answer);
    // why not use $db? I don't know.
    $field_name  = $_REQUEST['field_name'];

    if($reg_id=='') {
        $reg_id = $default_reg_id;
    }

    // get the reg_id so you can note update reg_login with the status

    $grl_res = $s_login_reg -> get_reg_login($login_id, $reg_id);

    $grl_arr = $grl_res -> fetch_assoc();
    $reg_login_id = $grl_arr['id'];

    if(strlen( $answer ) > 0) {
        // we are adding or updating
        // just for fun, in case there's some ajax issue, make sure there isn't already an answer for this login
        // because this is all about fun
        $qa_res = $s_login_reg -> get_l_reg_answer( $login_id, $question_id );
        if( $qa_res -> num_rows > 0 ) {
            // to play it safe I update
            $qa_arr = $qa_res->fetch_assoc();
            $lra_id = $qa_arr['id'];
            $s_login_reg -> update_l_reg_answer ( $lra_id, $answer );
            $s_login_reg -> reg_login_status_update ( $reg_login_id, 3 );
        } else {
            // we insert
            $s_login_reg -> insert_l_reg_answer ( $login_id, $question_id, $answer );
            $s_login_reg -> reg_login_status_update ( $reg_login_id, 3 );
        }
    } else {
        // answer is blank, so we delete
        $s_login_reg -> delete_l_reg_answer ( $login_id, $question_id, $answer );
        // update it anyway because why not?
        $s_login_reg -> reg_login_status_update ( $reg_login_id, 3 );
    }
}

if(ca($action) == 'update_reg_login_status') {
    // currently not used
    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; 

    $reg_login_id = $_REQUEST['reg_login_id'];
    $reg_status   = $_REQUEST['reg_status'];

    $success = $s_login_reg -> reg_login_status_update ( $reg_login_id, $reg_status );
    echo $success;
}

if(ca($action) == 'update_p_reg_answer') {
    // action=update_p_reg_answer&participant_id,question_id=4356,1&field_name=answer&field_val=Male
    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db; # god i need to fix this

    $pqa=explode(',',$_REQUEST['pqa']);
    $participant_id=$pqa[0];
    $question_id=$pqa[1];
    $answer=$_REQUEST['field_val'];
    $answer=$s_participant_reg -> db ->real_escape_string($answer);
    $field_name=$_REQUEST['field_name']; // not that I care in this instance

    // kinda need to figure this out sooner rather than later troy
    if($reg_id=='') {
        $reg_id = $default_reg_id;
    }

    $waiver_res = $s_participant_reg->waiver_by_participant($participant_id, $reg_id);
    $waiver_arr = $waiver_res -> fetch_assoc();
    $waiver_id = $waiver_arr['id'];

    $qa_res = $s_participant_reg -> get_p_reg_answer($participant_id, $question_id);
    if($qa_res -> num_rows != 0) {
        // totally forgot about REPLACE INTO. That is neat sql.
        $qa_arr = $qa_res->fetch_assoc();
        $qa_answer=$qa_arr['answer'];
        if($answer != $qa_answer) {
            $pra_id=$qa_arr['id'];
            $s_participant_reg -> update_p_reg_answer($pra_id, $answer);
            $s_participant_reg -> mark_waiver_begun($waiver_id);
        }
    } else {
            $s_participant_reg -> insert_p_reg_answer($participant_id, $question_id, $answer);
            $s_participant_reg -> mark_waiver_begun($waiver_id);
    }
    echo '{"status":"success"}';
}

/*
if(ca($action) == 'get_login_screen') {
    // this is for client logins
    // is this still used?
    if($_SESSION['log_level'] > 0) {
        $s_billing = new S_billing;
        $s_billing -> db = $db;

        echo "<h3>".$sl->gp('Welcome')." $u_login_fname</h3>";
        require('templates/billing_line_item.php');
    } else {
        require('templates/login_form.php');
    }
}
 */

if(ca($action) == 'get_billing_list') {
    /* second try here, and so I'm a little pissed. What I should have done
     * was get all logins belonging to a class, then cycled through them
     */
    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_event = new S_event;
    $s_event -> db = $db;

    $class_id='';

    if($_REQUEST['class_id'] * 1 > 0) {
        $class_id=$_REQUEST['class_id'];
    } else {
        $class_id = $default_class_id;
    }

    $show_csv = $_REQUEST['show_csv'];

    $s_event -> class_id = $class_id;

    if($_REQUEST['orphans']=='true') {
        // grab all orphaned participants
        $ap_res=$s_participant -> get_orphan_participants();
        $ap=result_as_array(new serialized_Render(), $ap_res, 'ep_id');
    } else {

        // grab all the participants in the class
        $ap_res=$s_participant -> participants_in_class($class_id);
        $ap=result_as_array(new serialized_Render(), $ap_res, 'ep_id');
    }
    /*
    echo '<pre>';
    print_r($ap);
    die();
     */
    $logins_return = array();
    /*
    echo '<pre>';
    print_r($_SERVER);
    echo '</pre>'; die();
     */

    // get all your statuses for later user
    $all_status_res = $s_participant -> get_all_status();
    while($as_arr = $all_status_res -> fetch_assoc()) {
        // set up class names based on human readable statuses
        $status_filter=str_replace(' ', '',strtolower($as_arr['status']));
        $status_id_filter = $as_arr['id'];
        $stats_filter[$status_id_filter]=$status_filter;
    }

    $request_uri=$_SERVER['REQUEST_URI'];
    $html.='<a class="download_button" href="'.$request_uri.'&show_csv=true">Download found set</a>';
    $html.= "<table id='billing_table'>
        <thead>
            <tr>
                <th>".$sl->gp('Parent Name')."</th>
                <th>".$sl->gp('Participant Name')."</th>
                <th>".$sl->gp('Event Name')."</th>
                <th>".$sl->gp('Amount Due')."</th>
                <th>".$sl->gp('Amount Paid')."</th>
                <th>".$sl->gp('Amount Owed')."</th>
                <th>".$sl->gp('Notes')."</th>
            </tr>
        </thead>
        <tbody>";
    $csv=$sl->gp('Unique ID')."\t".$sl->gp('Parent Name')."\t".$sl->gp('Participant Name')."\t".$sl->gp('Event Name')."\t".$sl->gp('Amount Due')."\t".$sl->gp('Amount Paid')."\t".$sl->gp('Amount Owed')."\t".$sl->gp('Notes');
    foreach($ap as $ep_id => $val) {
        $render_line_item = false;
        $participant_id   = $val['participant_id'];
        $event_id         = $val['event_id'];
        // is the below overkill? yes
        if($event_id * 1 > 0) {
            $event_res        = $s_event -> better_get_event($event_id);
            $event_data       = $event_res->fetch_assoc();
            $et_name          = $event_data['et_name'];
            $status_id        = $val['status_id'];
        } else {
            $et_name = 'Orphan';
            $status_id=0;
        }
        // extract($val);
        // grab participant data 

        $participant_res  = $s_participant -> get_participant($participant_id);
        $participant_arr  = $participant_res -> fetch_assoc();
        $p_fname          = trim($participant_arr['fname']);
        $p_lname          = trim($participant_arr['lname']);

        // grab the login who owns the participant
        // if a participant has multiple logins this breaks
        // but that's probably for the better
        $part_login_res = $s_participant -> get_logins_by_participant($participant_id);
        $login_data = $part_login_res ->fetch_assoc();
        $login_name = $login_data['fname'].' '.$login_data['lname'];
        $email = $login_data['email'];
        $login_id = $login_data['id'];
        if($event_id == 0) {
            $ep_res = $s_event -> orphan_event_by_ep_id($ep_id);
        } else {
            $ep_res = $s_event -> event_by_ep_id($ep_id);
        }
        /*
        if($ep_id == 3819 || $ep_id == 4335) {
            echo "just ran one of Signy's <br>";
        }
         */

        while($ep_arr = $ep_res -> fetch_assoc()) {
            $event_participant_id = $ep_arr['event_participant_id'];
            $status_id            = $ep_arr['status_id'];
            $event_id             = $ep_arr['event_id'];
            // echo "id is $event_participant_id  <br>";
            // you are going to stuff a lot of stuff in here
            // see if we have a billing record
            $pb_res = $s_billing->event_billing_by_epid($event_participant_id);
            if($pb_res -> num_rows != 0) {
                $render_line_item = true;
                // why is this a while?!? you already get all the events by participants above!!!
                // this is event_billing and that's different.
                while($pb_arr = $pb_res->fetch_assoc()) {
                    $pb_id              = $pb_arr['id'];
                    $event_line_item_id = $pb_arr['event_line_item_id'];
                    $event_line_item    = $pb_arr['event_line_item'];
                    $another_event_participant_id_the_reason_for_which_is_beyond_me = $pb_arr['event_participant_id'];
                    if(strlen($event_line_item) == 0) {
                        if($event_participant_id == 4891) {
                            echo "we have cows!";
                            die();
                        }
                        // ah so this means there is no event_line item attached
                        // to the event_participant_billing
                        // get the event's name from the event_id
                        $oops_res = $s_event-> event_name_by_event_participant_id ($another_event_participant_id_the_reason_for_which_is_beyond_me);
                        // ^^ here marks where I finally cracked
                        $oops_arr = $oops_res -> fetch_assoc();
                        $event_line_item = $oops_arr['et_name'];
                        $stat= $s_billing -> update_event_participant_billing('event_line_item', $event_line_item, $pb_id);
                    }
                    $amount_due         = $pb_arr['amount_due'];
                    $ep_b_meta          = $pb_arr['ep_b_meta'];
                    if($amount_due > 1) {
                        $total_items_amount_due++;
                    }
                    $amount_paid        = $pb_arr['amount_paid'];
                    $amount_owed        = $amount_due-$amount_paid;
                    if($amount_owed <= 0) {
                        $pay_status = 'paid';
                    } else {
                        if($amount_paid == 0) {
                            $pay_status = 'unpaid';
                        } else {
                            $pay_status = 'partially_paid';
                        }
                    }
                    $li_result = $s_billing -> get_event_line_item ($event_line_item_id);
                    $li_row = $li_result -> fetch_assoc();
                    $line_item = $li_row['line_item']; // not used
                    $html.= "<tr class='status_$status_id $pay_status'>
                        <td class='email'>
                        <span style='display: none;'>??".$stats_filter[$status_id]."'</span>
                        
                        <a href='mailto:$email'>$login_name</a></td>
                        <td>$p_fname $p_lname</td>
                        <td>$et_name</td>
                        <td>$<input class='input_money editable' type='text' id='amount_due_$pb_id' name='event_participant_billing|amount_due|pb_id|$pb_id' value='".$amount_due."'></td>
                        <td>$<input class='input_money editable' type='text' id='amount_paid_$pb_id' name='event_participant_billing|amount_paid|pb_id|$pb_id' value='".$amount_paid."'></td>
                        <td id='amount_owed_$pb_id'>$".number_format($amount_owed, 2);
                    // this should really grab the status for the event
                    // no longer. I'm separating payment status from event status
                    $csv.="\n$pb_id\t$login_name\t$p_fname $p_lname\t$et_name\t$amount_due\t$amount_paid\t$amount_owed\t$ep_b_meta";
                    if($amount_owed > 0) {
                        $html.= "<span style='display: none;'>partial</span>";
                    }
                    $html.= " </td>
                        <td><textarea style='width: 124px; height: 4em;' class='editable' id='ep_b_meta_$pb_id' name='event_participant_billing|ep_b_meta|pb_id|$pb_id'>$ep_b_meta</textarea></td>
                        
                        </tr>";
                    $total_amount_owed+=$amount_owed;
                }
            } else {
                // this means they are in an event
                // but we don't have a billing record for it
                $html.= "<tr class='missing_line_item'>
                    <td><a href='mailto:$email'>$login_name</a></td>
                    <td>$p_fname $p_lname</td>
                    <td>$et_name</td>
                    <td colspan='3'><a href='includes/rpc.php?action=insert_event_billing&participant_id=$participant_id&class_id=$class_id'>Fix me</a></td>
                    </tr>";
                    $csv.="\n$pb_id\t$login_name\t$p_fname $p_lname\t$et_name\tNo billing record\t\t\t$ep_b_meta";
            }
        }
    }
    $html.= "</table>
        <table>
            <tr><td colspan='5'>Total number of accounts due:</td><td>$total_items_amount_due</td></tr>
            <tr><td colspan='5' class='total'>Total:</td><td>$".number_format($total_amount_owed,2)."</td></tr>
        </table>";
    if($show_csv==true) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=billing_list.csv');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . strlen($csv));
        ob_clean();
        flush();
        echo $csv;
    } else {
        echo $html;
    }
}

if(ca($action) == 'update_event_participant_billing') {
    $s_billing = new S_billing;
    $s_billing -> db = $db;

    // action=update_event_participant_billing&pb_id=1715&field_name=amount_due&field_val=48.00 
    $pb_id=$_REQUEST['pb_id'];
    $field_name = $_REQUEST['field_name'];
    $field_val = $_REQUEST['field_val'];

    $allowed_fieldnames=array('amount_due', 'amount_paid','ep_b_meta');

    if(in_array($field_name, $allowed_fieldnames)) {
        $stat= $s_billing -> update_event_participant_billing($field_name, $field_val, $pb_id);
        if($stat !=='') {
            echo '{"status":"success"}';
        }
    }
}

if(ca($action) == 'insert_event_billing') {
    // this inserts billing info for anybody who is in an event and doesn't have billing info

    $s_class = new S_class;
    $s_class -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_event = new S_event;
    $s_event -> db = $db;

    $participant_id=$_GET['participant_id'];
    if($_REQUEST['class_id']=='' ) {
        $class_id=$default_class_id; // use to get what event we're dealing with
    } else {
        $class_id = $_REQUEST['class_id'];
    } // ternary much? No I don't, and mostly I'm glad.

    $s_class->class_id=$class_id;

    $class_res   = $s_class->get_class();
    $class_row   = $class_res->fetch_assoc();
    $class_name  = $class_row['name'];

    $event_line_item_id=1; // this pretty much shows where I'm at with that

    $ret=$s_participant -> participant_in_class_events($participant_id, $class_id);
    $amount_due=$price['group']; 
    while ($epids = $ret->fetch_assoc()) {
        $ep_id = $epids['id'];
        $event_id =$epids['event_id'];
        $event_res = $s_event -> better_get_event($event_id);
        $event_data = $event_res->fetch_assoc();
        // hokay so we don't show event_line_item in the line items table anymore.
        // instead we show class name. That means I'm free to make a giant event_
        // line_item that combines class + et_name.
        $event_line_item = $class_name.', '.$event_data['et_name'];
        // debug // echo "$participant_id is in $ep_id <br>";
        // so now see if there's an event_participant_id in billing
        $b_res = $s_billing -> billing_by_ep_id ($ep_id);
        if($b_res -> num_rows == 0) {
            $tf=$s_billing -> insert_event_billing ($event_line_item_id, $event_line_item, $participant_id, $ep_id, $amount_due, 0, 'Added using the Fix me link on '.date('Y-m-d'));
            // echo "<br>and so $tf <br>";
            // looks like this kid needs to be billing.
            // if this kid is in another class this breaks
            //
            // or maybe needs to go to Billings?
            // dude I would love to go to montana
        } else {
            // echo "already in there <br>";
            // looks like this kid needs a billing update
        }
    }

    header("Location: /schedule/show_billing.php");

}

if(ca($action) == 'login') {
    $email     = $_REQUEST['email'];
    $password  = $_REQUEST['password'];
    $datatype  = $_REQUEST['datatype'];
    $password  = md5($password);
    $login_data = $s_Login -> log_me_in($email, $password);
    if($login_data != false) {
        extract($login_data);
        if($datatype=='' || $datatype=='html') {
            echo '<h3>'.$sl->gp('Welcome').' '.$fname.'</h3>';
            echo '<p><a href="#" id="logout_button">'.$sl->gp('Log out').'</a></p>';
        } elseif($datatype=='json') {
            echo '{"myresult":"true"}';
        }
        $update_log = $s_Login -> update_last_log();
        $_SESSION['log_level']=$login_data['log_level'];
        $_SESSION['login_hash']=$login_data['login_hash'];
    } else {
        // login failed
        if($datatype=='' || $datatype=='html') {
            echo '<h3>'.$sl->gp('Sorry, there was an error. Please try again.').'</h3>';
            require('./templates/login_form.php');
        } elseif($datatype=='json') {
            echo '{"myresult":"false"}';
        }
    }
}

if(ca($action) == 'logout') {
    $datatype = $_REQUEST['datatype'];
    // clear session vars
    $_SESSION = array();
    // delete the cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // destroy the session.
    session_destroy();
    if(strlen($datatype) == 0) {
        echo '<h3>'.$sl->gp('Logged out').'</h3><p>'.$sl->gp('You can log back in below.').'</p>';
        require('templates/login_form.php');
        die();
    }
    if($datatype ==  'json' ) {
        echo '{"myresult":"true"}';
    }
}

if(ca($action)=='get_all_classes') {
    $s_class = new S_class(false);
    $s_class -> db = $db;
    $result=$s_class->get_all_classes();
    // $arr = result_as_array($result);
    $class_arr = result_as_array(new serialized_Render(), $result, 'id');
    $class_arr[$default_class_id]['default']='true';
    echo json_encode($class_arr);
}

if(ca($action)=='get_all_event_types') {
    $s_event = new S_event;
    $s_event -> db = $db;
    $result = $s_event -> get_all_event_types();
    echo result_as_json($result);
}

if(ca($action)=='get_all_locations') {
    $s_event = new S_class(false);
    $s_event -> db = $db;
    $result = $s_event -> get_all_locations();
    echo result_as_json($result);
}

if(ca($action)=='get_all_leaders') {
    $s_event = new S_class(false);
    $s_event -> db = $db;
    $result = $s_event -> get_all_leaders();
    echo result_as_json($result);
}

if(ca($action) == 'load_schedule') {
    // load a weekly schedule
    $id=$_REQUEST['class_id'];
    $phase = $_REQUEST['phase'];
    $s_class = new S_class($id);
    $s_class -> db = $db;

    $s_event = new S_event;
    $s_event -> db = $db;

    $s_private_event = new S_private_event;
    $s_private_event -> db = $db;

    if($id > 0) {
        // we are searching by class
        $by_class=true;
        $result = $s_class->get_class($id);
        $row = $result->fetch_assoc();


        $name=$row['name'];
        $id=$row['id'];
        $start=$row['start'];
        $end=$row['end'];
    } elseif ($_REQUEST['sched_type']=='date_range') {
        // wtf kind of elseif is this anyway.
        $by_range = true;
        $start = $_REQUEST['start'];
        $end   = $_REQUEST['end'];
        $start_math = strtotime($start);
        $end_math   = strtotime($end);
        $diff  = $end_math - $start_math;
        $days  = round($diff / 86400); // moved to inside the if 
        if($diff < 1) { die('<h2>Please select an end date after the start date</h2>'); }
    }
        // debug // echo "<h2>$days</h2>";
    $days++; // ONLY NEEDED WHEN CLASSES START ON A TUESDAY!!
    $sa=explode('-', $start);
    // 2012-09-04
    $this_month=date('n', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
    $this_year=date('Y', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
    $today=date('w', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
    $dom=date('j', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
    $m=0; // by what we increment day of month (or dom)
    if($by_class == true) {
        echo '<pre>';
        // print_r($sa);
        $new_test_date = date('Y-m-d', mktime(0,0,0,$sa[1],$sa[2],$sa[0]));
        // echo '<h3> it is '.$new_test_date.'</h3>';
        echo '</pre>';
    }
    if($by_class==true) {
        $days++; // increment by 1 to get to the end of the your end date, not the begining
        while($m <= $days) {
        // echo "here days is $days and m is $m and dom is $dom<br>";
            $addup=$dom+$m;
            $newdate= date('j', mktime(0,0,0,$this_month, $dom+$m, $this_year));
            $newdate_test= date('Y-m-d', mktime(0,0,0,$this_month, $dom+$m, $this_year));
            $newday= date('w', mktime(0,0,0,$this_month, $dom+$m, $this_year));
            // debug // echo $newdate .': '.$newday.' : '.$newdate_test.'<br>';
            if(strlen($week[$newday]==0)) {
                $week[$newday]=$newdate;
                $betterweek[$newdate_test]=$newdate;
            }
            $m++;
        }
    } elseif($by_range==true) {
        $days++; // increment by 1 to get to the end of the your end date, not the begining
        // debug // echo "m is $m and days is $days <br>";
        while($m < $days) {
            // $newdate= date('j', mktime(0,0,0,$this_month, $dom+$m, $this_year));
            $newdate= $dom+$m; // wow ... after all that dicking around
            $newdate_test= date('Y-m-d', mktime(0,0,0,$this_month, $dom+$m, $this_year));
            // not used $newday= date('w', mktime(0,0,0,$this_month, $dom+$m, $this_year));
            $week[]=$newdate; // this gets its date calcs inside div_weekly_schedule
            $betterweek[$newdate_test]=$newdate;
            $m++;
        }
    }
    ksort($betterweek);
    ksort($week);
    // echo 'start day name is '.$start_day_name.'<br>';
    // debug // print_r($week); // so week starts on the 8th but your range is starting on 9
    // debug // echo "getting events from $start and for $days <br>";
    $ret = $s_event -> get_events_in_date_range($start, $days);
    $i=0;
    while ($events = $ret->fetch_assoc()) {
        $daytime=$events['daytime'];
        $events['privgroup']='group'; // really bad
        $e[$daytime][$i]=$events;
        $i++;
    }
    // debug // echo '<pre>'; print_r($e); die();
    $ret = $s_private_event -> get_private_events_from_start ($start, $days);
    // you modified this function here ^^ on 6/3/14. Hope I didn't break it future-troy.
    while ($private_events = $ret->fetch_assoc()) {
        $p_daytime=$private_events['daytime'];
        $private_events['privgroup']='private'; // also really bad
        $private_events['et_name']='Private'; // also really bad
        $e[$p_daytime][$i]=$private_events;
        $i++;
    }
    /*
    echo '<pre>';
    print_r($e);
    echo '</pre>';
     */
    // styling for the schedule template 
    $schedule_week_width=350*$days;
    if($phase == 2) {
        require('templates/phase2_weekly.php');
    } else {
        require('templates/div_weekly_schedule.php');
    }
}

if(ca($action) == 'event_details') {
    // get a little box with neat event info to show stuff

    $s_event = new S_event;
    $s_event -> db = $db;

    $s_private = new S_private_event;
    $s_private -> db = $db;

    $s_leader = new S_leader;
    $s_leader -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_location = new S_location;
    $s_location -> db = $db;

    // save all locations in an array

    $location_result = $s_location -> get_all_locations();
    while($la = $location_result -> fetch_assoc()) {
        $id=$la['id'];
        $location_hash[$id]=$la['location'];
    }

    // save all event types in an array
    $event_result = $s_event -> get_all_event_types();
    while ($event_row = $event_result -> fetch_assoc()) {
        // debug // echo "I am totally extracting you \n";
        $id = $event_row['id'];
        $et_code = $event_row['et_code'];
        $event_codes[$id]=$et_code;
    }

    // $daytime              = $_REQUEST['daytime'];
    $active_event_type = $_REQUEST['active_event_type'];
    $active_daytime_id = $_REQUEST['active_daytime_id'];


    // get the event_daytime
    // yeah yeah yeah
    if($active_event_type == 'private') {
        // Ok get your event

        $private_event_daytime_id = $active_daytime_id;
        $event_result = $s_private -> get_private_event_daytime($private_event_daytime_id);
        $p_arr=$event_result -> fetch_assoc();
        $private_event_id = $p_arr['private_event_id'];
        $daytime = $p_arr['daytime'];
        $p_res = $s_private -> get_private_event($private_event_id);
        $arr=$p_res -> fetch_assoc();
        $private_start         = $arr['start'];
        $private_end           = $arr['end'];
        $duration              = $arr['duration'];
        $location_id           = $arr['location_id'];
        $leader_id             = $arr['leader_id'];
        $participant_id        = $arr['participant_id'];
        $status_id             = $arr['status_id'];
        $private_event_time    = date('g:i', $_REQUEST['date_time']);
        $private_event_daytime = date('Y-m-d G:i:00', $_REQUEST['date_time']);

        // get student
        $p_result = $s_participant -> get_participant($participant_id);
        $part_row = $p_result->fetch_assoc();
        $private_participant=$part_row['fname'].' '.$part_row['lname'];

        // get leader
        $l_res=$s_leader -> get_leader_by_id($leader_id);
        $l_arr = $l_res ->fetch_assoc();
        $event_leader = $l_arr['fname'].' '.$l_arr['lname'];

        // get location
        $loc_res = $s_location -> get_location_by_id($location_id);
        $loc_arr = $loc_res ->fetch_assoc();
        $location = $loc_arr['location'];
        $loc_name_arr = explode(" ", $location);

        // check for metadata
        $ped_res = $s_private -> ped_by_pe($private_event_id, $private_event_daytime);
        if($ped_res) {
            $ped_arr = $ped_res -> fetch_assoc();
            $ped_meta = $ped_arr['ped_meta'];
            $ped_meta_id = $ped_arr['id'];
        }

        echo '<h3>Private Lesson</h3>
            <table id="display_private_event" class="'.$private_event_id.'">
            <tr><td>Time</td><td>'.$private_event_time.'</td></tr>
            <tr><td>Start</td><td>'.$private_start.'</td></tr>
            <tr><td>End</td><td>'.$private_end.'</td></tr>
            <tr><td>Location</td><td>'.$location.'</td></tr>
            <tr><td>Student</td><td>'.$private_participant.'</td></tr>
            <tr><td>Instructor</td><td>'.$event_leader.'</td></tr>
            </table>';


    } elseif ($active_event_type == 'group') {
        $event_daytime_id = $active_daytime_id;
        // get event data to display
        $event_res = $s_event -> get_event_by_et_id ($event_daytime_id);
        $arr = $event_res -> fetch_assoc();
        $event_id = $arr['id'];
        $location_id = $arr['location_id'];
        $leader_id = $arr['leader_id'];

        // get location
        $loc_res = $s_location -> get_location_by_id($location_id);
        $loc_arr = $loc_res ->fetch_assoc();
        $location = $loc_arr['location'];
        $loc_name_arr = explode(" ", $location);
        $loc_abbrev = strtolower($loc_name_arr[0]);
        // use this fragment for the final style name
        $g_loc_fragment[$event_daytime_id] = strtolower($loc_abbrev);

        // get the participants
        $ep_res = $s_participant -> participants_in_event($event_id);
        $ep_arr=result_as_array(new serialized_Render(), $ep_res, 'participant_id');
        // debug // echo '<pre>'; print_r($ep_arr); echo '</pre>';
        $br = '';
        foreach($ep_arr as $ep_id => $epa) {
            // print_r($epa);
            $participant_id = $epa['participant_id'];
            $name = $epa['fname'].' '.$epa['lname'];
            $age = ceil($epa['participant_age_months']/12);
            $participants.=$br.$name;
            $br='<br/>';
        }

        // get leader
        $l_res=$s_leader -> get_leader_by_id($leader_id);
        $l_arr = $l_res ->fetch_assoc();
        $event_leader = $l_arr['fname'].' '.$l_arr['lname'];

        // get the event daytime so you can use it to grab al lthe other events
        $ed_res = $s_event -> get_event_daytime($event_daytime_id);
        if($ed_res) {
            $ed_arr  = $ed_res -> fetch_assoc();
            $daytime = $ed_arr['daytime'];
        }

        echo '<h3>Group Class</h3>
            <table id="display_group_event" class="'.$event_daytime_id.' display_event">
            <tr><td>Time</td><td>'.$daytime.'</td></tr>
            <td>Location</td><td>'.$location.'</td></tr>
            <td>Participants</td><td>'.$participants.'</td></tr>
            <td>Instructor</td><td>'.$event_leader.'</td></tr>
            </table>';

    }

    // get all adjacent stuff
    // start with private classes
    $priv_res = $s_private -> get_private_events_at_time ($daytime);

    $i=0;
    while($priv_arr = $priv_res -> fetch_assoc()) {
        extract($priv_arr);
        $priv_ret[$id]=$id;
        $p_res = $s_private -> get_private_event($private_event_id);
        $arr=$p_res -> fetch_assoc();
        $location_id           = $arr['location_id'];
        $private_event_id      = $arr['id'];
        // get location
        $loc_name_arr = explode(" ", $location_hash[$location_id]);

        $loc_abbrev = strtolower($loc_name_arr[0]);

        $style[$id] = " private_".$i."_".$loc_abbrev." ";
        $code[$id]  = "Private";
        $i++;
    }

    // now do group classes
    $i=0;
    $ev_res = $s_event -> get_events_at_time($daytime);
    while($ev_arr = $ev_res -> fetch_assoc()) {
        extract($ev_arr);
        $group_ret[$id]=$id;
        $event_res = $s_event -> get_event_by_et_id ($id);
        $arr = $event_res -> fetch_assoc();
        $location_id = $arr['location_id'];
        $et_id = $arr['et_id'];
        $loc_abbrev_arr = explode(" ", $location_hash[$location_id]);
        $g_loc_abbrev = strtolower($loc_abbrev_arr[0]);
        $style[$id]=" group_".$i."_".$g_loc_abbrev." ";
        $code[$id] = $event_codes[$et_id];
        $i++;
    }

    foreach($group_ret as $id) {
        echo '<span 
            parent_holder="detail_'.$private_event_id.'_'.$id.'" 
            active_event_type="group" 
            active_daytime_id="'.$id.'" class="'.$style[$id].' detail_hover group_detail_hover">'.$code[$id].'</span>';
    }

    foreach($priv_ret as $id) {
        echo '<span 
            parent_holder="detail_'.$private_event_id.'_'.$id.'" 
            active_event_type="private" 
            active_daytime_id="'.$id.'" 
            class = "'.$style[$id].' detail_hover private_detail_hover">Private </span>';
    }
}

if(ca($action) == 'new_daily_schedule') {

    $day = $_REQUEST['day'];

    $leader_toshow = $_REQUEST['leader_toshow'];

    $class_id = $_REQUEST['class_id'];

    if($class_id == '') {
        $class_id = $default_class_id;
    }


    $s_class = new S_class(false);
    $s_class -> db = $db;
    $s_class->class_id = $class_id;

    $s_event = new S_event;
    $s_event -> db = $db;

    $s_private = new S_private_event;
    $s_private -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    // get all leaders so we can filter
    $leader_result = $s_class -> get_all_leaders();
    while($la =$leader_result -> fetch_assoc()) {
        $id=$la['id'];
        $leader_arr[$id]=$la['leader'];
        if($leader_toshow == $id) {
            $sel=' SELECTED ';
            $leader_header = $leader_arr[$leader_toshow];
        } else {
            $sel='';
        }
        $leader_list.='<option '.$sel.' value="'.$id.'">'.$la['leader'].'</option>';
    }


    $location_result = $s_class -> get_all_locations();
    while($la = $location_result -> fetch_assoc()) {
        $id=$la['id'];
        $location_arr[$id]=$la['location'];
    }

    $day_arr = array();

    $group_res = $s_class -> get_all_events_by_day($day);
    $group_arr=result_as_array(new serialized_Render(), $group_res, 'event_id');



    $group_res = $s_class -> get_all_events_by_day($day);
    $group_arr=result_as_array(new serialized_Render(), $group_res, 'event_id');
    // echo '<pre>'; print_r($group_arr); echo '</pre>';
    if(is_array($group_arr)) {
        foreach($group_arr as $ga) {
            $students=""; // reset this var so we can make a list of students
            $show_event    = true;
            $id            = $ga['id'];
            $event_id      = $ga['event_id'];
            $et_id         = $ga['et_id'];
            $edt_meta      = $ga['edt_meta'];
            $duration      = $ga['duration'];
            // get the event type
            $et_arr        = result_as_array(new serialized_Render(), $s_event->get_event_type_by_id($et_id), 'id');
            $et_name       = $et_arr[$et_id]['et_name'];
            $et_desc       = $et_arr[$et_id]['et_desc'];
            $location_id   = $ga['location_id'];
            $location      = $location_arr[$location_id];
            $leader_id     = $ga['leader_id'];
            $leader        = $leader_arr[$leader_id];
            $event_day     = $ga['event_day'];
            $daytime       = $ga['daytime'];
            $event_day_arr = date_parse ( $daytime );
            // $hour = $event_day_arr['hour'];
            $standard_hour=date("g", strtotime($daytime));
            $twentyfour_hour=date("G", strtotime($daytime));
            $minutes=date("i", strtotime($daytime));
            if(strlen($leader_toshow) > 0) {
                if($leader_toshow != $leader_id) {
                    $show_event = false;
                }
            }

            // get all the students in the event
            $ep_res = $s_participant -> participants_in_event($event_id);
            $ep_arr=result_as_array(new serialized_Render(), $ep_res, 'participant_id');
            // debug // echo '<pre>'; print_r($ep_arr); echo '</pre>';
            foreach($ep_arr as $ep_id => $epa) {
                // print_r($epa);
                $participant_id = $epa['participant_id'];
                $name = $epa['fname'].' '.$epa['lname'];
                $age = ceil($epa['participant_age_months']/12);
                // get parents
                $part_login_res = $s_participant -> get_logins_by_participant($participant_id);
                $login_data = $part_login_res ->fetch_assoc();
                $login_fullname = $login_data['fname'].' '.$login_data['lname'];
                $login_email = $login_data['email'];
                $login_id = $login_data['id'];
                if($login_id != '') {
                    $address_res    = $s_login_reg -> address_by_login_type($login_id, 1);
                    $address_info   = $address_res -> fetch_assoc();
                    $phone_h        = $address_info['phone_h'];
                    $phone_w        = $address_info['phone_w'];
                    $phone_c        = $address_info['phone_c'];
                } else {
                    $login_fullname='Unknown parent';
                }
                // $students.="$name, $age<br>";
                $students[$id] .= "<div class='daily_schedule_participant'>$name</div>

        <div class='daily_schedule_login'>
            <a href='mailto:$login_email'>$login_fullname<a/>
            <!--<br>h: $phone_h
            <br>w: $phone_w
            <br>c: $phone_c-->
        </div>";
            }
            // echo '<pre>'; print_r($students); echo '</pre>';

            if($show_event == true) {
                $rowcount[$twentyfour_hour][$minutes]++;
                $day_arr[$twentyfour_hour][$minutes]['group'][$id]=array(
                    'id'=>$id,
                    'students'=>$students[$id],
                    'event_id'=>$event_id,
                    'type' => 'group',
                    'location_id'=>$location_id,
                    'location'=>$location,
                    'leader_id'=>$leader_id,
                    'et_name'=>$et_name,
                    'et_desc'=>" $et_desc",
                    'leader'=>$leader,
                    'event_day'=>$event_day,
                    'daytime'=>$daytime,
                    'event_day_arr'=>$event_day_arr,
                    'edt_meta'=>$edt_meta,
                    'duration'=>$duration,
                );
                // $n_rowspan[$twentyfour_hour][$minutes] += count($day_arr[$twentyfour_hour][$minutes]['group']);
            }
        }
    }

    $priv_res = $s_private -> get_private_events_from_start($day, 1);
    if($priv_res -> num_rows > 0) {
        $priv_arr=result_as_array(new serialized_Render(), $priv_res, 'id');
    }
    // debug // echo '<pre>'; print_r($priv_arr); echo '</pre>';

    if(is_array($priv_arr)) {
        foreach($priv_arr as $pa) {
            $show_event    = true;
            $id            = $pa['id'];
            $ped_id        = $pa['ped_id'];
            $location_id   = $pa['location_id'];
            $location      = $pa['location'];
            $duration      = $pa['duration'];
            $leader_id     = $pa['leader_id'];
            $leader        = $pa['leader'];
            $daytime       = $pa['daytime'];
            $event_day_arr = date_parse ( $daytime );
            // $hour = $event_day_arr['hour'];
            // echo "for $daytime we have: ";
            // print_r($event_day_arr);
            $standard_hour          = date("g", strtotime($daytime));
            $twentyfour_hour        = date("G", strtotime($daytime));
            $minutes                = date("i", strtotime($daytime));
            $daytime                = $pa['daytime'];
            $fullname               = $pa['participant_fullname'];
            $dob                    = $pa['dob'];
            $participant_id         = $pa['participant_id'];
            $days_different         = $pa['days_different'];
            $ped_meta               = $pa['ped_meta'];
            $week                   = ceil($days_different/7);
            if($week == 0) {
                // this is the same day as the first class
                // echo "changed week to 1 for $fullname - $ped_id - $id <br>";
                $week = 1;
            }
            // get login info from the participant
            $participant_age_months = $pa['participant_age_months'];
            if($participant_id*1 > 0) {
                $login_res      = $s_participant -> get_logins_by_participant($participant_id);
                $login_arr      = $login_res -> fetch_assoc();
                // debug // print_r($login_arr);
                $login_fullname = $login_arr['fname'].' '.$login_arr['lname'];
                $login_email    = $login_arr['email'];
                $login_id       = $login_arr['id'];
                // get the mom's login address / phone info
                $address_res    = $s_login_reg -> address_by_login_type($login_id, 1);
                $address_info   = $address_res -> fetch_assoc();
                $phone_h        = $address_info['phone_h'];
                $phone_w        = $address_info['phone_w'];
                $phone_c        = $address_info['phone_c'];
            } else {
                $login_fullname = 'Missing Student';
            }
            // echo $login_fullname.'<br>';
            if(strlen($leader_toshow) > 0) {
                if($leader_toshow != $leader_id) {
                    $show_event = false;
                }
            } else {
                $rowcount[$twentyfour_hour][$minutes]++;
            }
            if($show_event == true) {
                $day_arr[$twentyfour_hour][$minutes]['private'][$id]=array(
                    'id'=>$id,
                    'event_id'=>$event_id,
                    'ped_id'=>$ped_id,
                    'ped_meta'=>$ped_meta,
                    'type' => 'private',
                    'location_id'=>$location_id,
                    'location'=>$location,
                    'duration'=>$duration,
                    'leader_id'=>$leader_id,
                    'leader'=>$leader,
                    'event_day'=>$event_day,
                    'daytime'=>$daytime,
                    'fullname'=>$fullname,
                    'login_fullname'=>$login_fullname,
                    'phone_h'   =>$phone_h,
                    'phone_w'   =>$phone_w,
                    'phone_c'   =>$phone_c,
                    'dob'=>$dob,
                    'age'=>$participant_age_months,
                    'event_day_arr'=>$event_day_arr,
                );
                // $n_rowspan[$twentyfour_hour][$minutes] += count($day_arr[$twentyfour_hour][$minutes]['private']);
            }
        }
    }



    $table_data='<table border=1 id="daily_schedule"> 
        <tr class="daily daily_group"> 
        <th class="daily_header spacer" >Date Dropdown</th> <th class="daily_header spacer" >Day Dropdown</th>
    <th class="daily_header daily_event"><span class="phrase">class</span></th>
    <th class="daily_header daily_detail detail_1"><span class="phrase">detail</span></th>
    <th class="daily_header daily_info"><span class="phrase">student & parent information</span></th>
    <th class="daily_header daily_detail detail_2"><span class="phrase">detail</span></th>
    <th class="daily_header daily_location"><span class="phrase">location</span></th>
    <th class="daily_header daily_leader"><span class="phrase">instructor</span></th>
    <th class="daily_header daily_event_type"><span class="phrase">type</span></th>
    <th class="daily_header daily_notes"><span class="phrase">session notes</span></th>
    </tr>';

    // echo '<Pre>';
    // print_r($day_arr);

    $min_hr = $daily_start_hour*1;
    $max_hr = $daily_end_hour*1;

    echo $table_data;
    while($min_hr < $max_hr) {
        $constraint_counter = 0;
        $hr_show=$min_hr.$time_append;
        $hr_show = str_pad($hr_show, 4, "0", STR_PAD_LEFT);
        $standard_hour= date("g", strtotime("$day $hr_show"));
        $twentyfour_hour = date("G", strtotime("$day $hr_show"));

        while($constraint_counter < 60 ) {
            $ctr=str_pad($constraint_counter, 2, "0", STR_PAD_LEFT);
            // show groups first
            if(is_array($day_arr[$min_hr][$ctr]['group'])) {
                $showthis = $day_arr[$min_hr][$ctr]['group'];
                foreach($showthis as $id => $display_arr) {
                    // fuck it
                    // make up the hour stuff
                    $start = $min_hr[$ctr];
                    extract($display_arr);
                    echo '<tr>';
                    echo '<td> space for hour </td>';
                    echo '<td>';
                        $d = $ctr;
                        $i=0;
                        echo "$min_hr:$d";
                        echo "<br>";
                        $i = $i+10;
                        while($i < $duration) {
                            $d = $d+10;
                            $i = $i+10;
                            echo "$min_hr:$d";
                            echo "<br>";
                        }
                    echo '</td>';
                    echo '<td>'.$et_name.'</td>';
                    echo '<td>deet here</td>';
                    echo '<td>Student/parent table</td>';
                    echo '<td>deet 2 here</td>';
                    echo '<td>'.$location.'</td>';
                    echo '<td>'.$leader.'</td>';
                    echo '<td>group</td>';
                    echo '<td>'.$edt_meta.'</td>';
                    echo '</tr>';
                    // echo "a group: at $min_hr : $ctr -- ".$display_arr['id']." for ".$display_arr['duration'];
                    // print_r($display_arr);
                }
            }

            if(is_array($day_arr[$min_hr][$ctr]['private'])) {
                $showthis = $day_arr[$min_hr][$ctr]['private'];
                foreach($showthis as $id => $display_arr) {
                    // fuck it
                    extract($display_arr);
                    echo '<tr>';
                    echo '<td>space for hour</td>';
                    echo '<td>';
                        $d = $ctr;
                        $i=0;
                        echo "$min_hr:$d";
                        echo "<br>";
                        $i = $i+10;
                        while($i <= $duration) {
                            $d = $d+10;
                            $i = $i+10;
                            echo "$min_hr:$d";
                            echo "<br>";
                        }
                    echo '</td>';
                    echo '<td>'.$fullname.'</td>';
                    echo '<td>deet here</td>';
                    echo '<td>Student/parent table</td>';
                    echo '<td>deet 2 here</td>';
                    echo '<td>'.$location.'</td>';
                    echo '<td>'.$leader.'</td>';
                    echo '<td>private</td>';
                    echo '<td>'.$edt_meta.'</td>';
                    echo '</tr>';
                    // print_r($display_arr);
                }
            }

            $constraint_counter = $constraint_counter + 10;

        }

        $min_hr++;
    }
        echo '</table>';
}


if(ca($action) == 'daily_schedule') {

    $day = $_REQUEST['day'];

    $leader_toshow = $_REQUEST['leader_toshow'];

    $class_id = $_REQUEST['class_id'];

    if($class_id == '') {
        $class_id = $default_class_id;
    }


    $s_class = new S_class(false);
    $s_class -> db = $db;
    $s_class->class_id = $class_id;

    $s_event = new S_event;
    $s_event -> db = $db;

    $s_private = new S_private_event;
    $s_private -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    // get all leaders so we can filter
    $leader_result = $s_class -> get_all_leaders();
    while($la =$leader_result -> fetch_assoc()) {
        $id=$la['id'];
        $leader_arr[$id]=$la['leader'];
        if($leader_toshow == $id) {
            $sel=' SELECTED ';
            $leader_header = $leader_arr[$leader_toshow];
        } else {
            $sel='';
        }
        $leader_list.='<option '.$sel.' value="'.$id.'">'.$la['leader'].'</option>';
    }


    $location_result = $s_class -> get_all_locations();
    while($la = $location_result -> fetch_assoc()) {
        $id=$la['id'];
        $location_arr[$id]=$la['location'];
    }

    $day_arr = array();

    $group_res = $s_class -> get_all_events_by_day($day);
    $group_arr=result_as_array(new serialized_Render(), $group_res, 'event_id');
    // echo '<pre>'; print_r($group_arr); echo '</pre>';
    if(is_array($group_arr)) {
        foreach($group_arr as $ga) {
            $students=""; // reset this var so we can make a list of students
            $show_event    = true;
            $id            = $ga['id'];
            $event_id      = $ga['event_id'];
            $et_id         = $ga['et_id'];
            $edt_meta      = $ga['edt_meta'];
            // get the event type
            $et_arr        = result_as_array(new serialized_Render(), $s_event->get_event_type_by_id($et_id), 'id');
            $et_name       = $et_arr[$et_id]['et_name'];
            $et_desc       = $et_arr[$et_id]['et_desc'];
            $location_id   = $ga['location_id'];
            $location      = $location_arr[$location_id];
            $leader_id     = $ga['leader_id'];
            $leader        = $leader_arr[$leader_id];
            $event_day     = $ga['event_day'];
            $daytime       = $ga['daytime'];
            $event_day_arr = date_parse ( $daytime );
            // $hour = $event_day_arr['hour'];
            $standard_hour=date("g", strtotime($daytime));
            $twentyfour_hour=date("G", strtotime($daytime));
            $minutes=date("i", strtotime($daytime));
            if(strlen($leader_toshow) > 0) {
                if($leader_toshow != $leader_id) {
                    $show_event = false;
                }
            }

            // get all the students in the event
            $ep_res = $s_participant -> participants_in_event($event_id);
            $ep_arr=result_as_array(new serialized_Render(), $ep_res, 'participant_id');
            // debug // echo '<pre>'; print_r($ep_arr); echo '</pre>';
            foreach($ep_arr as $ep_id => $epa) {
                // print_r($epa);
                $participant_id = $epa['participant_id'];
                $name = $epa['fname'].' '.$epa['lname'];
                $age = ceil($epa['participant_age_months']/12);
                // get parents
                $part_login_res = $s_participant -> get_logins_by_participant($participant_id);
                $login_data = $part_login_res ->fetch_assoc();
                $login_fullname = $login_data['fname'].' '.$login_data['lname'];
                $login_email = $login_data['email'];
                $login_id = $login_data['id'];
                if($login_id != '') {
                    $address_res    = $s_login_reg -> address_by_login_type($login_id, 1);
                    $address_info   = $address_res -> fetch_assoc();
                    $phone_h        = $address_info['phone_h'];
                    $phone_w        = $address_info['phone_w'];
                    $phone_c        = $address_info['phone_c'];
                } else {
                    $login_fullname='Unknown parent';
                }
                // $students.="$name, $age<br>";
                $students[$id] .= "<div class='daily_schedule_participant'>$name</div>

        <div class='daily_schedule_login'>
            <a href='mailto:$login_email'>$login_fullname<a/>
            <br>h: $phone_h
            <br>w: $phone_w
            <br>c: $phone_c
        </div>";
            }
            // echo '<pre>'; print_r($students); echo '</pre>';

            if($show_event == true) {
                $rowcount[$twentyfour_hour][$minutes]++;
                $day_arr[$twentyfour_hour][$minutes]['group'][$id]=array(
                    'id'=>$id,
                    'students'=>$students[$id],
                    'event_id'=>$event_id,
                    'type' => 'group',
                    'location_id'=>$location_id,
                    'location'=>$location,
                    'leader_id'=>$leader_id,
                    'et_name'=>$et_name,
                    'et_desc'=>" $et_desc",
                    'leader'=>$leader,
                    'event_day'=>$event_day,
                    'daytime'=>$daytime,
                    'event_day_arr'=>$event_day_arr,
                    'edt_meta'=>$edt_meta,
                );
                // $n_rowspan[$twentyfour_hour][$minutes] += count($day_arr[$twentyfour_hour][$minutes]['group']);
            }
        }
    }

    $priv_res = $s_private -> get_private_events_from_start($day, 1);
    if($priv_res -> num_rows > 0) {
        $priv_arr=result_as_array(new serialized_Render(), $priv_res, 'id');
    }
    // debug // echo '<pre>'; print_r($priv_arr); echo '</pre>';
    if(is_array($priv_arr)) {
        foreach($priv_arr as $pa) {
            $show_event    = true;
            $id            = $pa['id'];
            $ped_id        = $pa['ped_id'];
            $location_id   = $pa['location_id'];
            $location      = $pa['location'];
            $leader_id     = $pa['leader_id'];
            $leader        = $pa['leader'];
            $daytime       = $pa['daytime'];
            $event_day_arr = date_parse ( $daytime );
            // $hour = $event_day_arr['hour'];
            // echo "for $daytime we have: ";
            // print_r($event_day_arr);
            $standard_hour          = date("g", strtotime($daytime));
            $twentyfour_hour        = date("G", strtotime($daytime));
            $minutes                = date("i", strtotime($daytime));
            $daytime                = $pa['daytime'];
            $fullname               = $pa['participant_fullname'];
            $dob                    = $pa['dob'];
            $participant_id         = $pa['participant_id'];
            $days_different         = $pa['days_different'];
            $ped_meta               = $pa['ped_meta'];
            $week                   = ceil($days_different/7);
            if($week == 0) {
                // this is the same day as the first class
                // echo "changed week to 1 for $fullname - $ped_id - $id <br>";
                $week = 1;
            }
            // get login info from the participant
            $participant_age_months = $pa['participant_age_months'];
            if($participant_id*1 > 0) {
                $login_res      = $s_participant -> get_logins_by_participant($participant_id);
                $login_arr      = $login_res -> fetch_assoc();
                // debug // print_r($login_arr);
                $login_fullname = $login_arr['fname'].' '.$login_arr['lname'];
                $login_email    = $login_arr['email'];
                $login_id       = $login_arr['id'];
                // get the mom's login address / phone info
                $address_res    = $s_login_reg -> address_by_login_type($login_id, 1);
                $address_info   = $address_res -> fetch_assoc();
                $phone_h        = $address_info['phone_h'];
                $phone_w        = $address_info['phone_w'];
                $phone_c        = $address_info['phone_c'];
            } else {
                $login_fullname = 'Missing Student';
            }
            // echo $login_fullname.'<br>';
            if(strlen($leader_toshow) > 0) {
                if($leader_toshow != $leader_id) {
                    $show_event = false;
                }
            } else {
                $rowcount[$twentyfour_hour][$minutes]++;
            }
            if($show_event == true) {
                $day_arr[$twentyfour_hour][$minutes]['private'][$id]=array(
                    'id'=>$id,
                    'event_id'=>$event_id,
                    'ped_id'=>$ped_id,
                    'ped_meta'=>$ped_meta,
                    'type' => 'private',
                    'location_id'=>$location_id,
                    'location'=>$location,
                    'leader_id'=>$leader_id,
                    'leader'=>$leader,
                    'event_day'=>$event_day,
                    'daytime'=>$daytime,
                    'fullname'=>$fullname,
                    'login_fullname'=>$login_fullname,
                    'phone_h'   =>$phone_h,
                    'phone_w'   =>$phone_w,
                    'phone_c'   =>$phone_c,
                    'dob'=>$dob,
                    'age'=>$participant_age_months,
                    'event_day_arr'=>$event_day_arr,
                );
                // $n_rowspan[$twentyfour_hour][$minutes] += count($day_arr[$twentyfour_hour][$minutes]['private']);
            }
        }
    }


    /*
    echo '<pre>';
    print_r($rowcount);
    echo '</pre>';
    */

    // works! // 
    // debug // echo '<pre>'; print_r($day_arr);
    // wow past troy you are a trip

    // split the table up by hour based on the existing time constraint
    $rowspan            = 60/$daily_schedule_time_constraint;
    // this is set by just splitting based on the # of segments in an hour. that won't always work
    // if there are more than one event in that segment but maybe it's a good place to start

    $min_hr = $daily_start_hour*1;
    $max_hr = $daily_end_hour*1;
    $time_append='00';

    $table_data='<table id="daily_schedule">
    <tr class="daily daily_group">
    <th class="daily_header spacer" >&nbsp;</th>
    <th class="daily_header spacer" >&nbsp;</th>
    <th class="daily_header daily_event"><span class="phrase">event</span></th>
    <th class="daily_header daily_location"><span class="phrase">location</span></th>
    <th class="daily_header daily_leader"><span class="phrase">instructor</span></th>
    <th class="daily_header daily_students"><span class="phrase">students</span></th>
    <th class="daily_header daily_week"><span class="phrase">week</span></th>
    <th class="daily_header daily_notes"><span class="phrase">notes</span></th>
    </tr>';
    while($min_hr < $max_hr) {
        $constraint_counter = 0;
        $hr_show=$min_hr.$time_append;
        $hr_show = str_pad($hr_show, 4, "0", STR_PAD_LEFT);
        $standard_hour= date("g", strtotime("$day $hr_show"));
        $twentyfour_hour = date("G", strtotime("$day $hr_show"));
        // echo "$hr_show (made up of $min_hr and $time_append is $standard_hour\n";

        /* OK listen you are so fucked. You need to know the number of classes at each datetime so you can make sure you have enough rows to hold them. That's one thing and maybe the easy thing.
         *
         * Once you know the # of rows you also need to break out that one array to make sure you're getting all the events. As it stands now if you have the same event at the same time it doesn't show on the printable schedule because you don't iterate through the array. you just extract it (search for the word "regret" to see what I mean) and print the first one that comes along 
         *
         * compare 1518 of includes/bak.rpc to line 1526 or so of this file
         *
         * you have a decent count of each row, but it's in the wrong place. the count happens after you've already declared the # of rows you need to span, so you need to change the logic.
         *
         * of course you're avoiding the fact that your logic is embedded thoroughly in your presentation layer and this whole thing needs rewriting.
         *
         * OK you need to declare 2 sets of rowspans. One is the hourly rowspan and one is the rowspan for those segments of an hour that contain classes.
         *
         * OK so neat you have the rowspans right now for the internal stuff but you don't have a way to distinguish anymore between privates and groups
         *
         * yeah basically that whole key is worthless. man I really should rewrite this. the key is worthless because when I thread together the different classes by time I lose whether something is private or not
         *
         * Nah it's not as bad as I thought. I thought I did array['private']['other keys']; but really I do array['other keys']['private']['one more key for good measure']
         *
         * funny thing is I have a type which is private or group so I don't even need that key.
         *
         *
         * yet when I remove it all hell breaks loose
         *
         * OK well I have it looking much better but when you filter by instructor we lose rowspans
         * */

        $tryme = 0;
        while($constraint_counter < 60 ) {
            $ctr=str_pad($constraint_counter, 2, "0", STR_PAD_LEFT);
            // echo "ctr for $standard_hour is $ctr <br>";
            $tryme += $rowcount[$twentyfour_hour][$ctr];
            $constraint_counter = $constraint_counter + $daily_schedule_time_constraint;
        }
        $constraint_counter = 0;
        $hr_has_classes=false;
        $newrowspan=$rowspan;
        $hr_row="<!-- start standard_hour tr --><tr>
            <td class='hour_display' rowspan=$tryme>$standard_hour </td>";
        $tr='';
        while($constraint_counter < 60 ) {
            $ctr=str_pad($constraint_counter, 2, "0", STR_PAD_LEFT);
            if(is_array($day_arr[$min_hr][$ctr])) {
                // debug // echo 'yah array for day-arr '.$min_hr.' : '.$ctr.'<br>';
                if($rowcount[$standard_hour][$ctr] == '') {
                    /*
                    echo "that thing you worry about is happening <br>";
                    echo "but rowcount is $rowcount and its count is ";
                    echo count($rowcount);
                    echo "don't you feel better now? this is for standard: $standard_hour and ctr: $ctr<br>";
                    echo '<pre>';
                    print_r($rowcount);
                    echo '</pre>';
                    echo "there that as the print_r <br>";
                    $rowcount[$standard_hour][$ctr] = 1;
                     */
                } else {
                    // debug // echo "it is not blank for $standard_hour $ctr<br>";
                }
                $event_card = $day_arr[$min_hr][$ctr];
                // $n_rowspan[$twentyfour_hour][$minutes] += count($day_arr[$twentyfour_hour][$minutes]['private'])
                /*
                echo '<pre>';
                print_r($event_card);
                echo '</pre>';
                 */
                $da_count = key($event_card);
                // $hr_row .= "<td rowspan=".$rowcount[$standard_hour][$ctr]." class='event_time'>$standard_hour:$ctr </td><!-- where it ends -->";
                $hr_row .= "<td rowspan=".$rowcount[$twentyfour_hour][$ctr]." class='event_time'>$standard_hour:$ctr </td><!-- where it ends -->";
                $hr_has_classes=true;
                // $results = print_r($day_arr[$min_hr][$constraint_counter], true);
                // echo '<pre>';print_r($event_card); echo '</pre>';
                /* 
                 * scratch 
                echo '<pre>';
                foreach($event_card as $key => $val) {
                    print_r($val);
                }
                echo '<pre>';
                die();
                 */




                foreach($event_card as $key => $val) {
                    foreach($val as $sub_key => $data) {
                        if($key != $prev_key) {
                            $extra_style=' margin-top: 16px; ';
                        } else {
                            $table_header='';
                            $extra_style='';
                        }
                        // $sub_key=key($val);
                        $data = $val[$sub_key];
                        // debug // echo '<pre>'; print_r($data); echo '</pre>';
                        extract($data); // will I regret that?
                        /*
                        $login_email = $data['login_email'];
                        $et_name     = $data['et_name'];
                        $et_desc     = $data['et_desc'];
                        $location     = $data['location'];
                        $leader     = $data['leader'];
                        $students     = $data['students'];
                         */
                        // I did
                        // and I do again
                        // I should date those to better record my infamy
                        // let's add 2015-09-05 to that list of dates then
                        // can I ... add the same date twice?
                        // let's just say the regret goes waaayyy beond that extract()
                        $age_name=' months ';
                        $plural = '';
                        if($age >= $age_month_cutoff) {
                            // echo "age is $age and age_month_cutoff is $age_month_cutoff for $fullname <br>";
                            // $age=floor($age_month_cutoff/12);
                            $age=floor($age/12);
                            if($age > 1) {
                                $plural='s';
                            }
                            $age_name = 'year'.$plural;
                        }
                        if($key == 'group' ) {
                            $group_emails.="$login_email,";
                            $results = $tr.'<!-- dynamic tr -->
            <td class="daily_box daily_event">'.$et_name.$et_desc.'</td>
            <td class="daily_box daily_location">'.$location.'</td>
            <td class="daily_box daily_leader">'.$leader.'</td>
            <td class="daily_box daily_students">'.$students.'</td>
            <td class="daily_box daily_week">N/A</td>
            <td class="daily_box daily_notes">'.$edt_meta.'</td><!-- right after edt_meta -->
            </tr><!-- row tr end -->
                                ';
                        }
                        if($key == 'private' ) {
                            $private_emails.="$login_email,";
                            $results = $tr.'<!-- end dynamic tr actually it looks like begin dynamic tr to me -->
            <td class="daily_box daily_private daily_event">Private Class</td>
            <td class="daily_box daily_private daily_location">'.$location.'</td>
            <td class="daily_box daily_private daily_leader">'.$leader.'</td>
            <td class="daily_box daily_private daily_pname"><div class="daily_schedule_participant">'.$fullname.', '.$age.' '.$age_name.'</div>
            <div class="daily_schedule_login">
                <a href="mailto:'.$login_email.'">'.$login_fullname.'<a/>
                <br>h: '.$phone_h.'
                <br>w: '.$phone_w.'
                <br>c: '.$phone_c.'
            </div>
            </td>
            <td class="daily_box daily_private daily_week">'.$week.' </td>
            <td class="daily_box daily_private ped_meta" id="ped_meta_'.$ped_id.'">
                <span id="ped_meta_show_'.$ped_id.'">'.nl2br($ped_meta).'</span>
            </td></tr><!-- right after ped_meta -->';
                        }
                        $hr_row.=$results;
                        $prev_key = $key;
                    }
                }

            } else {
                $newrowspan--;
            }
            $constraint_counter = $constraint_counter + $daily_schedule_time_constraint;
            $count_cval = 0;
        }
            $tr='<tr><!-- dynamic tr populated -->';

        $min_hr++;
        if($hr_has_classes == true) {
            // laugh if you will future-troy
            // I am NOT LAUGHING.
            $pattern="/rowspan_hour\=\d+/";
            $replace="rowspan=$newrowspan";
            $hr_row = preg_replace ($pattern, $replace, $hr_row);
            $table_data.=$hr_row;
        }
    }
    // a little late for that
    require('templates/printable_daily_schedule.php');
}

if(ca($action) == 'update_ped_meta') {
    // update meta info for a private event
    $s_private = new S_private_event;
    $s_private -> db = $db;

    $ped_id = $_REQUEST['ped_meta_id'];
    $ped_meta = $_REQUEST['ped_meta'];

    $result = $s_private -> update_private_event_daytime_meta($ped_id, $ped_meta);
    $status=array('status' => $result, 'ped_meta'=>nl2br($ped_meta));
    echo json_encode($status);
}

if(ca($action) == 'private_event_card') {
    $s_private = new S_private_event;
    $s_private -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    // you need start and end for 2 out of 3 use cases
    $start = $_REQUEST['start'];
    $end   = $_REQUEST['end'];

    $card_type=$_REQUEST['card_type'];

    // you will want to change this because pe_dates might always be sending a value
    // even when you want to use a different set of dates.
    // past-troy ... stop saying bizarre things
    if(strlen($_REQUEST['pe_dates']) > 0) {
        $pe_dates = $_REQUEST['pe_dates'];
        $pd_arr = explode(':', $pe_dates);
        $start = $pd_arr[0];
        $end   = $pd_arr[1];
    }

    $edit=false;
    $inc_card=false;

    switch($card_type) {
    case "block":
        // requires a start/end and 
        $isblock=true;
        $inc_card=true;
        $inc_private_card_picker=true;
        $inc_block_picker=false;
        $extra_class=' block ';
        $edit  = true;
        // only look at pe_dates in this way if your type is block
        // quit saying bizarre stuff past-troy
        if(strlen($_REQUEST['pe_dates']) > 0) {
            $pe_dates = $_REQUEST['pe_dates'];
            $pd_arr = explode(':', $pe_dates);
            $start = $pd_arr[0];
            $end   = $pd_arr[1];
        }
        $pevents_res = $s_private -> get_private_events_in_date_range($start, $end);

        $pecard_title='<h3>'.$start.' to '.$end.'</h3>';
        break;
    case "week":
        $inc_card=true;
        $extra_class=' week ';
        $weeks=$_REQUEST['pe_dates'];
        $pe_dates = $weeks;
        $pd_arr = explode(':', $pe_dates);
        $start = $pd_arr[0];
        $end   = $pd_arr[1];
        $show_template=true;
        $pecard_title='<h3>Week of '.$start.' - '.$end.'</h3>';
        $pevents_res = $s_private -> get_private_events_from_start($start, 7); // derive 7 by subtracting start from end
        break;
    case "day":
        $inc_card=true;
        $extra_class=' day ';
        $edit  = true;
        break;
    default:
        $inc_card=true;
        $settingshit=true;
        $inc_block_picker=true;
    }

    // set up the menu of blocks to choose from
    $pe_res = $s_private -> get_unique_current_events();
    while($pe_arr=$pe_res -> fetch_assoc()) {
        // this is to get only unique starting/ending dates, so it's expected that
        // some values get overwritten by the array keys
        // stop saying bizarre stuff past-troy
        $start_end =          $pe_arr['start_end'];
        $menu_start =         $pe_arr['start'];
        $menu_end =           $pe_arr['end'];
        $option[$start_end] = $menu_start.' to '.$menu_end;
    }

    if(is_array($option)) {
        foreach($option as $var => $val) {
            $pe_date_items.='<option value="'.$var.'">'.$val.'</option>';
        }
    }

    if($edit==true) {
        $extra_class.=' editable ';
    }

    if(strlen($start) > 0 && strlen($end) > 0) {
        $show_template=true;
        while($pa = $pevents_res -> fetch_assoc()) {
            $id                     = $pa['id'];
            // debug // echo "id is $id <br>";
            $participant_id         = $pa['participant_id'];
            $participant_dob        = $pa['participant_dob'];
            $participant_age_months = $pa['participant_age_months'];
            $num_days               = $pa['num_days']; // because you select by block this number is always the same. that's why you rely on it outside the loop.
            // either you are saying bizarre stuff, or you are doing bizarre stuff, or both past-troy
            $num_weeks              = ceil($num_days/7);
            
            if($participant_age_months > 36) {
                $participant_age = floor($participant_age_months/3).' '.$sl->gp('years');
            } else {
                if($participant_age_months > 0) {
                    $participant_age = $participant_age_months.' '.$sl->gp('months');
                } else {
                    $participant_age = '';
                }
            }
            if($participant_id !=0) {
                $login_res       = $s_participant -> get_logins_by_participant($participant_id);
                $login_arr       = $login_res -> fetch_assoc();
                $login_fullname  = $login_arr['fname'].' '.$login_arr['lname'];
            } else {
                $login_fullname = 'None';
            }
            $priv_event[$id]=$pa;
            $priv_event[$id]['participant_age']=$participant_age;
            $priv_event[$id]['login_fullname']=$login_fullname;
            $priv_event[$id]['week']=$sl -> gp('Choose from the menu above');
        }

        // we are the mighty ferengi pecard
        $pecard_picker = '<select id="pecard_picker" name="pecard_picker">
            <option value="">Pick a week</option>';
        $start_obj = date_create($start);
        $end_date_obj = date_create($end);
        for($i=1; $i<=$num_weeks; $i++) {
            /* sorry future-troy */
            if($i > 1) {
                $week_days = 7;
            } else {
                $week_days = 0;
            }
            $date_string = $week_days.' days';

            // end_date_string always adds 7 days
            $end_date_string = '7 days';
            date_add($start_obj, date_interval_create_from_date_string ($date_string));
            $new_start = date_format($start_obj, 'Y-m-d');

            $end_obj = date_create($new_start);
            date_add($end_obj, date_interval_create_from_date_string ($end_date_string));
            // make sure your end date doesn't extend past the end of the block of classes
            $interval = date_diff($end_obj, $end_date_obj);
            if($interval->format('%R')=='-') {
                $new_end = $end;
            } else {
                $new_end = date_format($end_obj, 'Y-m-d');
            }
            $new_pe_date=$new_start.':'.$new_end;
            // debug // $pecard_picker.='<option value="'.$pe_dates.'">'.$i.' | '.$new_start.' | '.$new_end.'</option>';
            $pecard_picker.='<option value="'.$new_pe_date.'">Week '.$i." ($new_start to $new_end)</option>";
        }
        $pecard_picker.='</select>';
    }

    if($inc_block_picker == true) {
        require('templates/private_block_picker.php');
    }
    // I stick the divs out here so the JS can find them
    // and NO future-troy this is NOT bizarre
    // https://www.youtube.com/watch?v=ROtgYNmfMxc
    if($inc_card == true) {
        if($inc_private_card_picker == true) {
            require('templates/private_card_picker.php');
        }
        echo '<div id="private_event_card">';
        echo $pecard_title; 
        // build the select menu for selecting private event blocks
        require ('templates/private_event_card.php');
        echo '</div>';
    }
}

if(ca($action) == 'get_private_event') {
    $private_event_id=$_REQUEST['private_event_id'];
    $s_class = new S_class(false);
    $s_class -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_private = new S_private_event;
    $s_private -> db = $db;

    $ped_arr = array();

    $event_result = $s_private -> get_private_event($private_event_id);
    $arr=$event_result -> fetch_assoc();
    $private_start         = $arr['start'];
    $private_end           = $arr['end'];
    $duration              = $arr['duration'];
    $location_id           = $arr['location_id'];
    $leader_id             = $arr['leader_id'];
    $participant_id        = $arr['participant_id'];
    $status_id             = $arr['status_id'];
    $private_event_time    = date('g:i', $_REQUEST['date_time']);
    $private_event_daytime = date('Y-m-d G:i:00', $_REQUEST['date_time']);
    $p_result = $s_participant -> get_participant($participant_id);
    $part_row = $p_result->fetch_assoc();

    // 2013-11-16 07:50:00


    // get private event daytime info; info for JUUUST THIS EVENT.
    // you could put an attendance flag here by the way and enable
    // a quick way to skip classes

    $ped_res = $s_private -> ped_by_pe($private_event_id, $private_event_daytime);
    if($ped_res) {
        $ped_arr = $ped_res -> fetch_assoc();
        $ped_meta = $ped_arr['ped_meta'];
        $ped_meta_id = $ped_arr['id'];
    }

    // OK YOU SHOW THE PRIVATE EVENT NOW BU TYOU NEED TO MAKE IT EDITABLE.
    // there's a bunch of unused code for this in show_schedule that you
    // can repurpose. Just need to add a button for edit note in the modal (it's
    // already couched in an 'if' for existing private events for you),
    // and then add the click event to show_schedule. search for the word 'clever' 
    // to see what you did to yourself there.

    // I wonder if I should make get_participant return 
    // a generic fname/lname if participant_id is 0.
    // I think you have bigger problems in that instance right past troy?

    $private_participant=$part_row['fname'].' '.$part_row['lname'];


    $location_result = $s_class -> get_all_locations();
    $location_list = result_as_html_list(new html_Render(), $location_result, 'id', 'location', $location_id);

    $leader_result = $s_class -> get_all_leaders();
    $leader_list = result_as_html_list(new html_Render(), $leader_result, 'id', 'leader', $leader_id);

    require('templates/myModal.php');
}

if(ca($action) == 'add_private_event') {
    $s_private = new S_private_event;
    $s_private -> db = $db;

    $start = $_REQUEST['private_start'];
    $end = $_REQUEST['private_end'];
    $duration = $_REQUEST['private_duration'];
    $location_id = $_REQUEST['private_location_id'];
    $leader_id = $_REQUEST['private_leader_id'];
    $participant_id = $_REQUEST['private_participant_id'];
    $date_time = $_REQUEST['date_time']; // used to get the hour
    $ped_meta = $_REQUEST['ped_meta']; // notes applied to all private events
    $private_event_time = date('H:i', $date_time);
    $selected_days = $_REQUEST['selected_days'];
    if($participant_id == '') {
        $participant_id=0;
    }

    // any cleaning

    $s_private -> add_private_event ($start, $end, $selected_days, $private_event_time, $duration, $location_id, $leader_id, $participant_id, $ped_meta);
}

if(ca($action) == 'delete_private_event') {
    $private_event_id=$_REQUEST['private_event_id'];

    $s_private = new S_private_event;
    $s_private -> db = $db;

    $result = $s_private -> delete_private_event($private_event_id);
}

if(ca($action) == 'delete_private_event_daytime') {
    // not in service yet
    $private_event_daytime_id=$_REQUEST['private_event_daytime_id'];

    $s_private = new S_private_event_daytime;
    $s_private -> db = $db;

    $result = $s_private -> delete_private_event_daytime($private_event_daytime_id);
}

if(ca($action) == 'edit_private_event') {
    $s_private = new S_private_event;
    $s_private -> db = $db;

    // ok this is going to be a doozie
    // a yankie doodle doozie
}

if(ca($action) == 'get_event') {
    // you need to populate all your menus. You'll regret this.
    $s_class = new S_class(false);
    $s_class -> db = $db;

    $s_event = new S_event;
    $s_event -> db = $db;

    $event_id=$_REQUEST['event_id'];
    $date_time=$_REQUEST['date_time'];
    $event_time= date('g:i A', $date_time);
    $event_result = $s_event -> get_event($event_id);
    $arr=$event_result -> fetch_assoc();
    $edt_id = $_REQUEST['edt_id'];
    if(is_int($edt_id * 1)) {
        // we have an event daytime. get all its data
        $edt_result = $s_event -> get_event_daytime($edt_id);
        $edt_data   = $edt_result ->fetch_assoc();
        // debug // print_r($edt_data);
        // all I care about for now is the metadata
        $edt_meta = $edt_data['edt_meta'];
    } else {
echo "well it isn't an int sothere <br>";
}
    extract($arr);
    // extract $arr and populate your defaults with its results

    $location_result = $s_class -> get_all_locations();
    $location_list = result_as_html_list(new html_Render(), $location_result, 'id', 'location', $location_id);

    $leader_result = $s_class -> get_all_leaders();
    $leader_list = result_as_html_list(new html_Render(), $leader_result, 'id', 'leader', $leader_id);

    $event_result = $s_event -> get_all_event_types();
    $event_list = result_as_html_list(new html_Render(), $event_result, 'id', 'event', $et_id);

    require('templates/myModal.php');

}

if(ca($action) == 'get_class_nav') {
    $s_event = new S_event;
    $s_event -> db = $db;

    $s_class = new S_class(false);
    $s_class -> db = $db;

    // get the class type and class menus
    $event_result = $s_event -> get_all_event_types();
    $event_list = result_as_html_list(new html_Render(), $event_result, 'id', 'event', $et_id);

    $class_result = $result=$s_class->get_all_classes();
    $class_list = result_as_html_list(new html_Render(), $class_result, 'id', 'name', $default_class_id);

    foreach($day_list as $var => $val) {
        $day_list.='<option value="'.$var.'">  '.$val.'</option>';
    }

    $location_res = $s_class -> get_all_locations();
    $location_arr = result_as_array(new serialized_Render(), $location_res, 'location_id');
    $selected = '';
    // I am breaking this to fix the radio buttons
    // $location_boxes = array_as_html_radio_buttons(new html_Render(), $location_arr, 'location_id', 'location', '', $selected);
    $field_data=array();
    $field_data['name_name']='name_name';
    $field_data['id_name']='location_id';
    $field_data['label_name']='location';
    $field_data['value_key']='id';
    $field_data['value_name']='id';
    $location_boxes = new_as_html_radio_buttons(new html_Render(), $field_data, $location_arr, 'location_id');
    // echo $location_boxes;
    require('templates/class_select_form.php');

    die();
}

if(ca($action) == 'view_classes_login_welcome') {
    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $s_event = new S_event;
    $s_event -> db = $db;

    // get all participants belonging to the login
    if(is_logged_in()===true) {
        echo "<h3>".$sl->gp('Welcome'). " $u_login_fname</h3><!-- ".__LINE__."-->";
        $show_billing=false;
    if(is_array($login_part_arr) && count($login_part_arr) > 0) {
        // I think this will be ok
        // I think not
        // debug // echo '<pre>'; print_r($login_part_arr);
        foreach($login_part_arr as $participant_id => $participant_array) {
            // I want to make sure we need to show billing_line_item
            // plus this is the first step toward refactoring it.
            $pb_res = $s_billing->event_billing_by_participant($participant_id);
            if($pb_res -> num_rows > 0)  {
                $show_billing=true;
            }
        }
    }
	// debug // echo '<pre>';print_r($login_part_arr);echo '</pre>';
        if($show_billing == true) {
            require('templates/billing_line_item.php');
        }
        echo '<p><a href="#" id="logout_button">'.$sl->gp('Log out').'</a></p>';
    } else {
        require('./templates/login_form.php'); ?>
        <div id="request_password">
            <?php require('./templates/request_login.php'); ?>
        </div>
    <?php
    }
}

if(ca($action) == 'student_get_class_nav') {
    // copy to dev_student_get_class_nav when the time comes
    $s_event = new S_event;
    $s_event -> db = $db;

    $s_class = new S_class(false);
    $s_class -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $s_class->class_id = $default_class_id; // I should put this behind a function 
    $ecid=$default_class_id-1; // (extra class id so that parents can search the previous session)
    $fcid=$default_class_id+1; // (extra class id so that parents can search the next session)
    $class_id_arr=array($fcid, $default_class_id, $ecid);
    // so that when I have it in a proper config it's easier to get.
    // it would require a proper config to do that though hah.

    $leader_res = $s_class -> get_all_leaders(); // get all the leaders
    $leader_arr=result_as_array(new serialized_Render(), $leader_res, 'id');
    // you want all the leaders selected by default
    $selected = array();
    /*
    foreach($leader_arr as $var => $val) {
        $selected[]=$var;
    }
    */
    // $leader_boxes = array_as_html_checkboxes(new html_Render(), $leader_arr, 'leader', $selected, 'leader_id');
    // echo '<pre>';print_r($leader_arr);echo '</pre>';
    $field_data['name_name']='leader_id';
    $field_data['id_name']='leader_id';
    $field_data['label_name']='leader';
    $field_data['value_key']='id';
    $field_data['value_name']  = 'id';
    // $field_data['input_class']=' ui-helper-hidden-accessible ';
    $leader_boxes = new_as_html_check_boxes(new html_Render(), $field_data, $leader_arr, array());
    // debug // print_r($leader_boxes);
    // get the class type and class menus
    $event_result = $s_event -> get_all_event_types();
    $event_list = result_as_html_list(new html_Render(), $event_result, 'id', 'event', $et_id);

    // $class_result = $result=$s_class->get_class();
    $class_result = $s_class->get_class_set($class_id_arr);
    $class_list = result_as_html_list(new html_Render(), $class_result, 'id', 'name', $default_class_id);
    foreach($day_list as $var => $val) {
        $day_list.='<option value="'.$var.'">  '.$val.'</option>';
    }

    $log_in_tab_title='Make a Payment';
    if(is_logged_in()==true) {
        $log_in_tab_title='Manage';
    }
    require('templates/user_student_nav.php');
    // require('templates/class_select_form.php');
    die();
}

if(ca($action) == 'get_events') {
    $show_config = true;
    // $se = skip empties (don't show empty events)
    $se = false;
    $re = $_SERVER["HTTP_REFERER"];
    $file=basename($re);
    // I'm a bad, bad man
    // this should not be needed anymore
    // but since I'm still a bad man I'm leaving it
    if($file == 'view_classes.php') {
        $se=false;
        $show_config=false;
    }
    if($file == 'show_classes.php') {
        $show_config=true;
    }
    $s_class = new S_class($_REQUEST['class_id']);
    $s_class -> db = $db;

    $s_event = new S_event;
    $s_event -> db = $db;

    $s_leader = new S_leader;
    $s_leader -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $s_event -> class_id=$_REQUEST['class_id'];

    $s_result = $s_class->get_class();
    $class_row = $s_result->fetch_assoc();
    $class_name = $class_row['name'];
    if($_REQUEST['et_id']*1 > 0) {
        $e_res=$s_event->get_event_by_type($_REQUEST['et_id']);
    } elseif($_REQUEST['day'] *1 > 0) {
        $class_id=$_REQUEST['class_id'];
        $day=$_REQUEST['day'];
        $day_name=$day_list[$day];
        $er=$s_class -> get_first_event_date_by_day($day_name);
        $e_arr = $er->fetch_assoc();
        $start_date = $e_arr['start_date'];
        $sd_arr=explode(' ', $start_date);
        $sd_arr=explode(' ', $start_date);
        $day=$sd_arr[0];
        if($day == '') {
            $e_res ='';
        } else {
            $e_res = $s_class -> get_all_events_by_day($day);
        }
    }

    if(!is_object($e_res)) {
        echo '<h2>'.$sl -> gp('No events match what we are looking for.').'</h2>';
        die();
    }
    $event_arr=result_as_array(new serialized_Render(), $e_res, 'event_id');
    if(!is_array($event_arr)) {
        echo '<h2>'.$sl -> gp('No events match what we are looking for.').'</h2>';
        die();
    }

    echo '<h3 class="class_name_print">'.$class_name.'<span id="location_header"></span></h3>';
    /* I should store all "filterable" items in an array so that I can automate these titles more */
    /*
    echo '<h3 class="filter_pool_name"></h3>';
    echo '<h3 class="filter_instructor_name"></h3>';
     */
    // get all statuses so you can just return the array
    $stat_res=$s_participant->get_all_status();
    while($sa = $stat_res->fetch_assoc()) {
        extract($sa);
        $status_arr[$id]=$status;
    }
    // get all the participants in every event of this class
    
    $show_event = true; // use this to show or hide any event for whatever reason
    // work into the perm system
    // $show_config = false;
    $ea_c = 0;
    $page_break_num = 16; // make this configurable 
    foreach($event_arr as $event_data) {
        // this needs to be enabled via JavaScript because you apply filtering via JavaScript that affects the number of events on a page.
        /*
        if($ea_c == $page_break_num) {
            echo '<div style = "display: block; page-break-before: always; color: #ffffff">---</div>';
            $ea_c = 0;
        }
         */
        extract($event_data);
        $l_res=$s_leader -> get_leader_by_id($leader_id);
        $l_arr = $l_res ->fetch_assoc();
        // $event_leader = $l_arr['fname']. ' '.$l_arr['lname'];
        $event_leader = $l_arr['fname'];
        $ep_res = $s_participant -> participants_in_event($event_id);
        $ep_arr=result_as_array(new serialized_Render(), $ep_res, 'participant_id');

        $et_arr=result_as_array(new serialized_Render(), $s_event->get_event_type_by_id($et_id), 'id');
        $et_name=$et_arr[$et_id]['et_name'];
        $et_desc=$et_arr[$et_id]['et_desc'];
        if(count($ep_arr) == 0) {
            if($se == true) {
                $show_event = false;
            } else {
                $show_event = true;
            }
        } else {
            $show_event = true;
        }

        if(is_array($ep_arr)) {
            foreach($ep_arr as $ek => $es) {
                // debug // print_r($es);
                extract($es); // blah
                $active_participant[$ek]['fname']                = $fname;
                $active_participant[$ek]['lname']                = $lname;
                $active_participant[$ek]['status_id']            = $status_id;
                $active_participant[$ek]['event_participant_id'] = $event_participant_id;
                $active_participant[$ek]['participant_id']       = $participant_id;
                $active_participant[$ek]['pay_status']           = $s_billing -> is_ep_paid ($event_participant_id);
                /* pay_status takes precedence over event_participant_status' fully_paid because that really isn't a 
                 * status that belongs in event_participant_status to begin with */
            }
        }

        if(is_array($active_participant[$ek])) {
            $ap=extract($active_participant[$ek]);
        } 
        if($show_event == true) {
            $i=0; // counter against the number of available participant slots in an event
            require('templates/event_card.php');
        }
        $ea_c++; // increment the event array counter
    }
}

if(ca($action) == 'get_event_card') {
    $s_event = new S_event;
    $s_event -> db = $db;

    $s_leader = new S_leader;
    $s_leader -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $event_id=$_REQUEST['event_id'];

    // get all statuses so you can just return the array
    $stat_res=$s_participant->get_all_status();
    // I feel dirty
    while($sa = $stat_res->fetch_assoc()) {
        extract($sa);
        $status_arr[$id]=$status;
    }

    $event_res = $s_event -> better_get_event($event_id);
    $event_data = $event_res->fetch_assoc();
    extract($event_data);
    $event_leader = $fname;

    $ep_res = $s_participant -> participants_in_event($event_id);
    $ep_arr=result_as_array(new serialized_Render(), $ep_res, 'participant_id');
    if(is_array($ep_arr)) {
        foreach($ep_arr as $ek => $es) {
            // debug // echo "key is $ek and val is $es <br>";
            // debug // print_r($es);
            extract($es);
            // debug // echo "so ek is $ek and fname is $fname <br>";
            $active_participant[$ek]['fname']                = $fname;
            $active_participant[$ek]['lname']                = $lname;
            $active_participant[$ek]['status_id']            = $status_id;
            $active_participant[$ek]['event_participant_id'] = $event_participant_id;
            $active_participant[$ek]['participant_id']       = $participant_id;
            $active_participant[$ek]['pay_status']           = $s_billing -> is_ep_paid ($event_participant_id);
            // $active_participants[]=$ep_subarr_key;
        }
    }
    if(is_array($active_participant[$ek])) {
        $ap=extract($active_participant[$ek]);
    }


    $i=0; // counter against the number of available events
    // $reloaded='reloaded';
    // moved to the template // echo '<div id="event_holder_'.$event_id.'">';
    $show_config = true; // PERMISSIONS
    require('templates/event_card.php');
    // moved to the template // echo '</div>';
}

if(ca($action) == 'download_class_logins') {
    $class_id = $_GET['class_id'];
    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $ap_res=$s_participant -> participants_in_class($class_id);
    $ap=result_as_array(new serialized_Render(), $ap_res, 'participant_id');
    $logins_return = array();
    foreach($ap as $var => $val) {
        // print_r( $val);
        extract($val);
        $part_res = $s_participant -> get_logins_by_participant($participant_id);
        $part_login_arr=result_as_array(new serialized_Render(), $part_res, 'id');
        foreach($part_login_arr as $login_arr) {
            extract($login_arr);
            $name = '"'.$fname.'","'.$lname.'"'."\n";
            $email=trim($email);
            if(strlen($email) > 0) {
                if(!in_array($name, $logins_return)) {
                    $logins_return[] = $name;
                }
            }
        }
    }
    foreach($logins_return as $log_names) {
        echo $log_names;
    }
}


if(ca($action) == 'download_class_students') {
    // $action = (empty($_POST['action'])) ? 'default' : $_POST['action'];
    $class_id=$_REQUEST['class_id'];
    $status_id = (empty($_REQUEST['status_id'])) ? '' : $_REQUEST['status_id'];
    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_event = new S_event;
    $s_event -> db = $db;


    if($class_id*1 > 0) {
        $ap_res=$s_participant -> participants_in_class($class_id, $status_id);
        $ap=result_as_array(new serialized_Render(), $ap_res, 'participant_id');
    } else {
        die('specifiy a class');
    }
    foreach($ap as $var => $val) {
        // print_r($val);
        extract($val);
        $result = $s_participant -> get_participant($participant_id);
        $part_row = $result->fetch_assoc();
        extract($part_row);
        // now time to shoot a squirrel with a howitzer
        $event_res = $s_event -> better_get_event($event_id);
        $event_data = $event_res->fetch_assoc();
        $et_name = $event_data['et_name'];
        echo $newline.$fname.",".$lname.",".$et_name;
        $newline="\n";
    }
}

if(ca($action) == 'get_class_students') {
    $class_id=$_REQUEST['class_id'];
    $s_participant = new S_participant;
    $s_participant -> db = $db;
    $s_login = new S_login;
    $s_login -> db = $db;


    // get an array of all active students

    if($class_id*1 > 0) {
        $ap_res=$s_participant -> participants_in_class($class_id);
        $ap=result_as_array(new serialized_Render(), $ap_res, 'participant_id');
    } else {
        $ap_res=$s_participant -> get_all_participants('');
        $ap=result_as_array(new serialized_Render(), $ap_res, 'participant_id');
    }

    $partres=$s_participant->get_all_participants('fname');
    if(is_object($partres)) {
        $partarr=result_as_array(new serialized_Render(), $partres, 'id');
        $current_letter='A';
        $substrtest=1;
        $end_div='</div>';
        foreach($partarr as $pval) {
            $id=$pval['id'];
            // check to see if they are not already placed
                $test_letter = strtoupper(substr($pval['fname'], 0, 1));
                // $li.= "test is $test_letter and cur is $current_letter <br>";
                if ( $test_letter != strtoupper($current_letter)) {
                    $current_letter =  strtoupper(substr($pval['fname'], 0, $substrtest));
                    $h="</div>\n\t<h3 style='width: 250px;'>$current_letter</h3><div style='overflow: visible; width: 250px;' class='student_letter_container'>\t";
                } else {
                    $h='';
                    $end_div='';
                }
                $li.="\n".$h.'<p class=" participant draggable" id="'.$pval['id'].'">'.$pval['fname'].' '.$pval['lname'].'</p>';
        }
        $list= '<div style="width: 250px;" id="studentList"><h3>A</h3><div style="overflow: visible;">'.$li.'</div>';
    } else {
        echo $sl->gp('Your search found no students.');
    }
    echo $list;
}

if(ca($action) == 'insert_event_participant') {
    // something has changed and s_class needs a class id defined for it now. that happens down below.
    $s_event = new S_event;
    $s_event -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $participant_id=$_REQUEST['participant_id'];
    // clean up a bad variable
    $e_arr=explode('_',$_REQUEST['event_id']);
    $event_id=$e_arr[1];

    $event_line_item_id=1; // going to have to do something about this

    $event_res = $s_event -> better_get_event($event_id);
    $event_data = $event_res->fetch_assoc();
    // an attempt to but a bandaid on the bug where empty event line items are coming in
    $event_line_item   = $event_data['et_name'];
    // $s_class->class_id = $event_data['class_id'];

    $s_class = new S_class($event_data['class_id']);
    $s_class -> db = $db;

    $class_res   = $s_class->get_class();
    $class_row   = $class_res->fetch_assoc();
    $class_name  = $class_row['name'];

    $event_line_item = $class_name.', '.$event_data['et_name'];

    if(strlen($event_line_item)==0) {
        $event_line_item="event: $event_id, participant: $participant_id";
    }

    $amount_due=$price['group']; // going to have to do something about this too

    $ep_id=$s_participant -> insert_event_participant($event_id, $participant_id);
    $tf=$s_billing -> insert_event_billing ($event_line_item_id, $event_line_item, $participant_id, $ep_id, $amount_due, 0, '');
    if($tf==true) {
        $json_arr=array("status"=>"success","event_id"=>$event_id);
        $json=json_encode($json_arr);
        echo $json;
    }
}

if(ca($action) == 'insert_participant') {
    // insert a participant and check for a login to add the participant to login_participant as well
    /* fuck all THIS
    $fname=mysqli_real_escape_string($db, $_REQUEST['fname']);
    $lname=mysqli_real_escape_string($db, $_REQUEST['lname']);
     */
    $fname    = clean_string($_REQUEST['fname']);
    $lname    = clean_string($_REQUEST['lname']);
    $dob      = clean_string($_REQUEST['dob']);
    $reg_id   = $_REQUEST['reg_id'];

    // for registration form
    if($reg_id=='') {
        $reg_id = $default_reg_id;
    }

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db;

    $s_reg = new S_reg;
    $s_reg -> db = $db; # god i need to fix this

    // find out if we have a participant already
    $part_res = $s_participant -> get_participants_by_login($login_id);
    if($part_res -> num_rows != 0) {
        $amount_due=$registration['sibling'];
    } else {
        $amount_due=$registration['orig'];
    }

    $participant_id=$s_participant->new_insert_participant($fname, $lname, $dob);
    // debug // echo "id is $participant_id <br>";
    // now you need to insert the participant/login relationship
    if($participant_id * 1 > 0 && $_REQUEST['login_id']*1 > 0) {
        // insert the relationship
        $login_id=$_REQUEST['login_id'];
        $login_participant_id=$s_participant->insert_login_participant($login_id, $participant_id);
        // see if we need to generate a registration form and waiver for this login and participant

        // check for login's registration
        $res = $s_login_reg -> get_reg_login ($login_id, $reg_id);
        if($res->num_rows == 0) {
            $s_login_reg -> reg_login_insert($login_id, $reg_id);
            $reg_login_id=$db->insert_id;
            $reg_status = 1; // since this is a new record this is not yet begun
        } else {
            $s_login_arr = $res -> fetch_assoc();
            $reg_login_id = $s_login_arr['id'];
            $reg_status = 3; // since they finished an existing registration this is now in progress.
        }

        $w_res = $s_participant_reg -> waiver_by_participant($participant_id, $reg_id);
        $w_num = $w_res->num_rows;
        if($w_num == 0) {
            $s_participant_reg -> insert_participant_waiver_item($participant_id, $reg_id, 'amount_due', $amount_due);
            $waiver_id=$db->insert_id;
            // you need to update with the waiver text
            $w_res = $s_reg -> get_registration_document ( 1 ); 
            $w_arr = $w_res -> fetch_assoc();
            // debug // echo "p_name is $p_name <br>";
            $waiver = sprintf($w_arr['document_text'], $p_name);
            $waiver = $s_participant_reg -> db ->real_escape_string($waiver);
            $s_participant_reg -> update_participant_waiver_item($waiver_id, 'waiver', $waiver);
            // make sure the reg_status of the form is 3 (begun) since we have a new waiver to fill out
            $success = $s_login_reg -> reg_login_status_update ( $reg_login_id, $reg_status );
        }
    }
    $enc_id = bin2hex(base64_encode($login_id));
    echo '{"enc_id":"'.$enc_id.'"}'; // I will regret this
}

if(ca($action) == 'update_login') {
    $login_id = $_REQUEST['login_id'];
    $field_name = clean_string($_REQUEST['field_name']);
    $field_val  = clean_string($_REQUEST['field_val']);
    // get around a bug in my generalized update script
    $s_login = new S_login;
    $s_login -> db = $db;
    $update_test = $s_login -> update_login($field_name, $field_val, $login_id);
    if($update_test == true) {
        echo '{"status":"success"}';
    }
}

if(ca($action) == 'update_participant') {
    $s_participant = new S_participant;
    $s_participant -> db = $db;

    // action=update_participant&participant_id=4357&field_name=fname&field_val=Signy
    // the allowed fieldnames should be in the function or else generated as a function. For login they're in a function. Sorry for the discrepancy Future-Troy
    $allowed_fieldnames=array('fname', 'lname', 'dob');
    $field_name=clean_string($_REQUEST['field_name']);
    $field_val=clean_string($_REQUEST['field_val']);
    $participant_id=$_REQUEST['participant_id'];
    if(in_array($field_name, $allowed_fieldnames)) {
        $s_participant -> update_participant($field_name, $field_val, $participant_id);
    }
}

if(ca($action) == 'remove_login_participant') {
    // removes a participant/login relationship
    // and removes the participant from the DB if specified
    // if you do that it also removes the participant from any events
    // you know though ... it keeps their waivers. Because safety.
    // thanks for that last note past-troy
    // that said -- while the participant's name is in the waiver it isn't in
    // the survey.
    // I should delete *unfinished* waivers though.
    $login_id=$_REQUEST['login_id'];
    $participant_id=$_REQUEST['participant_id'];

    $s_participant = new S_participant;
    $s_participant -> db = $db;


    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db;

    $p_res = $s_participant_reg->all_participant_waiver($participant_id);

    $s_participant_reg->delete_waiver_by_participant($participant_id);
    $s_participant_reg->delete_participant_answers($participant_id);

    while($pa = $p_res->fetch_assoc()) {
        $waiver_id=$pa['id'];
        $ws_id = $pa['ws_id'];
        if($ws_id !=2) {
            // delete the waiver
            // $s_participant_reg->delete_waiver($waiver_id);
            // aaaand here is where I wish I had reg_id in p_reg_answer
            // if I want to clean out incomplete registrations I need
            // to follow the answer through the question to the section
            // to get the reg_id that matches the waiver I just deleted.
            // so screw it; we're just deleting all waivers and stuff
            // for a participant. Sorry past-troy it was a good idea.
        }
    }
    $s_participant->remove_login_participant($login_id, $participant_id);

    if($_REQUEST['perm']=='true') {
        echo "removing the participant permanently <br>";
        // permanently remove the participant
        $s_billing = new S_billing;
        $s_billing -> db = $db; // I swear to god troy

        $s_billing ->remove_all_event_participant_billing($participant_id);
        echo $s_participant->remove_participant($participant_id);
        echo $s_participant->remove_participant_from_all_events($participant_id);
    }
}

if(ca($action) == 'orphan_event_participant') {
    // we're deleting the participant from an event but keeping the billing info in place.
    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_event = new S_event;
    $s_event -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $event_participant_id=$_REQUEST['event_participant_id'];

    // get existing class info before making this orphan
    $class_info = $s_event -> class_by_ep_id($event_participant_id);
    $class_name = $class_info['name'];

    // get existing event name as well

    $ename_res = $s_event-> event_name_by_event_participant_id ($event_participant_id);
    $ename_arr = $ename_res -> fetch_assoc();
    $et_name = $ename_arr['et_name'];

    // orphan the event participant
    $result = $s_participant -> update_event_participant('event_id', '0', $event_participant_id);

    if($result !==false) {
        echo '{"status":"success"}';
        // update the billing metadata with the class id
        $append_result = $s_billing -> append_ep_billing_metadata ($event_participant_id, "\nOrphan/$class_name $et_name");
    } else {
        echo '{"status":"fail"}';
    }
}

if(ca($action) == 'remove_event_participant') {
    // remove a participant from an event and also remove the
    // related billing info
    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $event_participant_id=$_REQUEST['event_participant_id'];

    $s_participant ->remove_event_participant($event_participant_id);
    $s_billing ->remove_event_participant_billing($event_participant_id);

}

if(ca($action) == 'get_modal') {

    $s_class = new S_class(false);
    $s_class -> db = $db;

    $location_result = $s_class -> get_all_locations();
    $location_list = result_as_html_list(new html_Render(), $location_result, 'id', 'location', '');

    $leader_result = $s_class -> get_all_leaders();
    $leader_list = result_as_html_list(new html_Render(), $leader_result, 'id', 'leader', '');

    $s_event = new S_event;
    $s_event -> db = $db;

    $event_result = $s_event -> get_all_event_types();
    $event_list = result_as_html_list(new html_Render(), $event_result, 'id', 'event', '');

    $class_id      = $_REQUEST['class_id'];
    if($class_id*1 == 0) {
        // we don't have a class id because we are searching by date
        // so load up the class ... class with the default and set up 
        // a dropdown for the modal
        $s_class->class_id = $default_class_id; 
        // $class_id = $default_class_id; // don't ask
        $class_result = $result=$s_class->get_all_classes();
        $class_list = result_as_html_list(new html_Render(), $class_result, 'id', 'name', $default_class_id);
        $class_dropdown=true;
    } else {
        $class_dropdown=false;
    }
    $date_time     = $_REQUEST['date_time'];
    $private_start = $_REQUEST['private_start'];
    $private_end   = $_REQUEST['private_end'];
    $dt_arr=explode('|', $date_time);
    $event_day=$dt_arr[0];
    $event_day=date('l', $date_time);
    $event_time=substr($dt_arr[2], 2, 5); // not perfect but it'll have to do
    $event_time= date('g:i A', $date_time);
    // split up date_time to get the actual day & time.
    // default duration
    // ternary much?
    if($duration=='') {
        $duration=$default_duration;
    }
    if($number_participants == '') {
        $number_participants=$default_number_participants;
    }
    require('templates/myModal.php');
}

if(ca($action) == 'delete_event') {
    $s_event = new S_event;
    $s_event -> db = $db;
    $s_event -> event_id=$_REQUEST['event_id'];
    $s_event -> delete_event();
}

if(ca($action) == 'edit_event') {
    $s_event = new S_event;
    $s_event -> db = $db;
    $s_participant = new S_participant;
    $s_participant -> db = $db;
    $s_event -> event_id=$_REQUEST['event_id'];
    // debug // print_r($_REQUEST);
    $data['class_id']=$_REQUEST['class_id'];
    $data['location_id']=$_REQUEST['location_id'];
    $data['leader_id']=$_REQUEST['leader_id'];
    $data['et_id']=$_REQUEST['event_type']; // change event_type to et_id man
    $data['duration']=$_REQUEST['duration'];
    $data['number_participants']=$_REQUEST['number_participants'];
    $edt_meta = $_REQUEST['edt_meta'];
    $edt_id = $_REQUEST['edt_id'];
    $result = $s_event -> edit_event($data);
    $result = $s_event -> update_event_daytime_meta($edt_id, $edt_meta);
}

if(ca($action) == 'toggle_participant_status') {
    $s_event = new S_event;
    $s_event -> db = $db;

    // action=toggle_participant_status&participant_id=123&event_id=127&status_id=2
    $event_participant_id=$_REQUEST['event_participant_id'];
    $status_id=$_REQUEST['status_id'];

    $s_event->update_participant_status($event_participant_id, $status_id);

}

if(ca($action) == 'add_class') {
    // this should be add_events shouldn't it?
    // if you knew php better you would use s_class to set up event dates and
    // then populate s_event with those dates. Unfortunatley if you took the
    // time to learn that shit you'd never get this done

    // action=add_class&class_id=1&event_id=&date_time=2012-09-04_2_08%3A30%3A00&event_type=1&location=1&duration=20&occurance_rate=weekly&number_participants=2&leader=2

    // prepare the date and time
    // $da=explode('_', urldecode($_REQUEST['date_time']));
    // print_r($da);
    $data['timestamp']=$_REQUEST['date_time'];
    $data['date']=$da[0];
    $data['day_number']=$da[1];
    $data['event_time']=$da[2];
    $data['class_id']=$_REQUEST['class_id'];
    $data['location_id']=$_REQUEST['location_id'];
    $data['occurance_rate']=$_REQUEST['occurance_rate'];
    $data['number_participants']=$_REQUEST['number_participants'];
    $data['leader_id']=$_REQUEST['leader_id'];
    $data['duration']=$_REQUEST['duration'];
    $data['et_id']=$_REQUEST['event_type'];
    $s_class = new S_class($data['class_id']);
    $s_class -> db = $db;

    $result = $s_class->get_class($id);
    $class_row = $result->fetch_assoc();
    $data['start']=$class_row['start'];

    $result = $s_class -> add_class_events($data);
    // changed because now I want to return the event_id
    if($result!==false) {
        echo 'true';
    }
}

if(ca($action) == 'get_logins_json') {
    // get all (or one) logins (or login) returned in a JSON list 
    $s_login = new S_login;
    $s_login -> db = $db;

    $login_id = $_REQUEST['login_id'];
    $front_wrapper="";
    $end_wrapper="";

    if ( $login_id == '') {
        $front_wrapper="[";
        $login_res = $s_login -> get_all_logins('fname');
        $login_arr=result_as_array(new serialized_Render(), $login_res, 'id');
        $end_wrapper="\n]";
    } else {
        $login_res = $s_login -> get_login_from_id($login_id);
        $login_arr=result_as_array(new serialized_Render(), $login_res, 'id');
    }
    $json=json_encode($login_arr);
    /*
    echo $json;
    die();
     */
    $ret='';
//     $ret='[';
    foreach($login_arr as $subarr) {
        extract($subarr); //  it's like lice
        $enc_id = bin2hex(base64_encode($id));
        $ret.="$comma
    {
        \"value\": \"$fname $lname $email\",
        \"fname\": \"$fname\",
        \"lname\": \"$lname\",
        \"email\": \"$email\",
        \"login_id\": \"$id\",
        \"enc_id\": \"$enc_id\",
        \"log_level\": \"$log_level\"
    }";
    $comma=",";
    }
    $ret=$front_wrapper.$ret.$end_wrapper;
    echo $ret;
}

if(ca($action) == 'insert_login') {
    $fname     = $_REQUEST['login_fname'];
    $lname     = $_REQUEST['login_lname'];
    $email     = $_REQUEST['login_email'];
    $password  = $s_Login->encrypt_password($_REQUEST['login_password']);
    $log_level = (strlen($_REQUEST['log_level']) > 0) ? $_REQUEST['log_level'] : 1;
    // debug // print_r($_REQUEST);
    // $s_Login gets initialized at the beginning, which will bite me in the ass; can't wait

    // echo "here fname is $fname, lname is $lname, email is $email and password is $password <br>";
    $login_id = $s_Login -> insert_login ($fname, $lname, $email, $password, $log_level);

    // http://swiftscheduler.com/schedule/includes/rpc.php?action=insert_login&fname=Kate&lname=Vitullo&email=kate%40troyvit.com&login_id=&password=abc123
    echo '{"login_id":"'.$login_id.'"}';
}

if(ca($action) == 'get_login_levels') {
    // need to tie to this still
    $login_level_id=$_REQUEST['login_level_id'];
    $level_result = $s_Login -> get_all_login_levels('id');
    $location_list = result_as_html_list(new html_Render(), $level_result, 'id', 'login_level', $login_level_id);
    echo $location_list;
}

if(ca($action) == 'get_all_participants_json') {
    $s_participant = new S_participant;
    $s_participant -> db = $db;
    $ap_res=$s_participant -> get_all_participants('');
    $p_arr=result_as_array(new serialized_Render(), $ap_res, 'participant_id');
    $comma='';
    $ret='[';
    foreach($p_arr as $val) {
        extract($val);
        $participant_res = $s_participant -> get_participant($participant_id);
        $participant_arr=$participant_res -> fetch_assoc();
        $fname=trim($participant_arr['fname']);
        $lname=trim($participant_arr['lname']);
        $id=trim($participant_arr['id']);
        $ret.="$comma { \"value\": \"$fname $lname\", \"participant_id\": \"$id\" }";
        $comma=",";
    }
    $ret=$ret."\n".']';
    echo $ret;
}
if(ca($action) == 'get_participants_json') {
    // get all participants for a class returned in a JSON list 
    $s_participant = new S_participant;
    $s_participant -> db = $db;
    // debug // $participant_res = $s_participant -> get_all_participants('fname limit 10');
    /*
    $participant_res = $s_participant -> get_all_participants('fname limit 100');
    $participant_arr=result_as_array(new serialized_Render(), $participant_res, 'id');
     */
    // I'm too tired to write a new function right now
    if($_REQUEST['class_id'] == 'all' || $_REQUEST['class_id']=='') {
        $need_p_names=false;
        $p_res = $s_participant -> get_all_participants('fname');
    } else {
        $need_p_names=true;
        $class_id = (empty($_REQUEST['class_id'])) ? $default_class_id : $_REQUEST['class_id'];
        $p_res = $s_participant -> participants_in_class($class_id);
    }
    $p_arr = result_as_array(new serialized_Render(), $p_res, 'id');
    // print_r($p_arr);
    $comma='';
    $ret='[';
    foreach($p_arr as $val) {
        $id              = $val['id'];
        $event_id        = $val['event_id'];
        $participant_id  = $val['participant_id'];
        $status_id       = $val['status_id'];
        $ep_meta         = $val['ep_meta'];
        if($need_p_names == true) {
            $participant_res = $s_participant -> get_participant($participant_id);
            $participant_arr=$participant_res -> fetch_assoc();
            $fname=trim($participant_arr['fname']);
            $lname=trim($participant_arr['lname']);
            $id=trim($participant_arr['id']);
        } else {
            $fname=trim($val['fname']);
            $lname=trim($val['lname']);
            $id=trim($val['id']);
        }
        // get login id
        $l_res = $s_participant -> get_logins_by_participant ($participant_id);
        $l_arr = $l_res ->fetch_assoc();
        $login_id = $l_arr['id'];
        // echo '<pre>';
        // print_r($l_arr);
        $login_id = $l_arr['id'];
        $ret.="$comma { \"value\": \"$fname $lname\", \"participant_id\": \"$id\", \"login_id\": \"$login_id\" }";
        $comma=",";
    }
    $ret=$ret."\n".']';
    echo $ret;
}

if(ca($action) == 'get_event_by_participant') {
    $participant_id = $_REQUEST['participant_id'];
    $class_id       = $_REQUEST['class_id'];

    $s_event = new S_event;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    $s_event -> db = $db;

    if($class_id == '') {
        $s_event -> class_id = $default_class_id;
    } else {
        $s_event -> class_id = $class_id;
    }

    $stat_res=$s_participant->get_all_status();
    // I feel dirty
    // I run this same code in 3 places (soon to be 4)
    while($sa = $stat_res->fetch_assoc()) {
        extract($sa);
        $status_arr[$id]=$status;
    }

    $e_res = $s_event -> event_by_participant($participant_id);
    $event_arr=result_as_array(new serialized_Render(), $e_res, 'event_id');
    // debug // print_r($event_arr);
    if(is_array($event_arr)) {
        foreach($event_arr as $subarr) {
            extract($subarr);
            $event_res = $s_event -> better_get_event($event_id);
            $event_data = $event_res->fetch_assoc();
            extract($event_data);
            $event_leader = $fname;

            $ep_res = $s_participant -> participants_in_event($event_id);
            $ep_arr=result_as_array(new serialized_Render(), $ep_res, 'participant_id');
            if(is_array($ep_arr)) {
                foreach($ep_arr as $ek => $es) {
                    $active_participant[$ek]['event_participant_id'] = $es['event_participant_id'];
                    $active_participant[$ek]['fname']                = $es['fname'];
                    $active_participant[$ek]['event_id']             = $es['event_id'];
                    $active_participant[$ek]['lname']                = $es['lname'];
                    $active_participant[$ek]['status_id']            = $es['status_id'];
                    $active_participant[$ek]['participant_id']       = $es['participant_id'];
                    // echo $event_participant_id.'<br>';
                    $active_participant[$ek]['pay_status']           = $s_billing -> is_ep_paid ($es['event_participant_id']);
                    // $active_participants[]=$ep_subarr_key;
                }
            }
            if(is_array($active_participant[$ek])) {
                // extract is like the <pre> of arrays
                $ap=extract($active_participant[$ek]);
            }

            $i=0; // counter against the number of available events
            // why is this here and not inside event_card?
            require('templates/event_card.php');
        }
    } else {
        echo '<h3>'.$sl->gp('No class found').'</h3>';
    }
}

if(ca($action) == 'update_waiver_by_id') {
    // you have an update_participant_waiver but it depends more on participant_id htan particpant_waiver_id
    // and untangling that would actually not save you any coding, so sorry Future-Troy
    $participant_waiver_id = $_REQUEST['participant_waiver_id'];
    $field_name            = $_REQUEST['field_name'];
    $field_val             = $_REQUEST['field_val'];
    // all fields should be updateable because the first time the record is inserted it's as a placeholder that 
    // contains just participant_id and reg_id
    $allowed_fieldnames=array('amount_due', 'waiver_status', 'signature', 'waiver', 'signature_date');
    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db;

    if(in_array($field_name, $allowed_fieldnames)) {
        echo "updating! \n";
        $s_participant_reg -> update_participant_waiver_item($participant_waiver_id, $field_name, $field_val);
    }
}

if(ca($action) == 'update_participant_section_group') {
    $participant_id   = $_REQUEST['participant_id'];
    $section_group_id = $_REQUEST['section_group_id'];
    $reg_login_id     = $_REQUEST['reg_login_id'];
    $reg_id           = $_REQUEST['reg_id'];

    $s_section_group = new S_section_group;
    $s_section_group -> db = $db;

    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db;

    $participant_section_group_id = $s_section_group -> update_participant_section_group($participant_id, $section_group_id, $reg_login_id);

    echo "id is $participant_section_group_id <br>";

    $waiver_res = $s_participant_reg->waiver_by_participant($participant_id, $reg_id);
    $waiver_arr = $waiver_res -> fetch_assoc();
    $participant_waiver_id = $waiver_arr['id'];
    echo "id is $participant_waiver_id <br>";

    $field_name='participant_section_group_id';
    $field_val=$participant_section_group_id;

    $s_participant_reg -> update_participant_waiver_item($participant_waiver_id, $field_name, $field_val);
}

if(ca($action) == 'get_participants_by_login') {
    $s_reg = new S_reg;
    $s_reg -> db = $db; # god i need to fix this

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db;

    $s_section_group = new S_section_group;
    $s_section_group -> db = $db;

    $s_login_reg = new S_login_reg;
    $s_login_reg -> db = $db; # god i need to fix this

    $login_id=$_REQUEST['login_id'];
    $part_res = $s_participant -> get_participants_by_login($login_id);
    $part_arr=result_as_array(new serialized_Render(), $part_res, 'id');
    $num_participants = count($part_arr);

    if(is_array($part_arr)) {
        echo '<h3>'.$sl->gp('Students').'</h3>';
        $participant_num=0;
        foreach($part_arr as $participants) {
            $participant_num++;
            $edit_participant=true;
            extract($participants); // die past-troy die
            $participant_id = $id; // you too future-troy

            // logic for the template

            // find out if the waiver is incomplete
            // and if money is owed

            $w_res = $s_participant_reg -> all_participant_waiver($participant_id);
            $w_arr = $w_res -> fetch_assoc();
            $w_num = $w_res->num_rows;
            if($w_num == 0) {
                $reg_id=$default_reg_id;
                $amount_due = $registration['orig'];
                // hoboy
                $s_participant_reg -> insert_participant_waiver_item($participant_id, $reg_id, 'amount_due', $amount_due);
                $waiver_id=$db->insert_id;
                // you need to update with the waiver text
                $reg_document = $s_reg -> get_registration_document ( 1 ); 
                $rd__arr      = $w_res -> fetch_assoc();
                // debug // echo "p_name is $p_name <br>";
                $waiver = sprintf($w_arr['document_text'], $p_name);
                $waiver = $s_participant_reg -> db ->real_escape_string($waiver);
                $s_participant_reg -> update_participant_waiver_item($waiver_id, 'waiver', $waiver);
                // now try again
                $w_res = $s_participant_reg -> all_participant_waiver($participant_id);
                $w_arr = $w_res -> fetch_assoc();
            }
            // debug // echo '<pre>';print_r($w_arr);echo '</pre>';
            $amount_due                    = $w_arr['amount_due'];
            $waiver_status                 = $w_arr['waiver_status'];
            $ws_id                         = $w_arr['ws_id'];
            $reg_id                        = $w_arr['reg_id'];
            $participant_section_group_id  = $w_arr['participant_section_group_id']; // this field name got picked on a lot in school.
            // see if they are in a section_group yet
            $def_sel=''; // default select value for the form section group dropdown
            if($participant_section_group_id > 0) {
                $psg_res = $s_section_group -> participant_section_group_by_id($participant_section_group_id );
                if($psg_res -> num_rows != 0) {
                    $psg_arr = $psg_res ->fetch_assoc();
                    $section_group_id = $psg_arr['section_group_id'];
                    $participant_section_group_id = $psg_arr['id'];
                    $reg_login_id = $psg_arr['reg_login_id'];
                }
            } else {
                $section_group_id=0;
                $reg_login_id=false;
                $rl_res = $s_login_reg -> get_reg_login ($login_id, $reg_id);
                $s_login_arr = $rl_res -> fetch_assoc();
                $reg_login_id = $s_login_arr['id'];
                $def_sel=' selected="selected" ';
            }
            $participant_waiver_id = $w_arr['participant_waiver_id'];
            if($amount_due == '') {
                if($participant_num==1) {
                    $amount_due=$registration['orig'];
                } else {
                    $amount_due=$registration['sibling'];
                }
            }

            // get all forms into a dropdown menu
            $g_res = $s_section_group -> get_all_section_groups();
            $g_arr = result_as_array(new serialized_Render(), $g_res, 'id');
            $selected='';
            $sg_sel='';
            foreach($g_arr as $sg_id => $sg_arr) {
                $group_name = $sg_arr['group_name'];
                if($sg_id == $section_group_id) {
                    $selected=' selected="selected" ';
                    $w_status_show = ': '.$waiver_status;
                } else {
                    $selected='';
                    $w_status_show = '';
                }
                $sg_sel.='<option '.$selected.' value="'.$participant_id.':'.$reg_login_id.':'.$reg_id.':'.$sg_id.'">'.$group_name.$w_status_show.'</option>'; 
            }
            $sg_sel='<option '.$def_sel.' value="0">Select a registration form</option>'.$sg_sel;
            if($waiver_status=='') {
                $waiver_status = 'no registration';
            }
            if($ws_id != 2) { 
                // 2 means complete, and I'm sorry future-future-troy (and future-troy)
                $amount_due='<input class="input_money editable" type="text" name="waiver_by_id|amount_due|participant_waiver_id|'.$participant_waiver_id.'" id="participant_waiver_amount_'.$participant_waiver_id.'" value="'.$amount_due.'">';
            }
            $part_controls='';
            $participant_class='';
            $id_append='';
            if($edit_participant==true) {
                $id_append='_'.$participant_id;
                $part_controls='<button class="remove_participant" id="del_participant'.$id_append.'">'.$sl->gp('Delete Student').'</button>';
                $participant_class='participant_field';
                $dob_extra='participant_calendar';
            } 
            if($add_participant==true) {
                $part_controls='<button class="add_participant">'.$sl->gp('Add Student').'</button>';
                $id_append='_add';
                $participant_class='new_participant_field';
                $dob_extra='';
            }

            // end logic for the template
            require('templates/participant_edit.php');
        }
    }
    $edit_participant=false;
    $add_participant=true;
    ?><h3><?php echo $sl->gp('Add Student'); ?></h3>
<?php
    // empty form to add one ... weird
    $fname='';
    $lname='';
    $id='';
    $dob='';
    $part_controls='<button class="add_participant">'.$sl->gp('Add').'</button>';
    $id_append='_add';
    $participant_class='new_participant_field';
    $dob_extra='participant_calendar';
    require('templates/participant_edit.php');
}

if(ca($action) == 'dedupe_participant') {

    $fname=$_REQUEST['fname'];
    $lname=$_REQUEST['lname'];

    $s_Login = new s_Login;
    $s_Login -> db = $db;

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_section_group = new S_section_group;
    $s_section_group -> db = $db;

    $s_participant_reg = new S_participant_reg;
    $s_participant_reg -> db = $db;

    // get all participant ids

// not used yet

}

if(ca($action) == 'request_new_password') {
    $s_Login = new s_Login;
    $s_Login -> db = $db;

    $email=$_REQUEST['email'];
    if(strlen($email)==0) {
        // whoah mr email validator
        echo '<h3>'.$sl->gp('Error!').'</h3><p>'.$sl->gp('Email is blank').'</p>';
        // require('./templates/login_form.php');
        require('./templates/request_login.php');
        die();
    }
    if(strlen($_SESSION['login_hash']) > 0) {
        $id = $login_data['id'];
        // debug // echo '<pre>'; print_r($login_data);
        // debug // echo "<p>ah so you are logged in</p>";
    } else {
        // check to see if the email exists in the db
        // echo "checking against $email <br>";
        $login_res = $s_Login -> get_login_from_email($email);
        $login_data = $login_res ->fetch_assoc();
        // debug // print_r($login_data);
        if(is_array($login_data)) {
            extract($login_data);
        }
    }
    if($id*1> 0) {
        // create the new password
        $new_password = $s_Login -> new_password ($email, $id);
        // mail it to the user
        $pw_data['email'] = $email;
        $pw_data['fname']=$login_data['fname'];
        $pw_data['password']=$new_password;
        $s_Login -> send_password($pw_data);
        echo '<h3>'.$sl->gp('Success').'</h3><p>'.$sl->gp('Your new password was mailed to').' '.$email.'</p>';
    } else {
        echo '<h3>'.$sl->gp('Error!').'</h3><p>'.$email.$sl->gp('does not exist in our database').'</p>';
    }
    // require('./templates/login_form.php');
}
?>
