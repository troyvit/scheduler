<?php
// I hope you never used this because it inserts the login_id where it should insert the reg_login_id.
require('config.php');
require('functions.php');

$s_login_reg = new S_login_reg;
$s_login_reg -> db = $db; # god i need to fix this

$s_reg = new S_reg;
$s_reg -> db = $db; # god i need to fix this

$s_section_group = new S_section_group;
$s_section_group -> db = $db;

$s_participant_reg = new S_participant_reg;
$s_participant_reg -> db = $db; # god i need to fix this

$s_participant = new S_participant;
$s_participant -> db = $db;

$section_group_id=1;

$regres = $s_login-> get_all_reg_logins();
while($regarr = $regres->fetch_assoc()) {
    extract($regarr); // I forgive you past-troy (this is past-troy talking though)
    /*
    | id                       | int(11)             | NO   | PRI | NULL    | auto_increment |
    | login_id                 | int(10) unsigned    | NO   |     | NULL    |                |
    | reg_id                   | int(10) unsigned    | NO   |     | NULL    |                |
    | registration_signature   | text                | YES  |     | NULL    |                |
    | registration_signed_name | text                | YES  |     | NULL    |                |
    | registration_sig_date    | datetime            | YES  |     | NULL    |                |
    | registration_sig_hash    | text                | NO   |     | NULL    |                |
    | agreement_text           | text                | YES  |     | NULL    |                |
    | agreement_signature      | text                | YES  |     | NULL    |                |
    | agreement_signed_name    | text                | YES  |     | NULL    |                |
    | agreement_sig_date       | datetime            | YES  |     | NULL    |                |
    | agreement_sig_hash       | text                | NO   |     | NULL    |                |
    | reg_status               | tinyint(3) unsigned | NO   |     | 0       |                |
    +--------------------------+---------------------+------+-----+---------+----------------+
    */
    // get all the participants for the login;
    $part_res = $s_participant -> get_participants_by_login($login_id);
    $part_arr=result_as_array(new serialized_Render(), $part_res, 'id');
    foreach($part_arr as $participant_id => $participants) {
        // insert participant_id, login_id into participant_section_group
        // use reg_login_id instead of login_id ?? $success = $s_section_group = insert_participant_section_group($participant_id, $login_id, $section_group_id) 
    }
    // ok you have all the crap you need to insert default participant_sections now.
}



?>
