<?php
/* 

    [0] => 2013-02-18T21:01:41
    [1] => 2013-02-18T21:03:22
    [2] => Haan
    [3] => Jenny
    [4] => Jesse
    [5] => "jkspruill@yahoo.com"
    [6] => "933 Ninebark Ln"
    [7] => "Longmont"
    [8] => 80503
    [9] => 720-289-2894
    [10] => 
    [11] => "Marley 04-17-11"
    [12] => 
    [13] => 
    [14] => 
    [15] => 
    [16] => 
    [17] => 
    [18] => 
    [19] => 
    [20] => 
    [21] => 
    [22] => 
    [23] => 

 */

// this totally breaks if some ass is named larry namely and has an emaily of lnamely@gmail.com

require('config.php');
require('functions.php');

$file=$argv[1];

$s_participant = new S_participant;
$s_participant -> db = $db;

$s_login = new S_login;
$s_login -> db = $db;

$lines = file ($file);

$line_sep_arr = array (",", "\t");
// $line_sep_arr = array ("\t", ",");
$line_sep = '';

foreach($lines as $line) {
    /* create some fresh arrays */
    $participants = array();
    $existing_participants = array();
    $existing_login = false;
    // remove cruft
    $line = clean_line ($line);
    if($line == clean_line($lines[0])) {
        // skip the header but use it to verify the csv-ness and establish the order of records
        foreach($line_sep_arr as $ls) {
            $test_arr = explode($ls, $line);
            $c = count($test_arr);
            echo "c is $c for $ls \n";
            if($c > 2) {
                echo "count is bigger than 2 because ls is $ls \n";
                $line_sep = $ls;
                break;
            }
        }
        $position = test_first_line($line, $line_sep);
        print_r ($position);
        echo "\n and that is position on line 70";
        // this should actually be better
        // aaaand it's getting there
        continue;
    }
    if($line_sep == '') {
        echo "invalid file";
        die();
    }
    echo "line_sep is $line_sep \n";
    $arr = explode( $line_sep, $line );
    // print_r($arr);
    // print_r($position);
    // debug // 
    print_r($arr);
    $lnamepos = $position['lname'];
    echo "lname pos is $lnamepos \n";
    $fnamepos = $position['fname'];
    $emailpos = $position['email'];
    // so bad, but better than before
    $p1pos    = $position['child1'];
    $p2pos    = $position['child2'];
    $p3pos    = $position['child3'];
    $p4pos    = $position['child4'];
    $p5pos    = $position['child5'];
    // echo "\n\nlnamepos is $lnamepos\n";
    $lname=ucfirst(strtolower(clean_string($arr[$lnamepos])));
    $fname=ucfirst(strtolower(clean_string($arr[$fnamepos])));
    $email=strtolower(clean_string($arr[$emailpos])); // THANKYOU TINA GOLD
    echo "so here fname, part of arr[$fnamepos] is $fname \n";
    echo "first: $fname | last: $lname  email: $email \n";
    echo "lname is $lname, fname is $fname, and email is $email \n";
    $login_id = $s_login -> test_for_login($fname, $lname, $email);
    echo "id is $login_id \n";
    if( $login_id == false) {
        echo "no login found for $fname $lname, $email ($login_id), creating new login \n";
        $login_id = $s_login -> insert_login($fname, $lname, $email, 1);
    } else {
        $existing_login = true;
        $ep_res = $s_participant -> get_participants_by_login ( $login_id );
        while( $ep_arr = $ep_res -> fetch_assoc()) {
            extract($ep_arr);
            $existing_participants[$id] = array('fname' => $fname, 'lname' => $lname);
        }
    }
    // make sure *that* worked
    if($login_id == false ) {
        echo "error trying to insert $fname $lname ($email) so we bailed \n\n\n";
        die();
    } else {
        echo "we are checking ".$arr[$p1pos]." ... is there anybody there?\n";
        $participants[]=clean_string($arr[$p1pos]);
        $participants[]=clean_string($arr[$p2pos]);
        $participants[]=clean_string($arr[$p3pos]);
        $participants[]=clean_string($arr[$p4pos]);
        $participants[]=clean_string($arr[$p5pos]);
        foreach($participants as $participant) {
            if(strlen($participant) > 0) {
                $participant_fname = clean_participant($participant);
                $participant_lname = $lname;
                if($existing_login == true) {
                    // the login already exists, so make sure the participant name doesn't
                    // debug // echo "running compare on $fname $lname \n";
                    if ( compare_participant ( $existing_participants, $participant_fname, $participant_lname ) == false ) {
                        echo 'inserting '.$fname.' '.$lname."\n\n";
                        $participant_id = $s_participant -> insert_participant ($participant_fname, $participant_lname);
                        $pl_id = $s_participant -> insert_login_participant($login_id, $participant_id);
                    } else {
                        echo 'we got a true when we compared participant for '.$fname.' '.$lanme.' and so we did not insert'."\n";
                    }
                } else {
                    // this is a new login, so let's insert all the login's participants
                    $participant_id = $s_participant -> insert_participant ($participant_fname, $participant_lname);
                    $pl_id = $s_participant -> insert_login_participant($login_id, $participant_id);
                }
            }
        }
    }
    unset($arr);
}

