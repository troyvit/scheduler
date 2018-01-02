<?php
require('../includes/config.php');
require('../includes/functions.php');

$class_id=6;

$s_Login = new s_Login;
$s_Login -> db = $db;

$s_participant = new S_participant;
$s_participant -> db = $db;

$s_billing = new S_billing;
$s_billing -> db = $db;

$s_event = new S_event;
$s_event -> db = $db;

$res = $s_billing -> get_all_event_billing();
$b = result_as_array(new serialized_Render(), $res, 'id');
foreach($b as $var => $val) {
    $participant_id = $val['participant_id'];
    $ep_id = $val['event_participant_id'];
    $ep_res = $s_event -> get_event_participant($ep_id);
    $ep_data = $ep_res->fetch_assoc();
    // print_r($ep_data);
    $event_id=$ep_data['event_id'];
    if($event_id *1 < 1) {
        $politely_named_ids_that_have_a_problem_ids[]=$ep_id;
    } else {
        $event_res = $s_event -> better_get_event($event_id);
        $event_data = $event_res->fetch_assoc();
        $event_line_item = $event_data['et_name'];
        $id = $val['id'];
        $up = 'UPDATE event_participant_billing SET event_line_item="'.$event_line_item.'" WHERE id='.$id.';';
        // echo $up."\n";
        $field_name='event_line_item';
        $field_val=$event_line_item;
        $pb_id=$id;
        $s_billing -> update_event_participant_billing($field_name, $field_val, $pb_id);
    }
}
