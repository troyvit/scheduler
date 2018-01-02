<?php
require('./config.php');
require('./functions.php');

$s_participant = new S_participant;
$s_participant -> db = $db;

// $file=file('/home/vit/find_all_birthdays/all.csv');
$file=file('/home/vit/bento.csv');
$pdpat='/[A-Za-z ]+\s[0-9-\/]+/';
$f = 0;
$ups = 0;
$upsc = 0;
foreach($file as $line) {
    // echo $line;
    $larr=explode(",", $line);
    $lname=$larr[2];
    // echo "\n";
    foreach($larr as $hoboy) {
        $match = preg_match($pdpat, $hoboy, $matches);
        if(count($matches) > 0) {
            $found_arr=explode(' ', $matches[0]);
            $fname[$f]  = $found_arr[0];
            $dob[$f]    = $found_arr[1];
            $plname[$f]  = $lname;
            // debug // echo "lname[$f] is $lname \n";
            $f++;
        } else {
            // echo 'failed on '.$hoboy."\n";
        }
    }
}
foreach($fname as $var => $val) {
    // echo $fname[$var]; echo ' '; echo $plname[$var]; echo ' '; echo $dob[$var]; echo "\n";
    $fn=$fname[$var];
    $ln=$plname[$var];
    $db=$dob[$var];
    $es=test_date($db);
    if($es=='-' || $es=='/') {
        // prep date here
    } elseif('almost' == $es) {
        $db=$db.'-01-01';
        $es='-';

    } else {
        $bfname[$var] = $fn;
        $blname[$var] = $ln;
        $bdob[$var]   = $db;
        continue;
    }
    $res = $s_participant -> check_participant($fn, $ln, $db);
    $num = $res -> num_rows;
    if($num > 1) {
        // echo "we are hosed!!! because $fn $ln is $num!\n";
    } elseif($num < 1) {
        // echo "we are hosed!!! because $fn $ln is $num!\n";
    } else {
        $p_data = $res ->fetch_assoc();
        $participant_id = $p_data['id'];
        $ups++;
        $db=str_replace('/', '-', $db);
        // now we need to get the date in the right damned order
        $date_arr=explode('-', $db);
        if(count($date_arr) == 3 ) {
            // we have a full date at least
            if(strlen($date_arr[0])==4) {
                // this is set up yyyy-MM-d
                $ready_date[$participant_id]=$db;
                $upsc++;
            } elseif(strlen($date_arr[0])<=2) {
                // ok it's a shortened date
                $ready_date[$participant_id]='20'.$date_arr[2].'-'.$date_arr[0].'-'.$date_arr[1];
                $upsc++;
               //  echo "changed $db to $ready_date[$participant_id] \n";
            } else {
                echo strlen($date_arr[0])." which is something bad here on ".__LINE__." for $db \n";
                $upsc++; // not right
            }
        } elseif(count($date_arr) == 2) {
            // this date is propbably month/year
            if(strlen($date_arr[1])==4) {
                $ready_date[$participant_id]=$date_arr[1].'-'.$date_arr[0].'-01';
                $upsc++;
                echo "changed THIS $db to $ready_date[$participant_id] \n";
            } else {
                $ready_date[$participant_id]='20'.$date_arr[1].'-'.$date_arr[0].'-01';
                $upsc++;
                // echo "date_arr[1] is ".$date_arr[1]."for $db \n";
                // echo "changed $db to $ready_date[$participant_id] \n";
            }
        } else {
                $upsc++; // not right
            echo "nothing for $db \n";
        }
    }
}

$id='';
$dob='';

foreach($ready_date as $id => $dob) {
    $s_participant->update_participant('dob', $dob, $id);
}
echo "I want to change $ups but I'm only changing $upsc \n";

/*
foreach($bfname as $var => $val) {
    echo "$bfname[$var] $blname[$var] : $bdob[$var]";
    echo "\n";
}
 */

function test_date($date) {
    if(strpos($date, '-') !==false) {
        return '-';
    }
    if(strpos($date, '/') !==false) {
        return '/';
    }
    $tf = preg_match('/\d\d\d\d/', $date, $matches);
    if(count($matches) > 0) {
        return 'almost';
    }
    return false;
}

// preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE, 3);