// 
// how does this change with the login_id? I don't see how it can. this thing sucks because
// it keeps you from automatically adding 2 parts with the same name to the same login. 
// that's an edge case for sure. point here though is that if you also diff'd by login_id
// it wouldn't make a difference. Actually I don't know why the login_id is even in this function.
// the name you're pulling from the text file won't have it will it? well yeah it would, but more to
// the point the names it's comparing against are already limited by login_id. I'm removing it.

function compare_participant($existing_participants, $fname, $lname) {
    // compare fname, lname and login_id to the array of existing participants that belong to this login_id
    print_r($existing_participants);
    foreach($existing_participants as $id => $ep_array) {
        $matches = 0;
        foreach ($ep_array as $var => $val) {
            echo 'val is '.$val.' and $$var ('.$var.') is '.$$var."\n";
            if($val == $$var) {
                $matches++;
            }
            // echo "var is $var and $val is $val and ...\n".$$var." is ".$$val."\n";
        }
        echo "\n\n\n";
        echo "for this one we got $matches \n";
        echo "and we need ";
        echo count($ep_array);
        echo "\n";
        echo "we found $matches, and so ... ";
        if($matches == count($ep_array)) {
            echo "Match!";
            echo "\n\n\n";
            return true;
        } 
    }
    return false;
    echo "\n\n\n";
}
function clean_participant ($part) {
    echo "we are cleaning $part \n";
    $parr = explode(" ", $part);
    $join = '';
    $keep = '';
    foreach($parr as $pname) {
        $match = preg_match('/\d+/', $pname, $matches);
        if($match != true) {
            $pname = clean_string($pname);
            $keep .= $join.$pname;
            $join=' ';
        }
    }
    echo "keep is $keep \n";
    return $keep;
}

function clean_line ($line) {
    $line = str_replace('"', '', $line);
    return $line;
}

function build_test_body() {
    $test_body=array();
    $test_body['fname'][]  = "first";
    $test_body['fname'][]  = "fname";
    $test_body['lname'][]  = "last";
    $test_body['lname'][]  = "lname";
    $test_body['email'][]  = "email";
    $test_body['email'][]  = "e-mail";
    $test_body['child1'][] = "child1";
    $test_body['child2'][] = "child2";
    $test_body['child3'][] = "child3";
    $test_body['child4'][] = "child4";
    return $test_body;
}

function test_first_line ( $line, $line_sep ) {
    // take the thing and test it.
    // just kidding. Take this item and compare it to a list of strings that help describe it. 
    // For instance if the line has "First Name" in it and it matches a test param called "first" then
    // bam you have first name
    $tb = build_test_body();
    $arr = explode( $line_sep, $line );
    // print_r($arr);
    foreach($arr as $var => $val) {
        foreach( $tb as $test_name => $test ) {
            foreach($test as $tval) {
                // echo "running strpos looking for $tval in $val \n";
                if(stripos($val, $tval) !== false) {
                    echo "found $tval in $val \n";
                    // one of our test body strings matches a column in the text file
                    $position[$test_name]=$var;
                    // echo "so postiong $var is $test_name now \n";
                    if( count($position) == count($tb) ) {
                        // we have filled all our test positions
                        // echo "returning $position \n";
                        return $position;
                    }
                    continue;
                }
            }
        }
    }
    return $position;
}

?>
