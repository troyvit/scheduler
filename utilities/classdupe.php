<?php
// DON'T FORGET TO DO FOR BOTH POOLS!!!
// DON'T FORGET TO DO FOR BOTH POOLS!!!
// DON'T FORGET TO DO FOR BOTH POOLS!!!
// DON'T FORGET TO DO FOR BOTH POOLS!!!
//
//
// THIS ASSUMES YOU WANT A DEPOSIT OF 25 ADDED AS WELL
date_default_timezone_set('America/Los_Angeles'); // REALLY?!?!

require('../includes/config.php');
require('../includes/functions.php');

$price['group']=390;
$amount_due = $price['group']; // you so funny troy

$cf_id=20; // copy-from id
$ct_id=21; // copy-to id

/* you need to set up an array of days that represent the 1st 5 days of the new schedule's week.
 * $day['Monday']=2013-06-13 (or more appropriately probably the timestamp);
 * look on line 49 of rpc.php.
 *
 */

// $this_month=date('n', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
/* test
$sd['Monday']    = date('Y-m-d', mktime (0,0,0, 6, 3, 2013));
$sd['Tuesday']   = date('Y-m-d', mktime (0,0,0, 6, 4, 2013));
$sd['Wednesday'] = date('Y-m-d', mktime (0,0,0, 6, 5, 2013));
$sd['Thursday']  = date('Y-m-d', mktime (0,0,0, 6, 6, 2013));
$sd['Friday']    = date('Y-m-d', mktime (0,0,0, 6, 7, 2013));
$sd['Saturday']  = date('Y-m-d', mktime (0,0,0, 6, 8, 2013));
$sd['Sunday']    = date('Y-m-d', mktime (0,0,0, 6, 9, 2013));
 */

/* fall 2014 */
// this is so insane
// what I would have to do is, grab the first day, find the first monday before it, then count up the rest of the week.
// I can't believe I still have to do this
// | 10 | Summer 2015 | 2015-06-01 | 2015-08-14 |
//
$sd['Monday']    = date('U-n-j-Y', mktime (0,0,0, 12, 17, 2018));
$sd['Tuesday']   = date('U-n-j-Y', mktime (0,0,0, 12, 18, 2018));
$sd['Wednesday'] = date('U-n-j-Y', mktime (0,0,0, 12, 19, 2018));
$sd['Thursday']  = date('U-n-j-Y', mktime (0,0,0, 12, 20, 2018));
$sd['Friday']    = date('U-n-j-Y', mktime (0,0,0, 12, 21, 2018));
$sd['Saturday']  = date('U-n-j-Y', mktime (0,0,0, 12, 22, 2018));
$sd['Sunday']    = date('U-n-j-Y', mktime (0,0,0, 12, 23, 2018));

/* ok since it bugs me every time. All I really need is the start date.
 * then while($i < 7) { get the next day's date in U-m-j-Y format an the next day's date in l format (that's a lowercase L).
 * then I just build the $sd array that way 
 * I marked below where you'd stick it. Need to look php dates back up again. */

$numdays = 7;

$i = 0;

// print_r($sd);

$id=$ct_id; // new class id
$occurance_rate = 'weekly';

    $s_class = new S_class($id);
    $s_class -> db = $db;
    $s_class -> class_id = $cf_id; // old class id 

    $result = $s_class->get_class();
    $class_row = $result->fetch_assoc();
    $start = $class_row['start'];

    // stick the new way to find dates here

    $class_name = $class_row['name'];

    $s_event = new S_event;
    $s_event -> db = $db;
    $s_event -> class_id = $cf_id; // old class id

    $s_participant = new S_participant;
    $s_participant -> db = $db;

    $s_billing = new S_billing;
    $s_billing -> db = $db;

    // 1 represents the round pool
    // 2 represents the lap pool
    // get all round pool events
    // $e_res = $s_event -> event_by_location(1);

    // get all lap pool events
    $e_res = $s_event -> event_by_location(2); 

    // billing stuff
    $event_line_item_id=1; // this pretty much shows where I'm at with that


    while($event_arr = $e_res -> fetch_assoc()) {
        $event_id=$event_arr['id'];
        $ep_res = $s_participant -> participants_in_event($event_id);
        $ep_arr=result_as_array(new serialized_Render(), $ep_res, 'participant_id');
        if(is_array($ep_arr)) {
            foreach($ep_arr as $ek => $es) {
                $p_ids[]=$es['participant_id'];
            }
        }

        // debug // echo "so id is $event_id \n";
        $full_event_obj = $s_event -> better_get_event($event_id);
        $fe_arr = $full_event_obj ->fetch_assoc();
        // debug // print_r($fe_arr);
        extract($fe_arr); // STOP IT TROY
        // gather the data you need to use add_class_events
        $day = $fe_arr['event_day'];
        $daytime = $fe_arr['daytime'];
        $et_name = $fe_arr['et_name'];
        $dt_arr = explode(' ', $daytime);
        $dt_time = explode(':', $dt_arr[1]);

        print_r($dt_time);
        $hour = $dt_time['0'];
        $minute =$dt_time['1'] ;
        $second = '00';
        if($sd[$day]=='') {
            echo "blank, moving on \n";
            // this is an empty event
            // echo "event_day is $event_day and it came from event id $event_id\n";
            // echo "day is $day and timestamp is ";
            // print_r($sd);
            continue;
        }
        // 2013-01-02 17:45:00 
        $sd_arr = explode('-', $sd[$day]);
        $month = $sd_arr[1];
        $ts_day = $sd_arr[2];; // do I need this?
        $year = $sd_arr[3];
        $timetamp=$sd_arr[0]; // not really needed anymore
        // you have the timestamp but you don't have the exact time buddy
        $data['timestamp'] = $sd[$day];
        echo "hour: $hour, minute: $minute, mont: $month, day: $ts_day, year: $year for the copy of $event_id\n";
        $data['timestamp'] = date('U', mktime ($hour, $minute, 00, $month, $ts_day, $year));
        echo date('Y-m-d H:i:00', $data['timestamp']);
        echo "\n";
        $data['day'] = $day;
        $data['class_id']=$ct_id; // new class id
        $data['location_id']=$location_id;
        $data['occurance_rate']=$occurance_rate;
        $data['number_participants']=$number_participants;
        $data['leader_id']=$leader_id;
        $data['duration']=$duration;
        $data['et_id']=$et_id;
        $data['start'] = $start;
        $s_class -> class_id = $ct_id; // new class id 
        echo "we are about to add_class_events \n";
        $new_event_id = $s_class -> add_class_events($data);
        echo "there, we added sclass events and as a result we have $new_event_id \n";
        foreach($p_ids as $participant_id) {
            echo "not really adding $participant_id into $new_event_id \n";
            // make sure they are unconfirmed
            $ep_id = $s_participant -> insert_event_participant($new_event_id, $participant_id);
            // billing time
            $event_line_item = $class_name.', '.$et_name;
            $tf=$s_billing -> insert_event_billing ($event_line_item_id, $event_line_item, $participant_id, $ep_id, $amount_due, 0, 'Added using the class copy tool on '.date('Y-m-d'));
            echo "that was insert event particpant \n";
            echo "now let's do billing \n";


        }
        $p_ids = array();
        $s_class -> class_id = $cf_id; // old class id
        // I am so wrong
        // debug // print_r($data);
    }
    die();
?>
