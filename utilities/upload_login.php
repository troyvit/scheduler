<?php
/*
Array
(
    [fname] => 0
    [lname] => 1
    [address_1] => 2
    [city] => 3
    [state] => 4
    [zip] => 5
    [h_phone] => 6
    [c_phone] => 7
    [email] => 8
    [child1] => 9
    [c1_dob] => 10
    [child2] => 11
    [c2_dob] => 12
    [child3] => 13
    [c3_dob] => 14
)
*/

// this totally breaks if some ass is named larry namely and has an emaily of lnamely@gmail.com

require('../includes/config.php');
require('../includes/functions.php');

$file=$argv[1];

$s_participant = new S_participant;
$s_participant -> db = $db;

$s_login = new S_login;
$s_login -> db = $db;

$s_login_reg = new S_login_reg;
$s_login_reg -> db = $db;

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
    echo "line is $line \n"; 
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
    $p1pos         = $position['child1'];
    $p2pos         = $position['child2'];
    $p3pos         = $position['child3'];
    $p4pos         = $position['child4'];
    $p5pos         = $position['child5'];
    $dob1pos       = $position['c1_dob'];
    $dob2pos       = $position['c2_dob'];
    $dob3pos       = $position['c3_dob'];
    $dob4pos       = $position['c4_dob'];
    $address_1pos  = $position['address_1'];
    $citypos       = $position['city'];
    $statepos      = $position['state'];
    $zippos        = $position['zip'];
    $h_phonepos    = $position['h_phone'];
    $c_phonepos    = $position['c_phone'];
    // echo "\n\nlnamepos is $lnamepos\n";
    $lname=ucfirst(strtolower(clean_string($arr[$lnamepos])));
    $fname=ucfirst(strtolower(clean_string(clean_fname($arr[$fnamepos]))));
    $reject_fname=reject_fname($fname);
    if($reject_fname==true) {
        continue;
    }
    $email=strtolower(clean_string($arr[$emailpos])); // THANKYOU TINA GOLD
    $old_email=$email;
    $email = clean_email($email);
    if($email=='' and $old_email !='') {
        echo "we killed $old_email\n";
        die();
    }
    echo "so here fname, part of arr[$fnamepos] is $fname \n";
    echo "first: $fname | last: $lname  email: $email \n";
    echo "lname is $lname, fname is $fname, and email is $email \n";
    $login_id = $s_login -> test_for_login($fname, $lname, $email);
    echo "id is $login_id \n";
    if( $login_id == false) {
        echo "no login found for $fname $lname, $email ($login_id), creating new login \n";
        $login_id = $s_login -> insert_login($fname, $lname, $email, 1);
        // NOW YOU NEED TO WRITE A REGISTRATION CLASS AND CREATE THE ABILITY TO INSERT REG INFO IN THERE.
        // build the array you need for login_address.
        $d['login_id']=$login_id;
        $d['address_type_id']=1; // hard coded to home address
        $d['is_primary']=1; // hard coded to primary
        $d['fname']=$fname;
        $d['lname']=$lname;
        $d['address_1']=$arr[$address_1pos];
        $d['city']=$arr[$citypos];
        $d['state']=$arr[$statepos];
        $d['zip']=$arr[$zippos];
        $d['h_phone']=$arr[$h_phonepos];
        $d['c_phone']=$arr[$c_phonepos];
        $d['b_phone']=$arr[$b_phonepos];
        $d['email']=$arr[$emailpos];
        $d['country']='USA'; // Sorry Future-Troy
        $s_login_reg->insert_login_address($d);
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
        // because the world is bad and I am part of the world
        $participants[1]=clean_string($arr[$p1pos]);
        $participants[2]=clean_string($arr[$p2pos]);
        $participants[3]=clean_string($arr[$p3pos]);
        $participants[4]=clean_string($arr[$p4pos]);
        $participants[5]=clean_string($arr[$p5pos]);
        echo "arr[5] is ".$arr[5].", which is different from $dob1pos, which is ".$arr[$dob1pos]."\n";
        $p_dob[1]=clean_date($arr[$dob1pos]);
        $p_dob[2]=clean_date($arr[$dob2pos]);
        $p_dob[3]=clean_date($arr[$dob3pos]);
        $p_dob[4]=clean_date($arr[$dob4pos]);
        $p_dob[5]=clean_date($arr[$dob5pos]); // even though there is no 5
        if($lname=='Shadix') {
            echo "ran clean_date against ".$p_dob[1]."\n";
            echo "dob pos is $dob1pos\n";
            echo "p2pos is $p2pos\n";
            echo "p3pos is $p3pos\n";
            echo "p4pos is $p4pos\n";
            echo "p5pos is $p5pos\n";
            // debug // print_r($participants);
            echo "I bet that's blank, but what about this: \n\n";
            print_r($arr);
            print_r($p_dob);
            // debug //die();
        }
        foreach($participants as $pkey => $participant) {
            $dob=$p_dob[$pkey];
            if(strlen($participant) > 0) {
                $participant_fname = clean_participant($participant);
                $participant_lname = $lname;
                if($existing_login == true) {
                    // the login already exists, so make sure the participant name doesn't
                    // debug // echo "running compare on $fname $lname \n";
                    if ( compare_participant ( $existing_participants, $participant_fname, $participant_lname ) == false ) {
                        echo 'inserting '.$fname.' '.$lname."\n\n";
                        $participant_id = $s_participant -> new_insert_participant ($participant_fname, $participant_lname, $dob);
                        $pl_id = $s_participant -> insert_login_participant($login_id, $participant_id);
                    } else {
                        echo 'we got a true when we compared participant for '.$fname.' '.$lanme.' and so we did not insert'."\n";
                    }
                } else {
                    if($dob == false) {
                        $dob='';
                    }
                    // this is a new login, so let's insert all the login's participants
                    $participant_id = $s_participant -> new_insert_participant ($participant_fname, $participant_lname, $dob);
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

function reject_fname($fname) {
    if(strpos($fname, '_x0007_') !== false) {
        return true;
    }
    return false;
}

function clean_fname ($fname) {
    if(strpos($fname, ' and') !== false) {
        $fname=strstr($fname, ' and', true);
    }

    if(strpos($fname, ' &') !== false) {
        $fname=strstr($fname, ' &', true);
    }
    return $fname;
}

/* custom for swim kids georgia */
function clean_email ($email) {
    if(strpos($email, '(') !== false) {
        $pattern = '/.*?\((.*?)\)/';
        $replacement='${1}';
        $email = preg_replace($pattern, $replacement, $email);
    }
    return $email;
}

function clean_line ($line) {
    $line = str_replace('"', '', $line);
    return $line;
}

function clean_date ($date) {
    echo "we are cleaning $date \n";
    // special for nadyne
    $date = trim($date);
    if(strlen($date) > 0) {
        $date_arr=explode('.', $date);
        print_r($date_arr);
        $count_date = count($date_arr);
        echo "count of date is $count_date\n";
        if ($count_date < 3) {
            echo "too short $count_date\n";
            return false;
        }
        $m=trim($date_arr[0]);
        $d=trim($date_arr[1]);
        $y=trim($date_arr[2]);
        $date = "$y-$m-$d";
        $date = new DateTime($date);
        echo "we are returning ";
        return $date->format('Y-m-d');
    } else {
        echo "no date!\n";
        return false;
    }
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
    $test_body['c1_dob'][] = "c1 dob";
    $test_body['c2_dob'][] = "c2 dob";
    $test_body['c3_dob'][] = "c3 dob";
    $test_body['c4_dob'][] = "c4 dob";
    $test_body['address_1'][] = "address_1";
    $test_body['city'][] = "city";
    $test_body['state'][] = "state";
    $test_body['zip'][] = "zip";
    $test_body['h_phone'][] = "h_phone";
    $test_body['c_phone'][] = "c_phone";
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
