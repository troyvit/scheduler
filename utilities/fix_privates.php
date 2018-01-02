<?php
require('../includes/config.php');
require('../includes/functions.php');

class fuphp {
    var $db;

    function get_first_last($private_event_id) {
        $query='SELECT min(daytime) as start, max(daytime) as end FROM private_event_daytime WHERE private_event_id='.$private_event_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "error!"; die();
        }
    }

    function update_pe($id, $start, $end) {
        $query = 'UPDATE private_event SET start="'.$start.'", end="'.$end.'" WHERE id='.$id;
        echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "error!"; die();
        }
    }

    function get_private_events () {
        $query='SELECT * FROM private_event';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "error!"; die();
        }
    }
}

$fuphp = new fuphp;
$fuphp -> db = $db;

// get the ids
$res = $fuphp -> get_private_events();
while($arr = $res -> fetch_assoc()) {
    $id = $arr['id'];
    $fl = $fuphp -> get_first_last($id);
    $farr = $fl -> fetch_assoc();
    $start = $farr['start'];
    $end = $farr['end'];
    if($start != '' && $end !='') {
        echo "\n";
        $fuphp -> update_pe ($id, $start, $end);
    }
}

